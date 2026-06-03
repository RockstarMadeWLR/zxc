<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect('dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fio = trim($_POST['fio']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    // Валидация
    if (strlen($login) < 6 || !preg_match('/^[a-zA-Z0-9]+$/', $login)) {
        $errors['login'] = 'Логин должен содержать минимум 6 символов (латиница и цифры)';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) $errors['login'] = 'Такой логин уже существует';
    }
    
    if (strlen($password) < 8) {
        $errors['password'] = 'Пароль должен быть минимум 8 символов';
    }
    
    if (empty($fio)) $errors['fio'] = 'Введите ФИО';
    if (empty($phone)) $errors['phone'] = 'Введите телефон';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Введите корректный email';
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO users (login, password, fio, phone, email, role) VALUES (?, ?, ?, ?, ?, 'user')");
        if ($stmt->execute([$login, $password, $fio, $phone, $email])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['login'] = $login;
            $_SESSION['role'] = 'user';
            redirect('dashboard.php');
        }
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
            <h1>Регистрация</h1>
            
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="login" placeholder="Логин (латиница, цифры)" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
                    <?php showError('login', $errors); ?>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" placeholder="Пароль (мин. 8 символов)">
                    <?php showError('password', $errors); ?>
                </div>
                
                <div class="input-group">
                    <input type="text" name="fio" placeholder="ФИО" value="<?= htmlspecialchars($_POST['fio'] ?? '') ?>">
                    <?php showError('fio', $errors); ?>
                </div>
                
                <div class="input-group">
                    <input type="tel" name="phone" placeholder="Телефон" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    <?php showError('phone', $errors); ?>
                </div>
                
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <?php showError('email', $errors); ?>
                </div>
                
                <button type="submit" class="btn-primary">Зарегистрироваться</button>
            </form>
            
            <div class="auth-link">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </div>
        </div>
    </div>
</body>
</html>