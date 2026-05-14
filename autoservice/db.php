<?php
$host = '127.0.0.1';
$user = 'admin';        // ваш пользователь MySQL
$password = 'admin';    // ваш пароль MySQL
$database = 'autoservice_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>