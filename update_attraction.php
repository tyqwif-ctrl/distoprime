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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен. Используйте PUT.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Отсутствует ID достопримечательности.']);
    exit;
}

$id = (int)$data['id']; 
$updates = [];

// Формируем список обновляемых полей
if (array_key_exists('title', $data)) $updates[] = "title = '" . $conn->real_escape_string($data['title']) . "'";
if (array_key_exists('description', $data)) $updates[] = "description = '" . $conn->real_escape_string($data['description']) . "'";
if (array_key_exists('city', $data)) $updates[] = "city = '" . $conn->real_escape_string($data['city']) . "'";
if (array_key_exists('short_description', $data)) $updates[] = "short_description = '" . $conn->real_escape_string($data['short_description']) . "'";
if (array_key_exists('image_url', $data)) $updates[] = "image_url = '" . $conn->real_escape_string($data['image_url']) . "'";
if (array_key_exists('working_hours', $data)) $updates[] = "working_hours = '" . $conn->real_escape_string($data['working_hours']) . "'";
if (array_key_exists('website', $data)) $updates[] = "website = '" . $conn->real_escape_string($data['website']) . "'";

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Нет данных для обновления.']);
    exit;
}

$set_clause = implode(', ', $updates);
$sql = "UPDATE attractions SET $set_clause WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Достопримечательность успешно обновлена.']);
    } else {
        // Успешный запрос, но данных нет или они не изменились
        echo json_encode(['success' => true, 'message' => 'Данные не изменились.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении: ' . $conn->error]);
}

$conn->close();
?>