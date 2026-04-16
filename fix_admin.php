<?php
require_once 'config.php';

// Удаляем старого админа, если есть
$pdo->prepare("DELETE FROM users WHERE login = 'Admin26'")->execute();

// Хэшируем пароль Demo20
$hashed_password = password_hash('Demo20', PASSWORD_DEFAULT);

// Добавляем админа заново
$stmt = $pdo->prepare("INSERT INTO users (login, password, full_name, phone, email, role) VALUES (?, ?, ?, ?, ?, ?)");
$result = $stmt->execute(['Admin26', $hashed_password, 'Администратор', '0000000000', 'admin@uchus.ru', 'admin']);

if ($result) {
    echo "✅ Админ успешно создан!<br>";
    echo "Логин: Admin26<br>";
    echo "Пароль: Demo20<br>";
} else {
    echo "❌ Ошибка: " . implode(" ", $stmt->errorInfo());
}

// Проверка, что пароль работает
$test = $pdo->prepare("SELECT * FROM users WHERE login = 'Admin26'");
$test->execute();
$admin = $test->fetch();
if ($admin && password_verify('Demo20', $admin['password'])) {
    echo "<br>✅ Проверка пройдена: пароль Demo20 подходит!";
} else {
    echo "<br>❌ Проверка не пройдена";
}
?>