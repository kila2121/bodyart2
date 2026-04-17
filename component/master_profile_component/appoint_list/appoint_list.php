<div class="appointments-list">
    <?php foreach ($toMeAppointments as $app):
        $statusClass = '';
        $statusText = '';

        switch ($app['status']) {
            case 'pending':
                $statusClass = 'status-pending';
                $statusText = 'Ожидание подтверждения';
                break;
            case 'confirmed':
                $statusClass = 'status-confirmed';
                $statusText = 'Подтверждено';
                break;
            case 'completed':
                $statusClass = 'status-completed';
                $statusText = 'Выполнено';
                break;
            case 'cancelled':
                $statusClass = 'status-cancelled';
                $statusText = 'Отменено';
                break;
        }
        ?>
        <div class="appointment-card" id="appointment-<?= $app['id'] ?>">
            <div class="appointment-header">
                <div class="appointment-service">
                    <h3><?= htmlspecialchars($app['service_name'] ?: 'Услуга') ?></h3>
                    <span class="appointment-price"><?= number_format($app['price'], 0, '.', ' ') ?> ₽</span>
                </div>
                <span class="appointment-status <?= $statusClass ?>"><?= $statusText ?></span>
            </div>

            <div class="appointment-body">
                <div class="appointment-client">
                    <i class="fas fa-user"></i>
                    <span>Клиент: <?= htmlspecialchars($app['client_name'] ?: 'Не указан') ?></span>
                </div>

                <div class="appointment-client-phone">
                    <i class="fas fa-phone"></i>
                    <span><?= htmlspecialchars($app['client_phone'] ?: 'Телефон не указан') ?></span>
                </div>

                <div class="appointment-datetime">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('d.m.Y', strtotime($app['start_time'])) ?> в
                    <?= date('H:i', strtotime($app['start_time'])) ?>
                </div>

                <?php if (!empty($app['notes'])): ?>
                    <div class="appointment-notes">
                        <i class="fas fa-comment"></i>
                        <?= htmlspecialchars($app['notes']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="appointment-footer">
                <?php if ($app['status'] === 'pending'): ?>
                    <div class="appointment-actions">
                        <button class="button button-confirm" value="confirmed"
                            onclick="updateStatus(<?= $app['id'] ?>, this.value)">
                            <i class="fas fa-check"></i> Подтвердить
                        </button>
                        <button class="button button-reject" value="cancelled"
                            onclick="updateStatus(<?= $app['id'] ?>, this.value)">
                            <i class="fas fa-times"></i> Отклонить
                        </button>
                    </div>
                <?php elseif ($app['status'] === 'confirmed'): ?>
                    <div class="appointment-actions-group">
                        <button class="button button-complete" value="completed"
                            onclick="updateStatus(<?= $app['id'] ?>, this.value)">
                            <i class="fas fa-check-double"></i> Завершить
                        </button>
                        <button class="button button-extend" value="completed"
                            onclick="updateStatusAndExtend(<?= $app['id'] ?>, 'completed', <?= $masterId ?>, '<?= htmlspecialchars($master['fio']) ?>', '<?= htmlspecialchars($master['spec']) ?>', <?= $app['id_user'] ?>)">
                            <i class="fas fa-plus"></i> Продлить
                        </button>
                    </div>
                <?php elseif ($app['status'] === 'completed' && !empty($GLOBALS['show_upload_button'])): ?>
                    <button class="button button-upload" onclick="openUploadModal(<?= $app['id'] ?>)">
                        <i class="fas fa-camera"></i> Добавить фото
                    </button>
                <?php endif; ?>

                <?php if ($app['status'] === 'confirmed'): ?>
                    <button class="button button-cancel" onclick="updateStatus(<?= $app['id'] ?>, 'cancelled')">
                        Отменить запись
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
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

<script>
    async function cancelAppointment(id) {
        if (confirm('Отменить запись?')) {
            await fetch('/action.php?action=cancel_appointment', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.showMessage('success', 'Запись успешно отменена');
                    } else {
                        window.showMessage('error', 'Ошибка отмены записи');
                    }

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                })
                .catch(e => {
                    console.error('Ошибка:', e);
                    alert('Произошла отметы записи');
                })
        }
    }

    async function updateStatus(id, status) {
        if (!confirm('Изменить статус?')) return;

        await fetch('/action.php?action=update_appointment_status', {
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
                    window.showMessage('success', 'Статус успешно обновлен');
                } else {
                    window.showMessage('error', 'Ошибка изменения статуса');
                }

                setTimeout(() => {
                    location.reload();
                }, 1500);
            })
            .catch(e => {
                console.error('Ошибка:', e);
                alert('Произошла обновления статуса');
            });
    }

    async function updateStatusAndExtend(id, status, masterId, masterName, masterSpec, clientId) {
        if (!confirm('Завершить запись и создать новую для клиента?')) return;

        try {
            const response = await fetch('/action.php?action=update_appointment_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id, status: status })
            });

            const data = await response.json();

            if (data.success) {
                window.openExtendModal(masterId, masterName, masterSpec, clientId);
            } else {
                alert('Ошибка: ' + data.message);
            }
        } catch (e) {
            console.error('Ошибка:', e);
            alert('Произошла ошибка при обновлении статуса');
        }
    }
</script>