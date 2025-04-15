<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';

if (!isset($_SESSION['userlogin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cartId = $data['cartId'];
$action = $data['action'];

$sql = "SELECT quantity FROM cart WHERE cart_id = ? AND userlogin = ?";
$result = executeQuery($sql, ["is", $cartId, $_SESSION['userlogin']]);
$currentQuantity = $result->fetch_assoc()['quantity'];

if ($action === 'increase') {
    $newQuantity = $currentQuantity + 1;
} elseif ($action === 'decrease') {
    $newQuantity = max(1, $currentQuantity - 1);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND userlogin = ?";
$result = executeQuery($sql, ["iis", $newQuantity, $cartId, $_SESSION['userlogin']]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}
