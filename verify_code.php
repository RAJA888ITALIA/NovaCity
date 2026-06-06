<?php
session_start();
require_once 'private/config.php';
include 'includes/db.php';

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$PageTitle = "Verifica il codice";
$error = "";
$success = "";

// Include header
include 'includes/header.php';

// Check session
if (!isset($_SESSION['reg_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['reg_email'];

// Initialize attempts
if (!isset($_SESSION['verify_attempts'])) {
    $_SESSION['verify_attempts'] = 0;
}

$maxAttempts = 5;

// Fetch user
$stmt = $conn->prepare("
    SELECT id, name, verification_code, verification_expires, last_verification_sent, email_verified
    FROM users
    WHERE username=?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $error = "Utente non trovato.";
} elseif ($user['email_verified'] == 1) {
    unset($_SESSION['reg_email'], $_SESSION['verify_attempts']);
    header("Location: login.php");
    exit();
}

// Cooldown setup
$now = time();
$cooldown = 120;
$remaining = 0;

if ($user['last_verification_sent']) {
    $last_sent = strtotime($user['last_verification_sent']);
    $remaining = max(0, $cooldown - ($now - $last_sent));
}

// Handle code verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {

    if ($_SESSION['verify_attempts'] >= $maxAttempts) {
        $error = "Hai superato il numero massimo di tentativi. Devi reinviare il codice.";
    } else {
        $code = trim($_POST['code']);

        if ($user['verification_code'] != $code) {
            $_SESSION['verify_attempts']++;
            $left = $maxAttempts - $_SESSION['verify_attempts'];
            $error = "Codice errato. Tentativi rimasti: $left";
        } elseif (strtotime($user['verification_expires']) < $now) {
            $error = "Codice scaduto. Reinvia per riceverne uno nuovo.";
        } else {
            $update = $conn->prepare("
                UPDATE users
                SET email_verified=1, verification_code=NULL
                WHERE id=?
            ");
            $update->bind_param("i", $user['id']);
            $update->execute();
            $update->close();

            unset($_SESSION['reg_email'], $_SESSION['verify_attempts']);
            $success = "Email verificata! Ora puoi accedere.";
        }
    }
}

// Handle resend
if (isset($_GET['resend'])) {

    if ($remaining > 0) {
        $error = "Attendi $remaining secondi prima di reinviare.";
    } else {

        // Reset attempts on resend
        $_SESSION['verify_attempts'] = 0;

        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $conn->prepare("
            UPDATE users
            SET verification_code=?, verification_expires=?, last_verification_sent=NOW()
            WHERE id=?
        ");
        $stmt->bind_param("ssi", $code, $expires, $user['id']);
        $stmt->execute();
        $stmt->close();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = EMAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EMAIL_USERNAME;
            $mail->Password = EMAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(EMAIL_VERIFY, 'NovaCity');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Nuovo codice di verifica NovaCity';
            $mail->Body = "
                <p>Ciao <b>" . htmlspecialchars($user['name']) . "</b>,</p>
                <p>Il tuo nuovo codice di verifica è: <b>$code</b></p>
                <p>Valido 10 minuti.</p>
            ";

            $mail->send();
            $success = "Nuovo codice inviato! Controlla la tua email.";

        } catch (Exception $e) {
            $error = "Errore invio email. Riprova.";
        }

        $remaining = $cooldown;
    }
}
?>

<section class="auth">
<div class="auth-card">
<h1>Verifica il tuo account</h1>

<?php if ($error): ?>
<div class="error-box"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="success-box"><?= htmlspecialchars($success) ?></div>
<?php if (!isset($_SESSION['reg_email'])): ?>
<div class="go-login">
<a href="login.php">Accedi al tuo account</a>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (isset($_SESSION['reg_email'])): ?>

<?php if ($_SESSION['verify_attempts'] < $maxAttempts): ?>
<form method="post">
<div class="field">
<label>Inserisci il codice di verifica</label>
<input type="text" name="code" maxlength="6" required>
</div>
<button class="btn primary full">Verifica</button>
</form>
<?php endif; ?>

<p class="resend">
Non hai ricevuto il codice?
<?php if ($remaining > 0): ?>
<span id="resend-btn" class="disabled">[Reinvia] (<?= $remaining ?>s)</span>
<?php else: ?>
<a id="resend-btn" class="resend-enable" href="?resend=1">[Reinvia]</a>
<?php endif; ?>
</p>

<?php endif; ?>
</div>
</section>

<link rel="stylesheet" href="assets/style.css">

<style>
.disabled { color: gray; pointer-events: none; cursor: default; text-decoration: none; }
.resend { margin-top: 1.25rem; }
.resend-enable { text-decoration: none; color: #007bff; font-size: 0.85rem; }
.resend-enable:hover { text-decoration: underline; }
.go-login { margin-top: 50px; text-align: center; }
.go-login a {
    display: inline-block;
    width: 100%;
    height: 42px;
    line-height: 42px;
    background: #2f83a8;
    color: white;
    border-radius: 8px;
    font-weight: 700;
    text-decoration: none;
}
.go-login a:hover { background: #256b89; }
</style>

<script>
const resendBtn = document.getElementById('resend-btn');

<?php if ($remaining > 0): ?>
let endTime = localStorage.getItem('resend_end');
if (!endTime) {
    endTime = Date.now() + <?= $remaining ?> * 1000;
    localStorage.setItem('resend_end', endTime);
} else {
    endTime = parseInt(endTime);
}

const countdownTimer = setInterval(() => {
    let remaining = Math.ceil((endTime - Date.now()) / 1000);
    if (remaining <= 0) {
        clearInterval(countdownTimer);
        localStorage.removeItem('resend_end');
        const link = document.createElement('a');
        link.id = 'resend-btn';
        link.href = '?resend=1';
        link.className = 'resend-enable';
        link.textContent = '[Reinvia]';
        resendBtn.replaceWith(link);
    } else {
        resendBtn.textContent = `[Reinvia] (${remaining}s)`;
    }
}, 1000);
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>

