<?php
ob_start(); // Start output buffering

// Include necessary files and check user authentication
include __DIR__ . '/../login-backend.php';

if (!isset($_SESSION['userlogin']) || $_SESSION['userrole'] != 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['section'])) {
    $section = $_GET['section'];
} else {
    $section = 'products';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" type="text/css" href="css/admin-panel.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>

<header>
    <p><img src="images/LOGO.png" width="208" height="52" alt="Tenzor-RT Logo"></p>
    
</header>

<nav>
    <ul>
        <li><a href="index.php/">Главная</a></li>
        <li><a href="https://bibliary-web-bronir.ru/izdeliaKatalog.php">Изделия</a></li>
        <li><a href="?section=products">Управление Товарами</a></li>
        <li><a href="out.php">Выйти</a></li>
    </ul>
</nav>

<main>
    <div class="admin-panel">
        <h1>Административная Панель</h1>
        <nav>
            <ul>
                <li><a href="?section=products">Список Продуктов</a></li>
                <li><a href="?section=add-product">Добавить Новый Продукт</a></li>
            </ul>
        </nav>

        <!-- Include the relevant section -->
        <?php
        switch ($section) {
            case 'products':
                include 'products.php';
                break;
            case 'add-product':
                include 'add-product.php';
                break;
            case 'edit-product':
                include 'edit-product.php';
                break;
            default:
                include 'products.php';
                break;
        }
        ?>
    </div>
</main>

<!-- Menu for deleting/editing records -->
<div class="admin-actions">
    <?php
    // Include the menu for deleting/editing records here
    if ($section == 'products') {
        include 'products-actions.php'; // Create this file to contain the actions menu
    } elseif ($section == 'edit-product') {
        include 'edit-product-actions.php'; // Create this file to contain the actions menu
    }
    ?>
</div>

<footer>
    <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/admin-panel.js"></script>

</body>
</html>

<?php
ob_end_flush(); // Flush the output buffer
?>
