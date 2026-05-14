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

function getTableColumns($conn, $table) {
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    return $cols;
}

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

$columns = getTableColumns($conn, $table);
$pk = getPrimaryKey($table);
$result = $conn->query("SELECT * FROM `$table` ORDER BY `$pk` DESC LIMIT 200");

$editRow = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

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
            <a href="javascript:void(0)" onclick="showLogoutModal()" class="nav-item"><i class="fas fa-sign-out-alt"></i> Выход</a>
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

<!-- Модальное окно подтверждения выхода -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-white); border-radius: 16px; padding: 28px; max-width: 380px; text-align: center; box-shadow: 0 20px 35px -10px rgba(0,0,0,0.3); animation: modalFadeIn 0.2s ease;">
        <i class="fas fa-sign-out-alt" style="font-size: 48px; color: var(--warning); margin-bottom: 16px; display: inline-block;"></i>
        <h3 style="margin-bottom: 12px; color: var(--text-heading);">Выход из системы</h3>
        <p style="margin-bottom: 24px; color: var(--text-muted);">Вы уверены, что хотите выйти?</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button onclick="closeLogoutModal()" class="btn btn-outline" style="padding: 10px 24px;">
                <i class="fas fa-times"></i> Отмена
            </button>
            <a href="logout.php?confirm=yes" class="btn btn-danger" style="padding: 10px 24px;">
                <i class="fas fa-check"></i> Выйти
            </a>
        </div>
    </div>
</div>

<style>
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

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
    
    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }
    
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }
    
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('logoutModal');
        if (event.target === modal) {
            closeLogoutModal();
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeLogoutModal();
        }
    });
</script>
</body>
</html>