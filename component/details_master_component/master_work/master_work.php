<div class="master-section works-section">
    <div class="section-header-with-link">
        <h2 class="section-heading">
            <i class="fas fa-camera"></i>
            Последние работы
        </h2>
    </div>
    <div class="works-grid">
        <?php foreach ($works as $work): ?>
            <div class="work-item" onclick="window.location.href='/index.php?page=gallery&id=<?= $work['id'] ?>'">
                <img src="<?= htmlspecialchars($work['url']) ?>"
                    alt="<?= htmlspecialchars($work['title'] ?? 'Работа мастера') ?>" loading="lazy">
                <?php if (!empty($work['title'])): ?>
                    <div class="work-overlay">
                        <span><?= htmlspecialchars($work['title']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($work['is_featured']): ?>
                    <div class="work-featured">
                        <i class="fas fa-star"></i>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>