<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['роль'] !== 'Администратор' && $_SESSION['роль'] !== 'Оператор' && $_SESSION['роль'] !== 'Механик')) {
    header('Location: dashboard.php');
    exit;
}

$role = $_SESSION['роль'];
$allowed_tables = ['clients', 'services', 'parts', 'orders', 'order_items', 'payments'];
if ($role === 'Администратор') {
    $allowed_tables[] = 'users';
}

$table = $_GET['table'] ?? 'orders';
if (!in_array($table, $allowed_tables)) {
    $table = 'orders';
}

$message = '';
$error = '';

// Функция получения первичного ключа для таблицы
function getPrimaryKey($table) {
    $keys = [
        'clients'      => 'client_id',
        'services'     => 'service_id',
        'parts'        => 'part_id',
        'orders'       => 'order_id',
        'order_items'  => 'item_id',
        'payments'     => 'payment_id',
        'users'        => 'user_id'
    ];
    return $keys[$table] ?? 'id';
}

// Функция получения всех колонок таблицы
function getTableColumns($conn, $table) {
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

// Удаление записи
if (isset($_GET['delete'])) {
    $pk = getPrimaryKey($table);
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Запись удалена.";
    } else {
        $error = "Ошибка удаления: " . $conn->error;
    }
    $stmt->close();
}

// Добавление / редактирование
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $data = $_POST;
    unset($data['action'], $data['id']);

    if ($action === 'add') {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        $types = str_repeat('s', count($data));
        $values = array_values($data);
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            $message = "Запись добавлена.";
        } else {
            $error = "Ошибка: " . $conn->error;
        }
        $stmt->close();
    }
    elseif ($action === 'edit' && isset($_POST['id'])) {
        $pk = getPrimaryKey($table);
        $id = $_POST['id'];
        $set = [];
        $types = '';
        $values = [];
        foreach ($data as $col => $val) {
            $set[] = "`$col` = ?";
            $types .= 's';
            $values[] = $val;
        }
        $values[] = $id;
        $types .= 'i';
        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE `$pk` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        if ($stmt->execute()) {
            $message = "Запись обновлена.";
        } else {
            $error = "Ошибка: " . $conn->error;
        }
        $stmt->close();
    }
}

// Получение данных для таблицы
$columns = getTableColumns($conn, $table);
$pk = getPrimaryKey($table);
$result = $conn->query("SELECT * FROM `$table` ORDER BY `$pk` DESC LIMIT 200");

// Редактируемая запись
$editRow = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Названия таблиц на русском
$tableNames = [
    'clients' => 'Клиенты',
    'services' => 'Услуги',
    'parts' => 'Запчасти',
    'orders' => 'Заказы',
    'order_items' => 'Позиции заказов',
    'payments' => 'Платежи',
    'users' => 'Пользователи'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление данными – АвтоСервис</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-wrench"></i><span>АвтоСервис</span></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Дашборд</a>
            <a href="tables.php?table=orders" class="nav-item <?= $table === 'orders' ? 'active' : '' ?>"><i class="fas fa-clipboard-list"></i> Заказы</a>
            <a href="tables.php?table=clients" class="nav-item <?= $table === 'clients' ? 'active' : '' ?>"><i class="fas fa-users"></i> Клиенты</a>
            <a href="tables.php?table=services" class="nav-item <?= $table === 'services' ? 'active' : '' ?>"><i class="fas fa-tools"></i> Услуги</a>
            <a href="tables.php?table=parts" class="nav-item <?= $table === 'parts' ? 'active' : '' ?>"><i class="fas fa-boxes"></i> Склад</a>
            <a href="tables.php?table=payments" class="nav-item <?= $table === 'payments' ? 'active' : '' ?>"><i class="fas fa-money-bill-wave"></i> Расчеты</a>
            <?php if ($role === 'Администратор'): ?>
                <a href="tables.php?table=users" class="nav-item <?= $table === 'users' ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Пользователи</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Выход</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h2>Управление таблицей: <?= $tableNames[$table] ?? ucfirst($table) ?></h2>
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
                    <i class="fas fa-sun icon-sun"></i>
                    <i class="fas fa-moon icon-moon"></i>
                </button>
                <div class="user-badge">
                    <i class="fas fa-user-shield"></i>
                    <span><?= htmlspecialchars($role) ?></span>
                </div>
            </div>
        </header>

        <div class="table-selector">
            <?php foreach ($allowed_tables as $t): ?>
                <a href="?table=<?= $t ?>" class="tab <?= $table === $t ? 'active' : '' ?>"><?= $tableNames[$t] ?? ucfirst($t) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

        <!-- Форма добавления/редактирования -->
        <div class="form-card">
            <h3><?= $editRow ? '✏ Редактирование записи' : '➕ Добавить новую запись' ?></h3>
            <form method="post" class="crud-form">
                <input type="hidden" name="action" value="<?= $editRow ? 'edit' : 'add' ?>">
                <?php if ($editRow): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editRow[$pk]) ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <?php foreach ($columns as $col):
                        if ($col === $pk) continue;
                        $value = $editRow ? htmlspecialchars($editRow[$col] ?? '') : '';
                        ?>
                        <div class="form-group">
                            <label for="<?= $col ?>"><?= $col ?></label>
                            <input type="text" name="<?= $col ?>" id="<?= $col ?>" value="<?= $value ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $editRow ? 'Сохранить' : 'Добавить' ?></button>
                    <?php if ($editRow): ?><a href="?table=<?= $table ?>" class="btn btn-secondary">Отмена</a><?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Таблица данных -->
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><?php foreach ($columns as $col): ?><th><?= $col ?></th><?php endforeach; ?><th>Действия</th></tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td><?= htmlspecialchars($row[$col] ?? '') ?></td>
                        <?php endforeach; ?>
                        <td class="actions">
                            <a href="?table=<?= $table ?>&edit=<?= urlencode($row[$pk]) ?>" class="btn-icon" title="Редактировать"><i class="fas fa-edit"></i></a>
                            <a href="?table=<?= $table ?>&delete=<?= urlencode($row[$pk]) ?>" class="btn-icon delete" title="Удалить" onclick="return confirm('Удалить запись?')"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
// Переключение темы
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

// Загрузка сохранённой темы
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
}
</script>
</body>
</html>