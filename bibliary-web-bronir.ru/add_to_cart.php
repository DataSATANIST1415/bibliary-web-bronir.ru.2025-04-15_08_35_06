<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';

if (!isset($_SESSION['userlogin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $userlogin = $_SESSION['userlogin'];

    $sql = "INSERT INTO cart (userlogin, product_id, quantity) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
    
    try {
    $result = executeQuery($sql, ["sii", $userlogin, $product_id, $quantity]);
    if ($result !== false) {
        echo "<script>window.location.href = 'cart.php';</script>";
        exit;
    } else {
        throw new Exception("Неизвестная ошибка при выполнении запроса");
    }
} catch (Exception $e) {
    error_log("Ошибка при добавлении товара в корзину: " . $e->getMessage());
    echo "Ошибка при добавлении товара в корзину: " . $e->getMessage();
    echo "<br>Пожалуйста, попробуйте еще раз или обратитесь к администратору.";
}

    
} else {
    header('Location: index.php');
    exit;
}
