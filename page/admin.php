<?php

if (!isset($_SESSION['id']) || $_SESSION['status'] !== 100) {
    header("Location: /index.php");
    exit();
}

// Получение данных
try {
    $masters = $db->dbs->query("
        SELECT m.*, u.login
        FROM master m
        LEFT JOIN user u ON (u.phone = m.phone OR u.email = m.email) AND u.status = 80
        WHERE m.is_Active = 1
        ORDER BY m.fio
    ")->fetchAll(PDO::FETCH_ASSOC);

    $services = $db->dbs->query("
        SELECT * FROM services 
        WHERE is_active = 1 
        ORDER BY category, name
    ")->fetchAll(PDO::FETCH_ASSOC);

    $pendingReviews = $db->dbs->query("
        SELECT r.*, u.login 
        FROM reviews r
        LEFT JOIN user u ON r.id_user = u.id
        WHERE r.is_approved = 0
        ORDER BY r.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $users = $db->dbs->query("
        SELECT id, login, email, fio, status, role 
        FROM user 
        ORDER BY date_reg DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $stats = $db->dbs->query("
        SELECT
            (SELECT COUNT(*) FROM master WHERE is_Active = 1) as total_masters,
            (SELECT COUNT(*) FROM services WHERE is_active = 1) as total_services,
            (SELECT COUNT(*) FROM user) as total_users,
            (SELECT COUNT(*) FROM reviews WHERE is_approved = 0) as pending_reviews,
            (SELECT COUNT(*) FROM appointment WHERE status = 'pending') as pending_appointments
    ")->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->dbs->prepare("
                SELECT a.*, 
                       u.fio as user_name,
                       s.name as service_name,
                       m.fio as master_name
                FROM appointment a
                JOIN user u ON a.id_user = u.id
                JOIN services s ON a.id_service = s.id
                JOIN master m ON a.id_master = m.id
                ORDER BY a.created_at DESC
            ");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "Ошибка загрузки данных: " . $e->getMessage();
}

ob_start();
?>
<?php include_once "component/change_theme/changeTheme.php"; ?>
<div class="admin-panel">
    <h1>Админ-панель</h1>

    <!-- Статистика -->
    <div class="stats">
        <div class="stat-card">
            <div class="number"><?= $stats['total_masters'] ?? 0 ?></div>
            <div>Мастера</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['total_services'] ?? 0 ?></div>
            <div>Услуги</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['total_users'] ?? 0 ?></div>
            <div>Пользователи</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['pending_reviews'] ?? 0 ?></div>
            <div>На модерации</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $stats['pending_appointments'] ?? 0 ?></div>
            <div>Новых записей</div>
        </div>
    </div>

    <!-- Вкладки -->
    <div class="nav-tabs">
        <button class="active" onclick="showTab('masters')">Мастера</button>
        <button onclick="showTab('services')">Услуги</button>
        <button onclick="showTab('reviews')">Отзывы
            <?= $stats['pending_reviews'] ? '(' . $stats['pending_reviews'] . ')' : '' ?></button>
        <button onclick="showTab('users')">Пользователи</button>
        <button onclick="showTab('appointments')">Записи
            <?= $stats['pending_appointments'] ? '(' . $stats['pending_appointments'] . ')' : '' ?></button>
    </div>

    <!-- Мастера -->
    <?php
    include_once "component/admin_panel/masters_section/masters.php";
    ?>

    <!-- Услуги -->
    <?php
    include_once "component/admin_panel/services_section/services.php";
    ?>

    <!-- Отзывы -->
    <?php
    include_once "component/admin_panel/reviews_section/reviews.php";
    ?>

    <!-- Пользователи -->
    <?php
    include_once "component/admin_panel/users_section/users.php";
    ?>

    <!-- Записи -->
    <?php
    include_once "component/admin_panel/appoint_section/appoint.php";
    ?>
</div>

<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-tabs button').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        event.target.classList.add('active');
    }

    function showForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideForm(formId) {
        document.getElementById(formId).style.display = 'none';
    }

    function editMaster(id) {
        showForm('editMasterForm');
        document.getElementById('editMasterFormElement').reset();
        document.getElementById('current_master_photo').innerHTML = 'Загрузка...';

        fetch('/api/get_master_by_id.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_master_id').value = data.master.id;
                    document.getElementById('edit_master_fio').value = data.master.fio;
                    document.getElementById('edit_master_spec').value = data.master.spec;
                    document.getElementById('edit_master_experience').value = data.master.experience;
                    document.getElementById('edit_master_description').value = data.master.description;

                    if (data.master.avatar_url && data.master.avatar_url !== '/masters/default.jpg') {
                        document.getElementById('current_master_photo').innerHTML =
                            '<img src="' + data.master.avatar_url + '" style="max-width: 100px; max-height: 100px;">';
                    } else {
                        document.getElementById('current_master_photo').innerHTML = 'Нет фото';
                    }
                } else {
                    alert('Ошибка: ' + data.message);
                    hideForm('editMasterForm');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при загрузке данных');
                hideForm('editMasterForm');
            });
    }

    function editService(id) {
        showForm('editServiceForm');
        document.getElementById('editServiceFormElement').reset();

        fetch('/api/get_service_by_id.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_service_id').value = data.service.id;
                    document.getElementById('edit_service_name').value = data.service.name;
                    document.getElementById('edit_service_category').value = data.service.category;
                    document.getElementById('edit_service_price').value = data.service.price;
                    document.getElementById('edit_service_duration').value = data.service.duration;
                    document.getElementById('edit_service_description').value = data.service.description;
                } else {
                    alert('Ошибка: ' + data.message);
                    hideForm('editServiceForm');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при загрузке данных');
                hideForm('editServiceForm');
            });
    }

    function updateStatus(id, status) {
        if (!confirm('Изменить статус?')) return;

        fetch('/action.php?action=update_appointment_status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id, status: status })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(e => {
                console.error('Ошибка:', e);
                alert('Ошибка изменения статуса');
            });
    }

    function updateUserRole(id, status) {
        if (!confirm('Изменить роль?')) return;

        fetch('/action.php?action=change_user_role', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id, status: status })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(e => {
                console.error('Ошибка:', e);
                alert('Ошибка изменения роли');
            });
    }

    // Инициализация при загрузке
    document.addEventListener('DOMContentLoaded', function () {
        // Сохраняем активную вкладку
        const activeTab = localStorage.getItem('adminActiveTab');
        if (activeTab) {
            const tab = document.querySelector(`[onclick="showTab('${activeTab}')"]`);
            if (tab) tab.click();
        }

        document.querySelectorAll('.nav-tabs button').forEach(btn => {
            btn.addEventListener('click', function () {
                const tabId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                localStorage.setItem('adminActiveTab', tabId);
            });
        });
    });
</script>

<?php
$content = ob_get_clean();

$template = new Template("BodyArt Studio - Админ");
$template->addStyle("/component/admin_panel/masters_section/masters.css");
$template->addStyle("/component/admin_panel/reviews_section/reviews.css");
$template->addStyle("/component/admin_panel/services_section/services.css");
$template->addStyle("/component/admin_panel/users_section/users.css");
$template->addStyle("/component/admin_panel/appoint_section/appoint.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->addStyle("/styles/page/admin.css");
$template->render($content);
?>