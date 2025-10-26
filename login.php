<?php
session_start();
include 'db_connect.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Пожалуйста, заполните все поля.";
    } else {
        // 🔥 КЛЮЧЕВОЕ ИЗМЕНЕНИЕ: Выбираем поле `role`
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Успешный вход
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                
                // 🔥 СОХРАНЕНИЕ РОЛИ
                $_SESSION['role'] = $user['role']; 
                
                header('Location: index.php');
                exit;
            } else {
                $error = "Неправильный логин или пароль.";
            }
        } else {
            $error = "Неправильный логин или пароль.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php">🏛️ Достопримечательности</a></h1>
        <div class="user-status">
            <a href="register.php" class="button button-register">Регистрация</a>
        </div>
    </header>
    <div class="container">
        <h2>Вход</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>