<div class="master-section reviews-section">
    <div class="section-header-with-link">
        <h2 class="section-heading">
            <i class="fas fa-star"></i>
            Отзывы клиентов
        </h2>
        <a href="/page/reviews.php?master=<?= $master['id'] ?>" class="section-link">
            Все отзывы <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="reviews-list">
        <?php foreach($reviews as $review): 
            $reviewName = $review['login'] ?: ($review['fio'] ?: 'Гость');
            $firstLetter = mb_strtoupper(mb_substr($reviewName, 0, 1));
            $colors = ['#ff3366', '#10b981', '#f59e0b', '#8b5cf6'];
            $color = $colors[array_rand($colors)];
        ?>
        <div class="review-item">
            <div class="review-header">
                <div class="review-author">
                    <?php if (!empty($review['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($review['avatar_url']) ?>" 
                            alt="<?= htmlspecialchars($reviewName) ?>"
                            class="review-avatar-img">
                    <?php else: ?>
                    <div class="review-avatar" style="background: <?= $color ?>;">
                        <?= $firstLetter ?>
                    </div>
                    <?php endif; ?>
                    <span class="review-name"><?= htmlspecialchars($reviewName) ?></span>
                </div>
                <div class="review-rating">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                    <?php endfor; ?>
                </div>
            </div>
            <p class="review-text"><?= htmlspecialchars(mb_substr($review['comment'], 0, 150)) ?>...</p>
            <span class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>