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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен. Используйте DELETE.']);
    exit;
}

// DELETE-запросы могут приходить как URL-кодированные данные
parse_str(file_get_contents("php://input"), $delete_vars);
$id = isset($delete_vars['id']) ? (int)$delete_vars['id'] : 0;

if ($id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Отсутствует ID для удаления.']);
    exit;
}

$sql = "DELETE FROM attractions WHERE id = $id";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Достопримечательность успешно удалена.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Достопримечательность с ID ' . $id . ' не найдена.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении: ' . $conn->error]);
}

$conn->close();
?>