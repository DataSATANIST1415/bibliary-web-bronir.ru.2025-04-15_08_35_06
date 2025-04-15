<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';

if (!isset($_SESSION['userlogin'])) {
    header('Location: login.php');
    exit;
}

$userlogin = $_SESSION['userlogin'];

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);

// Получение информации о заказе
$sql = "SELECT o.*, p.name AS payment_method_name 
        FROM orders o
        JOIN payment_methods p ON o.payment_method_id = p.payment_method_id
        WHERE o.order_id = ? AND o.customer_id = ?";
$order = executeQuery($sql, ["is", $order_id, $userlogin])->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Получение товаров заказа
$sql = "SELECT oi.*, p.name, p.main_photo
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?";
$orderItems = executeQuery($sql, ["i", $order_id]);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение заказа - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/order-confirmation.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">
</head>
<body>
    <header>
        <p><img src="images/LOGO2.png" width="220" height="100" alt="Tenzor-RT Logo"></p>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="IzdeliaKatalog.php">Изделия</a></li>
            <li><a href="cart.php">Корзина</a></li>
            <li><a href="order-history.php">История заказов</a></li>
            <li><a href="out.php">Выйти</a></li>
        </ul>
    </nav>
    
    <main>
        <h1>Подтверждение заказа</h1>
        <div class="order-confirmation">
            <h2>Спасибо за ваш заказ!</h2>
            <p>Номер заказа: <?php echo $order['order_id']; ?></p>
            <p>Дата заказа: <?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></p>
            <p>Статус: <?php echo $order['status']; ?></p>
            <p>Способ оплаты: <?php echo $order['payment_method_name']; ?></p>
            <p>Адрес доставки: <?php echo $order['delivery_address']; ?></p>
            
            <h3>Товары в заказе:</h3>
            <?php while ($item = $orderItems->fetch_assoc()): ?>
                <div class="order-item">
                    <img src="images/<?php echo htmlspecialchars($item['main_photo']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span>Количество: <?php echo $item['quantity']; ?></span>
                    <span>Цена: <?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</span>
                </div>
            <?php endwhile; ?>
            
            <p class="order-total">Итого: <?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</p>
        </div>
    </main>

    <footer>
        <p>© 
