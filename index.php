<?php
session_start();
require_once 'includes/db.php';

$courses = $pdo->query("SELECT * FROM courses LIMIT 4")->fetchAll();
$reviews = $pdo->query("
    SELECT r.*, u.fio, c.name as course_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN applications a ON r.application_id = a.id
    JOIN courses c ON a.course_id = c.id
    ORDER BY r.created_at DESC 
    LIMIT 2
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Учусь.РФ — образовательный портал</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hero {
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 80px;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 10%, #1a1a2e 0%, #0a0a0f 100%);
            z-index: -2;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(108,92,231,0.03) 40px, rgba(108,92,231,0.03) 80px);
            animation: shift 60s linear infinite;
        }
        @keyframes shift {
            0% { transform: translate(0,0); }
            100% { transform: translate(80px,80px); }
        }
        .hero-content { max-width: 800px; padding: 0 20px; }
        .hero h1 {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #a855f7 50%, #6c5ce7 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero p { font-size: 1.2rem; color: #a0a0b0; margin-bottom: 40px; }
        .hero-buttons { display: flex; gap: 20px; justify-content: center; }
        
        .about { padding: 60px 0; margin-bottom: 60px; }
        .about-inner { display: grid; grid-template-columns: 1fr 1.2fr; gap: 50px; align-items: center; }
        .about-number { font-size: 8rem; font-weight: 800; background: linear-gradient(135deg, #6c5ce7, #a855f7); -webkit-background-clip: text; background-clip: text; color: transparent; line-height: 1; opacity: 0.6; }
        .about h3 { font-size: 0.9rem; letter-spacing: 3px; text-transform: uppercase; color: #a855f7; margin-bottom: 20px; }
        .about h2 { font-size: 2.5rem; margin-bottom: 20px; }
        .about p { color: #b0b0b0; line-height: 1.7; }
        
        .stats-showcase { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 30px; margin: 80px 0; }
        .stat-item { flex: 1; min-width: 180px; background: rgba(18,18,26,0.6); backdrop-filter: blur(10px); border-radius: 60px 20px 60px 20px; padding: 35px 20px; text-align: center; border: 1px solid rgba(108,92,231,0.2); transition: all 0.3s; }
        .stat-item:hover { border-radius: 20px 60px 20px 60px; border-color: #6c5ce7; background: rgba(18,18,26,0.9); }
        .stat-value { font-size: 3rem; font-weight: 800; color: #6c5ce7; }
        .stat-label { margin-top: 10px; color: #888; letter-spacing: 1px; }
        
        .courses-head { text-align: center; margin-bottom: 50px; }
        .courses-head h2 { font-size: 2.2rem; display: inline-block; position: relative; }
        .courses-head h2::after { content: ''; position: absolute; bottom: -10px; left: 20%; width: 60%; height: 2px; background: linear-gradient(90deg, transparent, #6c5ce7, #a855f7, transparent); }
        .course-list { display: flex; flex-direction: column; gap: 20px; margin: 50px 0; }
        .course-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; background: rgba(18,18,26,0.4); padding: 20px 30px; border-left: 3px solid #6c5ce7; transition: all 0.3s; }
        .course-row:hover { background: #12121a; transform: translateX(10px); }
        .course-info h3 { margin-bottom: 5px; }
        .course-type-badge { font-size: 0.7rem; color: #a855f7; letter-spacing: 1px; }
        .course-action a { padding: 10px 25px; background: transparent; border: 1px solid #6c5ce7; border-radius: 30px; text-decoration: none; color: #6c5ce7; transition: 0.2s; }
        .course-action a:hover { background: #6c5ce7; color: white; }
        
        .testimonials { background: linear-gradient(135deg, #0f0f14 0%, #12121a 100%); padding: 70px 30px; border-radius: 80px 20px 80px 20px; margin: 80px 0; }
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 40px; margin-top: 40px; }
        .testimonial-card { position: relative; padding: 30px; }
        .quote-mark { font-size: 5rem; font-family: serif; color: #6c5ce7; opacity: 0.3; position: absolute; top: -10px; left: 10px; }
        .testimonial-text { position: relative; font-style: italic; line-height: 1.6; margin-bottom: 20px; }
        .testimonial-author { border-top: 1px dashed #2a2a35; padding-top: 15px; font-size: 0.9rem; }
        .testimonial-author strong { color: #a855f7; }
        
        .cta-wave { text-align: center; padding: 80px 20px; position: relative; margin-bottom: -100px; z-index: 2; }
        .cta-wave h2 { font-size: 2.5rem; margin-bottom: 20px; }
        .cta-wave .btn-primary { background: linear-gradient(135deg, #6c5ce7, #a855f7); padding: 14px 45px; border-radius: 50px; }
        
        @media (max-width: 900px) {
            .about-inner { grid-template-columns: 1fr; text-align: center; }
            .hero h1 { font-size: 2.5rem; }
            .course-row { flex-direction: column; text-align: center; gap: 15px; }
            .stat-item { min-width: 140px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="uploads\logo-icon.png.png" alt="Учусь.РФ" style="height: 45px; width: auto;">
                <span style="background: linear-gradient(135deg, #6c5ce7, #a855f7); -webkit-background-clip: text; background-clip: text; color: transparent; font-weight: bold; font-size: 1.3rem;">Учусь.РФ</span>
            </div>
            <nav>
                <a href="index.php">Главная</a>
                <a href="#courses">Курсы</a>
                <a href="#reviews">Отзывы</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Кабинет</a>
                    <a href="logout.php">Выход</a>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                    <a href="register.php">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="hero">
            <div class="hero-bg"></div>
            <div class="hero-content">
                <h1>Образование,<br>которое меняет траекторию</h1>
                <p>Повышение квалификации, профессиональная переподготовка, курсы по охране труда. Дистанционно, с документами государственного образца.</p>
                <div class="hero-buttons">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="application.php" class="btn-primary">Начать обучение</a>
                    <?php else: ?>
                        <a href="register.php" class="btn-primary">Стать студентом</a>
                        <a href="login.php" class="btn-outline">Войти в кабинет</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="about">
                <div class="about-inner">
                    <div>
                        <div class="about-number">5+</div>
                        <div style="color: #666; margin-top: 10px;">лет на рынке<br>образования</div>
                    </div>
                    <div>
                        <h3>Почему выбирают нас</h3>
                        <h2>Не просто курсы,<br>а профессиональное развитие</h2>
                        <p>Мы создали среду, где теория соединяется с практикой. Каждый курс разработан экспертами с учетом актуальных требований работодателей и профессиональных стандартов. Более 5000 выпускников уже изменили свою карьеру с нами.</p>
                    </div>
                </div>
            </div>

            <div class="stats-showcase">
                <div class="stat-item"><div class="stat-value">5000+</div><div class="stat-label">выпускников</div></div>
                <div class="stat-item"><div class="stat-value">97%</div><div class="stat-label">трудоустройство</div></div>
                <div class="stat-item"><div class="stat-value">50+</div><div class="stat-label">программ</div></div>
                <div class="stat-item"><div class="stat-value">24/7</div><div class="stat-label">доступ к урокам</div></div>
            </div>

            <div class="courses-head" id="courses"><h2>Направления обучения</h2></div>
            <div class="course-list">
                <?php foreach ($courses as $course): ?>
                <div class="course-row">
                    <div class="course-info">
                        <h3><?= htmlspecialchars($course['name']) ?></h3>
                        <span class="course-type-badge"><?= htmlspecialchars($course['type']) ?></span>
                    </div>
                    <div class="course-action">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="application.php?course_id=<?= $course['id'] ?>">Выбрать</a>
                        <?php else: ?>
                            <a href="register.php">Записаться</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div id="reviews" class="testimonials">
                <h2 style="text-align: center;">Реальные истории</h2>
                <div class="testimonials-grid">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="testimonial-card">
                            <div class="quote-mark">"</div>
                            <div class="testimonial-text"><?= htmlspecialchars(mb_substr($review['review_text'], 0, 120)) ?>...</div>
                            <div class="testimonial-author"><strong><?= htmlspecialchars($review['fio']) ?></strong><br><small><?= htmlspecialchars($review['course_name']) ?></small></div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="testimonial-card"><div class="quote-mark">"</div><div class="testimonial-text">Отличная платформа для обучения. Материалы структурированы, преподаватели отвечают быстро. Удобно совмещать с работой.</div><div class="testimonial-author"><strong>Анна В.</strong><br><small>Повышение квалификации</small></div></div>
                        <div class="testimonial-card"><div class="quote-mark">"</div><div class="testimonial-text">Прошел переподготовку на веб-разработчика. После курса нашел работу за месяц. Спасибо команде Учусь.РФ.</div><div class="testimonial-author"><strong>Дмитрий К.</strong><br><small>Профпереподготовка</small></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="cta-wave">
            <h2>Новый уровень — реальность</h2>
            <p style="margin-bottom: 30px;">Присоединяйтесь к сообществу профессионалов</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="application.php" class="btn-primary">Подать заявку</a>
            <?php else: ?>
                <a href="register.php" class="btn-primary">Начать сейчас</a>
            <?php endif; ?>
        </div>
    </main>

    <footer style="background: #0a0a0f; border-top: 1px solid #1a1a2e; padding: 50px 20px 30px; margin-top: 60px;">
        <div class="container" style="text-align: center;">
            <div class="logo" style="justify-content: center; margin-bottom: 20px;">
                <img src="uploads\logo-icon.png.png" alt="Учусь.РФ" style="height: 70px; width: auto;">
            </div>
            <p style="color: #666;">Образовательный портал. Курсы повышения квалификации, переподготовки, охраны труда.</p>
            <div style="display: flex; justify-content: center; gap: 20px; margin: 25px 0;">
                <a href="https://vk.com" target="_blank"><img src="uploads\vk.png" alt="VK" style="width: 200px; height: 100px;"></a>
            </div>
            <p style="color: #444; margin-top: 20px; font-size: 0.8rem;">2026 Учусь.РФ</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>