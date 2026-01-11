<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'uks_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Ustawienia Administratora
define('ADMIN_EMAIL', 'dudux73@gmail.com'); // Tutaj będą przychodzić kopie
define('ADMIN_LOGIN', 'Admin');
define('ADMIN_PASS', 'Admin');

// Ustawienia SMTP (Poczta)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'dudux73@gmail.com'); // Twój adres Gmail
define('SMTP_PASS', 'ztyg kwzl rcct ebvf'); // <-- To z Kroku 2
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); // 'ssl' dla portu 465

session_start();
?>