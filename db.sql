-- --------------------------------------------------------
-- Хост: localhost
-- База данных: dostoprim_db
-- --------------------------------------------------------

-- 1. Удаление таблиц (для чистого старта)
DROP TABLE IF EXISTS `attractions`;
DROP TABLE IF EXISTS `users`;

---
-- 2. СТРУКТУРА: Создание таблицы `users` (ВКЛЮЧАЯ ROLE)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, 
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user', -- <-- ПОЛЕ ROLE
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;

---
-- 3. СТРУКТУРА: Создание таблицы `attractions`
CREATE TABLE IF NOT EXISTS `attractions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL, 
  `description` TEXT NOT NULL, 
  `city` VARCHAR(100) NOT NULL DEFAULT 'Москва', -- <-- Установил значение по умолчанию 'Москва'
  `image_url` VARCHAR(255) NULL, 
  `working_hours` VARCHAR(100) NULL, 
  `website` VARCHAR(255) NULL, 
  `short_description` VARCHAR(255) NULL 
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;

---
-- 4. ДАННЫЕ: Вставка тестовых пользователей
-- Тестовый АДМИН (testadmin) - Пароль: password123 (хеширован)
-- Обычный ПОЛЬЗОВАТЕЛЬ (testuser) - Пароль: user123 (хеширован)
INSERT INTO `users` (`username`, `password`, `email`, `role`) VALUES 
('testadmin', '$2y$10$tM9sE7E6uK4sR1eZ4G0Jm.nFzW1J0K2H4G9Vp6bJ3q5D8U4y5X4O2', 'admin@example.com', 'admin'),
('testuser', '$2y$10$7rT.eR9n4U0v8wL2zS6N2O0x5D9V3Q1Y8P7H6J5I4X3W2Z1A0B9C', 'user@example.com', 'user');

---
## 5. ДАННЫЕ: Вставка достопримечательностей ТОЛЬКО Москвы
INSERT INTO `attractions` (`title`, `description`, `short_description`, `city`, `image_url`, `working_hours`, `website`) VALUES
('Московский Кремль', 'Исторический центр Москвы, крепость, где находится резиденция Президента России. Включает соборы, дворцы и музеи.', 'Крупнейшая действующая крепость в Европе, символ России.', 'Москва', 'kreml.jpg', '10:00 - 17:00', 'http://www.kreml.ru'),
('Красная площадь', 'Главная и самая известная площадь Москвы, расположенная у стен Кремля. Место проведения парадов и исторических событий.', 'Центральная площадь Москвы, объект Всемирного наследия ЮНЕСКО.', 'Москва', 'red_square.jpg', 'Круглосуточно', NULL),
('Собор Василия Блаженного', 'Православный храм на Красной площади, шедевр русской архитектуры. Известен своими яркими, луковичными куполами.', 'Символ Москвы, уникальный образец русской архитектуры.', 'Москва', 'st_basils.jpg', '11:00 - 17:00', 'http://www.shm.ru'),
('Третьяковская галерея', 'Художественный музей, основанный купцом П.М. Третьяковым. Содержит богатейшую коллекцию русского искусства.', 'Главный музей национального русского искусства.', 'Москва', 'tretyakov_gallery.jpg', '10:00 - 21:00 (Вт-Вс)', 'http://www.tretyakovgallery.ru'),
('Парк Горького', 'Центральный парк культуры и отдыха. Предлагает зоны отдыха, прокат велосипедов, катки зимой и набережную реки Москвы.', 'Один из крупнейших и самых известных парков Москвы.', 'Москва', 'gorky_park.jpg', 'Круглосуточно', 'http://www.park-gorkogo.com'),
('ВДНХ (Выставка достижений народного хозяйства)', 'Крупный выставочный комплекс с павильонами, фонтанами и музеями. Отражает архитектуру советской эпохи.', 'Огромный выставочный и музейный комплекс.', 'Москва', 'vdnh.jpg', 'Круглосуточно (Территория)', 'http://www.vdnh.ru');