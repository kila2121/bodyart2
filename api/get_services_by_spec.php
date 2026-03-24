<?php
global $db;
require_once "../connect.php";

header('Content-Type: application/json');

if (empty($_GET['spec'])) {
    echo json_encode(['success' => false, 'message' => 'Специализация не указана']);
    exit();
}

try {
    $spec = $_GET['spec'];

    $stmt = $db->dbs->prepare("
        SELECT * FROM services 
        WHERE is_active = 1 
        ORDER BY name
    ");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'services' => $services,
        'spec' => $spec
    ]);

} catch (Exception $e) {
    error_log("Ошибка получения услуги по категории: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>