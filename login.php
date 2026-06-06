<?php
// Cookie sicuri
session_set_cookie_params([
    'secure' => true,      // solo HTTPS
    'httponly' => true,    // non accessibile da JS
    'samesite' => 'Lax'    // protezione CSRF di base
]);

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';

// Inizializza CAPTCHA
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = null;
}

// Reindirizza se già loggato
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$PageTitle = "Login";
include 'includes/header.php';

$error = "";
$success_message = "";
$MAX_ATTEMPTS = 7;
$BLOCK_TIME = 45; // minuti
$ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Credenziali non corrette.";
    } else {

        // 🔒 GLOBAL IP RATE LIMIT (KEY FIX)
        $stmtIP = $conn->prepare(
            "SELECT attempts, blocked_until 
             FROM login_attempts 
             WHERE username = '__IP__' AND ip_address = ?"
        );
        $stmtIP->bind_param("s", $ip);
        $stmtIP->execute();
        $ipResult = $stmtIP->get_result();
        $ipData = $ipResult->fetch_assoc();
        $stmtIP->close();

        // Reset blocco IP se scaduto
        if ($ipData && $ipData['blocked_until'] !== null) {
            if (strtotime($ipData['blocked_until']) <= time()) {
                $stmt = $conn->prepare(
                    "DELETE FROM login_attempts 
                     WHERE username = '__IP__' AND ip_address = ?"
                );
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $stmt->close();
                $ipData = null;
                $_SESSION['captcha'] = null;
            } else {
                $error = "Troppi tentativi errati. Riprova più tardi";
            }
        }

        // CAPTCHA check
        if (empty($error) && $_SESSION['captcha'] !== null) {
            if (!isset($_POST['captcha']) || (int)$_POST['captcha'] !== $_SESSION['captcha']['answer']) {
                $error = "Verifica CAPTCHA non valida.";

                // rigenera sempre
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $_SESSION['captcha'] = [
                    'question' => "$a + $b",
                    'answer' => $a + $b
                ];
            }
        }

        if (empty($error)) {

            // Controlla utente
            $stmt = $conn->prepare(
                "SELECT id, password, email_verified FROM users WHERE username = ?"
            );
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            $login_success = false;

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if ($user['email_verified'] == 1 &&
                    password_verify($password, $user['password'])) {
                    $login_success = true;
                }
            }

            // ================= SUCCESS =================
            if ($login_success) {

                // Reset tentativi IP
                $stmt = $conn->prepare(
                    "DELETE FROM login_attempts 
                     WHERE username = '__IP__' AND ip_address = ?"
                );
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $stmt->close();

                $_SESSION['captcha'] = null;
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];

                    // SUCCESS → reset tentativi
                $stmt2 = $conn->prepare(
                    "DELETE FROM login_attempts WHERE username = ? AND ip_address = ?"
                );
                $stmt2->bind_param("ss", $email, $ip);
                $stmt2->execute();
                $stmt2->close();

                $_SESSION['captcha'] = null;

                // 🔹 RIGENERA SESSIONE
                session_regenerate_id(true);

                // 🔹 SALVA USER_ID, IP E USER-AGENT
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                header("Location: dashboard.php");
                exit();
            }

            // ================= FAILURE =================

            usleep(1500000);

            $attempts = $ipData ? $ipData['attempts'] + 1 : 1;

            // CAPTCHA dopo 3 tentativi
            if ($attempts >= 3) {
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $_SESSION['captcha'] = [
                    'question' => "$a + $b",
                    'answer' => $a + $b
                ];
            }

            // Aggiorna tentativi IP
            if ($ipData) {
                if ($attempts >= $MAX_ATTEMPTS) {
                    $blockedUntil = date("Y-m-d H:i:s", time() + ($BLOCK_TIME * 60));

                    $stmt = $conn->prepare(
                        "UPDATE login_attempts 
                         SET attempts = ?, blocked_until = ? 
                         WHERE username = '__IP__' AND ip_address = ?"
                    );
                    $stmt->bind_param("iss", $attempts, $blockedUntil, $ip);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE login_attempts 
                         SET attempts = ? 
                         WHERE username = '__IP__' AND ip_address = ?"
                    );
                    $stmt->bind_param("is", $attempts, $ip);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO login_attempts (username, ip_address, attempts) 
                     VALUES ('__IP__', ?, 1)"
                );
                $stmt->bind_param("s", $ip);
                $stmt->execute();
                $stmt->close();
            }

            // Messaggio uniforme
            $remaining = max(0, $MAX_ATTEMPTS - $attempts);

            if ($remaining <= 0) {
                $error = "Troppi tentativi errati. Riprova più tardi";
            } elseif ($remaining <= 3) {
                $error = "Credenziali non corrette. Tentativi rimasti: $remaining";
            } else {
                $error = "Credenziali non corrette.";
            }
        }
    }
}
?>

<section class="auth">
    <div class="auth-card">
        <h1>Accedi a NovaCity</h1>
        <p class="subtitle">Gestisci le tue segnalazioni urbane</p>

        <?php if (!empty($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="field password-field">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">◉</span>
                </div>
            </div>

            <?php if ($_SESSION['captcha'] !== null): ?>
                <div class="field">
                    <label>Quanto fa <?= $_SESSION['captcha']['question'] ?>?</label>
                    <input type="number" name="captcha" required>
                </div>
            <?php endif; ?>

            <div class="forgot">
                <a href="forgot_password.php">Hai dimenticato la password?</a>
            </div>

            <button class="btn primary full">Accedi</button>
        </form>

        <div class="auth-footer">
            <span>Non hai un account?</span>
            <a href="register.php">Registrati</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
function togglePassword(fieldId, el) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        el.textContent = "⊘";
    } else {
        input.type = "password";
        el.textContent = "◉";
    }
}
</script>

<style>
.forgot a {
    text-decoration: none;
    color: #007bff;
    font-size: 0.85rem;
}

.forgot a:hover {
    text-decoration: underline;
}
</style>