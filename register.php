<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password']; // Сохраняем как есть
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $errors = [];

    // валидация
    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = "Логин должен содержать минимум 6 символов (латиница и цифры)";
    }
    if (strlen($password) < 8) {
        $errors[] = "Пароль должен быть не менее 8 символов";
    }
    if (empty($full_name)) $errors[] = "Введите ФИО";
    if (empty($phone)) $errors[] = "Введите телефон";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email";

    if (empty($errors)) {
        // Проверка уникальности логина
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors[] = "Логин уже существует";
        } else {
            // Сохраняем пароль в открытом виде
            $stmt = $pdo->prepare("INSERT INTO users (login, password, full_name, phone, email) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$login, $password, $full_name, $phone, $email])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $errors[] = "Ошибка регистрации";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Регистрация</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Логин (латиница, цифры, мин 6)" required>
        <input type="password" name="password" placeholder="Пароль (мин 8 символов)" required>
        <input type="text" name="full_name" placeholder="ФИО" required>
        <input type="text" name="phone" placeholder="Телефон" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>
</body>
</html>