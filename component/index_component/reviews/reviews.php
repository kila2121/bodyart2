<section class="reviews-preview">
    <div class="section-header">
        <h2 class="section-title">Что говорят клиенты</h2>
    </div>

    <div class="reviews-grid">
        <?php
        try {
            $previewReviews = $db->dbs->query("
                    SELECT r.*, u.login, u.fio, u.avatar_url 
                    FROM reviews r
                    LEFT JOIN user u ON r.id_user = u.id
                    WHERE r.is_approved = 1
                    ORDER BY r.created_at DESC
                    LIMIT 3
                ")->fetchAll(PDO::FETCH_ASSOC);

            // Массив с цветами для аватаров (если нет фото)
            $avatarColors = ['#ff3366', '#10b981', '#f59e0b', '#8b5cf6', '#3b82f6'];
            $avatarIndex = 0;

            foreach ($previewReviews as $review):
                $avatarIndex++;
                $avatarColor = $avatarColors[$avatarIndex % count($avatarColors)];

                // Имя пользователя (если нет login, используем fio или "Гость")
                $userName = $review['login'] ?: ($review['fio'] ?: 'Гость');

                // Первая буква имени для аватара
                $firstLetter = mb_strtoupper(mb_substr($userName, 0, 1));

                // Проверяем, есть ли аватар
                $hasAvatar = !empty($review['avatar_url']) && $review['avatar_url'] !== '/public/avatars/default.jpg';
                ?>
                <div class="review-card">
                    <div class="review-card-inner">
                        <!-- Верхняя часть с цитатой -->
                        <div class="review-quote">
                            <i class="fas fa-quote-left quote-icon"></i>
                        </div>

                        <!-- Текст отзыва -->
                        <p class="review-text"><?= htmlspecialchars(mb_substr($review['comment'], 0, 150)) ?>...</p>

                        <!-- Нижняя часть с автором и рейтингом -->
                        <div class="review-footer">
                            <div class="review-author-block">
                                <!-- Аватар -->
                                <?php if ($hasAvatar): ?>
                                    <img src="<?= htmlspecialchars($review['avatar_url']) ?>"
                                        alt="<?= htmlspecialchars($userName) ?>" class="review-avatar">
                                <?php else: ?>
                                    <div class="review-avatar-placeholder" style="background: <?= $avatarColor ?>;">
                                        <?= $firstLetter ?>
                                    </div>
                                <?php endif; ?>

                                <div class="review-author-info">
                                    <span class="review-author-name"><?= htmlspecialchars($review['fio']) ?></span>
                                    <span class="review-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d.m.Y', strtotime($review['created_at'])) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Рейтинг звездами -->
                            <div class="review-rating">
                                <?php
                                $rating = (int) $review['rating'];
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $rating):
                                        ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $rating): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                            <?php
                                    endif;
                                endfor;
                                ?>
                                        <span class="rating-value"><?= $rating ?>.0</span>
                            </div>
                        </div>
                        <!-- Декоративный элемент -->
                            <div class="review-glow" style="background: <?= $avatarColor ?>;"></div>
                            </div>
                        </div>
                        <?php
            endforeach;
        } catch (Exception $e) {
            echo '<div class="no-reviews">Отзывы временно недоступны</div>' . $e;
        }
        ?>
    </div>
</section>