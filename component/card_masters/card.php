<?php
function selectMasters()
{
    require_once "connect.php";
    global $db;
    try {
        $sql = "SELECT * FROM master ORDER BY spec, fio";
        $masters = $db->dbs->query($sql);
        return $masters->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка получения мастеров: " . $e->getMessage());
        return [];
    }
}

if (isset($GLOBALS['mastersData']) && !empty($GLOBALS['mastersData'])) {
    $mastersItems = $GLOBALS['mastersData'];
} else {
    $mastersItems = selectMasters();
}

if (empty($mastersItems)) {
    echo '<p class="no-masters">Мастера временно не доступны</p>';
} else {
    $html = '<div class="masters-grid">';

    foreach ($mastersItems as $master) {
        $statusClass = $master['is_Active'] ? 'active' : 'inactive';
        $statusText = $master['is_Active'] ? 'Активен' : 'Неактивен';
        $rating = isset($master['rating']) ? number_format($master['rating'], 1) : '0.0';
        $avatar = $master['avatar_url'] ? $master['avatar_url'] : '/public/avatars/default.jpg';

        $html .= '<div class="master-card" data-category="' . htmlspecialchars($master['spec']) . '">';

        // Аватар с ссылкой на детальную страницу
        $html .= '<form method="POST" action="/index.php" class="master-avatar-link-form">';
        $html .= '<input type="hidden" name="page" value="details_master">';
        $html .= '<input type="hidden" name="id" value="' . $master['id'] . '">';
        $html .= '<button type="submit" class="master-avatar-link-btn" style="background:none; border:none; padding:0; cursor:pointer; width:100%;">';
        $html .= '<div class="master-avatar">';
        $html .= '<img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($master['fio']) . '" loading="lazy"' . 'onerror="this.src=`/public/uploads/avatars/master_avatars/default.jpg`">';
        $html .= '</div>';
        $html .= '</button>';
        $html .= '</form>';

        // Имя с ссылкой
        $html .= '<form method="POST" action="/index.php" class="master-name-link">';
        $html .= '<input type="hidden" name="page" value="details_master">';
        $html .= '<input type="hidden" name="id" value="' . $master['id'] . '">';
        $html .= '<button type="submit" class="master-name-link-btn" style="background:none; border:none; width:100%; cursor:pointer;">';
        $html .= '<h3 class="master-name">' . htmlspecialchars($master['fio']) . '</h3>';
        $html .= '</button>';
        $html .= '</form>';

        $html .= '<div class="master-rating">';
        $html .= '<div class="rating-stars">';
        $fullStars = floor($rating);
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                $html .= '<i class="fas fa-star"></i>';
            } elseif ($i - 0.5 <= $rating) {
                $html .= '<i class="fas fa-star-half-alt"></i>';
            } else {
                $html .= '<i class="far fa-star"></i>';
            }
        }
        $html .= '</div>';
        $html .= '<span class="rating-value">' . $rating . '</span>';
        $html .= '</div>';

        $html .= '<p class="master-specialization"><i class="fas fa-bolt"></i> ' . htmlspecialchars($master['spec']) . '</p>';
        $html .= '<p class="master-experience"><i class="fas fa-clock"></i> Опыт: ' . htmlspecialchars($master['experience']) . ' лет</p>';

        $description = htmlspecialchars($master['description']);
        if (strlen($description) > 80) {
            $description = substr($description, 0, 80) . '...';
        }
        $html .= '<p class="master-description">' . $description . '</p>';

        $html .= '<div class="master-footer">';
        $html .= '<span class="status-badge ' . $statusClass . '"><i class="fas fa-circle"></i> ' . $statusText . '</span>';
        $html .= '<button class="book-btn" onclick="openMasterModal(' . $master['id'] . ', \'' . htmlspecialchars($master['fio']) . '\', \'' . htmlspecialchars($master['spec']) . '\')">';
        $html .= '<i class="fas fa-arrow-right"></i> Записаться';
        $html .= '</button>';
        $html .= '</div>';

        $html .= '</div>';
    }

    $html .= '</div>';
    echo $html;
}
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
    function openMasterModal(masterId, masterName, masterSpec) {
        // Показываем модалку
        document.querySelector('.appointment-modal-overlay').classList.add('active');
        document.querySelector('.appointment-modal').classList.add('active');
        document.body.classList.add('modal-open');

        // Меняем заголовок
        document.getElementById('modal-title').innerText = 'Запись к мастеру ' + masterName;

        // Показываем форму мастера, скрываем форму услуги
        document.getElementById('form-master').style.display = 'block';
        document.getElementById('form-service').style.display = 'none';

        // Устанавливаем ID мастера в скрытое поле
        document.getElementById('selected-master-id').value = masterId;

        // Загружаем услуги мастера
        const serviceSelect = document.getElementById('service-select-master');
        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">Загрузка услуг...</option>';

        fetch('/api/get_services_by_master.php?master_id=' + masterId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.services.length > 0) {
                    serviceSelect.innerHTML = '<option value="">Выберите услугу</option>';
                    data.services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = service.name + ' - ' + service.price + ' ₽ (' + service.duration + ' мин)';
                        serviceSelect.appendChild(option);
                    });
                    serviceSelect.disabled = false;
                } else {
                    serviceSelect.innerHTML = '<option value="">Нет доступных услуг</option>';
                    serviceSelect.disabled = true;
                }
            });

        // Сбрасываем дату и время
        const dateInput = document.getElementById('appointment-date-master');
        const timeSelect = document.getElementById('appointment-time-master');
        dateInput.disabled = true;
        dateInput.value = '';
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';

        // Обработчик выбора услуги
        serviceSelect.onchange = function () {
            if (this.value) {
                dateInput.disabled = false;
                document.getElementById('modal-service-id-master').value = this.value;
                // Сбрасываем время при смене услуги
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';
            } else {
                dateInput.disabled = true;
                dateInput.value = '';
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';
            }
        };

        // Обработчик выбора даты
        dateInput.onchange = function () {
            const serviceId = serviceSelect.value;

            if (this.value && masterId && serviceId) {
                timeSelect.disabled = false;
                timeSelect.innerHTML = '<option value="">Загрузка...</option>';

                fetch(`/api/get_available_times.php?date=${this.value}&master=${masterId}&service=${serviceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.times.length > 0) {
                            timeSelect.innerHTML = '<option value="">Выберите время</option>';
                            data.times.forEach(time => {
                                const option = document.createElement('option');
                                option.value = time;
                                option.textContent = time;
                                timeSelect.appendChild(option);
                            });
                            timeSelect.disabled = false;
                        } else {
                            timeSelect.innerHTML = '<option value="">Нет свободного времени</option>';
                            timeSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка загрузки времени:', error);
                        timeSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                    });
            }
        };
    }

    function closeAppointmentModal() {
        document.querySelector('.appointment-modal-overlay').classList.remove('active');
        document.querySelector('.appointment-modal').classList.remove('active');
        document.body.classList.remove('modal-open');
    }

    // Закрытие по ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAppointmentModal();
        }
    });
</script>