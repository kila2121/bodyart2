<?php
require_once "../connect.php";

header('Content-Type: application/json');

if (empty($_GET['date']) || empty($_GET['master']) || empty($_GET['service'])) {
    echo json_encode(['success' => false, 'message' => 'Не все параметры указаны']);
    exit();
}

try {
    $date = $_GET['date'];
    $masterId = (int) $_GET['master'];
    $serviceId = (int) $_GET['service'];

    // Получаем длительность услуги
    $stmt = $db->dbs->prepare("SELECT duration FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $duration = $stmt->fetchColumn();

    if (!$duration) {
        echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
        exit();
    }

    // Получаем занятое время мастера
    $startOfDay = $date . ' 00:00:00';
    $endOfDay = $date . ' 23:59:59';
    $stmt = $db->dbs->prepare("
        SELECT start_time, stop_time 
        FROM appointment 
        WHERE id_master = ? 
        AND start_time >= ? AND start_time <= ?
        AND status IN ('pending', 'confirmed')
        ORDER BY start_time
    ");
    $stmt->execute([$masterId, $startOfDay, $endOfDay]);
    $busySlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Рабочие часы (10:00 - 20:00)
    $startHour = 10;
    $endHour = 20;
    $interval = 30; // минут

    // Генерируем все возможные слоты
    $allTimes = [];
    for ($h = $startHour; $h < $endHour; $h++) {
        for ($m = 0; $m < 60; $m += $interval) {
            $time = sprintf("%02d:%02d", $h, $m);
            $allTimes[] = $time;
        }
    }

    // Фильтруем занятые слоты
    $availableTimes = [];
    foreach ($allTimes as $time) {
        $slotStart = strtotime("$date $time");
        $slotEnd = $slotStart + ($duration * 60);

        $isAvailable = true;

        foreach ($busySlots as $busy) {
            $busyStart = strtotime($busy['start_time']);
            $busyEnd = strtotime($busy['stop_time']);

            // Проверяем пересечение
            if (
                ($slotStart >= $busyStart && $slotStart < $busyEnd) ||
                ($slotEnd > $busyStart && $slotEnd <= $busyEnd) ||
                ($slotStart <= $busyStart && $slotEnd >= $busyEnd)
            ) {
                $isAvailable = false;
                break;
            }
        }

        // Проверяем, что запись заканчивается до конца рабочего дня
        if ($slotEnd <= strtotime("$date $endHour:00")) {
            if ($isAvailable) {
                $availableTimes[] = $time;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'times' => $availableTimes,
        'duration' => $duration
    ]);

} catch (Exception $e) {
    error_log("Ошибка получения времени: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
}
exit();
?>