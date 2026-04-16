<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Получить заявки
$stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

// Добавить отзыв
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $request_id = $_POST['request_id'];
    $review_text = trim($_POST['review_text']);
    if ($review_text) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, request_id, review_text) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $request_id, $review_text]);
        $success = "Отзыв добавлен";
    }
}

// Получить отзывы пользователя
$reviews = $pdo->prepare("SELECT r.*, req.course_type FROM reviews r JOIN requests req ON r.request_id = req.id WHERE r.user_id = ?");
$reviews->execute([$user_id]);
$user_reviews = $reviews->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div class="nav">
        <a href="dashboard.php" class="btn">Главная</a>
        <a href="create_request.php" class="btn">Новая заявка</a>
        <a href="logout.php" class="btn">Выход</a>
    </div>
    <h2>Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name']) ?></h2>

    <h3>📋 Мои заявки</h3>
    <?php if (count($requests) > 0): ?>
        <table>
            <tr><th>Курс</th><th>Дата старта</th><th>Способ оплаты</th><th>Статус</th><th>Отзыв</th></tr>
            <?php foreach ($requests as $req): ?>
            <tr>
                <td><?= htmlspecialchars($req['course_type']) ?></td>
                <td><?= $req['start_date'] ?></td>
                <td><?= $req['payment_method'] ?></td>
                <td><?= $req['status'] ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <input type="text" name="review_text" placeholder="Ваш отзыв" required>
                        <button type="submit" name="add_review">Оставить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>У вас пока нет заявок. <a href="create_request.php">Создать заявку</a></p>
    <?php endif; ?>

    <h3>⭐ Мои отзывы</h3>
    <?php if (count($user_reviews) > 0): ?>
        <?php foreach ($user_reviews as $rev): ?>
            <div style="border:1px solid #ccc; padding:10px; margin:10px 0">
                <strong>Курс: <?= htmlspecialchars($rev['course_type']) ?></strong><br>
                <?= htmlspecialchars($rev['review_text']) ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Вы еще не оставляли отзывов.</p>
    <?php endif; ?>
</div>
</body>
</html>