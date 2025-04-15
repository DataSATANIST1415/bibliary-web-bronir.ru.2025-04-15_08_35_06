<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['userlogin'])) {
    header('Location: login.php');
    exit;
}

$userlogin = $_SESSION['userlogin'];
$orders = getUserOrders($userlogin);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История заказов - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <link rel="stylesheet" type="text/css" href="css/order-history.css">
</head>
<body>
    <header>
        <p><img src="images/LOGO.png" width="220" height="52" alt="Tenzor-RT Logo"></p>
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
        <h1>История заказов</h1>
        <?php if (empty($orders)): ?>
            <p>У вас пока нет заказов.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h2>Заказ №<?php echo $order['order_id']; ?></h2>
                        <p>Дата: <?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></p>
                        <p>Статус: <?php echo $order['status']; ?></p>
                        <p>Сумма: <?php echo number_format($order['total_amount'], 2, ',', ' '); ?> ₽</p>
                        <p>Товары: <?php echo $order['products']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>
</body>
</html>
