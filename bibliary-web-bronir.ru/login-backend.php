<?php
ob_start(); // Start output buffering

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Вы ввели не всю информацию, вернитесь назад и заполните все поля!";
        header("Location: login.php");
        exit;
    }

    include("admin-panel/api.php");

    // Убедитесь, что $db доступен и не закрыт
    if (!isset($db) || $db === null) {
        die("Ошибка: переменная \$db не определена или равна null.");
    }

    // Подготовленный запрос для получения пользователя
    $query = $db->prepare("SELECT * FROM authorised_users WHERE userlogin = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result) {
        $myrow = $result->fetch_assoc();

        if (empty($myrow['userpassword'])) {
            $_SESSION['error'] = "Извините, введённый вами login или пароль неверный.";
            header("Location: login.php");
            exit;
        } else {
            // Проверка пароля с использованием password_verify
            if (password_verify($password, $myrow['userpassword'])) {
                $_SESSION['userlogin'] = $myrow['userlogin'];
                $_SESSION['username'] = $myrow['username'];
                $_SESSION['userrole'] = $myrow['userrole'];

                // Перенаправление в зависимости от роли
                if ($_SESSION['userrole'] == 'admin') {
                    echo "Перенаправление на админ-панель...";
                    header("Location: admin-panel/admin-panel.php");
                    exit;
                } elseif ($_SESSION['userrole'] == 'user') {
                    echo "Перенаправление на страницу пользователя...";
                    header("Location: index.php");
                    exit;
                } elseif ($_SESSION['userrole'] == 'cooker') {
                    echo "Перенаправление на страницу повара...";
                    header("Location: cooker-index.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Извините, введённый вами login или пароль неверный.";
                header("Location: login.php");
                exit;
            }
        }
    } else {
        $_SESSION['error'] = "Ошибка при выполнении запроса к базе данных.";
        header("Location: login.php");
        exit;
    }

    // Закрытие соединения после завершения работы
    $db->close();
}

ob_end_flush(); // Flush the output buffer
?>
