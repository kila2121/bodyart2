<?php
require_once "../connect.php";

header('Content-Type: application/json');

if (empty($_GET['service_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID услуги не указан']);
    exit();
}

try {
    $serviceId = (int) $_GET['service_id'];

    // Получаем категорию услуги
    $stmt = $db->dbs->prepare("SELECT category FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
        exit();
    }

    // Маппинг категорий на специализации мастеров
    $specMap = [
        'Тату' => ['Тату-мастер'],
        'Пирсинг' => ['Пирсер'],
        'Биомодификации' => ['Биомод'],
        'Уход' => ['Тату-мастер', 'Пирсер', 'Биомод'] // Уход могут все
    ];

    $specs = $specMap[$service['category']] ?? [];

    if (empty($specs)) {
        echo json_encode(['success' => false, 'message' => 'Нет подходящих мастеров']);
        exit();
    }

    // Создаем плейсхолдеры для IN
    $placeholders = implode(',', array_fill(0, count($specs), '?'));

    // Получаем мастеров с нужной специализацией
    $query = "SELECT id, fio, spec FROM master 
              WHERE is_Active = 1 AND spec IN ($placeholders) 
              ORDER BY fio";

    $stmt = $db->dbs->prepare($query);
    $stmt->execute($specs);
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'masters' => $masters]);

} catch (Exception $e) {
    error_log("Ошибка получения мастеров по услуге: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>