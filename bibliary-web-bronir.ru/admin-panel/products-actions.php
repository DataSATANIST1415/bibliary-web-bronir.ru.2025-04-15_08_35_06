<table>
    <tbody>
        <?php
        while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
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
