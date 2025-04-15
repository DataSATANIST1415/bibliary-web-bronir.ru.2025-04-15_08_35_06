<?php
ob_start();

include __DIR__ . '/../login-backend.php';
include __DIR__ . '/api.php';

if (!isset($_SESSION['userlogin']) || $_SESSION['userrole'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Переносим объявления функций В начало файла
function handleFileUpload($field, $product_id, $db, $isMultiple = false) {
    if (empty($_FILES[$field]['name'])) return;

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (!is_writable($uploadDir)) {
        throw new Exception("Директория для загрузки недоступна.");
    }

    if ($isMultiple) {
        $filePaths = [];
        foreach ($_FILES[$field]['tmp_name'] as $key => $tmp_name) {
            $filePaths[] = uploadFile($_FILES[$field], $uploadDir, $key);
        }
        $stmt = $db->prepare("UPDATE products SET additional_photos = ? WHERE product_id = ?");
        $stmt->bind_param("si", json_encode($filePaths), $product_id);
    } else {
        $filePath = uploadFile($_FILES[$field], $uploadDir);
        $stmt = $db->prepare("UPDATE products SET main_photo = ? WHERE product_id = ?");
        $stmt->bind_param("si", basename($filePath), $product_id);
    }
    $stmt->execute();
}

function uploadFile($file, $directory, $key = null) {
    $filename = isset($key) ? $file['name'][$key] : $file['name'];
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    $targetPath = $directory . $filename;

    $tmp_name = isset($key) ? $file['tmp_name'][$key] : $file['tmp_name'];
    if (!move_uploaded_file($tmp_name, $targetPath)) {
        throw new Exception("Ошибка загрузки файла.");
    }
    return $filename;
}

function insertProductFeatures($features, $product_id, $db) {
    $stmt = $db->prepare("INSERT INTO product_features (product_id, feature) VALUES (?, ?)");
    foreach ($features as $feature) {
        if (!empty(trim($feature))) {
            $stmt->bind_param("is", $product_id, trim($feature));
            $stmt->execute();
        }
    }
}

function insertProductSpecs($specs, $product_id, $db) {
    $stmt = $db->prepare("INSERT INTO product_specs (product_id, parameter, value) VALUES (?, ?, ?)");
    foreach ($specs as $spec) {
        if (!empty($spec['parameter']) && !empty($spec['value'])) {
            $stmt->bind_param("iss", 
                $product_id, 
                htmlspecialchars(trim($spec['parameter'])), 
                htmlspecialchars(trim($spec['value']))
            );
            $stmt->execute();
        }
    }
}

// Основная логика обработки POST-запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->begin_transaction();

        $name = htmlspecialchars(trim($_POST['name']));
        $description = htmlspecialchars(trim($_POST['description']));
        $category_id = (int)$_POST['category_id'];
        $page_name = htmlspecialchars(trim($_POST['page_name']));
        $page_description = htmlspecialchars(trim($_POST['page_description']));
        $catalog_page_name = htmlspecialchars(trim($_POST['catalog_page_name']));
        $cost = (float)$_POST['cost'];
        $features = $_POST['features'] ?? [];
        $specs = $_POST['specs'] ?? [];

        $stmt = $db->prepare(
            "INSERT INTO products 
            (name, catalog_page_name, description, category_id, page_name, page_description, cost) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sssissd",
            $name,
            $catalog_page_name,
            $description,
            $category_id,
            $page_name,
            $page_description,
            $cost
        );
        
        $stmt->execute();
        $product_id = $stmt->insert_id;

        handleFileUpload('main_photo', $product_id, $db);
        handleFileUpload('additional_photos', $product_id, $db, true);

        insertProductFeatures($features, $product_id, $db);
        insertProductSpecs($specs, $product_id, $db);

        $db->commit();
        header('Location: admin-panel.php?section=products');
        exit;
    } catch (Exception $e) {
        $db->rollback();
        echo "Ошибка: " . $e->getMessage();
        error_log("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
</head>
<body>
    <h2>Добавить новый товар</h2>
    <form method="post" enctype="multipart/form-data">
        <!-- Обязательные поля -->
        <label>Название: <input type="text" name="name" required></label><br>
        <label>Имя в каталоге: <input type="text" name="catalog_page_name" required></label><br>
        <label>Описание: <textarea name="description" required></textarea></label><br>
        <label>Страница: <input type="text" name="page_name" required></label><br>
        <label>Описание страницы: <textarea name="page_description" required></textarea></label><br>
        <label>Категория: 
            <select name="category_id" required>
                <?php
                $result = $db->query("SELECT * FROM categories");
                while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Стоимость: <input type="number" name="cost" step="0.01" min="0" required></label><br>
        
        <!-- Загрузка файлов -->
        <label>Главное фото: <input type="file" name="main_photo" required></label><br>
        <label>Дополнительные фото: <input type="file" name="additional_photos[]" multiple></label><br>
        
        <!-- Особенности -->
        <fieldset>
            <legend>Особенности</legend>
            <div id="features">
                <input type="text" name="features[]" placeholder="Особенность">
            </div>
            <button type="button" onclick="addFeatureField()">+ Добавить</button>
        </fieldset>
        
        <!-- Характеристики -->
        <fieldset>
            <legend>Характеристики</legend>
            <div id="specs">
                <div class="spec">
                    <input type="text" name="specs[0][parameter]" placeholder="Параметр">
                    <input type="text" name="specs[0][value]" placeholder="Значение">
                </div>
            </div>
            <button type="button" onclick="addSpecField()">+ Добавить</button>
        </fieldset>
        
        <button type="submit">Сохранить</button>
    </form>

    <script>
        let featureCount = 1;
        function addFeatureField() {
            const div = document.createElement('div');
            div.innerHTML = `<input type="text" name="features[]" placeholder="Особенность ${featureCount++}">`;
            document.getElementById('features').appendChild(div);
        }

        let specCount = 1;
        function addSpecField() {
            const div = document.createElement('div');
            div.className = 'spec';
            div.innerHTML = `
                <input type="text" name="specs[${specCount}][parameter]" placeholder="Параметр">
                <input type="text" name="specs[${specCount}][value]" placeholder="Значение">
            `;
            document.getElementById('specs').appendChild(div);
            specCount++;
        }
    </script>
</body>
</html>

<?php ob_end_flush(); ?>
