<?php
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Проверка роли администратора
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['role'] != 'admin') {
    die("Доступ запрещен. Только для администратора.");
}

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $request_id])) {
        $success = "Статус заявки №$request_id успешно изменен на «$new_status»";
    } else {
        $error = "Ошибка при изменении статуса";
    }
}

// Обработка удаления заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'];
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    if ($stmt->execute([$request_id])) {
        $success = "Заявка №$request_id удалена";
    } else {
        $error = "Ошибка при удалении";
    }
}

// Получение статистики
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM requests");
$stats['total'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM requests GROUP BY status");
$stats['by_status'] = $stmt->fetchAll();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch()['total'];

// Получение всех заявок с информацией о пользователях
$requests = $pdo->query("
    SELECT r.*, u.full_name, u.email, u.phone 
    FROM requests r 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC
")->fetchAll();

// Получение всех пользователей (для администрирования)
$users = $pdo->query("SELECT id, login, full_name, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

// Обработка изменения роли пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    if ($stmt->execute([$new_role, $user_id])) {
        $success = "Роль пользователя изменена";
    } else {
        $error = "Ошибка при изменении роли";
    }
}

// Обработка удаления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Не даем удалить самого себя
    if ($user_id == $_SESSION['user_id']) {
        $error = "Нельзя удалить самого себя";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $success = "Пользователь удален";
        } else {
            $error = "Ошибка при удалении";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        .tab.active {
            border-bottom: 3px solid #2c3e66;
            color: #2c3e66;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-new { background: #ff9800; color: white; }
        .status-learning { background: #2196f3; color: white; }
        .status-completed { background: #4caf50; color: white; }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            margin: 2px;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>👋 Панель администратора</h1>
        <div>
            <span style="margin-right: 15px;">👤 <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="dashboard.php" class="btn" style="background: #6c757d;">📱 Личный кабинет</a>
            <a href="logout.php" class="btn" style="background: #dc3545;">🚪 Выход</a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">✅ <?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error">❌ <?= $error ?></div>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Всего заявок</h3>
            <div class="number"><?= $stats['total'] ?></div>
        </div>
        <div class="stat-card">
            <h3>Пользователей</h3>
            <div class="number"><?= $stats['users'] ?></div>
        </div>
        <?php foreach ($stats['by_status'] as $stat): ?>
            <div class="stat-card">
                <h3><?= $stat['status'] ?></h3>
                <div class="number"><?= $stat['count'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Вкладки -->
    <div class="tabs">
        <button class="tab active" onclick="showTab('requests')">📋 Заявки</button>
        <button class="tab" onclick="showTab('users')">👥 Пользователи</button>
    </div>

    <!-- Вкладка с заявками -->
    <div id="tab-requests" class="tab-content active">
        <h2>Управление заявками</h2>
        <?php if (count($requests) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Контактные данные</th>
                            <th>Курс</th>
                            <th>Дата старта</th>
                            <th>Способ оплаты</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            switch ($req['status']) {
                                case 'Новая':
                                    $statusClass = 'status-new';
                                    $statusText = 'Новая';
                                    break;
                                case 'Идет обучение':
                                    $statusClass = 'status-learning';
                                    $statusText = 'Идет обучение';
                                    break;
                                case 'Обучение завершено':
                                    $statusClass = 'status-completed';
                                    $statusText = 'Обучение завершено';
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?= $req['id'] ?></td>
                                <td><?= htmlspecialchars($req['full_name']) ?></td>
                                <td>
                                    📧 <?= htmlspecialchars($req['email']) ?><br>
                                    📞 <?= htmlspecialchars($req['phone']) ?>
                                </td>
                                <td><?= htmlspecialchars($req['course_type']) ?></td>
                                <td><?= date('d.m.Y', strtotime($req['start_date'])) ?></td>
                                <td><?= htmlspecialchars($req['payment_method']) ?></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                                <td>
                                    <form method="post" style="display: inline-block;">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <select name="status" style="padding: 5px; font-size: 12px;">
                                            <option value="Новая" <?= $req['status'] == 'Новая' ? 'selected' : '' ?>>Новая</option>
                                            <option value="Идет обучение" <?= $req['status'] == 'Идет обучение' ? 'selected' : '' ?>>Идет обучение</option>
                                            <option value="Обучение завершено" <?= $req['status'] == 'Обучение завершено' ? 'selected' : '' ?>>Обучение завершено</option>
                                        </select>
                                        <button type="submit" name="change_status" class="btn-small">💾 Сохранить</button>
                                    </form>
                                    <form method="post" style="display: inline-block;" onsubmit="return confirm('Удалить заявку №<?= $req['id'] ?>?')">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <button type="submit" name="delete_request" class="btn-small btn-danger">🗑️ Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Пока нет ни одной заявки</p>
        <?php endif; ?>
    </div>

    <!-- Вкладка с пользователями -->
    <div id="tab-users" class="tab-content">
        <h2>Управление пользователями</h2>
        <?php if (count($users) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>ФИО</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user_item): ?>
                            <tr>
                                <td><?= $user_item['id'] ?></td>
                                <td><?= htmlspecialchars($user_item['login']) ?></td>
                                <td><?= htmlspecialchars($user_item['full_name']) ?></td>
                                <td><?= htmlspecialchars($user_item['email']) ?></td>
                                <td>
                                    <form method="post" style="display: inline-block;">
                                        <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                        <select name="role" style="padding: 5px;">
                                            <option value="user" <?= $user_item['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                                            <option value="admin" <?= $user_item['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                                        </select>
                                        <button type="submit" name="change_role" class="btn-small">💾 Изменить</button>
                                    </form>
                                </td>
                                <td><?= date('d.m.Y', strtotime($user_item['created_at'])) ?></td>
                                <td>
                                    <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" style="display: inline-block;" onsubmit="return confirm('Удалить пользователя <?= htmlspecialchars($user_item['login']) ?>? Все его заявки и отзывы также будут удалены.')">
                                            <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn-small btn-danger">🗑️ Удалить</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999;">Текущий</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Нет пользователей</p>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tabName) {
    // Скрываем все вкладки
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    // Показываем выбранную
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Обновляем активную кнопку
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
}
</script>
</body>
</html>