<?php
session_start();
include 'includes/db.php';

$error = "";
$success = "";

if (!isset($_GET['token']) || empty($_GET['token'])) 
{
    die("Token non valido.");
}

$token = $_GET['token'];

// Fetch user by token
$stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) 
{
    die("Token non valido o già usato.");
}

$user = $result->fetch_assoc();
$stmt->close();

$current_time = date('Y-m-d H:i:s');
if ($user['reset_expires'] < $current_time) 
{
    die("Token scaduto. Richiedi un nuovo reset della password.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Password validation
    if ($password !== $confirm) {
        $error = "Le password non coincidono.";
    } 
    elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W_]/', $password)
    ) {
        $error = "Password non valida. Deve avere almeno 8 caratteri, una maiuscola, un numero e un carattere speciale.";
    } 
    else 
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
        $stmt->bind_param("si", $hashed, $user['id']);

        if ($stmt->execute()) {
            $success = "✅ Password reimpostata con successo! Ora puoi fare il login.";
        } 
        else
        {
            $error = "Errore durante il reset della password. Riprova.";
        }
        $stmt->close();
    }
}
?>

<?php $PageTitle = "Reset Password"; include 'includes/header.php'; ?>

<section class="auth">
    <div class="auth-card">
        <h1>Reset della Password</h1>
        <p class="subtitle">Inserisci una nuova password per il tuo account</p>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-box"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn primary full" style="margin-top:10px;">Vai al login</a>
        <?php else: ?>
            <form method="post">
                <div class="field password-field">
                    <label>Nuova Password</label>
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

                <button class="btn primary full">Reimposta Password</button>
            </form>
        <?php endif; ?>
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

<?php include 'includes/footer.php'; ?>
