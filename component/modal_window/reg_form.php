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
                    <small class="hint" id="loginHint"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="pass"><i class="fas fa-lock"></i> Пароль *</label>
                <div class="input-wrapper">
                    <input type="password" id="pass" name="pass" placeholder="Введите пароль" required>
                    <small class="hint" id="passHint"></small>
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
                    <small class="hint" id="phoneHint"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                        placeholder="example@mail.ru" required>
                    <small class="hint" id="emailHint"></small>
                </div>
            </div>

            <div class="form-group">
                <label for="date_b"><i class="fas fa-birthday-cake"></i> Дата рождения *</label>
                <div class="input-wrapper">
                    <input type="date" id="date_b" name="date_b" value="<?= htmlspecialchars($formData['date_b'] ?? '') ?>"
                        required>
                    <small class="hint" id="dateHint"></small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button submit-button" id="submitBtn" disabled>
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
    window.initPhoneMask = function () {
        let phoneInput = document.getElementById('phone');
        if (!phoneInput) return;

        const newInput = phoneInput.cloneNode(true);
        phoneInput.parentNode.replaceChild(newInput, phoneInput);

        newInput.addEventListener('input', function (e) {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 11) digits = digits.slice(0, 11);

            let formatted = '';
            if (digits.length > 0) {
                if (digits[0] !== '7') digits = '7' + digits;
                if (digits.length > 11) digits = digits.slice(0, 11);

                formatted = '+7';
                if (digits.length > 1) {
                    formatted += ' (' + digits.substring(1, Math.min(4, digits.length));
                }
                if (digits.length >= 4) {
                    formatted += ')';
                }
                if (digits.length > 4) {
                    formatted += ' ' + digits.substring(4, Math.min(7, digits.length));
                }
                if (digits.length > 7) {
                    formatted += '-' + digits.substring(7, Math.min(9, digits.length));
                }
                if (digits.length > 9) {
                    formatted += '-' + digits.substring(9, Math.min(11, digits.length));
                }
            }

            this.value = formatted;
        });
    };

    window.initValidation = function () {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;

        function validate() {
            const login = document.getElementById('login');
            const phone = document.getElementById('phone');
            const email = document.getElementById('email');
            const pass = document.getElementById('pass');
            const date_b = document.getElementById('date_b');

            const loginHint = document.getElementById('loginHint');
            const phoneHint = document.getElementById('phoneHint');
            const emailHint = document.getElementById('emailHint');
            const passHint = document.getElementById('passHint');
            const dateHint = document.getElementById('dateHint');

            const loginValid = login ? /^[a-zA-Z0-9]{3,20}$/.test(login.value) : false;
            const phoneDigits = phone ? phone.value.replace(/\D/g, '') : '';
            const phoneValid = phoneDigits.length === 11;
            const emailValid = email ? /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/.test(email.value) : false;
            const passValid = pass ? pass.value.length >= 6 : false;

            let dateValid = false;
            let dateError = '';

            if (date_b && date_b.value !== '') {
                const dateValue = date_b.value;
                const parts = dateValue.split('-');
                const year = parseInt(parts[0]);
                const month = parseInt(parts[1]);
                const day = parseInt(parts[2]);

                const currentYear = new Date().getFullYear();
                const minYear = currentYear - 100;
                const maxYear = currentYear - 18;

                const date = new Date(year, month - 1, day);
                const isValidDate = date.getFullYear() === year &&
                    date.getMonth() === month - 1 &&
                    date.getDate() === day;

                if (!isValidDate) {
                    dateError = 'Введите корректную дату';
                    dateValid = false;
                } else if (year < minYear) {
                    dateError = 'Возраст не может быть больше 100 лет';
                    dateValid = false;
                } else if (year > maxYear) {
                    dateError = 'Вам должно быть не менее 18 лет';
                    dateValid = false;
                } else {
                    dateError = '';
                    dateValid = true;
                }
            } else if (date_b && date_b.value === '') {
                dateError = 'Дата рождения обязательна';
                dateValid = false;
            }

            if (loginHint) {
                if (login && login.value !== '') {
                    if (!loginValid) {
                        loginHint.textContent = 'Латинские буквы и цифры, 3-20 символов';
                        loginHint.className = 'hint error';
                    } else {
                        loginHint.textContent = '';
                    }
                }
            }

            if (phoneHint && phone && phone.value !== '') {
                if (!phoneValid) {
                    phoneHint.textContent = 'Введите 11 цифр';
                    phoneHint.className = 'hint error';
                } else {
                    phoneHint.textContent = '';
                }
            }

            if (emailHint) {
                if (email && email.value !== '') {
                    if (!emailValid) {
                        emailHint.textContent = 'Введите корректный email (example@mail.ru)';
                        emailHint.className = 'hint error';
                    } else {
                        emailHint.textContent = '';
                    }
                }
            }

            if (passHint) {
                if (pass && pass.value !== '') {
                    if (!passValid) {
                        passHint.textContent = 'Введите минимум 6 символов';
                        passHint.className = 'hint error';
                    } else {
                        passHint.textContent = '';
                    }
                }
            }

            if (dateHint) {
                if (date_b && date_b.value !== '') {
                    if (!dateValid) {
                        dateHint.textContent = dateError;
                        dateHint.className = 'hint error';
                    } else {
                        dateHint.textContent = '';
                    }
                }
            }

            const allValid = loginValid && phoneValid && emailValid && passValid && dateValid;
            submitBtn.disabled = !allValid;
        }

        const inputs = ['login', 'phone', 'email', 'pass', 'date_b'];
        inputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', validate);
        });

        validate();
    };

    document.addEventListener('DOMContentLoaded', function () {
        const formsContainer = document.getElementById('auth-forms');

        window.initPhoneMask();
        window.initValidation();

        // Следим за изменениями в контейнере (для AJAX)
        const observer = new MutationObserver(function (mutations) {
            if (document.getElementById('phone')) {
                window.initPhoneMask();
                window.initValidation();
                observer.disconnect();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });

        if (formsContainer) {
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
                        window.initPhoneMask();
                        window.initValidation();
                    })
                    .catch(error => console.error('Ошибка:', error));
            });
        }
    });
</script>