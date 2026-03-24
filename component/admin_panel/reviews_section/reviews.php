<div id="reviews" class="tab-content">
    <h2>Отзывы на модерации</h2>
    <?php if (empty($pendingReviews)): ?>
        <p class="text-center">Нет отзывов, ожидающих модерации</p>
    <?php else: ?>
        <?php foreach ($pendingReviews as $r): ?>
            <div class="review-card">
                <div>
                    <strong><?= htmlspecialchars($r['login'] ?: 'Гость') ?></strong> |
                    Рейтинг: <?= $r['rating'] ?>/5
                </div>
                <div><?= nl2br(htmlspecialchars($r['comment'])) ?></div>
                <div class="actions">
                    <form method="POST" style="display: inline;" action="/index.php?action=approve_review">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-success">Одобрить</button>
                    </form>
                    <form method="POST" style="display: inline;" action="/index.php?action=reject_review">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-warning">Отклонить</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>