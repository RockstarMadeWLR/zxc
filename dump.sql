CREATE DATABASE IF NOT EXISTS uchus_portal;
USE uchus_portal;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fio VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL
);

INSERT INTO courses (id, name, type) VALUES
(1, 'Охрана труда (базовый курс)', 'Охрана труда'),
(2, 'Профпереподготовка: Веб-разработчик', 'Переподготовка'),
(3, 'Повышение квалификации: Управление проектами', 'Повышение квалификации'),
(4, 'Охрана труда для руководителей', 'Охрана труда'),
(5, 'Курсы переподготовки: Педагогика', 'Переподготовка');

CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    start_date DATE NOT NULL,
    payment_method ENUM('Карта', 'СБП', 'Наличные', 'Юр.счет') NOT NULL,
    status ENUM('Новая', 'Идет обучение', 'Обучение завершено') DEFAULT 'Новая',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    review_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Администратор (пароль: Demo20)
INSERT INTO users (login, password, fio, phone, email, role) 
VALUES ('Admin26', 'Demo20', 'Системный Администратор', '+7 (999) 999-99-99', 'admin@uchus.ru', 'admin');