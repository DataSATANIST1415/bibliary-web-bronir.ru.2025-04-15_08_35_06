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

// Получение товаров в корзине
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.cost, p.main_photo, p.catalog_page_name
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.userlogin = ?";
$cartItems = executeQuery($sql, ["s", $userlogin]);

$totalCost = 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/cart.css">
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
        <h1>Корзина</h1>
        <?php if ($cartItems->num_rows == 0): ?>
            <p>Ваша корзина пуста.</p>
        <?php else: ?>
            <div class="cart-items">
                <?php while ($item = $cartItems->fetch_assoc()): 
                    $itemTotal = $item['cost'] * $item['quantity'];
                    $totalCost += $itemTotal;
                ?>
                    <div class="cart-item">
                        <img src="images/<?php echo htmlspecialchars($item['main_photo']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="100">
                        <div class="item-details">
                            <h2><a href="<?php echo htmlspecialchars($item['catalog_page_name']); ?>.php"><?php echo htmlspecialchars($item['name']); ?></a></h2>
                            <p>Цена: <?php echo number_format($item['cost'], 2, ',', ' '); ?> ₽</p>
                            <p>Количество: 
                                <button class="quantity-btn" data-action="decrease" data-cart-id="<?php echo $item['cart_id']; ?>">-</button>
                                <span class="quantity"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" data-action="increase" data-cart-id="<?php echo $item['cart_id']; ?>">+</button>
                            </p>
                            <p>Итого: <?php echo number_format($itemTotal, 2, ',', ' '); ?> ₽</p>
                            <button class="remove-btn" data-cart-id="<?php echo $item['cart_id']; ?>">Удалить</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            

            <?php if (!empty($cartItems)): ?>
    <div class="cart-total">
        <h2>Итого: <?php echo number_format($totalCost, 2, ',', ' '); ?> ₽</h2>
        <button id="clear-cart" class="clear-cart-btn">Очистить корзину</button>
        <a href="order.php" class="checkout-btn">Оформить заказ</a>
    </div>
<?php endif; ?>

            
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    const removeButtons = document.querySelectorAll('.remove-btn');
    const clearCartButton = document.getElementById('clear-cart');

    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const cartId = this.dataset.cartId;
            updateQuantity(cartId, action);
        });
    });

    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.dataset.cartId;
            removeItem(cartId);
        });
    });

    if (clearCartButton) {
        clearCartButton.addEventListener('click', clearCart);
    }

    function updateQuantity(cartId, action) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ cartId: cartId, action: action }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Произошла ошибка при обновлении количества товара.');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }

    function removeItem(cartId) {
        if (confirm('Вы уверены, что хотите удалить этот товар из корзины?')) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cartId: cartId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при удалении товара из корзины.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    }

    function clearCart() {
        if (confirm('Вы уверены, что хотите очистить всю корзину?')) {
            fetch('clear_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Произошла ошибка при очистке корзины.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    }
});
</script>
</body>
</html>
