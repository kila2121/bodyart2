<div id="review-form-<?= $app['id'] ?>" class="review-form-container" style="display: none;">
    <form method="POST" action="/action.php?action=add_review">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">

        <div class="review-form-header">
            <h4>Оставить отзыв</h4>
            <button type="button" class="review-form-close" onclick="hideReview(<?= $app['id'] ?>)">×</button>
        </div>

        <div class="review-form-rating">
            <label>Оценка:</label>
            <div class="star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>-<?= $app['id'] ?>" required>
                    <label for="star<?= $i ?>-<?= $app['id'] ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="review-form-comment">
            <textarea name="comment" placeholder="Расскажите о своем опыте..." rows="4" required></textarea>
        </div>

        <button type="submit" class="review-form-submit">
            Отправить отзыв
        </button>
    </form>
</div>