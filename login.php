<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Прямое сравнение пароля без хэширования
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
    $stmt->execute([$login, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // Перенаправление в зависимости от роли
        if ($user['role'] == 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Неверный логин или пароль";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Вход в систему</h1>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">Регистрация успешна! Теперь войдите.</div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
    <p><a href="register.php">Еще не зарегистрированы? Регистрация</a></p>
</div>
</body>
</html>