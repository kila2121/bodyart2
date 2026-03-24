<div class="appointments-list">
    <?php foreach ($appointments as $app):
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
                <div class="appointment-master">
                    <div class="master-avatar-small">
                        <?php if (!empty($app['master_avatar'])): ?>
                            <img src="<?= htmlspecialchars($app['master_avatar']) ?>"
                                alt="<?= htmlspecialchars($app['master_name']) ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder small">
                                <?= mb_strtoupper(mb_substr($app['master_name'] ?: 'М', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span>Мастер: <?= htmlspecialchars($app['master_name'] ?: 'Не назначен') ?></span>
                </div>

                <div class="appointment-datetime">
                    <i class="fas fa-calendar-alt"></i>
                    <?= date('d.m.Y', strtotime($app['start_time'])) ?> в <?= date('H:i', strtotime($app['start_time'])) ?>
                </div>

                <?php if (!empty($app['notes'])): ?>
                    <div class="appointment-notes">
                        <i class="fas fa-comment"></i>
                        <?= htmlspecialchars($app['notes']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="appointment-footer">
                <?php if ($app['status'] === 'completed' && !$app['review_id']): ?>
                    <button class="button button-review" onclick="console.log(<?= $app['id'] ?>);showReview(<?= $app['id'] ?>)">
                        <i class="fas fa-star"></i> Оставить отзыв
                    </button>
                <?php elseif ($app['review_id']): ?>
                    <div class="review-info">
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= $app['review_rating'] ? '' : '-o' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="review-preview"><?= htmlspecialchars(mb_substr($app['review_comment'], 0, 50)) ?>...</span>
                    </div>
                <?php endif; ?>

                <?php if ($app['status'] === 'pending' || $app['status'] === 'confirmed'): ?>
                    <button class="button button-cancel" onclick="cancelAppointment(<?= $app['id'] ?>)">
                        Отменить запись
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $hasReview = isset($app['review_id']) && $app['review_id'];
        if ($app['status'] === 'completed' && !$hasReview) {
            include "component/user_component/reviews_section/reviews.php";
        }
        ?>

    <?php endforeach; ?>
</div>

<script>
    function showReview(appointmentId) {
        console.log('ID записи:', appointmentId);
        console.log('Ищем элемент:', 'review-form-' + appointmentId);
        console.log('Найден элемент:', document.getElementById('review-form-' + appointmentId));
        // Скрываем все открытые формы
        document.querySelectorAll('.review-form-container').forEach(form => {
            form.style.display = 'none';
        });

        // Показываем форму для этой записи
        const form = document.getElementById('review-form-' + appointmentId);
        if (form) {
            form.style.display = 'block';
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function hideReview(appointmentId) {
        const form = document.getElementById('review-form-' + appointmentId);
        if (form) {
            form.style.display = 'none';
        }
    }
</script>