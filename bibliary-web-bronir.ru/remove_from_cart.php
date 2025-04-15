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

$sql = "DELETE FROM cart WHERE cart_id = ? AND userlogin = ?";
$result = executeQuery($sql, ["is", $cartId, $_SESSION['userlogin']]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
}
