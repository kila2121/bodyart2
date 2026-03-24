<?php
require_once __DIR__ . "/../connect.php";

header('Content-Type: application/json');

$query = $_POST['q'] ?? $_GET['q'] ?? '';
$type = $_POST['type'] ?? $_GET['type'] ?? 'all';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$results = [];

// Поиск по услугам
if ($type == 'all' || $type == 'services') {
    $stmt = $db->dbs->prepare("
        SELECT id, name, price, category,
               MATCH(name, description) AGAINST(?) as relevance
        FROM services 
        WHERE MATCH(name, description) AGAINST(? IN NATURAL LANGUAGE MODE)
        ORDER BY relevance DESC
        LIMIT 5
    ");
    $stmt->execute([$query, $query]); // Передаем оба параметра
    $results['services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Поиск по мастерам
if ($type == 'all' || $type == 'masters') {
    $stmt = $db->dbs->prepare("
        SELECT id, fio, spec, rating, avatar_url,
               MATCH(fio, spec, description) AGAINST(?) as relevance
        FROM master 
        WHERE is_Active = 1 
            AND MATCH(fio, spec, description) AGAINST(? IN NATURAL LANGUAGE MODE)
        ORDER BY relevance DESC, rating DESC
        LIMIT 5
    ");
    $stmt->execute([$query, $query]);
    $results['masters'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Поиск по галерее
if ($type == 'all' || $type == 'gallery') {
    // В таблице gallery нет поля description, ищем только по title
    $stmt = $db->dbs->prepare("
        SELECT id, url, title, category,
               MATCH(title) AGAINST(?) as relevance
        FROM gallery
        WHERE MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)
        ORDER BY relevance DESC
        LIMIT 5
    ");
    $stmt->execute([$query, $query]);
    $results['gallery'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([
    'success' => true,
    'results' => $results
]);
?>