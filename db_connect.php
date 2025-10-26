<?php
// db_connect.php - Улучшенный файл подключения к базе данных

// 1. Используем константы для настроек
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', '');
define('DB_NAME', 'dostoprim_db');

// 2. Создаем подключение
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// 3. Проверяем подключение
if ($conn->connect_error) {
    // В боевой среде: регистрируем ошибку, но не выводим ее пользователю
    // die("Ошибка подключения к базе данных: " . $conn->connect_error); 
    
    // Для разработки на XAMPP:
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// 4. Установка кодировки
$conn->set_charset("utf8mb4");

// Примечание: В остальных PHP-файлах (index.php, login.php, и т.д.) 
// вы включаете этот файл:
// include 'db_connect.php';
// и используете переменную $conn для выполнения запросов.
?>