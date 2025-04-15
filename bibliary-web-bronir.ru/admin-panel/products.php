<?php
// Include necessary files and check user authentication
include __DIR__ . '/../login-backend.php';
include __DIR__ . '/../admin-panel/api.php'; // Include api.php to define $db

if (!isset($_SESSION['userlogin']) || $_SESSION['userrole'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all products from the database
$sql = "SELECT * FROM products";
$result = $db->query($sql);

?>

<h2>Список Продуктов</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Категория</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo $row['product_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['category_id']; ?></td>
                <td>
                    <a href="?section=edit-product&product_id=<?php echo $row['product_id']; ?>">Редактировать</a>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="delete">Удалить</button>
                    </form>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
if (isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];
    $deleteFeaturesStmt = $db->prepare("DELETE FROM product_features WHERE product_id = ?");
 $deleteFeaturesStmt->bind_param("i", $product_id);
   $deleteFeaturesStmt->execute();
   $deleteSpecsStmt = $db->prepare("DELETE FROM product_specs WHERE product_id = ?");
 $deleteSpecsStmt->bind_param("i", $product_id);
   $deleteSpecsStmt->execute();
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    header('Location: admin-panel.php?section=products');
    exit;
}
?>
