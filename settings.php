<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

$PageTitle = "Settings";
include 'includes/header.php';

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // --- Change Password ---
    if (isset($_POST['change_password'])) 
    {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Get current hashed password from DB
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $hashed_password = $result['password'];

        if (!password_verify($current_password, $hashed_password))
        {
            $error_message .= "Password corrente errata.<br>";
        } 
        elseif ($new_password !== $confirm_password)
        {
            $error_message .= "La nuova password e la conferma non coincidono.<br>";
        } 
        else 
        {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $_SESSION['user_id']);
            if ($stmt->execute()) 
            {
                $success_message .= "Password aggiornata con successo!<br>";
            } 
            else 
            {
                $error_message .= "Errore nell'aggiornamento della password.<br>";
            }
            $stmt->close();
        }
    }
}
?>

<section class="settings">
    <h1>Impostazioni Account</h1>

    <?php if (!empty($success_message)): ?>
        <div class="message success">
            <?= $success_message ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="message error">
            <?= $error_message ?>
        </div>
    <?php endif; ?>

    <!-- Change Password Form -->
    <form method="POST" class="settings-form">
        <input type="hidden" name="change_password" value="1">
        <h2>Cambia Password</h2>

        <div class="form-field">
            <label for="current_password">Password Attuale</label>
            <input type="password" name="current_password" id="current_password" required>
        </div>
        <div class="form-field">
            <label for="new_password">Nuova Password</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>
        <div class="form-field">
            <label for="confirm_password">Conferma Nuova Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button class="btn primary">Aggiorna Password</button>
    </form>

    <!-- Account Actions -->
    <div class="account-actions">
        <a href="logout.php" class="btn danger">Logout</a>
    </div>

    <!-- Back to Dashboard -->
    <div class="auth-footer">
        <a href="dashboard.php">← Torna al Dashboard</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
