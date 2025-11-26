<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Contract.php';

// Ten plik służy tylko do wyświetlania PDF w przeglądarce
if(!isset($_SESSION['admin_logged'])) die('Brak dostępu');

$id = $_GET['id'] ?? '';
$final = isset($_GET['final']) ? true : false; // Czy wersja finalna (z podpisem admina)

$contract = new Contract();
// I - Inline (otwórz w oknie)
$contract->generatePDF($id, $final, 'I');
?>