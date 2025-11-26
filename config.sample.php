<?php
// config.sample.php - Wzór konfiguracji
define('DB_HOST', 'localhost');
define('DB_NAME', 'uks_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Ustawienia Administratora
define('ADMIN_EMAIL', 'twoj@email.com');
define('ADMIN_LOGIN', 'Admin');
define('ADMIN_PASS', 'Admin');

// Ustawienia SMTP (Poczta)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'twoj@gmail.com');
define('SMTP_PASS', 'TU_WPISZ_HASLO_APLIKACJI'); // Puste pole
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); 

session_start();
?>