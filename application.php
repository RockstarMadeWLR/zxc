<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) redirect('login.php');

$courses = $pdo->query("SELECT * FROM courses ORDER BY type, name")->fetchAll();
$selected_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : '';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $start_date = $_POST['start_date'];
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];
    
    $date_parts = explode('.', $start_date);
    if (count($date_parts) == 3) {
        $start_date_mysql = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
    } else {
        $start_date_mysql = date('Y-m-d');
    }
    
    if (empty($course_id) || empty($start_date) || empty($payment_method)) {
        $error = 'Заполните все поля!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO applications (user_id, course_id, start_date, payment_method, status) VALUES (?, ?, ?, ?, 'Новая')");
            if ($stmt->execute([$user_id, $course_id, $start_date_mysql, $payment_method])) {
                $success = 'Заявка успешно отправлена! Администратор рассмотрит её в ближайшее время.';
            } else {
                $error = 'Ошибка при сохранении заявки';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Новая заявка — Учусь.РФ</title>
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
        <div class="form-container" style="max-width: 600px; margin: 0 auto;">
            <h2>📝 Оформление заявки на курс</h2>
            
            <?php if ($success): ?>
                <div class="notification success" style="background: rgba(80,250,123,0.2); border: 1px solid #50fa7b; color: #50fa7b; padding: 12px; border-radius: 10px; margin-bottom: 20px;">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="notification error" style="background: rgba(255,107,107,0.2); border: 1px solid #ff6b6b; color: #ff6b6b; padding: 12px; border-radius: 10px; margin-bottom: 20px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="applicationForm">
                <div class="input-group">
                    <label>Выберите курс</label>
                    <select name="course_id" required>
                        <option value="">-- Выберите курс --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= ($selected_course == $course['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($course['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label>Дата начала обучения</label>
                    <input type="text" name="start_date" id="start_date" placeholder="ДД.ММ.ГГГГ" required>
                    <small>Например: 01.09.2026</small>
                </div>
                
                <div class="input-group">
                    <label>Способ оплаты</label>
                    <select name="payment_method" required>
                        <option value="">-- Выберите способ --</option>
                        <option value="Карта">Карта</option>
                        <option value="СБП">СБП</option>
                        <option value="Наличные">Наличные</option>
                        <option value="Юр.счет">Юридический счет</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">Отправить заявку</button>
            </form>
        </div>
    </main>
    
    <script>
        const dateInput = document.getElementById('start_date');
        if (dateInput) {
            dateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value.length >= 2 && value.length < 4) value = value.slice(0,2) + '.' + value.slice(2);
                else if (value.length >= 4 && value.length < 6) value = value.slice(0,2) + '.' + value.slice(2,4) + '.' + value.slice(4);
                else if (value.length >= 6) value = value.slice(0,2) + '.' + value.slice(2,4) + '.' + value.slice(4,8);
                if (value.length > 10) value = value.slice(0,10);
                e.target.value = value;
            });
        }
        
        document.getElementById('applicationForm')?.addEventListener('submit', function(e) {
            const dateField = document.getElementById('start_date');
            const datePattern = /^\d{2}\.\d{2}\.\d{4}$/;
            if (!datePattern.test(dateField.value)) {
                e.preventDefault();
                alert('Введите дату в формате ДД.ММ.ГГГГ (например: 25.12.2026)');
                return false;
            }
        });
    </script>
</body>
</html>