<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
    $stmt->execute([$login, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') redirect('admin.php');
        else redirect('dashboard.php');
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<header>
    <div class="header-container">
        <div class="logo">📚 Учусь.РФ</div>
        <nav>
            <a href="index.php">Главная</a>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        </nav>
    </div>
</header>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo">Учусь.РФ</div>
            <h1>Вход в систему</h1>
            
            <?php if ($error): ?>
                <div class="notification error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="login" placeholder="Логин" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" placeholder="Пароль">
                </div>
                
                <button type="submit" class="btn-primary">Войти</button>
            </form>
            
            <div class="auth-link">
                Ещё не зарегистрированы? <a href="register.php">Регистрация</a>
            </div>
        </div>
    </div>
</body>
</html>