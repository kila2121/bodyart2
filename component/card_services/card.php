<?php

function selectServices()
{
    global $db;

    $sql = "SELECT s.*,
       COALESCE(g.url, '/public/uploads/gallery_work/default.jpg') as gallery_url,
       COALESCE(g.photo_count, 0) as photos_count
FROM services s
LEFT JOIN (
    SELECT 
        a.id_service,
        FIRST_VALUE(g.url) OVER (PARTITION BY a.id_service ORDER BY g.is_featured DESC, g.created_at DESC) as url,
        COUNT(*) OVER (PARTITION BY a.id_service) as photo_count,
        ROW_NUMBER() OVER (PARTITION BY a.id_service ORDER BY g.is_featured DESC, g.created_at DESC) as rn
    FROM gallery g
    JOIN appointment a ON a.id = g.id_appointment
    WHERE a.id_service IS NOT NULL
) g ON s.id = g.id_service AND g.rn = 1
WHERE s.is_active = 1
ORDER BY s.category, s.name;";

    try {
        $stmt = $db->dbs->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка получения услуг: " . $e->getMessage());
        return [];
    }
}

function getCategoryIcon($category)
{
    switch ($category) {
        case 'Тату':
            return 'fas fa-paint-brush';
        case 'Пирсинг':
            return 'fas fa-gem';
        case 'Биомодификации':
            return 'fas fa-bolt';
        case 'Уход':
            return 'fas fa-heart';
        default:
            return 'fas fa-tag';
    }
}

function formatDuration($minutes)
{
    if ($minutes < 60)
        return $minutes . ' мин';
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . ' ч ' . ($mins > 0 ? $mins . ' мин' : '');
}

function renderCards()
{
    $services = selectServices();
    if (empty($services))
        return '<p class="no-services">Услуги временно не доступны</p>';

    $html = '<div class="services-grid">';
    foreach ($services as $index => $service) {
        $categoryIcon = getCategoryIcon($service['category']);
        $durationFormatted = formatDuration($service['duration']);
        $priceFormatted = number_format($service['price'], 0, '', ' ');
        $imageUrl = !empty($service['gallery_url']) ? $service['gallery_url'] : '/public/uploads/gallery_work/default.jpg';

        $html .= '<div class="service-card" data-category="' . htmlspecialchars($service['category']) . '">';
        $html .= '<div class="service-image">';
        $html .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . htmlspecialchars($service['name']) . '" loading="lazy"' . 'onerror="this.src=`/public/uploads/gallery_work/default.jpg`">';
        if (!empty($service['photos_count']) && $service['photos_count'] > 1) {
            $html .= '<span class="photo-badge"><i class="fas fa-camera"></i> ' . $service['photos_count'] . '</span>';
        }
        $html .= '</div>';
        $html .= '<div class="service-content">';
        $html .= '<div class="service-category"><i class="' . $categoryIcon . '"></i><span>' . htmlspecialchars($service['category']) . '</span></div>';
        $html .= '<h3 class="service-title">' . htmlspecialchars($service['name']) . '</h3>';
        $description = htmlspecialchars($service['description']);
        if (strlen($description) > 100)
            $description = substr($description, 0, 100) . '...';
        $html .= '<p class="service-description">' . $description . '</p>';
        $html .= '<div class="service-features">';
        $html .= '<div class="feature"><i class="fas fa-clock"></i><span>' . $durationFormatted . '</span></div>';
        $html .= '<div class="feature price"><span class="price-value">' . $priceFormatted . ' ₽</span></div>';
        $html .= '</div>';
        $html .= '<button class="book-btn" onclick="openServiceModal(' . $service['id'] . ')">';
        $html .= '<i class="fas fa-arrow-right"></i> Записаться</button>';
        $html .= '</div></div>';
    }
    $html .= '</div>';
    return $html;
}

echo renderCards();
?>

<!-- Модалка записи -->
<div class="appointment-modal-overlay" onclick="closeAppointmentModal()"></div>
<div class="appointment-modal">
    <div class="modal-header">
        <h2 id="modal-title">Запись на услугу</h2>
        <button class="modal-close" onclick="closeAppointmentModal()">&times;</button>
    </div>
    <div class="modal-body">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . "/component/modal_window/appointment_form.php"; ?>
    </div>
</div>

<link rel="stylesheet" href="/component/modal_window/appointment_form.css">

<script>
    function openServiceModal(serviceId) {
        // Показываем модалку
        document.querySelector('.appointment-modal-overlay').classList.add('active');
        document.querySelector('.appointment-modal').classList.add('active');
        document.body.classList.add('modal-open');

        document.getElementById('modal-title').textContent = 'Запись на услугу';

        // Скрываем форму мастера, показываем форму услуги
        document.getElementById('form-master').style.display = 'none';
        document.getElementById('form-service').style.display = 'block';

        // Загружаем данные услуги
        fetch('/api/get_service_by_id.php?id=' + serviceId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.service;
                    document.getElementById('service-name').textContent = s.name;
                    document.getElementById('service-price').textContent = s.price + ' ₽';
                    document.getElementById('service-duration').textContent = s.duration + ' мин';
                    document.getElementById('modal-service-id').value = s.id;
                    document.getElementById('service-info').style.display = 'block';

                    if (window.loadMastersByService) {
                        window.loadMastersByService(serviceId);
                    }
                }
            });
    }

    function closeAppointmentModal() {
        document.querySelector('.appointment-modal-overlay').classList.remove('active');
        document.querySelector('.appointment-modal').classList.remove('active');
        document.body.classList.remove('modal-open');
    }
</script>