<?php
require_once dirname(__DIR__, 2) . "/classes/csrf.php";

$activeTab = $_POST['switchTab'] ?? $_GET['tab'] ?? $_SESSION['active_tab'] ?? 'reg';
$formData = $_SESSION['form_data'] ?? [];
$formError = $_SESSION['form_error'] ?? '';
$formSuccess = $_SESSION['success'] ?? '';

unset($_SESSION['form_data']);
unset($_SESSION['form_error']);
unset($_SESSION['success']);
unset($_SESSION['active_tab']);

function renderForm($tab = 'reg', $formData = [], $error = '', $success = '')
{
    ob_start();
    if ($tab === 'reg') {
        ?>

        <?php if ($error): ?>
            <div class="toast error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="toast success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/index.php?action=reg" class="modal_form" id="reg-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label for="login"><i class="fas fa-user"></i> Логин *</label>
                <div class="input-wrapper">
                    <input type="text" id="login" name="login" value="<?= htmlspecialchars($formData['login'] ?? '') ?>"
                        placeholder="Придумайте логин" required>
                </div>
            </div>

            <div class="form-group">
                <label for="pass"><i class="fas fa-lock"></i> Пароль *</label>
                <div class="input-wrapper">
                    <input type="password" id="pass" name="pass" placeholder="Введите пароль" required>
                </div>
            </div>

            <div class="form-group">
                <label for="fio"><i class="fas fa-user"></i> ФИО *</label>
                <div class="input-wrapper">
                    <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($formData['fio'] ?? '') ?>"
                        placeholder="Иванов Иван Иванович" required>
                </div>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Номер телефона *</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                        placeholder="+7 (999) 123-45-67" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                        placeholder="example@mail.ru" required>
                </div>
            </div>

            <div class="form-group">
                <label for="date_b"><i class="fas fa-birthday-cake"></i> Дата рождения *</label>
                <div class="input-wrapper">
                    <input type="date" id="date_b" name="date_b" value="<?= htmlspecialchars($formData['date_b'] ?? '') ?>"
                        required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button submit-button">
                    Зарегистрироваться
                </button>
                <button type="reset" class="button secondary-button">
                    Очистить
                </button>
            </div>
        </form>
        <div class="tab-links">
            <span>Есть аккаунт?</span>
            <a href="#" class="tab-link" data-tab="auth">Войти</a>
        </div>
        <?php
    } elseif ($tab === 'auth') {
        ?>
        <?php if ($error): ?>
            <div class="toast error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/index.php?action=auth" class="auth-form" id="auth-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="form-group">
                <label for="login"><i class="fas fa-user"></i> Логин</label>
                <div class="input-wrapper">
                    <input type="text" id="login" name="login" value="<?= htmlspecialchars($formData['login'] ?? '') ?>"
                        placeholder="Ваш логин" required>
                </div>
            </div>

            <div class="form-group">
                <label for="pass"><i class="fas fa-lock"></i> Пароль</label>
                <div class="input-wrapper">
                    <input type="password" id="pass" name="pass" placeholder="Введите пароль" required>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button submit-button">
                    Войти
                </button>
            </div>
        </form>
        <div class="tab-links">
            <span>Нет аккаунта?</span>
            <a href="#" class="tab-link" data-tab="reg">Зарегистрироваться</a>
        </div>
        <?php
    } else {
        ?>
        <h1>Такого таба нет</h1>
        <?php
    }
    return ob_get_clean();
}

if (isset($_POST['ajax']) && $_POST['ajax'] == 1 && isset($_POST['switchTab'])) {
    echo renderForm($_POST['switchTab'], $formData, $formError, $formSuccess);
    echo "<!-- activeTab: $activeTab -->";
    exit;
}

if (isset($message)) {
    echo $db->message($message);
}
?>

<div class="modal-window">
    <div class="registration-container">
        <div id="auth-forms">
            <?php echo renderForm($activeTab, $formData, $formError, $formSuccess); ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formsContainer = document.getElementById('auth-forms');

        formsContainer.addEventListener('click', function (e) {
            const tabLink = e.target.closest('.tab-link');
            if (!tabLink) return;

            e.preventDefault();
            const tab = tabLink.dataset.tab;

            fetch("/component/modal_window/reg_form.php", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'switchTab=' + tab + '&ajax=1'
            })
                .then(response => response.text())
                .then(html => {
                    formsContainer.innerHTML = html;
                })
                .catch(error => console.error('Ошибка:', error));
        });
    });
</script>