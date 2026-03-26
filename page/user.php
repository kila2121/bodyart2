<?php

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = 'Необходимо авторизоваться';
    header("Location: /index.php");
    exit();
}

$userId = $_SESSION['id'];

// Получаем данные пользователя
$user = null;
try {
    $stmt = $db->dbs->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ошибка получения пользователя: " . $e->getMessage());
}

if (!$user) {
    header("Location: /index.php");
    exit;
}

// Получаем записи пользователя
$appointments = [];
try {
    $stmt = $db->dbs->prepare("
        SELECT a.*, 
               s.name as service_name, s.price,
               m.fio as master_name, m.avatar_url as master_avatar,
               r.id as review_id, r.rating as review_rating, r.comment as review_comment
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
    error_log("Ошибка получения записей: " . $e->getMessage());
}

// Подсчет статистики
$totalAppointments = count($appointments);
$completedAppointments = 0;
$upcomingAppointments = 0;
$totalSpent = 0;

foreach ($appointments as $app) {
    if ($app['status'] === 'completed') {
        $completedAppointments++;
        $totalSpent += $app['price'];
    } elseif ($app['status'] === 'confirmed' || $app['status'] === 'pending') {
        $upcomingAppointments++;
    }
}

ob_start();
?>
<div class="user-profile">
    <?php include_once "component/change_theme/changeTheme.php"; ?>
    <div class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="<?= htmlspecialchars($user['login']) ?>">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?= mb_strtoupper(mb_substr($user['login'] ?: $user['fio'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1 class="profile-name"><?= htmlspecialchars($user['fio'] ?: $user['login']) ?></h1>
            <p class="profile-login">@<?= htmlspecialchars($user['login']) ?></p>
            <p class="profile-member">На сайте с <?= date('d.m.Y', strtotime($user['date_reg'])) ?></p>
        </div>
    </div>

    <?php include_once "component/user_component/stats/stats.php"; ?>

    <div class="profile-tabs">
        <button class="tab-btn active" data-tab="appointments">Мои записи</button>
        <button class="tab-btn" data-tab="settings">Настройки</button>
    </div>

    <div class="tab-content active" id="appointments-tab">
        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>У вас пока нет записей</p>
                <a href="/index.php?page=services" class="button">Записаться</a>
            </div>
        <?php else: ?>
            <?php include_once "component/user_component/appointment_list/appointment_list.php"; ?>
        <?php endif; ?>
    </div>

    <div class="tab-content" id="settings-tab">
        <?php include_once "component/user_component/settings/settings.php"; ?>
    </div>
</div>

<form id="avatar-upload-form" method="POST" action="/action.php?action=upload_avatar" enctype="multipart/form-data"
    style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/gif,image/webp">
</form>

<script>
    function cancelAppointment(appointmentId) {
        if (confirm('Вы уверены, что хотите отменить запись?')) {
            fetch('/action.php?action=cancel_appointment', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: appointmentId })
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
                    alert('Произошла ошибка при отмене записи');
                })
        }
    }

    // Переключение табов
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        function switchTab(tabId) {
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');

            // Сохраняем в URL hash
            window.location.hash = tabId;
        }

        // Проверяем hash при загрузке
        if (window.location.hash) {
            const tab = window.location.hash.substring(1); // убираем #
            const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
            if (activeBtn) {
                switchTab(tab);
            }
        }

        // Обработчики кликов
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const tab = this.dataset.tab;
                switchTab(tab);
            });
        });
    });

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
</script>

<?php
$content = ob_get_clean();
$template = new Template("BodyArt Studio - Личный кабинет");
$template->addStyle("/component/user_component/appointment_list/appointment_list.css");
$template->addStyle("/component/user_component/settings/settings.css");
$template->addStyle("/component/user_component/stats/stats.css");
$template->addStyle("/component/user_component/reviews_section/reviews.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->addStyle("/styles/page/user.css");
$template->render($content);
?>