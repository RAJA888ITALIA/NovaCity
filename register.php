<?php
session_start();
require_once 'private/config.php';

if (isset($_SESSION['user_id'])) 
{
    header('Location: dashboard.php');
    exit();
}


$PageTitle = "Registrazione";
include 'includes/header.php';
include 'includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

$error = "";
$success = "";
$show_allowed_warning = false; // Variabile per mostrare la box gialla
$nome = $cognome = $email = "";

// Lista dei domini email consentiti
$allowed_domains = [
    'gmail.com',
    'googlemail.com',
    'outlook.com',
    'hotmail.com',
    'live.com',
    'yahoo.com',
    'icloud.com',
    'me.com',
    'proton.me',
    'protonmail.com'
];

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Controllo dominio email
    $email_domain = substr(strrchr($email, "@"), 1);
    if (!empty($email_domain) && !in_array(strtolower($email_domain), $allowed_domains)) 
    {
        $show_allowed_warning = true; // Mostra la box gialla
    }
    // Password validation
    elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)
    ) 
    {
        $error = "La password non soddisfa i requisiti di sicurezza.";
    } 
    elseif ($password !== $confirm_password)
    {
        $error = "Le password non coincidono.";
    } 
    else 
    {
        // Check if email exists
        $check = $conn->prepare("SELECT id, email_verified FROM users WHERE username=?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) 
        {
            $user = $result->fetch_assoc();
            if ($user['email_verified'] == 1) 
            {
                $error = "Email già registrata. Accedi al tuo account.";
            }
            else 
            {
                // Not verified → go to code verification
                $_SESSION['reg_email'] = $email;
                header("Location: verify_code.php");
                exit();
            }
        } 
        else 
        {
            // Create new user with verification code
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = rand(100000, 999999); // 6-digit code
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $stmt = $conn->prepare(
                "INSERT INTO users 
                (surname, name, username, password, role, profile_pic, verification_code, email_verified, verification_expires, last_verification_sent)
                VALUES (?, ?, ?, ?, 'user', 'default_pic.jpg', ?, 0, ?, NOW())"
            );
            $stmt->bind_param("ssssss", $cognome, $nome, $email, $hashedPassword, $verification_code, $expires);

            if ($stmt->execute()) 
            {
                // Send code via email
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
                    $mail->Subject = 'Codice di verifica NovaCity';
                    $mail->Body = "
                        <p>Ciao <b>$nome</b>,</p>
                        <p>Il tuo codice di verifica per NovaCity è:</p>
                        <h2>$verification_code</h2>
                        <p>Valido per 24 ore.</p>
                    ";
                    $mail->send();

                    $_SESSION['reg_email'] = $email;
                    header("Location: verify_code.php");
                    exit();

                } 
                catch (Exception $e) 
                {
                    $error = "Registrazione completata, ma email non inviata.";
                }
            } 
            else 
            {
                $error = "Errore durante la registrazione.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<section class="auth">
    <div class="auth-card">
        <h1>Crea il tuo account</h1>
        <p class="subtitle">Entra a far parte di NovaCity</p>

        <?php if ($error): ?>
            <div class="error-box"><?= $error ?></div>
        <?php endif; ?>

        <!-- Box email consentite, mostrata solo se dominio non valido -->
        <?php if ($show_allowed_warning): ?>
        	<div class="error-box">
                <strong>Dominio non valido</strong>
            </div>
            <div id="allowedEmailsBox" class="warning-box">
                <strong>Email accettate:</strong><br>
                gmail.com<br>
                outlook.com / hotmail.com / live.com<br>
                yahoo.com<br>
                icloud.com / me.com<br>
                proton.me / protonmail.com
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
            </div>
            <div class="field">
                <label>Cognome</label>
                <input type="text" name="cognome" value="<?= htmlspecialchars($cognome) ?>" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="field password-field">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">◉</span>
                </div>
                <small>
                    • Minimo 8 caratteri<br>
                    • Almeno 1 lettera maiuscola<br>
                    • Almeno 1 numero<br>
                    • Almeno 1 carattere speciale (!@#$%^&*)
                </small>
            </div>
            <div class="field password-field">
                <label>Conferma Password</label>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">◉</span>
                </div>
            </div>
            <button class="btn primary full">Registrati</button>
        </form>

        <div class="auth-footer">
            <span>Hai già un account?</span>
            <a href="login.php">Accedi</a>
        </div>
    </div>
</section>

<script>
function togglePassword(fieldId, el) 
{
    const input = document.getElementById(fieldId);
    if (input.type === "password") 
    {
        input.type = "text";
        el.textContent = "⊘";
    } 
    else 
    {
        input.type = "password";
        el.textContent = "◉";
    }
}
</script>

<style>
.warning-box {
    background-color: #fff7e5;
    color: #b36b00;
    padding: 12px 15px;
    border-radius: 6px;
    margin-top: 15px;
    border: 1px solid #b36b00;
}
</style>

<?php include 'includes/footer.php'; ?>
