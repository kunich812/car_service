<?php
// Универсальное подключение к БД, работающее на любом компьютере

// Пробуем разные варианты подключения (admin/admin - в приоритете)
$configs = [
    // Сначала пробуем admin/admin (для этого компьютера)
    ['host' => '127.0.0.1', 'user' => 'admin', 'password' => 'admin', 'database' => 'carservice_bd'],
    ['host' => 'localhost', 'user' => 'admin', 'password' => 'admin', 'database' => 'carservice_bd'],
    
    // Для XAMPP/OpenServer (стандартные настройки)
    ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'database' => 'carservice_bd'],
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => 'carservice_bd'],
    
    // Если пароль root
    ['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'carservice_bd'],
    ['host' => 'localhost', 'user' => 'root', 'password' => 'root', 'database' => 'carservice_bd'],
    
    // Для OpenServer с mysql/mysql
    ['host' => '127.0.0.1', 'user' => 'mysql', 'password' => 'mysql', 'database' => 'carservice_bd'],
    ['host' => 'localhost', 'user' => 'mysql', 'password' => 'mysql', 'database' => 'carservice_bd'],
];

$conn = null;
$connection_error = null;

foreach ($configs as $config) {
    $testConn = @new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    
    if (!$testConn->connect_error) {
        $conn = $testConn;
        break;
    } else {
        $connection_error = $testConn->connect_error;
    }
}

// Если ни одно подключение не удалось — показываем понятную ошибку
if (!$conn) {
    die("
    <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9;'>
        <h2 style='color: #d32f2f;'>❌ Ошибка подключения к базе данных</h2>
        <p><strong>Последняя ошибка:</strong> " . htmlspecialchars($connection_error) . "</p>
        <hr>
        <h3>🔧 Возможные решения:</h3>
        <ol>
            <li>Убедитесь, что MySQL/MariaDB запущен</li>
            <li>Проверьте, что база данных <strong>carservice_bd</strong> существует</li>
            <li>Импортируйте файл <strong>autoservice.sql</strong> в phpMyAdmin</li>
        </ol>
    </div>
    ");
}

$conn->set_charset("utf8mb4");
?>