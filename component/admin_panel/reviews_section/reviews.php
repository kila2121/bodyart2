<div id="reviews" class="tab-content">
    <div class="nav-tabs-reviews">
        <button class="active" onclick="showTabReviews('moders')">Модерирование</button>
        <button onclick="showTabReviews('good_reviews')">Прошедшие отзывы</button>
    </div>

    <div class="tab-content-reviews active" id="moders">
        <h2>Отзывы на модерации</h2>
        <?php if (empty($pendingReviews)): ?>
            <p class="text-center">Нет отзывов, ожидающих модерации</p>
        <?php else: ?>
            <?php foreach ($pendingReviews as $r): ?>
                <div class="review-card">
                    <div>
                        <strong><?= htmlspecialchars($r['login'] ?: 'Гость') ?></strong> |
                        Рейтинг: <span><?= $r['rating'] ?>/5</span>
                    </div>
                    <div class="review-comment"><?= nl2br(htmlspecialchars($r['comment'])) ?></div>
                    <div class="actions">
                        <form method="POST" action="/index.php?action=approve_review">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-success">Одобрить</button>
                        </form>
                        <form method="POST" action="/index.php?action=reject_review">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-warning">Отклонить</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="tab-content-reviews" id="good_reviews">
        <h2>Прошедшие отзывы</h2>
        <?php if (empty($modernReviews)): ?>
            <p>Еще нет одобренных отзывов</p>
        <?php else: ?>
            <?php foreach ($modernReviews as $m): ?>
                <div class="review-card">
                    <div>
                        <strong><?= htmlspecialchars($m['login'] ?: 'Гость') ?></strong> |
                        Рейтинг: <span><?= $m['rating'] ?>/5</span>
                    </div>
                    <div class="review-comment"><?= nl2br(htmlspecialchars($m['comment'])) ?></div>

                    <?php if (!empty($m['admin_reply'])): ?>
                        <div class="review-admin-reply">
                            <strong>Ответ администратора</strong>
                            <p><?= nl2br(htmlspecialchars($m['admin_reply'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="actions">
                        <button class="btn btn-primary" onclick="showReplyModal(<?= $m['id'] ?>)">Добавить ответ</button>
                        <?php if (!empty($m['admin_reply'])): ?>
                            <form method="POST" action="/index.php?action=delete_reply"
                                onsubmit="return confirm('Удалить ответ администратора?')">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger">Удалить ответ</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="/index.php?action=reject_review"
                            onsubmit="return confirm('Удалить отзыв полностью?')">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-warning">Удалить отзыв</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="replyModalOverlay" class="reply-modal-overlay"></div>
<div id="replyModal" class="reply-modal">
    <div class="reply-modal-header">
        <h3>Ответ администратора</h3>
        <button class="reply-modal-close" onclick="closeReplyModal()">&times;</button>
    </div>
    <form method="POST" action="/index.php?action=reply_review">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="id" id="reply_review_id" value="">
        <div class="reply-modal-body">
            <textarea name="admin_reply" id="reply_text" placeholder="Введите ответ на отзыв..." rows="4"
                required></textarea>
        </div>
        <div class="reply-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeReplyModal()">Отмена</button>
            <button type="submit" class="btn btn-primary">Отправить</button>
        </div>
    </form>
</div>