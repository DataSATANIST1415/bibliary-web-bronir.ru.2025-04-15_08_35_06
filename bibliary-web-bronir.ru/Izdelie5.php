<!DOCTYPE html>
<html lang="en">
<head>
    <title>ООО "Тензор-РТ" - <?php echo htmlspecialchars($product['name'] ?? 'Продукт не найден'); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/IzdelieAccumBattery.css">
</head>
<body>
    <header>
        <p><img src="images/LOGO.png" width="208" height="52" alt="Tenzor-RT Logo"></p>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="IzdeliaKatalog.php">Изделия</a></li>
        </ul>
    </nav>
    
    <main>
        <?php
        $apiFilePath = __DIR__ . '/admin-panel/api.php';
        if (file_exists($apiFilePath)) {
            require_once $apiFilePath;
        } else {
            echo "Файл api.php не найден.";
            exit;
        }

        // Установка ID продукта для "Малый термоконтейнер"
        $productId = 5;

        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if (!$product) {
            echo "<h1>Продукт не найден.</h1>";
            echo "<p>Пожалуйста, проверьте URL или обратитесь к администратору.</p>";
            exit;
        }
        ?>

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
                            ?>
                            <img class="thumbnail" src="/images/<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="80" height="60">
                            <?php
                        }
                    }
                    ?>
                </div>
                <div id="fullscreen-overlay" class="fullscreen-overlay">
                    <span class="close-btn">×</span>
                    <img id="fullscreen-image" src="" alt="Полноэкранное изображение <?php echo htmlspecialchars($product['name']); ?>">
                    <button id="fullscreen-prev-btn" class="fullscreen-nav-btn">‹</button>
                    <button id="fullscreen-next-btn" class="fullscreen-nav-btn">›</button>
                </div>
            </div>
            <div class="product-info">
                <h2>Описание</h2>
                <p><?php echo htmlspecialchars($product['page_description']); ?></p>
                <h3>Ключевые особенности:</h3>
                <ul>
                    <?php
                    $sql = "SELECT feature FROM product_features WHERE product_id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("i", $product['product_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($feature = $result->fetch_assoc()) {
                        ?>
                        <li><?php echo htmlspecialchars($feature['feature']); ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
        
        <h2>Технические характеристики</h2>
        <table class="specs-table">
            <tbody>
                <?php
                $sql = "SELECT parameter, value FROM product_specs WHERE product_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $product['product_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($spec = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <th><?php echo htmlspecialchars($spec['parameter']); ?></th>
                        <td><?php echo htmlspecialchars($spec['value']); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </main>
    
    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>

    <script>
        // JavaScript для управления слайдером и полноэкранным режимом
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
</body></html>
