<?php
ob_start();
session_start();
include 'config.php';
include 'admin-panel/api.php';

if (!isset($_SESSION['userlogin'])) {
    header('Location: login.php');
    exit;
}

$userlogin = $_SESSION['userlogin'];

// Получение товаров из корзины
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.cost, p.main_photo
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.userlogin = ?";
$cartItems = executeQuery($sql, ["s", $userlogin]);

$totalCost = 0;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Валидация данных
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_NUMBER_INT);

    if ($name && $email && $phone && $address && $payment_method) {
        // Начало транзакции
        $db->begin_transaction();

        try {
            // Сохранение заказа в базу данных
            $sql = "INSERT INTO orders (customer_id, order_date, total_amount, status, delivery_address, payment_method_id) 
                    VALUES (?, NOW(), ?, 'Новый', ?, ?)";
            $result = executeQuery($sql, ["sdss", $userlogin, $totalCost, $address, $payment_method]);

            if ($result) {
                $order_id = $db->insert_id;

                // Сохранение товаров заказа
                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($sql);

                $cartItems->data_seek(0); // Сбрасываем указатель результата запроса
                while ($item = $cartItems->fetch_assoc()) {
                    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['cost']);
                    $stmt->execute();
                }

                // Очистка корзины
                executeQuery("DELETE FROM cart WHERE userlogin = ?", ["s", $userlogin]);

                // Подтверждение транзакции
                $db->commit();

                // Перенаправление на страницу подтверждения
                header("Location: order-confirmation.php?order_id=$order_id");
                exit;
            } else {
                throw new Exception("Ошибка при создании заказа");
            }
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $db->rollback();
            $error_message = "Произошла ошибка при оформлении заказа: " . $e->getMessage();
        }
    } else {
        $error_message = "Пожалуйста, заполните все поля формы.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/order.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">
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
        <h1>Оформление заказа</h1>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <h2>Информация о доставке</h2>
            <input type="text" name="name" required placeholder="Ваше имя">
            <input type="email" name="email" required placeholder="Email">
            <input type="tel" name="phone" required placeholder="Телефон">
            <textarea name="address" required placeholder="></textarea>
                        <textarea name="address" required placeholder="Адрес доставки"></textarea>

            <h2>Ваш заказ</h2>
            <?php 
            $cartItems->data_seek(0); // Сбрасываем указатель результата запроса
            $totalCost = 0; // Сбрасываем общую стоимость
            while ($item = $cartItems->fetch_assoc()): 
                $itemTotal = $item['cost'] * $item['quantity'];
                $totalCost += $itemTotal;
            ?>
                <div class="order-item">
                    <img src="images/<?php echo htmlspecialchars($item['main_photo']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span>Количество: <?php echo $item['quantity']; ?></span>
                    <span>Цена: <?php echo number_format($item['cost'], 2, ',', ' '); ?> ₽</span>
                    <span>Итого: <?php echo number_format($itemTotal, 2, ',', ' '); ?> ₽</span>
                </div>
            <?php endwhile; ?>

            <div class="order-total">
                <h3>Итого к оплате: <?php echo number_format($totalCost, 2, ',', ' '); ?> ₽</h3>
            </div>

            <h2>Способ оплаты</h2>
            <select name="payment_method" required>
                <option value="">Выберите способ оплаты</option>
                <?php
                $paymentMethods = executeQuery("SELECT payment_method_id, name FROM payment_methods");
                while ($method = $paymentMethods->fetch_assoc()) {
                    echo "<option value=\"" . $method['payment_method_id'] . "\">" . htmlspecialchars($method['name']) . "</option>";
                }
                ?>
            </select>

            <button type="submit">Подтвердить заказ</button>
        </form>
    </main>

    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>
</body>
</html>
<?php
ob_end_flush();
?>

