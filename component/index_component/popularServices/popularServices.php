<section class="popular-services">
    <div class="section-header">
        <h2 class="section-title">Популярные услуги</h2>
        <a href="/index.php?page=services" class="view-all">Все услуги <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="services-preview">
        <?php
        try {
            $previewServices = Cache::get('popular_services');
            if ($previewServices === false) {
                $previewServices = $db->dbs->query("
                    SELECT 
                        s.id, 
                        s.name, 
                        s.price, 
                        s.duration, 
                        s.category,
                        (
                            SELECT g.url 
                            FROM gallery g
                            LEFT JOIN appointment a ON a.id = g.id_appointment
                            WHERE a.id_service = s.id
                            ORDER BY g.is_featured DESC, g.created_at DESC
                            LIMIT 1
                        ) as gallery_url,
                        COUNT(a.id) as appointments_count
                    FROM services s
                    LEFT JOIN appointment a ON a.id_service = s.id
                    WHERE s.is_active = 1
                    GROUP BY s.id, s.name, s.price, s.duration, s.category
                    ORDER BY appointments_count DESC, s.name
                    LIMIT 4
                ")->fetchAll(PDO::FETCH_ASSOC);
                Cache::set('popular_services', $previewServices, 3600);
            }

            $categoryColors = [
                'Тату' => '#ff3366',
                'Пирсинг' => '#10b981',
                'Биомодификации' => '#f59e0b',
                'Уход' => '#8b5cf6'
            ];

            foreach ($previewServices as $service):
                $categoryColor = $categoryColors[$service['category']] ?? '#6c757d';

                $serviceImage = !empty($service['gallery_url'])
                    ? $service['gallery_url']
                    : '/public/uploads/gallery_work/default.jpg';
                ?>
                <div class="preview-card">
                    <div class="card-image" style="background-image: url('<?= htmlspecialchars($serviceImage) ?>'), url('/public/uploads/gallery_work/default.jpg');
                        background-repeat: no-repeat, no-repeat;">
                        <div class="card-overlay"
                            style="background: linear-gradient(180deg, transparent 0%, <?= $categoryColor ?> 100%);"></div>
                        <span class="card-category" style="background: <?= $categoryColor ?>;">
                            <?= htmlspecialchars($service['category']) ?>
                        </span>
                    </div>

                    <div class="card-content">
                        <h3><?= htmlspecialchars($service['name']) ?></h3>

                        <div class="card-info">
                            <div class="card-duration">
                                <i class="fas fa-clock" style="color: <?= $categoryColor ?>;"></i>
                                <span><?= $service['duration'] ?> мин</span>
                            </div>
                            <div class="card-price" style="color: <?= $categoryColor ?>;">
                                <?= number_format($service['price'], 0, '', ' ') ?> ₽
                            </div>
                        </div>

                        <button onclick="openServiceModal(<?= (int) $service['id'] ?>)" class="card-btn"
                            style="background: <?= $categoryColor ?>;">
                            Записаться
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <?php
            endforeach;
        } catch (Exception $e) {
        }
        ?>
    </div>
</section>

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