<?php
global $db;
require_once "../connect.php";

header('Content-Type: application/json');

if (empty($_GET['master_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID мастера не указан']);
    exit();
}

try {
    $masterId = (int) $_GET['master_id'];

    $stmt = $db->dbs->prepare("SELECT spec FROM master WHERE id = ?");
    $stmt->execute([$masterId]);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$master) {
        echo json_encode(['success' => false, 'message' => 'Мастер не найден']);
        exit();
    }

    $spec = $master['spec'];

    $categoryMap = [
        'Тату-мастер' => 'Тату',
        'Пирсер' => 'Пирсинг',
        'Биомод' => 'Биомодификации'
    ];

    $category = $categoryMap[$spec] ?? $spec;

    $stmt = $db->dbs->prepare("
        SELECT * FROM services 
        WHERE is_active = 1 
        AND category = ?
        ORDER BY name
    ");
    $stmt->execute([$category]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'services' => $services,
        'master_id' => $masterId,
        'spec' => $spec,
        'category' => $category
    ]);

} catch (Exception $e) {
    error_log("Ошибка получения услуги по мастеру: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>