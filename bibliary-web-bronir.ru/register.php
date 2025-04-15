<?php
ob_start();
session_start();

include 'config.php';
include 'admin-panel/api.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $userlogin = $_POST['userlogin'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают";
    } else {
        // Check if userlogin already exists
        $check_stmt = $db->prepare("SELECT userlogin FROM authorised_users WHERE userlogin = ?");
        $check_stmt->bind_param("s", $userlogin);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Этот логин уже занят. Пожалуйста, выберите другой.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO authorised_users (username, userlogin, userpassword, userrole) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $userlogin, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Регистрация успешна. Теперь вы можете войти.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Ошибка при регистрации: " . $db->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ООО "Тензор-РТ"</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header>
        <img src="images/LOGO.png" width="220" height="52" alt="Tenzor-RT Logo">
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="IzdeliaKatalog.php">Изделия</a></li>
            <li><a href="login.php">Войти</a></li>
        </ul>
    </nav>
    
    <main>
        <h1>Регистрация</h1>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <form action="" method="post">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required>

            <label for="userlogin">Логин:</label>
            <input type="text" id="userlogin" name="userlogin" required>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Подтвердите пароль:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Зарегистрироваться</button>
        </form>
    </main>

    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>
</body>
</html>
