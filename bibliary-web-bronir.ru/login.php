<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход на сайт - ООО "Тензор-РТ"</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <header>
        <p><img src="images/LOGO.png" width="208" height="52" alt="Tenzor-RT Logo"></p>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="IzdeliaKatalog.php">Изделия</a></li>
            <li><a href="login.php">Войти</a></li>
        </ul>
    </nav>
    
    <main>
        <div class="login-container">
            <h2>Вход в На сайт</h2>
            <form action="login-backend.php" method="post" novalidate>
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Введите имя пользователя" required>
                    <div class="invalid-feedback">
                        Пожалуйста, введите ваше имя пользователя.
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Введите пароль" required>
                    <div class="invalid-feedback">
                        Пожалуйста, введите ваш пароль.
                    </div>
                </div>
                <button type="submit" class="btn-primary">Войти</button>
            </form>
            <p id="error" ><?php if(isset($_SESSION['error'])) { echo $_SESSION['error']; unset($_SESSION['error']); } ?></p>
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </main>
    
    <footer>
        <p>© 2024 ООО "Тензор-РТ". Все права защищены.</p>
    </footer>

</body>
</html>