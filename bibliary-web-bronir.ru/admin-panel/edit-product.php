<?php
ob_start(); // Start output buffering

// Include necessary files and check user authentication
include __DIR__ . '/../login-backend.php';
include __DIR__ . '/../admin-panel/api.php';

if (!isset($_SESSION['userlogin']) || $_SESSION['userrole'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Check if product_id exists in $_GET
$product_id = $_GET['product_id'] ?? null;
if ($product_id === null) {
    echo "Продукт не найден";
    exit;
}

try {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        throw new Exception("Продукт не найден");
    }

    // Fetch features and specs for the product
    $features = array();
    $specs = array();

    $sql = "SELECT feature FROM product_features WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $features[] = $row['feature'];
    }

    $sql = "SELECT parameter, value FROM product_specs WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $specs[] = $row;
    }

    // Rest of your script here...

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $category_id = $_POST['category_id'];
        $main_photo = $_FILES['main_photo'];
        $additional_photos = $_FILES['additional_photos'];
        $page_name = $_POST['page_name'];
        $page_description = $_POST['page_description'];
        $catalog_page_name = $_POST['catalog_page_name'];
        $features = $_POST['features'] ?? [];
        $specs = $_POST['specs'] ?? [];

        // Update product in database
        $sql = "UPDATE products SET name = ?, description = ?, category_id = ?, page_name = ?, page_description = ?, catalog_page_name = ? WHERE product_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ssssssi", $name, $description, $category_id, $page_name, $page_description, $catalog_page_name, $product_id);
        $stmt->execute();

        // Update photos if necessary
        $directory = $_SERVER['DOCUMENT_ROOT'] . '/images/';
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        if (!is_writable($directory)) {
            throw new Exception("Destination directory is not writable.");
        }

        // Handle main photo upload
if (isset($main_photo['name']) && $main_photo['error'] == UPLOAD_ERR_OK) {
    $main_photo_path = uploadFile($main_photo, $directory); // используем функцию для загрузки
    $sql = "UPDATE products SET main_photo = ? WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("si", $main_photo_path, $product_id);
    if (!$stmt->execute()) {
        throw new Exception("SQL execute error: " . $stmt->error);
    }
}

// Handle additional photos upload
if (isset($additional_photos['name']) && count($additional_photos['name']) > 0) {
    $additional_photos_paths = array();
    foreach ($additional_photos['tmp_name'] as $key => $tmp_name) {
        if (!isset($additional_photos['error'][$key]) && $additional_photos['error'][$key] == UPLOAD_ERR_OK) {
            $additional_photos_paths[] = uploadFile($additional_photos, $directory, $key);
        }

    }
    $additional_photos_json = json_encode($additional_photos_paths);
    $sql = "UPDATE products SET additional_photos = ? WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("si", $additional_photos_json, $product_id);
    if (!$stmt->execute()) {
        throw new Exception("SQL execute error: " . $stmt->error);
    }
}
        // Update features
        if (!empty($features)) {
            // Delete existing features
            $sql = "DELETE FROM product_features WHERE product_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            // Insert new features
            foreach ($features as $feature) {
                $sql = "INSERT INTO product_features (product_id, feature) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("is", $product_id, $feature);
                $stmt->execute();
            }
        }

        // Update specifications
        if (!empty($specs)) {
            // Delete existing specs
            $sql = "DELETE FROM product_specs WHERE product_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();

            // Insert new specs
            foreach ($specs as $spec) {
                if (isset($spec['parameter']) && isset($spec['value'])) {
                    $parameter = $spec['parameter'];
                    $value = $spec['value'];
                    $sql = "INSERT INTO product_specs (product_id, parameter, value) VALUES (?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("iss", $product_id, $parameter, $value);
                    $stmt->execute();
                } else {
                    throw new Exception("Specification parameter or value is missing.");
                }
            }
        }

        // Redirect to product list
        ob_end_clean(); // Clean the output buffer before redirecting
        header('Location: admin-panel.php?section=products');
        exit;
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
    error_log("Error: " . $e->getMessage(), 0);
}

function uploadFile($file, $directory, $key = null) {
    $filename = isset($key) ? $file['name'][$key] : $file['name'];
    $tmp_name = isset($key) ? $file['tmp_name'][$key] : $file['tmp_name'];

    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    $uploadfile = $directory . $filename;

    if (!move_uploaded_file($tmp_name, $uploadfile)) {
        throw new Exception("Failed to move uploaded file.");
    }
    return $filename;
}

function getUploadError($error_code) {
    if (!is_int($error_code)) {
        throw new Exception("Error code must be an integer.");
    }
    $upload_errors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
    );
    return $upload_errors[$error_code] ?? 'Unknown upload error';
}
?>

<h2>Редактировать Продукт</h2>
<form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
    <label for="name">Название:</label>
    <input type="text" id="name" name="name" value="<?php echo $product['name']; ?>"><br><br>
    <label for="catalog_page_name">Имя страницы в каталоге:</label>
    <input type="text" id="catalog_page_name" name="catalog_page_name" value="<?php echo $product['catalog_page_name']; ?>"><br><br>
    <label for="description">Описание:</label>
    <textarea id="description" name="description"><?php echo $product['description']; ?></textarea><br><br>
    <label for="page_name">Страница (имя):</label>
    <input type="text" id="page_name" name="page_name" value="<?php echo $product['page_name']; ?>"><br><br>
    <label for="page_description">Описание страницы:</label>
    <textarea id="page_description" name="page_description"><?php echo $product['page_description']; ?></textarea><br><br>
    <label for="category_id">Категория:</label>
    <select id="category_id" name="category_id">
        <?php
        $sql = "SELECT * FROM categories";
        $result = $db->query($sql);

        while ($row = $result->fetch_assoc()) {
            ?>
            <option value="<?php echo $row['category_id']; ?>" <?php if ($row['category_id'] == $product['category_id']) echo 'selected'; ?>><?php echo $row['category_name']; ?></option>
            <?php
        }
        ?>
    </select><br><br>
    <label for="main_photo">Главное Изображение:</label>
    <input type="file" id="main_photo" name="main_photo"><br><br>
    <label for="additional_photos">Дополнительные Изображения:</label>
    <input type="file" id="additional_photos" name="additional_photos[]" multiple><br><br>

    <label for="features">Особенности:</label>
    <div id="features-container">
        <?php foreach ($features as $feature) { ?>
            <input type="text" name="features[]" value="<?php echo $feature; ?>"><br><br>
        <?php } ?>
        <button id="add-feature-btn">Добавить особенность</button>
    </div>

    <label for="specs">Технические характеристики:</label>
    <div id="specs-container">
        <?php foreach ($specs as $spec) { ?>
            <div class="spec-row">
                <input type="text" name="specs[parameter]" value="<?php echo $spec['parameter']; ?>">
                <input type="text" name="specs[value]" value="<?php echo $spec['value']; ?>">
            </div>
        <?php } ?>
        <button id="add-spec-btn">Добавить характеристику</button>
    </div>

    <button type="submit">Сохранить Изменения</button>
</form>

<script>
    document.getElementById('add-feature-btn').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'features[]';
        input.placeholder = 'Особенность';
        container.appendChild(input);
        container.appendChild(document.createElement('br'));
        container.appendChild(document.createElement('br'));
    });

    document.getElementById('add-spec-btn').addEventListener('click', function() {
        const container = document.getElementById('specs-container');
        const row = document.createElement('div');
        row.className = 'spec-row';
        const parameterInput = document.createElement('input');
        parameterInput.type = 'text';
        parameterInput.name = 'specs[parameter]';
        parameterInput.placeholder = 'Параметр';
        const valueInput = document.createElement('input');
        valueInput.type = 'text';
        valueInput.name = 'specs[value]';
        valueInput.placeholder = 'Значение';
        row.appendChild(parameterInput);
        row.appendChild(valueInput);
        container.appendChild(row);
        container.appendChild(document.createElement('br'));
        container.appendChild(document.createElement('br'));
    });
</script>

<?php
ob_end_flush(); // Flush the output buffer
?>
