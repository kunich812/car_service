<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['роль'];
$user_name = $_SESSION['имя'] . ' ' . $_SESSION['фамилия'];
$email = $_SESSION['email'];

// Функция получения статистики
function getStats($conn) {
    $stats = [];
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'accepted'");
    $stats['accepted'] = $res->fetch_assoc()['cnt'];
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'in_progress'");
    $stats['in_progress'] = $res->fetch_assoc()['cnt'];
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'completed'");
    $stats['completed'] = $res->fetch_assoc()['cnt'];
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
    $stats['total_orders'] = $res->fetch_assoc()['cnt'];
    $res = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE status = 'paid'");
    $stats['revenue'] = $res->fetch_assoc()['total'] ?? 0;
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM clients");
    $stats['clients'] = $res->fetch_assoc()['cnt'];
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM parts WHERE stock_quantity < min_stock");
    $stats['low_stock'] = $res->fetch_assoc()['cnt'];
    return $stats;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд – АвтоСервис</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-wrench"></i>
            <span>АвтоСервис</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i> Дашборд</a>
            <?php if ($role !== 'Клиент'): ?>
                <a href="tables.php?table=orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Заказы</a>
                <a href="tables.php?table=clients" class="nav-item"><i class="fas fa-users"></i> Клиенты</a>
                <a href="tables.php?table=services" class="nav-item"><i class="fas fa-tools"></i> Услуги</a>
                <a href="tables.php?table=parts" class="nav-item"><i class="fas fa-boxes"></i> Склад</a>
                <a href="tables.php?table=payments" class="nav-item"><i class="fas fa-money-bill-wave"></i> Расчеты</a>
            <?php endif; ?>
            <?php if ($role === 'Администратор'): ?>
                <a href="tables.php?table=users" class="nav-item"><i class="fas fa-user-shield"></i> Пользователи</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i> Выход</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <h2>🚗 Добро пожаловать, <?= htmlspecialchars($user_name) ?>!</h2>
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
                    <i class="fas fa-sun icon-sun"></i>
                    <i class="fas fa-moon icon-moon"></i>
                </button>
                <div class="user-badge">
                    <i class="fas fa-user-tag"></i>
                    <span><?= htmlspecialchars($role) ?></span>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <?php if ($role === 'Клиент'): ?>
                <!-- ДЛЯ КЛИЕНТА: его заказы -->
                <div class="info-card">
                    <div class="card-icon"><i class="fas fa-car"></i></div>
                    <div class="card-info">
                        <h3>Мои заказы</h3>
                        <?php
                        $client_id = $_SESSION['client_id'] ?? null;
                        if ($client_id) {
                            $sql = "SELECT o.*, c.car_brand, c.car_model, c.car_plate 
                                    FROM orders o 
                                    JOIN clients c ON o.client_id = c.client_id 
                                    WHERE o.client_id = ? 
                                    ORDER BY o.created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $client_id);
                            $stmt->execute();
                            $orders = $stmt->get_result();
                        ?>
                        <table class="mini-table">
                            <thead>
                                <tr><th>№ заказа</th><th>Автомобиль</th><th>Дата</th><th>Статус</th><th>Сумма</th></tr>
                            </thead>
                            <tbody>
                            <?php while ($order = $orders->fetch_assoc()): 
                                $total = $order['total_services'] + $order['total_parts'];
                                $statusText = [
                                    'accepted' => 'Принят',
                                    'in_progress' => 'В работе',
                                    'completed' => 'Выполнен',
                                    'closed' => 'Закрыт'
                                ][$order['status']] ?? $order['status'];
                            ?>
                                <tr>
                                    <td><?= $order['order_id'] ?></td>
                                    <td><?= htmlspecialchars($order['car_brand'] . ' ' . $order['car_model'] . ' (' . $order['car_plate'] . ')') ?></td>
                                    <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= $statusText ?></td>
                                    <td><?= number_format($total, 2) ?> ₽</td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        ?</table>
                        <?php } else { ?>
                            <p>У вас пока нет заказов.</p>
                        <?php } ?>
                    </div>
                </div>

            <?php elseif ($role === 'Механик'): ?>
                <!-- ДЛЯ МЕХАНИКА: задачи в работе -->
                <div class="info-card">
                    <div class="card-icon"><i class="fas fa-tasks"></i></div>
                    <div class="card-info">
                        <h3>Задачи в работе</h3>
                        <?php
                        $mechanic_id = $_SESSION['user_id'];
                        $sql = "SELECT o.*, c.full_name, c.car_brand, c.car_model, c.car_plate 
                                FROM orders o 
                                JOIN clients c ON o.client_id = c.client_id 
                                WHERE o.mechanic_id = ? AND o.status IN ('accepted', 'in_progress')
                                ORDER BY o.created_at ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $mechanic_id);
                        $stmt->execute();
                        $tasks = $stmt->get_result();
                        ?>
                        <?php if ($tasks->num_rows > 0): ?>
                            <table class="mini-table">
                                <thead>
                                    <tr><th>№ заказа</th><th>Клиент</th><th>Авто</th><th>Статус</th><th>Действие</th></tr>
                                </thead>
                                <tbody>
                                <?php while ($task = $tasks->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $task['order_id'] ?></td>
                                        <td><?= htmlspecialchars($task['full_name']) ?></td>
                                        <td><?= htmlspecialchars($task['car_brand'] . ' ' . $task['car_model']) ?></td>
                                        <td><?= $task['status'] === 'accepted' ? 'Ожидает' : 'В работе' ?></td>
                                        <td><a href="tables.php?table=orders&edit=<?= $task['order_id'] ?>" class="btn btn-sm btn-primary">Выполнить</a></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Нет активных задач.</p>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- ДЛЯ ОПЕРАТОРА И АДМИНИСТРАТОРА: полная статистика -->
                <?php $stats = getStats($conn); ?>
                <div class="stats-grid">
                    <div class="stat-card"><i class="fas fa-clipboard-list"></i><div class="stat-value"><?= $stats['accepted'] ?></div><div class="stat-label">Новых заказов</div></div>
                    <div class="stat-card"><i class="fas fa-tools"></i><div class="stat-value"><?= $stats['in_progress'] ?></div><div class="stat-label">В работе</div></div>
                    <div class="stat-card"><i class="fas fa-check-circle"></i><div class="stat-value"><?= $stats['completed'] ?></div><div class="stat-label">Выполнено</div></div>
                    <div class="stat-card"><i class="fas fa-users"></i><div class="stat-value"><?= $stats['clients'] ?></div><div class="stat-label">Клиентов</div></div>
                    <div class="stat-card"><i class="fas fa-chart-line"></i><div class="stat-value"><?= number_format($stats['revenue'], 0) ?> ₽</div><div class="stat-label">Выручка</div></div>
                    <?php if ($stats['low_stock'] > 0): ?>
                        <div class="stat-card" style="background:#FFF3E0;"><i class="fas fa-exclamation-triangle"></i><div class="stat-value" style="color:#E65100;"><?= $stats['low_stock'] ?></div><div class="stat-label">Требуют закупки</div></div>
                    <?php endif; ?>
                </div>

                <!-- Быстрые действия -->
                <div class="quick-actions">
                    <h3>Быстрые действия</h3>
                    <div class="action-buttons">
                        <a href="tables.php?table=orders" class="btn btn-outline"><i class="fas fa-plus-circle"></i> Новый заказ</a>
                        <a href="tables.php?table=parts" class="btn btn-outline"><i class="fas fa-boxes"></i> Склад</a>
                        <a href="tables.php?table=clients" class="btn btn-outline"><i class="fas fa-user-plus"></i> Добавить клиента</a>
                    </div>
                </div>

                <!-- Последние заказы -->
                <div class="recent-transactions">
                    <h3><i class="fas fa-receipt"></i> Последние заказы</h3>
                    <?php
                    $res = $conn->query("
                        SELECT o.*, c.full_name, c.car_plate 
                        FROM orders o
                        JOIN clients c ON o.client_id = c.client_id
                        ORDER BY o.created_at DESC LIMIT 10
                    ");
                    ?>
                    <table class="data-table">
                        <thead>
                            <tr><th>№ заказа</th><th>Клиент</th><th>Авто</th><th>Дата</th><th>Сумма</th><th>Статус</th></tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $res->fetch_assoc()): 
                            $total = $row['total_services'] + $row['total_parts'];
                            $statusClass = match($row['status']) {
                                'accepted' => 'badge-info',
                                'in_progress' => 'badge-warning',
                                'completed' => 'badge-success',
                                'closed' => 'badge-secondary',
                                default => 'badge-info'
                            };
                            $statusText = [
                                'accepted' => 'Принят',
                                'in_progress' => 'В работе',
                                'completed' => 'Выполнен',
                                'closed' => 'Закрыт'
                            ][$row['status']] ?? $row['status'];
                        ?>
                            <tr>
                                <td><?= $row['order_id'] ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['car_plate']) ?></td>
                                <td><?= date('d.m.Y', strtotime($row['created_at'])) ?></td>
                                <td><?= number_format($total, 2) ?> ₽</td>
                                <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    ?</table>
                </div>
            <?php endif; ?>
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