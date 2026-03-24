<div class="settings-container">
    <div class="settings-nav">
        <button class="settings-nav-btn active" data-section="profile">Личные данные</button>
        <button class="settings-nav-btn" data-section="master">Профиль мастера</button>
        <button class="settings-nav-btn" data-section="security">Безопасность</button>
    </div>

    <!-- Личные данные -->
    <div class="settings-section active" id="profile-section">
        <h2>Личные данные</h2>

        <form method="POST" action="/action.php?action=update_profile">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>ФИО</label>
                <input type="text" name="fio" value="<?= htmlspecialchars($user['fio']) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>

            <div class="form-group">
                <label>Дата рождения</label>
                <input type="date" name="date_b" value="<?= htmlspecialchars($user['date_b']) ?>">
            </div>

            <button type="submit" class="button button-primary">Сохранить</button>
        </form>
    </div>

    <!-- Профиль мастера -->
    <div class="settings-section" id="master-section">
        <h2>Профиль мастера</h2>

        <?php if (!empty($master_data)): ?>
            <form method="POST" action="/action.php?action=update_master_profile">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                <div class="form-group">
                    <label>Специализация</label>
                    <input type="text" name="spec" value="<?= htmlspecialchars($master_data['spec'] ?? '') ?>"
                        placeholder="Например: Тату-мастер" disabled>
                </div>

                <div class="form-group">
                    <label>Опыт работы (лет)</label>
                    <input type="number" name="experience" value="<?= htmlspecialchars($master_data['experience'] ?? '') ?>"
                        min="0" max="50" disabled>
                </div>

                <div class="form-group">
                    <label>О себе</label>
                    <textarea name="about" rows="4"
                        placeholder="Расскажите о своем опыте, стиле работы..."><?= htmlspecialchars($master_data['about'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="button button-primary">Обновить профиль</button>
            </form>
        <?php else: ?>
            <div class="empty-state">
                <p>Данные мастера недоступны</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Безопасность -->
    <div class="settings-section" id="security-section">
        <h2>Безопасность</h2>

        <form method="POST" action="/action.php?action=change_password">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label>Текущий пароль</label>
                <input type="password" name="old_pass">
            </div>

            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" name="new_pass">
            </div>

            <div class="form-group">
                <label>Подтверждение</label>
                <input type="password" name="confirm_pass">
            </div>

            <button type="submit" class="button button-primary">Изменить пароль</button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.settings-nav-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const section = this.dataset.section;

            document.querySelectorAll('.settings-nav-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.settings-section').forEach(s => s.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(section + '-section').classList.add('active');
        });
    });
</script>