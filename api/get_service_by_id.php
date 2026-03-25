<?php
global $db;
require_once "../connect.php";
require_once "../classes/cache.php";

header('Content-Type: application/json');

if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID не указан']);
    exit();
}

$id = (int) $_GET['id'];
$cacheKey = 'service_' . $id;

$result = Cache::get($cacheKey);
if ($result !== false) {
    echo json_encode($result);
    exit();
}

try {
    $stmt = $db->dbs->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($service) {
        $result = ['success' => true, 'service' => $service];
        Cache::set($cacheKey, $result, 3600);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
    }
} catch (Exception $e) {
    error_log("Ошибка получения услуги по id: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>