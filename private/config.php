
<?php
// config.php
// Gmail SMTP credentials [nascosto]
define('EMAIL_USERNAME', getenv('EMAIL_USERNAME'));
define('EMAIL_PASSWORD', getenv('EMAIL_PASSWORD'));
define('EMAIL_VERIFY', getenv('EMAIL_VERIFY'));
define('EMAIL_HOST', getenv('EMAIL_HOST'));

//ServerHost [nascosto]
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
?>
