<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE `логин` = ? AND `пароль` = ? AND `статус` = 'Активен'");
        $stmt->bind_param("ss", $login, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['логин'] = $user['логин'];
            $_SESSION['роль'] = $user['роль'];
            $_SESSION['имя'] = $user['имя'];
            $_SESSION['фамилия'] = $user['фамилия'];
            $_SESSION['email'] = $user['email'];
            
            // Если это клиент, найдём его client_id
            if ($user['роль'] === 'Клиент') {
                $stmt2 = $conn->prepare("SELECT client_id FROM clients WHERE email = ?");
                $stmt2->bind_param("s", $user['email']);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($client = $result2->fetch_assoc()) {
                    $_SESSION['client_id'] = $client['client_id'];
                }
                $stmt2->close();
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль, либо аккаунт заблокирован.';
        }
        $stmt->close();
    } else {
        $error = 'Заполните все поля.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АвтоСервис – Вход</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-wrench"></i>
                <h1>АвтоСервис</h1>
                <p>Вход в систему управления автосервисом</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" class="login-form">
                <div class="form-group">
                    <label for="login"><i class="fas fa-user"></i> Логин</label>
                    <input type="text" id="login" name="login" placeholder="Введите логин" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите пароль" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </button>
            </form>
            <div class="login-footer">
                <p>Демо-доступ:<br>
                Администратор: admin / admin123<br>
                Оператор: operator1 / op123<br>
                Механик: mechanic1 / mech123<br>
                Клиент: client_ivanov / 123456</p>
            </div>
        </div>
    </div>
</body>
</html>