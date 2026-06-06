<?php
// Secure session start
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'yourdomain.com', // replace with your domain
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

include 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) 
{
    header('Location: login.php');
    exit();
}

// Security headers
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self'; style-src 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");

$PageTitle = "Profilo";
$PageHead  = "";
include 'includes/header.php';

// Fetch user info including profile_pic
$stmt = $conn->prepare("SELECT surname, name, username, address, phone, cod_fis, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Profile picture path
$profile_pic_directory = 'assets/profile_pics/';
$profile_pic = !empty($user['profile_pic']) ? $profile_pic_directory . $user['profile_pic'] : $profile_pic_directory . 'default_pic.jpg';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Collect and sanitize user inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $surname = htmlspecialchars(trim($_POST['surname']), ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $cod_fis = htmlspecialchars(trim($_POST['cod_fis']), ENT_QUOTES, 'UTF-8');

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
    {
        $error_message = "Email non valida.";
    } 
    else 
    {
        // Update user info in database
        $stmt = $conn->prepare("UPDATE users SET username = ?, name = ?, surname = ?, address = ?, phone = ?, cod_fis = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $email, $name, $surname, $address, $phone, $cod_fis, $_SESSION['user_id']);

        if ($stmt->execute()) 
        {
            $success_message = "Profilo aggiornato con successo!";
            // Refresh user info
            $user['name'] = $name;
            $user['surname'] = $surname;
            $user['username'] = $email;
            $user['address'] = $address;
            $user['phone'] = $phone;
            $user['cod_fis'] = $cod_fis;
        } 
        else 
        {
            $error_message = "Errore nell'aggiornamento del profilo.";
        }
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) 
    {

        $allowedExtensions = ['jpg','jpeg','png','gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($fileExtension, $allowedExtensions)) 
        {
            $error_message = "Tipo di file non valido. Solo JPG, PNG, GIF sono permessi.";
        } 
        elseif ($_FILES['profile_pic']['size'] > $maxFileSize) 
        {
            $error_message = "Il file è troppo grande. Massimo 2MB.";
        } 
        elseif (!getimagesize($fileTmpPath)) 
        {
            $error_message = "Il file caricato non è un'immagine valida.";
        } 
        else 
        {
            // Generate unique file name
            $newFileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
            $destPath = $profile_pic_directory . $newFileName;

            // Move uploaded file
            if (move_uploaded_file($fileTmpPath, $destPath)) 
            {
                $stmtPic = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmtPic->bind_param("si", $newFileName, $_SESSION['user_id']);
                $stmtPic->execute();

                $profile_pic = $destPath;
                $success_message = "Profilo aggiornato con successo!";
            } 
            else 
            {
                $error_message = "Errore durante l'upload della foto.";
            }
        }
    }
}
?>

<section class="profile">
    <h1>Modifica il Tuo Profilo</h1>

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Profile Picture -->
        <div class="profile-picture-container">
            <label class="profile-picture-label">
                <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture" class="profile-picture" id="profile-img">
                <input type="file" name="profile_pic" class="profile-picture-input" accept="image/*">
                <span class="edit-icon">✎</span>
            </label>
        </div>

        <div class="form-field">
            <label for="name">Nome</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-field">
            <label for="surname">Cognome</label>
            <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($user['surname']) ?>" required>
        </div>
        <div class="form-field">
   			<label for="email">Email</label>
  		    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['username']) ?>" readonly>
        </div>

        <div class="form-field">
            <label for="address">Indirizzo</label>
            <input type="text" name="address" id="address" value="<?= htmlspecialchars($user['address']) ?>">
        </div>
        <div class="form-field">
            <label for="phone">Numero di Telefono</label>
            <div class="phone-input-wrapper">
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Telefono" required>
            </div>
        </div>
        <div class="form-field">
            <label for="cod_fis">Codice Fiscale</label>
            <input type="text" name="cod_fis" id="cod_fis" value="<?= htmlspecialchars($user['cod_fis']) ?>">
        </div>

        <div class="form-buttons">
            <button class="btn primary">Salva Modifiche</button>
            <a href="dashboard.php" class="btn cancel">Annulla</a>
        </div>
    </form>

    <div class="auth-footer">
        <a href="dashboard.php">← Torna al Dashboard</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
const fileInput = document.querySelector('.profile-picture-input');
const profileImg = document.getElementById('profile-img');

fileInput.addEventListener('change', function(e) 
{
    const file = e.target.files[0];
    if(file)
    {
        profileImg.src = URL.createObjectURL(file);
    }
});
</script>
