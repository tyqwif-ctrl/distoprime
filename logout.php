<?php
// logout.php - Выход пользователя
session_start();

// Уничтожаем сессию
session_destroy();

// Перенаправляем пользователя на главную страницу
header("Location: index.php"); 
exit();
?>