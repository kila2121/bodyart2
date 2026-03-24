<div class="settings-container">
    <!-- Простая навигация -->
    <div class="settings-nav">
        <button class="settings-nav-btn active" data-section="profile">Личные данные</button>
        <button class="settings-nav-btn" data-section="security">Безопасность</button>
    </div>

    <!-- Личные данные -->
    <div class="settings-section active" id="profile-section">
        <h2>Личные данные</h2>

        <form method="POST" action="/action.php?action=update_profile">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <!-- ФИО -->
            <div class="form-group">
                <label>ФИО</label>
                <input type="text" name="fio" value="<?= htmlspecialchars($user['fio']) ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <!-- Телефон -->
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>

            <!-- Дата рождения -->
            <div class="form-group">
                <label>Дата рождения</label>
                <input type="date" name="date_b" value="<?= htmlspecialchars($user['date_b']) ?>">
            </div>

            <button type="submit" class="button button-primary">Сохранить</button>
        </form>
    </div>

    <!-- Безопасность -->
    <div class="settings-section" id="security-section">
        <h2>Безопасность</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div>ошибка</div>
        <?php endif ?>
        <form method="POST" action="/action.php?action=change_password">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <!-- Текущий пароль -->
            <div class="form-group">
                <label>Текущий пароль</label>
                <input type="password" name="old_pass">
            </div>

            <!-- Новый пароль -->
            <div class="form-group">
                <label>Новый пароль</label>
                <input type="password" name="new_pass">
            </div>

            <!-- Подтверждение -->
            <div class="form-group">
                <label>Подтверждение</label>
                <input type="password" name="confirm_pass">
            </div>

            <button type="submit" class="button button-primary">Изменить пароль</button>
        </form>
    </div>
</div>

<script>
    // Переключение вкладок
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