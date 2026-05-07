<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Подключение к БД
$host = 'localhost';
$dbname = 'car_service';
$user = 'admin';  // ваш пользователь
$pass = '';        // ваш пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
    exit;
}

// Получаем метод и действие
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? null;

// Получаем тело запроса для POST/PUT
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Роутинг
switch ($method) {
    case 'GET':
        handleGet($pdo, $table, $id);
        break;
    case 'POST':
        handlePost($pdo, $table, $input);
        break;
    case 'PUT':
        handlePut($pdo, $table, $id, $input);
        break;
    case 'DELETE':
        handleDelete($pdo, $table, $id);
        break;
    default:
        echo json_encode(['error' => 'Метод не поддерживается']);
}

// ============ ОБРАБОТЧИКИ ============

function handleGet($pdo, $table, $id) {
    $allowed = ['clients', 'employees', 'orders', 'order_items', 'order_history', 'parts', 'payments', 'services', 'stock_movements', 'suppliers'];
    if (!in_array($table, $allowed)) {
        echo json_encode(['error' => 'Таблица не найдена']);
        return;
    }
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $pdo->query("SELECT * FROM $table ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

function handlePost($pdo, $table, $data) {
    $allowed = ['clients', 'employees', 'orders', 'order_items', 'order_history', 'parts', 'payments', 'services', 'stock_movements', 'suppliers'];
    if (!in_array($table, $allowed)) {
        echo json_encode(['error' => 'Таблица не найдена']);
        return;
    }
    
    // Удаляем id из данных, он auto_increment
    unset($data['id']);
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    
    $newId = $pdo->lastInsertId();
    $result = $pdo->query("SELECT * FROM $table WHERE id = $newId")->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

function handlePut($pdo, $table, $id, $data) {
    $allowed = ['clients', 'employees', 'orders', 'order_items', 'order_history', 'parts', 'payments', 'services', 'stock_movements', 'suppliers'];
    if (!in_array($table, $allowed)) {
        echo json_encode(['error' => 'Таблица не найдена']);
        return;
    }
    
    unset($data['id']);
    $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
    
    $sql = "UPDATE $table SET $setClause WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([...array_values($data), $id]);
    
    $result = $pdo->query("SELECT * FROM $table WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

function handleDelete($pdo, $table, $id) {
    $allowed = ['clients', 'employees', 'orders', 'order_items', 'order_history', 'parts', 'payments', 'services', 'stock_movements', 'suppliers'];
    if (!in_array($table, $allowed)) {
        echo json_encode(['error' => 'Таблица не найдена']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
}
?>