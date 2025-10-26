<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php'; 

// Проверка: Доступ только для администраторов
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен. Только для администраторов.']);
    exit;
}
// ... (остальной код создания)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен. Используйте POST.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['title'], $data['description'], $data['city'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Отсутствуют обязательные поля (title, description, city).']);
    exit;
}

$title = $conn->real_escape_string($data['title']);
$description = $conn->real_escape_string($data['description']);
$city = $conn->real_escape_string($data['city']);
$short_description = $conn->real_escape_string($data['short_description'] ?? '');
$image_url = $conn->real_escape_string($data['image_url'] ?? 'default.jpg');
$working_hours = $conn->real_escape_string($data['working_hours'] ?? NULL);
$website = $conn->real_escape_string($data['website'] ?? NULL);

$sql = "INSERT INTO attractions (title, description, city, short_description, image_url, working_hours, website) 
        VALUES ('$title', '$description', '$city', '$short_description', '$image_url', '$working_hours', '$website')";

if ($conn->query($sql) === TRUE) {
    http_response_code(201); 
    echo json_encode([
        'success' => true,
        'message' => 'Достопримечательность успешно добавлена.',
        'id' => $conn->insert_id,
        // Возвращаем данные, чтобы React мог обновить список
        'data' => array_merge($data, ['id' => $conn->insert_id]) 
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении: ' . $conn->error]);
}

$conn->close();
?>