<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) redirect('login.php');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_review'])) {
    $app_id = $_POST['application_id'];
    $review = trim($_POST['review_text']);
    if (!empty($review)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, application_id, review_text) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $app_id, $review]);
    }
}

$stmt = $pdo->prepare("
    SELECT a.*, c.name as course_name 
    FROM applications a 
    JOIN courses c ON a.course_id = c.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT r.*, a.course_id, c.name as course_name 
    FROM reviews r 
    JOIN applications a ON r.application_id = a.id 
    JOIN courses c ON a.course_id = c.id 
    WHERE r.user_id = ?
");
$stmt->execute([$user_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Личный кабинет — Учусь.РФ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">📚 Учусь.РФ</div>
            <nav>
                <a href="index.php">Главная</a>
                <a href="dashboard.php">Личный кабинет</a>
                <a href="application.php">Новая заявка</a>
                <a href="logout.php">Выйти</a>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <h2>👋 Добро пожаловать, <?= htmlspecialchars($_SESSION['login']) ?></h2>
        
        <div class="slider-section" style="margin: 40px 0;">
            <h3>📸 Курсы месяца</h3>
            <div class="slider-container">
                <button class="slider-btn prev">❮</button>
                <div class="slider-track">
                    <div class="slide"><img src="uploads/slide1.jpg" alt="Курс 1" onerror="this.style.display='none'; this.parentElement.style.background='#6c5ce7'; this.parentElement.innerHTML='<div style=\'height:400px; display:flex; align-items:center; justify-content:center; color:white;\'>Повышение квалификации</div>'"></div>
                    <div class="slide"><img src="uploads/slide2.jpg" alt="Курс 2" onerror="this.style.display='none'; this.parentElement.style.background='#a855f7'; this.parentElement.innerHTML='<div style=\'height:400px; display:flex; align-items:center; justify-content:center; color:white;\'>Профпереподготовка</div>'"></div>
                    <div class="slide"><img src="uploads/slide3.jpg" alt="Курс 3" onerror="this.style.display='none'; this.parentElement.style.background='#ec489a'; this.parentElement.innerHTML='<div style=\'height:400px; display:flex; align-items:center; justify-content:center; color:white;\'>Охрана труда</div>'"></div>
                    <div class="slide"><img src="uploads/slide4.jpg" alt="Курс 4" onerror="this.style.display='none'; this.parentElement.style.background='#f43f5e'; this.parentElement.innerHTML='<div style=\'height:400px; display:flex; align-items:center; justify-content:center; color:white;\'>Записывайтесь сейчас</div>'"></div>
                </div>
                <button class="slider-btn next">❯</button>
            </div>
        </div>
        
        <div class="applications-section">
            <h3>📋 Мои заявки</h3>
            <?php if (count($applications) > 0): ?>
                <div class="applications-grid">
                    <?php foreach ($applications as $app): ?>
                        <div class="app-card">
                            <h4><?= htmlspecialchars($app['course_name']) ?></h4>
                            <p>📅 Дата: <?= date('d.m.Y', strtotime($app['start_date'])) ?></p>
                            <p>💳 Оплата: <?= $app['payment_method'] ?></p>
                            <p>Статус: <span class="status status-<?= strtolower(str_replace(' ', '-', $app['status'])) ?>"><?= $app['status'] ?></span></p>
                            <?php if ($app['status'] == 'Обучение завершено'): ?>
                                <?php 
                                $hasReview = false;
                                foreach ($reviews as $rev) {
                                    if ($rev['application_id'] == $app['id']) $hasReview = true;
                                }
                                ?>
                                <?php if (!$hasReview): ?>
                                    <form method="POST" class="review-form">
                                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                        <textarea name="review_text" placeholder="Оставьте отзыв о курсе..." required></textarea>
                                        <button type="submit" name="add_review" class="btn-small">📝 Отправить отзыв</button>
                                    </form>
                                <?php else: ?>
                                    <p class="review-done">✅ Отзыв оставлен</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>У вас пока нет заявок. <a href="application.php">Создать заявку</a></p>
            <?php endif; ?>
        </div>
        
        <?php if (count($reviews) > 0): ?>
        <div class="reviews-section" style="margin-top: 40px;">
            <h3>⭐ Мои отзывы</h3>
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card" style="margin-bottom: 15px;">
                    <p><strong><?= htmlspecialchars($rev['course_name']) ?>:</strong></p>
                    <p><?= htmlspecialchars($rev['review_text']) ?></p>
                    <small>📅 <?= date('d.m.Y', strtotime($rev['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="js/script.js"></script>
</body>
</html>