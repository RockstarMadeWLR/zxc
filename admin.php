<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !isAdmin()) redirect('login.php');

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_status'])) {
    $app_id = $_POST['app_id'];
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $app_id]);
}

// Параметры фильтрации и пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status_filter = $_GET['status'] ?? '';

$query = "SELECT a.*, u.fio, u.login, c.name as course_name 
          FROM applications a 
          JOIN users u ON a.user_id = u.id 
          JOIN courses c ON a.course_id = c.id";
$count_query = "SELECT COUNT(*) FROM applications a";

if ($status_filter && in_array($status_filter, ['Новая', 'Идет обучение', 'Обучение завершено'])) {
    $query .= " WHERE a.status = '$status_filter'";
    $count_query .= " WHERE status = '$status_filter'";
}

$query .= " ORDER BY a.created_at DESC LIMIT $limit OFFSET $offset";

$applications = $pdo->query($query)->fetchAll();
$total = $pdo->query($count_query)->fetchColumn();
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Админ-панель — Учусь.РФ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">
    <img src="images/logo-icon.png" alt="Учусь.РФ" style="height: 45px; width: auto;">
    <span style="background: linear-gradient(135deg, #6c5ce7, #a855f7); -webkit-background-clip: text; background-clip: text; color: transparent; font-weight: bold;">Учусь.РФ</span>
</div>
        <nav>
            <a href="index.php">На сайт</a>
            <a href="dashboard.php">На главную</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </div>
</header>
    
    <main class="container">
        <h2>Управление заявками</h2>
        
        <!-- Фильтры -->
        <div class="filters">
            <a href="?status=" class="filter-btn <?= !$status_filter ? 'active' : '' ?>">Все</a>
            <a href="?status=Новая" class="filter-btn <?= $status_filter == 'Новая' ? 'active' : '' ?>">Новые</a>
            <a href="?status=Идет обучение" class="filter-btn <?= $status_filter == 'Идет обучение' ? 'active' : '' ?>">Идет обучение</a>
            <a href="?status=Обучение завершено" class="filter-btn <?= $status_filter == 'Обучение завершено' ? 'active' : '' ?>">Завершены</a>
        </div>
        
        <!-- Таблица заявок -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Курс</th>
                        <th>Дата старта</th>
                        <th>Оплата</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= $app['id'] ?></td>
                        <td><?= htmlspecialchars($app['fio']) ?><br><small><?= $app['login'] ?></small></td>
                        <td><?= htmlspecialchars($app['course_name']) ?></td>
                        <td><?= date('d.m.Y', strtotime($app['start_date'])) ?></td>
                        <td><?= $app['payment_method'] ?></td>
                        <td>
                            <span class="status status-<?= strtolower(str_replace(' ', '-', $app['status'])) ?>">
                                <?= $app['status'] ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="status-form" onsubmit="showNotification()">
                                <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                <select name="new_status" class="status-select">
                                    <option value="Новая" <?= $app['status'] == 'Новая' ? 'selected' : '' ?>>Новая</option>
                                    <option value="Идет обучение" <?= $app['status'] == 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
                                    <option value="Обучение завершено" <?= $app['status'] == 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
                                </select>
                                <button type="submit" name="change_status" class="btn-small">Изменить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $status_filter ?>" class="page-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <div id="notification-toast" class="toast hidden">Статус изменён!</div>
    
    <script>
        function showNotification() {
            const toast = document.getElementById('notification-toast');
            toast.classList.remove('hidden');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                toast.classList.add('hidden');
            }, 2000);
        }
    </script>
</body>
</html>