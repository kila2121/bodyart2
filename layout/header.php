<header class="header">
    <div class="headerContent">
        <div class="nav">
            <div class="logo">
                <img src="../public/logo.jpg" alt="Логотип BodyArt Studio - студия тату и пирсинга" width="60"
                    height="60" class="logo_img">
                <span class="logo_text">BODY<span class="logo_accent">ART</span> STUDIO</span>
            </div>
            <nav class="nav_links">
                <a href="/index.php?page=index" class="nav_link">О студии</a>
                <a href="/index.php?page=services" class="nav_link">Услуги</a>
                <a href="/index.php?page=masters" class="nav_link">Мастера</a>
                <a href="/index.php?page=gallery" class="nav_link">Галерея</a>
            </nav>
        </div>

        <div class="auth-buttons">
            <?php if (isset($_SESSION['id']) && $_SESSION['status'] === 100): ?>
                <!-- Администратор -->
                <form method="POST" action="/index.php?page=admin">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-admin">
                        Админ
                    </button>
                </form>

                <form method="POST" action="/index.php?page=user">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-profile">
                        <i class="fas fa-user-circle"></i> Профиль
                    </button>
                </form>

                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>

            <?php elseif (isset($_SESSION['id']) && $_SESSION['status'] === 80): ?>
                <form method="POST" action="/index.php?page=masterProfile">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-master">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['login']) ?>
                    </button>
                </form>

                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>

            <?php elseif (isset($_SESSION['id']) && $_SESSION['status'] === 1): ?>
                <form method="POST" action="/index.php?page=user">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-user">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['login']) ?>
                    </button>
                </form>

                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>

            <?php else: ?>
                <button class="button button-login" id="openModalBtn">
                    Войти
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>
<div class="modal-overlay"></div>
<div class="modal">
    <button class="modal-close">&times;</button>
    <?php include __DIR__ . "/../component/modal_window/reg_form.php"; ?>
</div>

<link rel="stylesheet" href="/component/modal_window/style_reg_form.css">

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.querySelector('.modal');
        const overlay = document.querySelector('.modal-overlay');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.querySelector('.modal-close');

        if (!modal || !overlay || !openBtn) return;

        const hasFormError = document.body.dataset.formError === 'true';
        const activeTab = document.body.dataset.activeTab || 'reg';

        if (hasFormError) {
            modal.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('modal-open');

            const formsContainer = document.getElementById('auth-forms');
            if (formsContainer) {
                formsContainer.dataset.activeTab = activeTab;
            }
        }

        function openModal() {
            modal.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('modal-open');

            const authTab = document.querySelector('[data-tab="auth"]');
            if (authTab) {
                authTab.click();
            }
        }

        function closeModal() {
            modal.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('modal-open');
        }

        openBtn.addEventListener('click', openModal);

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const burger = document.querySelector('.burger-menu');
        const nav = document.querySelector('.nav');

        if (burger && nav) {
            burger.addEventListener('click', function () {
                this.classList.toggle('active');
                nav.classList.toggle('active');
            });
        }

        const navLinks = document.querySelectorAll('.nav_link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    burger?.classList.remove('active');
                    nav?.classList.remove('active');
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                burger?.classList.remove('active');
                nav?.classList.remove('active');
            }
        });
    });
</script>