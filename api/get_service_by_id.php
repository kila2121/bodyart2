<?php
global $db;
require_once "../connect.php";

header('Content-Type: application/json');

if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID не указан']);
    exit();
}

try {
    $id = (int) $_GET['id'];
    $stmt = $db->dbs->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($service) {
        echo json_encode(['success' => true, 'service' => $service]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
    }
} catch (Exception $e) {
    error_log("Ошибка получения услуги по id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>