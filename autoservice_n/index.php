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
    
   <script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    })();
</script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        [data-theme="dark"] .login-page {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
        }
        
        .bg-icons {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-icons i {
            position: absolute;
            display: inline-block;
            animation: floatIcon 20s ease-in-out infinite;
            will-change: transform;
        }
        
        .bg-icons i.fa-car,
        .bg-icons i.fa-truck,
        .bg-icons i.fa-motorcycle {
            font-size: 80px;
            color: rgba(255, 255, 255, 0.25);
        }
        
        .bg-icons i.fa-wrench,
        .bg-icons i.fa-tools,
        .bg-icons i.fa-screwdriver,
        .bg-icons i.fa-oil-can {
            font-size: 45px;
            color: rgba(255, 255, 255, 0.2);
        }
        
        .bg-icons i.fa-cogs,
        .bg-icons i.fa-gear,
        .bg-icons i.fa-battery-full,
        .bg-icons i.fa-tachometer-alt,
        .bg-icons i.fa-filter,
        .bg-icons i.fa-fan {
            font-size: 38px;
            color: rgba(255, 255, 255, 0.2);
        }
        
        .bg-icons i.fa-microchip,
        .bg-icons i.fa-industry,
        .bg-icons i.fa-cog {
            font-size: 55px;
            color: rgba(255, 255, 255, 0.22);
        }
        
        [data-theme="dark"] .bg-icons i.fa-car,
        [data-theme="dark"] .bg-icons i.fa-truck,
        [data-theme="dark"] .bg-icons i.fa-motorcycle {
            color: rgba(255, 255, 255, 0.12);
        }
        
        [data-theme="dark"] .bg-icons i {
            color: rgba(255, 255, 255, 0.1);
        }
        
        .bg-icons i:nth-child(1) { top: 5%; left: 3%; animation-delay: 0s; animation-duration: 18s; }
        .bg-icons i:nth-child(2) { top: 15%; right: 5%; animation-delay: 2s; animation-duration: 22s; }
        .bg-icons i:nth-child(3) { bottom: 10%; left: 8%; animation-delay: 4s; animation-duration: 20s; }
        .bg-icons i:nth-child(4) { bottom: 20%; right: 10%; animation-delay: 1s; animation-duration: 25s; }
        .bg-icons i:nth-child(5) { top: 30%; left: 15%; animation-delay: 3s; animation-duration: 19s; }
        .bg-icons i:nth-child(6) { top: 60%; right: 15%; animation-delay: 5s; animation-duration: 23s; }
        .bg-icons i:nth-child(7) { bottom: 40%; left: 20%; animation-delay: 0.5s; animation-duration: 21s; }
        .bg-icons i:nth-child(8) { top: 75%; right: 25%; animation-delay: 2.5s; animation-duration: 17s; }
        .bg-icons i:nth-child(9) { top: 10%; right: 20%; animation-delay: 3.5s; animation-duration: 24s; }
        .bg-icons i:nth-child(10) { bottom: 5%; left: 25%; animation-delay: 1.5s; animation-duration: 20s; }
        .bg-icons i:nth-child(11) { top: 45%; left: 5%; animation-delay: 4.5s; animation-duration: 16s; }
        .bg-icons i:nth-child(12) { top: 85%; right: 8%; animation-delay: 0.8s; animation-duration: 26s; }
        .bg-icons i:nth-child(13) { top: 20%; left: 30%; animation-delay: 2.2s; animation-duration: 19s; }
        .bg-icons i:nth-child(14) { bottom: 30%; right: 30%; animation-delay: 3.8s; animation-duration: 22s; }
        .bg-icons i:nth-child(15) { top: 50%; left: 85%; animation-delay: 1.2s; animation-duration: 21s; }
        .bg-icons i:nth-child(16) { top: 70%; left: 12%; animation-delay: 2.8s; animation-duration: 18s; }
        .bg-icons i:nth-child(17) { bottom: 60%; right: 18%; animation-delay: 4.2s; animation-duration: 24s; }
        .bg-icons i:nth-child(18) { top: 8%; left: 45%; animation-delay: 1.8s; animation-duration: 20s; }
        
        @keyframes floatIcon {
            0% { transform: translate(0px, 0px) rotate(0deg); }
            20% { transform: translate(-15px, -20px) rotate(-5deg); }
            40% { transform: translate(10px, -35px) rotate(3deg); }
            60% { transform: translate(25px, -15px) rotate(8deg); }
            80% { transform: translate(-5px, -25px) rotate(-3deg); }
            100% { transform: translate(0px, 0px) rotate(0deg); }
        }
        
        .login-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at center, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 100%);
            pointer-events: none;
            z-index: 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            min-height: 520px;
            display: flex;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .login-card {
            background: var(--bg-white);
            border-radius: var(--r-xl);
            padding: 40px 36px;
            box-shadow: var(--shadow-xl);
            width: 100%;
            transition: all var(--transition-base);
        }
        
        .login-card:hover {
            transform: translateY(-3px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header i {
            font-size: 48px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            display: inline-block;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text-heading), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-header p {
            color: var(--text-muted);
            font-size: 13px;
        }
        
        .error-fixed {
            min-height: 65px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 10px 14px;
            border-radius: var(--r-md);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 13px;
            color: var(--text-muted);
        }
        
        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            font-size: 14px;
            background: var(--bg-white);
            color: var(--text-body);
            transition: all var(--transition-fast);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--r-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-top: 8px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(99, 102, 241, 0.35);
        }
        
        .demo-card {
            margin-top: 24px;
            padding: 14px;
            background: var(--bg-subtle);
            border-radius: var(--r-md);
            border: 1px solid var(--border);
        }
        
        .demo-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .demo-items {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            font-size: 11px;
        }
        
        .demo-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 8px;
            background: var(--bg-white);
            border-radius: var(--r-sm);
        }
        
        .demo-item span:first-child {
            color: var(--text-muted);
        }
        
        .demo-item span:last-child {
            color: var(--primary);
            font-family: monospace;
        }
        
        .theme-toggle-login {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--r-full);
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
            transition: all var(--transition-base);
        }
        
        .theme-toggle-login:hover {
            background: var(--bg-subtle);
            transform: scale(1.02);
        }
        
        @media (max-width: 768px) {
            .bg-icons i { transform: scale(0.7); }
        }
        
        @media (max-width: 480px) {
            .login-card { padding: 28px 24px; }
            .login-container { min-height: 480px; }
            .demo-items { grid-template-columns: 1fr; }
            .bg-icons i { transform: scale(0.5); }
        }
    </style>
</head>
<body class="login-page">
    
    <div class="bg-icons">
        <i class="fas fa-car"></i>
        <i class="fas fa-truck"></i>
        <i class="fas fa-motorcycle"></i>
        <i class="fas fa-car"></i>
        <i class="fas fa-wrench"></i>
        <i class="fas fa-tools"></i>
        <i class="fas fa-screwdriver"></i>
        <i class="fas fa-oil-can"></i>
        <i class="fas fa-wrench"></i>
        <i class="fas fa-cogs"></i>
        <i class="fas fa-gear"></i>
        <i class="fas fa-battery-full"></i>
        <i class="fas fa-tachometer-alt"></i>
        <i class="fas fa-filter"></i>
        <i class="fas fa-fan"></i>
        <i class="fas fa-microchip"></i>
        <i class="fas fa-industry"></i>
        <i class="fas fa-cog"></i>
        <i class="fas fa-microchip"></i>
    </div>
    
    <button class="theme-toggle-login" onclick="toggleTheme()">
        <i class="fas fa-sun icon-sun"></i>
        <i class="fas fa-moon icon-moon"></i>
        <span>Тема</span>
    </button>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-wrench"></i>
                <h1>АвтоСервис</h1>
                <p>Профессиональная система управления</p>
            </div>
            
            <div class="error-fixed">
                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post">
                <div class="form-group">
                    <label for="login"><i class="fas fa-user"></i> Логин</label>
                    <input type="text" id="login" name="login" placeholder="Введите логин" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                    <input type="password" id="password" name="password" placeholder="Введите пароль" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Войти в систему
                </button>
            </form>
            
            <div class="demo-card">
                <div class="demo-title">
                    <i class="fas fa-info-circle"></i> Демо-доступ
                </div>
                <div class="demo-items">
                    <div class="demo-item"><span>Администратор</span><span>admin / admin123</span></div>
                    <div class="demo-item"><span>Оператор</span><span>operator1 / op123</span></div>
                    <div class="demo-item"><span>Механик</span><span>mechanic1 / mech123</span></div>
                    <div class="demo-item"><span>Клиент</span><span>client_ivanov / 123456</span></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            
            if (currentTheme === 'dark') {
                html.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>