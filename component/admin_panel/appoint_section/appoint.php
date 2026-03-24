<div class="tab-content" id="appointments">
    <h2>Управление записями</h2>

    <div class="appointments-list">
        <?php
        try {

            foreach ($appointments as $app): ?>
                <div class="appointment-item">
                    <div class="appointment-info">
                        <strong><?= htmlspecialchars($app['service_name']) ?></strong><br>
                        Клиент: <?= htmlspecialchars($app['user_name']) ?><br>
                        Мастер: <?= htmlspecialchars($app['master_name']) ?><br>
                        Дата: <?= date('d.m.Y H:i', strtotime($app['start_time'])) ?>
                    </div>

                    <div class="appointment-status">
                        <select onchange="updateStatus(<?= $app['id'] ?>, this.value)">
                            <option value="pending" <?= $app['status'] == 'pending' ? 'selected' : '' ?>>Ожидание</option>
                            <option value="confirmed" <?= $app['status'] == 'confirmed' ? 'selected' : '' ?>>Подтверждено</option>
                            <option value="completed" <?= $app['status'] == 'completed' ? 'selected' : '' ?>>Выполнено</option>
                            <option value="cancelled" <?= $app['status'] == 'cancelled' ? 'selected' : '' ?>>Отменено</option>
                        </select>
                    </div>
                </div>
            <?php endforeach;
        } catch (Exception $e) {
            echo "Ошибка загрузки";
        }
        ?>
    </div>
</div>