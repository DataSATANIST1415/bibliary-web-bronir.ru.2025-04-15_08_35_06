<?php
// Получаем абсолютный путь к текущей директории
$currentDir = __DIR__;
// Строим путь к config.php, который находится на уровень выше
$configPath = dirname($currentDir) . '/config.php';

if (file_exists($configPath)) {
    require_once $configPath;
} else {
    echo "Файл config.php не найден.";
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Установка соединения
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->set_charset('utf8mb4');

// Функция для выполнения SQL запросов
function executeQuery($sql, $params = null) {
    global $db;
    if ($params) {
        $stmt = $db->prepare($sql);
        $stmt->bind_param(...$params);
        $success = $stmt->execute();
        if ($success) {
            $result = $stmt->get_result();
            return $result !== false ? $result : true;
        } else {
            error_log("SQL Error: " . $stmt->error);
            return false;
        }
    } else {
        $result = $db->query($sql);
        if (!$result) {
            error_log("SQL Error: " . $db->error);
            return false;
        }
        return $result;
    }
}


// Функция для добавления нового продукта
function addProduct($data) {
    global $db;
    $sql = "INSERT INTO products (name, catalog_page_name, description, category_id, main_photo, additional_photos, page_name, page_description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssssssss", $data['name'], $data['catalog_page_name'], $data['description'],
                      $data['category_id'], $data['main_photo'], $data['additional_photos'],
                      $data['page_name'], $data['page_description']);
    $stmt->execute();
    return $stmt->insert_id;
}

// Функция для обновления продукта
function updateProduct($data) {
    global $db;
    $sql = "UPDATE products SET name = ?, catalog_page_name = ?, description = ?, category_id = ?,
            main_photo = ?, additional_photos = ?, page_name = ?, page_description = ?
            WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssssssssi", $data['name'], $data['catalog_page_name'], $data['description'],
                      $data['category_id'], $data['main_photo'], $data['additional_photos'],
                      $data['page_name'], $data['page_description'], $data['product_id']);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Функция для удаления продукта
function deleteProduct($productId) {
    global $db;
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Функция для добавления особенностей продукта
function addFeature($productId, $feature) {
    global $db;
    $sql = "INSERT INTO product_features (product_id, feature) VALUES (?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $productId, $feature);
    $stmt->execute();
    return $stmt->insert_id;
}

// Функция для обновления особенностей продукта
function updateFeature($featureId, $feature) {
    global $db;
    $sql = "UPDATE product_features SET feature = ? WHERE feature_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("si", $feature, $featureId);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Функция для удаления особенностей продукта
function deleteFeature($featureId) {
    global $db;
    $sql = "DELETE FROM product_features WHERE feature_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $featureId);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Функция для добавления технических характеристик продукта
function addSpec($productId, $parameter, $value) {
    global $db;
    $sql = "INSERT INTO product_specs (product_id, parameter, value) VALUES (?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("iss", $productId, $parameter, $value);
    $stmt->execute();
    return $stmt->insert_id;
}

// Функция для обновления технических характеристик продукта
function updateSpec($specId, $parameter, $value) {
    global $db;
    $sql = "UPDATE product_specs SET parameter = ?, value = ? WHERE spec_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ssi", $parameter, $value, $specId);
    $stmt->execute();
    return $stmt->affected_rows;
}

// Функция для удаления технических характеристик продукта
function deleteSpec($specId) {
    global $db;
    $sql = "DELETE FROM product_specs WHERE spec_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $specId);
    $stmt->execute();
    return $stmt->affected_rows;
}

//функция товар пользователь заказ получить фейк
function getUserOrders($userlogin) {
    global $db;
    $sql = "SELECT o.order_id, o.order_date, o.status, 
                   SUM(oi.quantity * oi.price) as total_amount,
                   GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ' шт.)') SEPARATOR ', ') AS products
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.customer_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $userlogin);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}




// Обработка запросов
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'addProduct':
                $data = json_decode($_POST['data'], true);
                $productId = addProduct($data);
                echo json_encode(['success' => true, 'productId' => $productId]);
                break;
            case 'updateProduct':
                $data = json_decode($_POST['data'], true);
                $rowsAffected = updateProduct($data);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            case 'deleteProduct':
                $productId = $_POST['productId'];
                $rowsAffected = deleteProduct($productId);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            case 'addFeature':
                $productId = $_POST['productId'];
                $feature = $_POST['feature'];
                $featureId = addFeature($productId, $feature);
                echo json_encode(['success' => true, 'featureId' => $featureId]);
                break;
            case 'updateFeature':
                $featureId = $_POST['featureId'];
                $feature = $_POST['feature'];
                $rowsAffected = updateFeature($featureId, $feature);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            case 'deleteFeature':
                $featureId = $_POST['featureId'];
                $rowsAffected = deleteFeature($featureId);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            case 'addSpec':
                $productId = $_POST['productId'];
                $parameter = $_POST['parameter'];
                $value = $_POST['value'];
                $specId = addSpec($productId, $parameter, $value);
                echo json_encode(['success' => true, 'specId' => $specId]);
                break;
            case 'updateSpec':
                $specId = $_POST['specId'];
                $parameter = $_POST['parameter'];
                $value = $_POST['value'];
                $rowsAffected = updateSpec($specId, $parameter, $value);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            case 'deleteSpec':
                $specId = $_POST['specId'];
                $rowsAffected = deleteSpec($specId);
                echo json_encode(['success' => true, 'rowsAffected' => $rowsAffected]);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'getProducts':
                $sql = "SELECT product_id, name, description FROM products";
                $result = executeQuery($sql);
                if ($result) {
                    $products = array();
                    while ($row = $result->fetch_assoc()) {
                        $products[] = $row;
                    }
                    echo json_encode($products);
                } else {
                    echo json_encode([]);
                }
                break;
            case 'getProduct':
                $productId = $_GET['productId'];
                $sql = "SELECT * FROM products WHERE product_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    echo json_encode($row);
                } else {
                    echo json_encode(null);
                }
                break;
            case 'getFeatures':
                $productId = $_GET['productId'];
                $sql = "SELECT feature FROM product_features WHERE product_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                $features = array();
                while ($row = $result->fetch_assoc()) {
                    $features[] = $row['feature'];
                }
                echo json_encode($features);
                break;
            case 'getSpecs':
                $productId = $_GET['productId'];
                $sql = "SELECT parameter, value FROM product_specs WHERE product_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                $specs = array();
                while ($row = $result->fetch_assoc()) {
                    $specs[] = $row;
                }
                echo json_encode($specs);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
        }
    }
}
?>
