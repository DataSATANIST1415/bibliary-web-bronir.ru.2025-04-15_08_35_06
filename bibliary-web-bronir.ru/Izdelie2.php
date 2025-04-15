<?php
session_start();
include 'config.php';
include 'admin-panel/api.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 2; // Получаем ID продукта из URL

$sql = "SELECT * FROM products WHERE product_id = ?";
$result = executeQuery($sql, ["i", $product_id]);

$product = $result->fetch_assoc();

if (!$product) {
    echo "Продукт не найден";
    exit;
}

// Получаем особенности продукта
$sql = "SELECT feature FROM product_features WHERE product_id = ?";
$features = executeQuery($sql, ["i", $product_id])->fetch_all(MYSQLI_ASSOC);

// Получаем технические характеристики продукта
$sql = "SELECT parameter, value FROM product_specs WHERE product_id = ?";
$specs = executeQuery($sql, ["i", $product_id])->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/product.css">
    <link rel="stylesheet" type="text/css" href="css/IzdelieAccumBattery.css">
</head>
<body>
    <header>
        <p><img src="images/LOGO2.png" width="220" height="100" alt="Tenzor-RT Logo"></p>
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
            <?php endif; ?>
        </ul>
    </nav>
    
    <main>
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>

     <div class="product-details">
            <div class="product-image">
                <div class="slider-container">
                    <img id="main-image" src="/images/<?php echo htmlspecialchars($product['main_photo']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="420" height="280">
                    <button id="prev-btn">&lt;</button>
                    <button id="next-btn">&gt;</button>
                </div>
                <div class="thumbnail-container">
                    <?php
                    $additionalPhotos = json_decode($product['additional_photos'], true);
                    if ($additionalPhotos) {
                        foreach ($additionalPhotos as $photo) {
                            echo '<img class="thumbnail" src="/images/' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($product['name']) . '" width="80" height="60">';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="product-info">
                <h2>Описание</h2>
                <p><?php echo nl2br(htmlspecialchars($product['page_description'])); ?></p>
                <h3>Ключевые особенности:</h3>
                <ul>
                    <?php foreach ($features as $feature): ?>
                        <li><?php echo htmlspecialchars($feature['feature']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="price">Цена: <?php echo number_format($product['cost'], 2, ',', ' '); ?> ₽</p>

          <?php if (isset($_SESSION['userlogin'])): ?>
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <label for="quantity">Количество:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                        <button type="submit">Добавить в корзину</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Войдите</a>, чтобы добавить товар в корзину</p>
                <?php endif; ?>
            </div>
        </div>
        
        <h2>Технические характеристики</h2>
        <table class="specs-table">
            <tbody>
                <?php foreach ($specs as $spec): ?>
                    <tr>
                        <th><?php echo htmlspecialchars($spec['parameter']); ?></th>
                        <td><?php echo htmlspecialchars($spec['value']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

  </main>

    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>

    <div id="fullscreen-overlay" class="fullscreen-overlay">
        <span class="close-btn">×</span>
        <img id="fullscreen-image" src="" alt="Полноэкранное изображение">
        <button id="fullscreen-prev-btn" class="fullscreen-nav-btn">‹</button>
        <button id="fullscreen-next-btn" class="fullscreen-nav-btn">›</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('main-image');
            const thumbnails = document.querySelectorAll('.thumbnail');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const fullscreenOverlay = document.getElementById('fullscreen-overlay');
            const fullscreenImage = document.getElementById('fullscreen-image');
            const closeBtn = document.querySelector('.close-btn');
            const fullscreenPrevBtn = document.getElementById('fullscreen-prev-btn');
            const fullscreenNextBtn = document.getElementById('fullscreen-next-btn');
            let currentIndex = 0;

            function updateMainImage(index) {
                mainImage.src = thumbnails[index].src;
                mainImage.alt = thumbnails[index].alt;
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                thumbnails[index].classList.add('active');
                currentIndex = index;
            }

            function updateFullscreenImage(index) {
                fullscreenImage.src = thumbnails[index].src;
                fullscreenImage.alt = thumbnails[index].alt;
                currentIndex = index;
            }

            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', () => updateMainImage(index));
            });

            prevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                updateMainImage(currentIndex);
            });

            nextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % thumbnails.length;
                updateMainImage(currentIndex);
            });

            // Set initial active thumbnail
            updateMainImage(0);

            // Fullscreen functionality
            mainImage.addEventListener('click', () => {
                fullscreenImage.src = mainImage.src;
                fullscreenImage.alt = mainImage.alt;
                fullscreenOverlay.style.display = 'flex';
            });

            closeBtn.addEventListener('click', () => {
                fullscreenOverlay.style.display = 'none';
            });

            fullscreenOverlay.addEventListener('click', (e) => {
                if (e.target === fullscreenOverlay) {
                    fullscreenOverlay.style.display = 'none';
                }
            });

            // Add keyboard navigation for main and fullscreen images
            document.addEventListener('keydown', (e) => {
                if (fullscreenOverlay.style.display === 'flex') {
                    if (e.key === 'ArrowLeft') {
                        currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                        updateFullscreenImage(currentIndex);
                    } else if (e.key === 'ArrowRight') {
                        currentIndex = (currentIndex + 1) % thumbnails.length;
                        updateFullscreenImage(currentIndex);
                    } else if (e.key === 'Escape') {
                        fullscreenOverlay.style.display = 'none';
                    }
                } else {
                    if (e.key === 'ArrowLeft') {
                        currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                        updateMainImage(currentIndex);
                    } else if (e.key === 'ArrowRight') {
                        currentIndex = (currentIndex + 1) % thumbnails.length;
                        updateMainImage(currentIndex);
                    }
                }
            });

            // Add navigation buttons for fullscreen
            fullscreenPrevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                updateFullscreenImage(currentIndex);
            });

            fullscreenNextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % thumbnails.length;
                updateFullscreenImage(currentIndex);
            });
        });
    </script>
</body>
</html>