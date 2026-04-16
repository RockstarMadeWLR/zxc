<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course_type'];
    $date = $_POST['start_date'];
    $payment = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO requests (user_id, course_type, start_date, payment_method) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $course, $date, $payment])) {
        header("Location: dashboard.php?success=1");
        exit;
    } else {
        $error = "Ошибка при создании заявки";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Новая заявка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Оформление заявки на курс</h1>
    <a href="dashboard.php" class="btn">Назад</a>
    <?php if (isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>
    <form method="post">
        <select name="course_type" required>
            <option value="">Выберите курс</option>
            <option>Повышение квалификации</option>
            <option>Профессиональная переподготовка</option>
            <option>Охрана труда</option>
        </select>
        <input type="date" name="start_date" required>
        <select name="payment_method" required>
            <option>Банковская карта</option>
            <option>Безналичный расчет</option>
            <option>Оплата на сайте</option>
        </select>
        <button type="submit">Отправить заявку</button>
    </form>
</div>
</body>
</html>