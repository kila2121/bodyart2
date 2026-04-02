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

$userId = $_SESSION['id'];
$masterId = null;
try {
    $stmt = $db->dbs->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user['status'] == 80) {
        $stmt = $db->dbs->prepare("SELECT id FROM master WHERE email = ? OR phone = ?");
        $stmt->execute([$user['email'], $user['phone']]);
        $masterData = $stmt->fetch(PDO::FETCH_ASSOC);
        $masterId = $masterData ? (int) $masterData['id'] : null;
    }


} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    $masterId = null;
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

        $html .= '<form method="POST" action="/index.php" class="master-avatar-link-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
        $html .= '<input type="hidden" name="page" value="details_master">';
        $html .= '<input type="hidden" name="id" value="' . $master['id'] . '">';
        $html .= '<button type="submit" class="master-avatar-link-btn" style="background:none; border:none; padding:0; cursor:pointer; width:100%;">';
        $html .= '<div class="master-avatar">';
        $html .= '<img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($master['fio']) . '" loading="lazy"' . 'onerror="this.src=`/public/uploads/avatars/default.jpg`">';
        $html .= '</div>';
        $html .= '</button>';
        $html .= '</form>';

        $html .= '<form method="POST" action="/index.php" class="master-name-link">';
        $html .= '<input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">';
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
        $html .= '<button class="book-btn" onclick="openMasterModal(' . $master['id'] . ', \'' . htmlspecialchars($master['fio']) . '\', \'' . htmlspecialchars($master['spec']) . '\'), checkYourSelf()">';
        $html .= '<i class="fas fa-arrow-right"></i> Записаться';
        $html .= '</button>';
        $html .= '</div>';

        $html .= '</div>';
    }

    $html .= '</div>';
    echo $html;
}
?>

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

<script>
    function resetFormState() {
        const yourselfBlock = document.getElementById('yourself');
        if (yourselfBlock) {
            yourselfBlock.style.display = 'none';
        }

        const serviceSelect = document.getElementById('service-select-master');
        const submitBtn = document.querySelector('#form-master button[type="submit"]');
        const notesField = document.getElementById('notes');

        if (notesField && notesField.disabled) {
            notesField.disabled = false;
            notesField.style.opacity = '';
        }
        if (submitBtn && submitBtn.disabled && <?= json_encode(isset($_SESSION['id'])) ?>) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '';
            submitBtn.style.cursor = '';
        }
    }

    function checkYourSelf() {
        setTimeout(() => {
            const masterId = parseInt(document.getElementById('selected-master-id').value);
            const masterIdUser = <?= json_encode($masterId) ?>;

            console.log('Проверка:', { masterIdUser, masterId });

            if (masterIdUser && masterId && masterIdUser === masterId) {
                const yourselfBlock = document.getElementById('yourself');
                if (yourselfBlock) {
                    yourselfBlock.style.display = 'flex';
                }

                const elementsToDisable = [
                    'service-select-master',
                    'appointment-date-master',
                    'appointment-time-master'
                ];

                elementsToDisable.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.disabled = true;
                        el.style.opacity = '0.6';
                        el.style.cursor = 'not-allowed';
                    }
                });

                const notesField = document.getElementById('notes');
                if (notesField) {
                    notesField.disabled = true;
                    notesField.style.opacity = '0.6';
                }

                const submitBtn = document.querySelector('#form-master button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.cursor = 'not-allowed';
                    submitBtn.title = 'Нельзя записаться к себе';
                }
            }
        }, 200);
    }
</script>