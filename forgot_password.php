<?php
session_start();
require_once 'private/config.php';
include 'includes/db.php';

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE username = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close(); // Close first stmt before reusing variable

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update_stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
        $update_stmt->bind_param("ssi", $token, $expires, $user['id']);
        $update_stmt->execute();
        $update_stmt->close();

        // Send email...
        $mail = new PHPMailer(true);
        try 
        {
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

            $link = "https://novacity.wuaze.com/reset_password.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = "Reset della password NovaCity";
            $mail->Body = "
                <p>Ciao <b>" . htmlspecialchars($user['name']) . "</b>,</p>
                <p>Hai richiesto di reimpostare la tua password.</p>
                <p>Clicca sul link qui sotto (valido 1 ora):<br>
                <a href='$link'>$link</a></p>
                <p>Se non sei stato tu, ignora questa email.</p>";
            
            $mail->send();
            $success = "Controlla la tua email per reimpostare la password.";

        } 
        catch (Exception $e) 
        {
            $error = "Errore nell'invio email. Contatta l'amministratore.";
        }

    } 
    else 
    {
        $error = "Email non trovata.";
    }
}
?>

<?php 
$PageTitle = "Recupero Password"; 
include 'includes/header.php'; 
?>

<section class="auth">
    <div class="auth-card">
        <h1>Recupera la tua password</h1>
        <p class="subtitle">Inserisci la tua email per ricevere il link di reset</p>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-box"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <button class="btn primary full">Invia link di reset</button>
        </form>

        <div class="auth-footer">
            <span>Hai già un account?</span>
            <a href="login.php">Accedi</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
