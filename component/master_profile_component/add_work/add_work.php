<?php
$appoint = $GLOBALS['master_appointments'] ?? [];

$completedAppointments = array_filter($appoint, function ($app) {
    return isset($app['status']) && $app['status'] === 'completed';
});
?>

<button type="button" class="add-work-btn">
    <i class="fas fa-plus"></i>
    Добавить работу
</button>

<div class="work-upload-container" id="work-upload-container" style="display: none;">
    <div class="work-upload-form">
        <div class="work-upload-header">
            <h3>Добавить новую работу</h3>
            <button type="button" class="work-upload-close" id="work-upload-close">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="/action.php?action=upload_work" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label for="work-title">
                    <i class="fas fa-heading"></i>
                    Название работы
                </label>
                <input type="text" name="title" id="work-title" placeholder="Например: Тату дракона" class="form-input">
            </div>

            <div class="form-group">
                <label for="work-category">
                    <i class="fas fa-tag"></i>
                    Категория
                </label>
                <select name="category" id="work-category" class="form-select">
                    <option value="tattoo">Тату</option>
                    <option value="piercing">Пирсинг</option>
                    <option value="biomod">Биомод</option>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment_id">
                    <i class="fas fa-calendar-check"></i>
                    Выберите заказ *
                </label>
                <select name="appointment_id" id="appointment_id" class="form-select" required>
                    <option value="">-- Выберите заказ --</option>
                    <?php foreach ($completedAppointments as $app): ?>
                        <option value="<?= (int) $app['id'] ?>">
                            №<?= $app['id'] ?> -
                            <?= htmlspecialchars($app['service_name'] ?? 'Услуга') ?> -
                            <?= date('d.m.Y', strtotime($app['start_time'])) ?>
                            (Клиент: <?= htmlspecialchars($app['client_name'] ?? 'Не указан') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="work-input">
                    <i class="fas fa-image"></i>
                    Фотография работы
                </label>
                <div class="file-upload-area">
                    <input type="file" name="work_photo" id="work-input"
                        accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <div class="file-upload-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Нажмите для выбора файла</p>
                        <span class="file-upload-hint">JPEG, PNG, GIF, WEBP до 5MB</span>
                    </div>
                </div>
            </div>

            <div class="work-upload-actions">
                <button type="button" class="btn btn-secondary" id="work-upload-cancel">Отмена</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i>
                    Загрузить работу
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.querySelector(".add-work-btn");
        const container = document.getElementById("work-upload-container");
        const closeBtn = document.getElementById("work-upload-close");
        const cancelBtn = document.getElementById("work-upload-cancel");
        const fileInput = document.getElementById("work-input");
        const placeholder = document.querySelector(".file-upload-placeholder p");

        if (addBtn && container) {
            addBtn.addEventListener('click', function () {
                container.style.display = "block";
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        if (closeBtn && container) {
            closeBtn.addEventListener('click', function () {
                container.style.display = "none";
            });
        }

        if (cancelBtn && container) {
            cancelBtn.addEventListener('click', function () {
                container.style.display = "none";
            });
        }

        if (fileInput && placeholder) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    placeholder.textContent = this.files[0].name;
                } else {
                    placeholder.textContent = "Нажмите для выбора файла";
                }
            });
        }

        window.addEventListener('click', function (event) {
            if (container && container.style.display === "block") {
                if (!container.contains(event.target) && !addBtn.contains(event.target)) {
                    container.style.display = "none";
                }
            }
        });
    });
</script>