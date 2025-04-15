<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ООО "Тензор-РТ" - Каталог изделий</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/IzdeliaKatalog.css">
</head>
<body>
    <header>
        <p><img src="images/LOGO.png" width="208" height="52" alt="Tenzor-RT Logo"></p>
    </header>
    
    <nav>
    <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="IzdeliaKatalog.php">Изделия</a></li>
            <?php if (isset($_SESSION['userlogin'])): ?>
                <li><a href="cart.php">Корзина</a></li>
                <li><a href="order-history.php">История заказов</a></li>
                <li><a href="out.php">Выйти</a></li>
            <?php else: ?>
                <li><a href="login.php">Войти</a></li>
                <li><a href="register.php">Зарегистрироваться</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <main>
        <h2>Каталог изделий</h2>
        <p>Наша компания специализируется на разработке и изготовлении высококачественного промышленного оборудования. Ниже представлены основные категории нашей продукции.</p>

        <?php
        $apiFilePath = __DIR__ . '/admin-panel/api.php';
        if (file_exists($apiFilePath)) {
            require_once $apiFilePath;
        } else {
            echo "Файл api.php не найден.";
            exit;
        }

        $sql = "SELECT product_id, name, description, main_photo AS image, cost FROM products";
        $result = executeQuery($sql);

        if ($result === false) {
            echo "Ошибка выполнения запроса";
            exit;
        }
        ?>

        <div class="product-grid">
            <?php
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="product-card">
                    <img src="/images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="280" height="200">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="price"><?php echo number_format($row['cost'], 2, ',', ' '); ?> ₽</p>
                    </div>
                    <a href="/Izdelie<?php echo $row['product_id']; ?>.php" class="cta-button">Подробнее</a>
                    
                </div>
            <?php } ?>
        </div>

        <?php
        // Закрываем соединение после использования
        $db->close();
        ?>

        <!-- Остальной контент -->
    </main>
    
    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    addToCart(productId);
                });
            });
        });

        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Товар добавлен в корзину!');
                    // Здесь можно добавить код для обновления иконки корзины, если она есть
                } else {
                    alert('Произошла ошибка при добавлении товара в корзину.');
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    </script>

</body>
</html>
