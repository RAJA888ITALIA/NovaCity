<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

$user_id   = $_SESSION['user_id'];
$PageTitle = "Nuovo Report";

include 'includes/header.php';
$error   = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category    = $_POST['category'];
    $location    = trim($_POST['location']);
    $status      = "pending";

    $filename = "";
    if (!empty($_FILES['image']['name'])) 
    {
        $filename = time() . "_" . basename($_FILES['image']['name']);
        // Ensure uploads folder exists
        if (!is_dir('uploads')) 
        {
            mkdir('uploads', 0755, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $filename);
    }

    if (isset($conn) && $conn)
    {
        $stmt = $conn->prepare("INSERT INTO reports (user_id, title, description, category, location, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $title, $description, $category, $location, $filename, $status);

        if ($stmt->execute()) 
        {
            $success = "✅ Report inviato con successo! Reindirizzamento in corso...";
            echo "<script>
                    setTimeout(() => { window.location.href = 'dashboard.php'; }, 1500);
                  </script>";
        } 
        else 
        {
            $error = "Errore durante l'invio del report: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<section class="auth">
    <div class="auth-card">
        <h1>📢 Invia una Segnalazione</h1>
        <p class="subtitle">(Campi con * obbligatori)</p>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-box"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="field">
                <label>Titolo del problema*</label>
                <input type="text" name="title" required>
            </div>

            <div class="field">
                <label>Descrizione*</label>
                <textarea name="description" rows="4" required></textarea>
            </div>

            <div class="field">
                <label>Categoria*</label>
                <select name="category" required>
                    <option value="Strada">Strada</option>
                    <option value="Illuminazione">Illuminazione</option>
                    <option value="Rifiuti">Rifiuti</option>
                    <option value="Altro">Altro</option>
                </select>
            </div>

            <div class="field">
                <label>Posizione (via, zona, indirizzo)*</label>
                <input type="text" name="location" required>
            </div>

            <div class="field">
                <label>Carica una foto (opzionale)</label>
                <input type="file" name="image" accept="image/*" class="file-input">
            </div>

            <button class="btn primary">Invia Report</button>
        </form>

        <div class="auth-footer">
            <a href="dashboard.php">← Torna al Dashboard</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
