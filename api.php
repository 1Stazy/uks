<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Contract.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$contract = new Contract();

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $contract->create($_POST);
    echo json_encode($res);
    exit;
}

// Sekcja Admina (powinna być zabezpieczona sesją)
if(!isset($_SESSION['admin_logged'])) {
    if($action != 'login') {
        echo json_encode(['success'=>false, 'message'=>'Brak dostępu']);
        exit;
    }
}

if ($action === 'login') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if($u === ADMIN_LOGIN && $p === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Błędne dane']);
    }
}

if ($action === 'accept') {
    echo json_encode(['success' => $contract->accept($_POST['id'])]);
}

if ($action === 'reject') {
    echo json_encode(['success' => $contract->reject($_POST['id'], $_POST['reason'])]);
}

if ($action === 'toggle_warranty') {
    echo json_encode(['success' => $contract->toggleWarranty($_POST['id'])]);
}
if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>