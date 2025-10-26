<?php
// register.php - Страница регистрации
session_start();
include 'db_connect.php';

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $message = "Пожалуйста, заполните все поля.";
    } else {
        // Хеширование пароля для безопасности
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Подготовленный запрос для предотвращения SQL-инъекций
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        try {
            if ($stmt->execute()) {
                // Успех! Перенаправляем на страницу входа с сообщением
                header("Location: login.php?registered=success");
                exit();
            } else {
                $message = "Ошибка при регистрации. Возможно, логин или email уже заняты.";
            }
        } catch (mysqli_sql_exception $e) {
             $message = "Ошибка: Пользователь с таким логином или email уже существует.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Регистрация пользователя</h2>
        <?php if (!empty($message)): ?>
            <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <label for="username">Логин:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Зарегистрироваться</button>
        </form>
        <p style="text-align: center;">Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</body>
</html>