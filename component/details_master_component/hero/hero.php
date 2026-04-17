<div class="master-hero">
    <?php include_once "component/change_theme/changeTheme.php"; ?>
    <div class="master-hero-content">
        <a href="/index.php?page=masters" class="back-link">
            <i class="fas fa-arrow-left"></i>
            <span>Все мастера</span>
        </a>

        <div class="master-hero-main">
            <div class="master-hero-avatar">
                <img src="<?= htmlspecialchars(!empty($master['avatar_url']) ? $master['avatar_url'] : '/public/uploads/avatars/default.jpg') ?>"
                    alt="<?= htmlspecialchars($master['fio']) ?>">
            </div>

            <div class="master-hero-info">
                <h1 class="master-name"><?= htmlspecialchars($master['fio']) ?></h1>

                <div class="master-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?= htmlspecialchars($master['experience']) ?></span>
                        <span class="stat-label">лет опыта</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?= $worksCount ?: 0 ?></span>
                        <span class="stat-label">работ</span>
                    </div>
                    <div class="stat-item">
                        <span
                            class="stat-value"><?= !empty($master['rating']) && $master['rating'] > 0 ? number_format($master['rating'], 1) : '0.0' ?></span>
                        <span class="stat-label">рейтинг</span>
                    </div>
                </div>

                <div class="master-tags">
                    <span class="master-tag">
                        <i class="fas fa-bolt"></i>
                        <?= htmlspecialchars($master['spec']) ?>
                    </span>
                    <?php
                    // Проверяем, в отпуске ли мастер сегодня
                    $isOnVacationToday = false;
                    $vacationEndDate = null;
                    try {
                        $stmt = $db->dbs->prepare("
                            SELECT date_end FROM master_unavailable 
                            WHERE id_master = ? AND date_start <= CURDATE() AND date_end >= CURDATE()
                            LIMIT 1
                        ");
                        $stmt->execute([$master['id']]);
                        $vacation = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($vacation) {
                            $isOnVacationToday = true;
                            $vacationEndDate = $vacation['date_end'];
                        }
                    } catch (Exception $e) {
                        error_log("Ошибка проверки отпуска: " . $e->getMessage());
                    }
                    ?>
                    <?php if ($master['is_Active'] && !$isOnVacationToday): ?>
                        <span class="master-tag active-tag">
                            <i class="fas fa-check-circle"></i>
                            Принимает клиентов
                        </span>
                    <?php elseif ($isOnVacationToday && $vacationEndDate): ?>
                        <span class="master-tag vacation-tag">
                            <i class="fas fa-umbrella-beach"></i>
                            В отпуске до <?= date('d.m.Y', strtotime($vacationEndDate)) ?>
                        </span>
                    <?php else: ?>
                        <span class="master-tag inactive-tag">
                            <i class="fas fa-clock"></i>
                            Временно не принимает
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>