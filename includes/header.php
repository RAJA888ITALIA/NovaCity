<!DOCTYPE html>
<html lang="it">
<head>
    <?php	
    date_default_timezone_set('Europe/Rome'); 
	if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') 
    {
	    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  		  header('Location: ' . $redirect);
  		  exit();
    }
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="1y6NCChcKgpPWqlTs70wGH5OpEbeJSQWzcN5SpwYrWc" />
    <title><?= htmlspecialchars($PageTitle ?? 'NovaCity'); ?></title>
    <link rel="icon" href="assets/SiteLogo.png" type="image/png">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="page-wrapper"><!-- START wrapper -->

    <header class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <img src="assets/NovaCityLogo.png" alt="NovaCity Logo" class="navbar-logo">
                Nova<span>City</span>
            </a>

            <?php if (isset($_SESSION['user_id'])):
                // Fetch user details for profile
                $stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();

                $profile_pic_directory = 'assets/profile_pics/';
                $profile_pic = !empty($user['profile_pic']) ? $profile_pic_directory . $user['profile_pic'] : $profile_pic_directory . 'default_pic.jpg';
                ?>
                <div class="profile-dropdown">
                    <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture" class="profile-pic" id="profile-pic">
                    <div class="dropdown-menu" id="profile-dropdown">
                        <a href="profile.php">Profilo</a>
                        <a href="settings.php">Impostazioni</a>
                        <a href="logout.php" class="logout-link">
                            Logout
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="16" height="16"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 class="logout-icon">
                                <path d="M3 3h9a2 2 0 0 1 2 2v4" />
                                <path d="M14 15v4a2 2 0 0 1-2 2H3z" />
                                <path d="M10 12h11" />
                                <path d="M18 9l3 3-3 3" />
                            </svg>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <nav class="navbar-menu">
                    <a href="index.php">Home</a>
                    <a href="login.php">Accedi</a>
                    <a href="register.php">Registrati</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

<script>
    // Handle dropdown toggle for the profile menu
    const profilePic = document.getElementById('profile-pic');
    if(profilePic){
        profilePic.addEventListener('click', function() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });

        // Close dropdown if clicked outside of it
        window.addEventListener('click', function(event) {
            if (!event.target.matches('#profile-pic')) {
                const dropdown = document.getElementById('profile-dropdown');
                if (dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                }
            }
        });
    }
</script>