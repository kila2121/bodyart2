<div class="sticky-action">
    <button class="appointment-btn"
        onclick="openMasterModal(<?= $master['id'] ?>, '<?= htmlspecialchars(addslashes($master['fio'])) ?>', '<?= htmlspecialchars(addslashes($master['spec'])) ?>')">
        <span>Записаться к мастеру</span>
        <i class="fas fa-arrow-right"></i>
    </button>
    <p class="master-since">
        <i class="fas fa-calendar-alt"></i>
        В студии с
        <?= date('d.m.Y', strtotime($master['created_at'])) ?>
    </p>
</div>

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
        document.querySelector('.appointment-modal-overlay').classList.add('active');
        document.querySelector('.appointment-modal').classList.add('active');
        document.body.classList.add('modal-open');

        document.getElementById('modal-title').innerText = 'Запись к мастеру ' + masterName;

        document.getElementById('form-master').style.display = 'block';
        document.getElementById('form-service').style.display = 'none';

        document.getElementById('selected-master-id').value = masterId;

        const serviceSelect = document.getElementById('service-select-master');
        serviceSelect.disabled = true;
        serviceSelect.innerHTML = '<option value="">Загрузка услуг...</option>';

        async function getServiceByMaster() {
            await fetch('/api/get_services_by_master.php?master_id=' + masterId)
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
        }

        getServiceByMaster();

        const dateInput = document.getElementById('appointment-date-master');
        const timeSelect = document.getElementById('appointment-time-master');
        dateInput.disabled = true;
        dateInput.value = '';
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';

        serviceSelect.onchange = function () {
            if (this.value) {
                dateInput.disabled = false;
                document.getElementById('modal-service-id-master').value = this.value;
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';
            } else {
                dateInput.disabled = true;
                dateInput.value = '';
                timeSelect.disabled = true;
                timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';
            }
        };

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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAppointmentModal();
        }
    });
</script>