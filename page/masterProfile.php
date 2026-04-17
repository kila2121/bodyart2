<?php

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'master') {
    header("Location: /index.php");
    exit;
}

$userId = $_SESSION['id'];

try {
    $stmt = $db->dbs->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['status'] != 80) {
        header("Location: /index.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    header("Location: /index.php");
    exit;
}

try {
    $stmt = $db->dbs->prepare("SELECT * FROM master WHERE email = ? OR phone = ?");
    $stmt->execute([$user['email'], $user['phone']]);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ошибка: " . $e->getMessage());
    $master = null;
}

$toMeAppointments = [];
$appointments = [];
$works = [];
$stats = ['total' => 0, 'completed' => 0, 'upcoming' => 0, 'works_count' => 0];

if ($master) {
    $masterId = $master['id'];

    try {
        $stmt = $db->dbs->prepare("
            SELECT a.*, s.name as service_name, s.price,
                   u.fio as client_name, u.phone as client_phone
            FROM appointment a
            LEFT JOIN services s ON a.id_service = s.id
            LEFT JOIN user u ON a.id_user = u.id
            WHERE a.id_master = ?
            ORDER BY a.start_time DESC
        ");
        $stmt->execute([$masterId]);
        $toMeAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $db->dbs->prepare("
            SELECT g.*, a.id as appointment_id, a.start_time
            FROM gallery g
            JOIN appointment a ON a.id = g.id_appointment
            WHERE a.id_master = ?
            ORDER BY g.created_at DESC
        ");
        $stmt->execute([$masterId]);
        $works = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($toMeAppointments as $app) {
            if ($app['status'] === 'completed')
                $stats['completed']++;
            elseif (in_array($app['status'], ['pending', 'confirmed']))
                $stats['upcoming']++;
        }
        $stats['total'] = count($toMeAppointments);
        $stats['works_count'] = count($works);



        $stmt = $db->dbs->prepare("
    SELECT a.*, 
           s.name as service_name, 
           s.price,
           m.fio as master_name, 
           m.avatar_url as master_avatar,
           r.id as review_id, 
           r.rating as review_rating, 
           r.comment as review_comment
    FROM appointment a
    LEFT JOIN services s ON a.id_service = s.id
    LEFT JOIN master m ON a.id_master = m.id
    LEFT JOIN reviews r ON a.id = r.id_appointment
    WHERE a.id_user = ?
    ORDER BY a.created_at DESC
");
        $stmt->execute([$userId]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Ошибка: " . $e->getMessage());
    }
}

$GLOBALS['master_appointments'] = $toMeAppointments;
$GLOBALS['master_by_master'] = $appointments;
$GLOBALS['master_data'] = $master;
$GLOBALS['show_upload_button'] = true;

ob_start();
?>

<div class="user-profile master-profile">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="<?= htmlspecialchars($user['login']) ?>"
                onerror="this.scr='public/uploads/avatars/default.jpg'" loading="lazy">
        </div>
        <div class="profile-info">
            <h1 class="profile-name"><?= htmlspecialchars($user['fio'] ?: $user['login']) ?></h1>
            <p class="profile-login">@<?= htmlspecialchars($user['login']) ?></p>
            <p class="profile-member">На сайте с <?= date('d.m.Y', strtotime($user['date_reg'])) ?></p>
            <?php if ($master): ?>
                <p class="profile-spec">
                    Специализация: <?= htmlspecialchars($master['spec']) ?>, опыт <?= $master['experience'] ?> лет
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($master): ?>
        <?php include_once "component/master_profile_component/stats/stats.php"; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Ваш профиль мастера ещё не создан администратором.</p>
        </div>
    <?php endif; ?>

    <?php if ($master): ?>
        <div class="profile-tabs">
            <button class="tab-btn active" data-tab="appointments">Записи ко мне</button>
            <button class="tab-btn" data-tab="works">Мои работы</button>
            <button class="tab-btn active" data-tab="myAppointments">Мои записи (как клиент)</button>
            <button class="tab-btn" data-tab="settings">Настройки</button>
        </div>

        <div class="tab-content active" id="appointments-tab">
            <?php if (empty($toMeAppointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>К вам пока никто не записан</p>
                </div>
            <?php else: ?>
                <?php include_once "component/master_profile_component/appoint_list/appoint_list.php"; ?>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="myAppointments-tab">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>У вас пока нет записей</p>
                    <a href="/index.php?page=services" class="buttonApp">Записаться</a>
                </div>
            <?php else: ?>
                <?php include_once "component/user_component/appointment_list/appointment_list.php"; ?>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="works-tab">
            <?php if (empty($works)): ?>
                <?php include_once "component/master_profile_component/add_work/add_work.php"; ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <p>У вас пока нет работ в галерее</p>
                    <p class="hint">Добавьте фото к выполненным записям</p>
                </div>
            <?php else: ?>
                <?php include_once "component/master_profile_component/add_work/add_work.php"; ?>
                <?php include_once "component/master_profile_component/work_list/work_list.php"; ?>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="settings-tab">
            <?php include_once "component/master_profile_component/settings/settings.php"; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="upload-modal-overlay"></div>
<div class="modal" id="upload-modal">
    <button class="modal-close" onclick="closeUploadModal()">&times;</button>
    <div class="modal-window">
        <h2>Добавить фото работы</h2>
        <form action="/action.php?action=upload_work" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="appointment_id" id="upload-appointment-id" value="">
            <div class="form-group">
                <label for="work-title">Название (необязательно)</label>
                <input type="text" name="title" id="work-title" placeholder="Например: Тату дракона">
            </div>
            <div class="form-group">
                <label for="work-photo">Выберите фото *</label>
                <input type="file" name="work_photo" id="work-photo" accept="image/*" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Загрузить</button>
                <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<form id="avatar-upload-form" method="POST" action="/action.php?action=upload_avatar" enctype="multipart/form-data"
    style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/gif,image/webp">
</form>

<script>
    function openUploadModal(appointmentId) {
        document.getElementById('upload-appointment-id').value = appointmentId;
        document.getElementById('upload-modal').classList.add('active');
        document.getElementById('upload-modal-overlay').classList.add('active');
        document.body.classList.add('modal-open');
    }

    function closeUploadModal() {
        document.getElementById('upload-modal').classList.remove('active');
        document.getElementById('upload-modal-overlay').classList.remove('active');
        document.body.classList.remove('modal-open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        function switchTab(tabId) {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');

            window.location.hash = tabId;
        }

        if (window.location.hash) {
            const tab = window.location.hash.substring(1); // убираем #
            const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
            if (activeBtn) {
                switchTab(tab);
            }
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const tab = this.dataset.tab;
                switchTab(tab);
            });
        });

        document.getElementById('upload-modal-overlay').addEventListener('click', closeUploadModal);

        const avatarContainer = document.querySelector(".profile-avatar");
        const avatarInput = document.getElementById('avatar-input');

        if (avatarContainer) {
            avatarContainer.addEventListener('click', function () {
                avatarInput.click();
            });
        }

        if (avatarInput) {
            avatarInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    document.getElementById('avatar-upload-form').submit();
                }
            });
        }
    });

    async function deleteWork(workId) {
        if (confirm('Вы уверены, что хотите удалить это фото?')) {
            await fetch('/action.php?action=delete_work', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: workId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload()
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                })
                .catch(e => {
                    console.error('Ошибка:', e);
                    alert('Произошла ошибка при удалении работы');
                })
        }
    }
</script>

<?php
$content = ob_get_clean();

$template = new Template("BodyArt Studio - Личный кабинет мастера");
$template->addStyle("/component/master_profile_component/stats/stats.css");
$template->addStyle("/component/master_profile_component/appoint_list/appoint_list.css");
$template->addStyle("/component/master_profile_component/settings/settings.css");
$template->addStyle("/component/master_profile_component/work_list/work_list.css");
$template->addStyle("/component/master_profile_component/add_work/add_work.css");
$template->addStyle("/component/user_component/appointment_list/appointment_list.css");
$template->addStyle("/styles/page/masterProfile.css");
$template->addScript("/script/function.js");
$template->render($content);
?>