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
    $stmt = $db->dbs->prepare("SELECT * FROM master WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($master) {
        echo json_encode(['success' => true, 'master' => $master]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Мастер не найден']);
    }
} catch (Exception $e) {
    error_log("Ошибка получения мастера по id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>