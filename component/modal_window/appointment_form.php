<div class="modal-body">
    <div class="appointment-form-container" id="form-master">
        <form method="POST" action="/action.php?action=create_appointment">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="service_id" id="modal-service-id-master" value="">
            <input type="hidden" name="master_id" id="selected-master-id" value="">

            <div class="form-group">
                <label for="service-select-master">Выберите услугу:</label>
                <select id="service-select-master" name="service_id_select" required disabled>
                    <option value="">Загрузка...</option>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment-date-master">Выберите дату:</label>
                <input type="date" id="appointment-date-master" name="date" required disabled
                    min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>">
            </div>

            <div class="form-group">
                <label for="appointment-time-master">Выберите время:</label>
                <select id="appointment-time-master" name="time" required disabled>
                    <option value="">Сначала выберите дату</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Комментарий</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Опишите пожелания, эскиз и т.д."></textarea>
            </div>

            <?php if (!isset($_SESSION['id'])): ?>
                <div class="auth-warning">
                    <p>Для записи необходимо
                        <a href="#" onclick="openAuthModalFromAppointment(); return false;">войти</a> или
                        <a href="#" onclick="openRegModalFromAppointment(); return false;">зарегистрироваться</a>
                    </p>
                </div>
            <?php endif; ?>
            <div style="display:none" id="yourself" class="self-booking-warning">
                <i class="fas fa-ban"></i>
                <span>Вы не можете записаться на услугу к самому себе!</span>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeAppointmentModal()">Отмена</button>
                <button type="submit" class="btn btn-primary" <?= !isset($_SESSION['id']) ? 'disabled' : '' ?>>Записаться</button>
            </div>
        </form>
    </div>

    <form method="POST" action="/action.php?action=create_appointment" id="form-service">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="service_id" id="modal-service-id" value="">

        <div class="service-info" id="service-info" style="display: none;">
            <h3 id="service-name"></h3>
            <div class="service-details">
                <span class="price" id="service-price"></span>
                <span class="duration" id="service-duration"></span>
            </div>
        </div>

        <div class="form-group">
            <label>Мастер</label>
            <select name="master_id" id="master-select" required disabled>
                <option value="">Сначала выберите услугу</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="date" id="appointment-date" min="<?= date('Y-m-d') ?>" required disabled>
            </div>

            <div class="form-group">
                <label>Время</label>
                <select name="time" id="appointment-time" required disabled>
                    <option value="">Сначала выберите дату</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Комментарий</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Опишите пожелания, эскиз и т.д."></textarea>
        </div>

        <?php if (!isset($_SESSION['id'])): ?>
            <div class="auth-warning">
                <p>Для записи необходимо
                    <a href="#" onclick="openAuthModalFromAppointment(); return false;">войти</a> или
                    <a href="#" onclick="openRegModalFromAppointment(); return false;">зарегистрироваться</a>
                </p>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="closeAppointmentModal()">Отмена</button>
            <button type="submit" class="btn btn-primary" <?= !isset($_SESSION['id']) ? 'disabled' : '' ?>>
                Записаться
            </button>
        </div>
    </form>
</div>
<script>
    window.loadMastersByService = async function (serviceId) {
        const masterSelect = document.getElementById('master-select');
        const dateInput = document.getElementById('appointment-date');
        const timeSelect = document.getElementById('appointment-time');

        dateInput.value = '';
        dateInput.disabled = true;
        timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';
        timeSelect.disabled = true;

        masterSelect.innerHTML = '<option value="">Загрузка мастеров...</option>';
        masterSelect.disabled = true;

        await fetch('/api/get_masters_by_service.php?service_id=' + serviceId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.masters && data.masters.length > 0) {
                    masterSelect.innerHTML = '<option value="">Выберите мастера</option>';
                    data.masters.forEach(master => {
                        const option = document.createElement('option');
                        option.value = master.id;
                        option.textContent = `${master.fio} (${master.spec})`;
                        masterSelect.appendChild(option);
                    });
                    masterSelect.disabled = false;
                } else {
                    masterSelect.innerHTML = '<option value="">Нет доступных мастеров</option>';
                    masterSelect.disabled = true;
                }
            });
    }

    window.loadAvailableTimes = async function (date, masterId, serviceId, formType) {
        const timeSelect = formType === 'master'
            ? document.getElementById('appointment-time-master')
            : document.getElementById('appointment-time');

        timeSelect.innerHTML = '<option value="">Загрузка...</option>';
        timeSelect.disabled = true;

        await fetch(`/api/get_available_times.php?date=${date}&master=${masterId}&service=${serviceId}`)
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
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const masterSelect = document.getElementById('master-select');
        const dateInput = document.getElementById('appointment-date');

        if (masterSelect) {
            masterSelect.addEventListener('change', function () {
                if (this.value && !this.disabled) {
                    dateInput.disabled = false;
                } else {
                    dateInput.disabled = true;
                    dateInput.value = '';
                    document.getElementById('appointment-time').innerHTML = '<option value="">Сначала выберите дату</option>';
                    document.getElementById('appointment-time').disabled = true;
                }
            });
        }

        if (dateInput) {
            dateInput.addEventListener('change', function () {
                const masterId = masterSelect?.value;
                const serviceId = document.getElementById('modal-service-id')?.value;
                if (this.value && masterId && serviceId) {
                    loadAvailableTimes(this.value, masterId, serviceId, 'service');
                }
            });
        }

    });

    function openAuthModalFromAppointment() {
        closeAppointmentModal();
        const modal = document.querySelector('.modal');
        const overlay = document.querySelector('.modal-overlay');
        if (modal && overlay) {
            modal.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('modal-open');
            const authTab = document.querySelector('[data-tab="auth"]');
            if (authTab) authTab.click();
        }
    }

    function openRegModalFromAppointment() {
        closeAppointmentModal();
        setTimeout(() => {
            const modal = document.querySelector('.modal');
            const overlay = document.querySelector('.modal-overlay');
            if (modal && overlay) {
                modal.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('modal-open');
                const regTab = document.querySelector('[data-tab="reg"]');
                if (regTab) regTab.click();
            }
        }, 300);
    }
</script>