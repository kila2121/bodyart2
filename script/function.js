window.openMasterModal = async function (masterId, masterName, masterSpec) {
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

        const dateInput = document.getElementById('appointment-date-master');
        const timeSelect = document.getElementById('appointment-time-master');
        dateInput.disabled = true;
        dateInput.value = '';
        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';

        serviceSelect.onchange = async function () {
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

        dateInput.onchange = async function () {
            const serviceId = serviceSelect.value;

            if (this.value && masterId && serviceId) {
                timeSelect.disabled = false;
                timeSelect.innerHTML = '<option value="">Загрузка...</option>';

                await fetch(`/api/get_available_times.php?date=${this.value}&master=${masterId}&service=${serviceId}`)
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

    window.closeAppointmentModal = function () {
        if (typeof resetFormState === 'function') {
            resetFormState();
        }
        
        document.querySelector('.appointment-modal-overlay').classList.remove('active');
        document.querySelector('.appointment-modal').classList.remove('active');
        document.body.classList.remove('modal-open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAppointmentModal();
        }
    });

window.openExtendModal = async function (masterId, masterName, masterSpec, clientId) {
    document.querySelector('.appointment-modal-overlay').classList.add('active');
    document.querySelector('.appointment-modal').classList.add('active');
    document.body.classList.add('modal-open');

    document.getElementById('modal-title').innerText = 'Продление записи для клиента';

    document.getElementById('form-master').style.display = 'block';
    document.getElementById('form-service').style.display = 'none';

    document.getElementById('selected-master-id').value = masterId;
    
    // Добавляем скрытые поля для продления
    let extendClientInput = document.getElementById('extend-client-id');
    if (!extendClientInput) {
        extendClientInput = document.createElement('input');
        extendClientInput.type = 'hidden';
        extendClientInput.id = 'extend-client-id';
        extendClientInput.name = 'extend_client_id';
        const form = document.querySelector('#form-master form');
        if (form) form.appendChild(extendClientInput);
    }
    extendClientInput.value = clientId;

    let extendFlag = document.getElementById('is_extend');
    if (!extendFlag) {
        extendFlag = document.createElement('input');
        extendFlag.type = 'hidden';
        extendFlag.id = 'is_extend';
        extendFlag.name = 'is_extend';
        extendFlag.value = '1';
        const form = document.querySelector('#form-master form');
        if (form) form.appendChild(extendFlag);
    }

    const serviceSelect = document.getElementById('service-select-master');
    serviceSelect.disabled = true;
    serviceSelect.innerHTML = '<option value="">Загрузка услуг...</option>';

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

    const dateInput = document.getElementById('appointment-date-master');
    const timeSelect = document.getElementById('appointment-time-master');
    dateInput.disabled = true;
    dateInput.value = '';
    timeSelect.disabled = true;
    timeSelect.innerHTML = '<option value="">Сначала выберите дату</option>';

    serviceSelect.onchange = async function () {
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

    dateInput.onchange = async function () {
        const serviceId = serviceSelect.value;

        if (this.value && masterId && serviceId) {
            timeSelect.disabled = false;
            timeSelect.innerHTML = '<option value="">Загрузка...</option>';

            await fetch(`/api/get_available_times.php?date=${this.value}&master=${masterId}&service=${serviceId}`)
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
};