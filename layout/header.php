<header class="header">
    <div class="headerContent">
        <div class="nav">
            <a href="/index.php?page=index">
                <div class="logo">
                    <img src="/public/logo.webp" srcset="/public/logo.webp 120w" sizes="60px" width="60" height="60"
                        alt="Логотип BodyArt Studio" class="logo_img" loading="eager">
                    <span class="logo_text">БОДИ<span class="logo_accent">АРТ</span> СТУДИО</span>
                </div>
            </a>
            <nav class="nav_links">
                <a href="/index.php?page=index" class="nav_link">О студии</a>
                <a href="/index.php?page=services" class="nav_link">Услуги</a>
                <a href="/index.php?page=masters" class="nav_link">Мастера</a>
                <a href="/index.php?page=gallery" class="nav_link">Галерея</a>
            </nav>
        </div>

        <div class="auth-buttons">
            <?php if (isset($_SESSION['id']) && $_SESSION['status'] === 100): ?>
                <form method="POST" action="/index.php?page=admin">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-admin">
                        <i class="fas fa-user-shield"></i><span> Админ</span>
                    </button>
                </form>
                <form method="POST" action="/index.php?page=user">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-profile">
                        <i class="fas fa-user-circle"></i><span> Профиль</span>
                    </button>
                </form>
                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i><span> Выйти</span>
                    </button>
                </form>

            <?php elseif (isset($_SESSION['id']) && $_SESSION['status'] === 80): ?>
                <form method="POST" action="/index.php?page=masterProfile">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-master">
                        <i class="fas fa-user-circle"></i><span> <?= htmlspecialchars($_SESSION['login']) ?></span>
                    </button>
                </form>
                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i><span> Выйти</span>
                    </button>
                </form>

            <?php elseif (isset($_SESSION['id']) && $_SESSION['status'] === 1): ?>
                <form method="POST" action="/index.php?page=user">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-user">
                        <i class="fas fa-user-circle"></i><span> <?= htmlspecialchars($_SESSION['login']) ?></span>
                    </button>
                </form>
                <form method="POST" action="/index.php?action=quit" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <button type="submit" class="button button-logout">
                        <i class="fas fa-sign-out-alt"></i><span> Выйти</span>
                    </button>
                </form>

            <?php else: ?>
                <button class="button button-login" id="openModalBtn">
                    <i class="fas fa-sign-in-alt"></i><span> Войти</span>
                </button>
            <?php endif; ?>
        </div>

        <button class="burger-menu" aria-label="Меню">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <div class="menu-overlay"></div>
</header>

<div class="modal-overlay"></div>
<div class="modal">
    <button class="modal-close">&times;</button>
    <?php include_once __DIR__ . "/../component/modal_window/reg_form.php"; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.querySelector('.modal');
        const overlay = document.querySelector('.modal-overlay');
        const openBtn = document.getElementById('openModalBtn');
        const closeBtn = document.querySelector('.modal-close');

        if (modal && overlay && openBtn) {
            const hasFormError = document.body.dataset.formError === 'true';
            const activeTab = document.body.dataset.activeTab || 'reg';

            if (hasFormError) {
                modal.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('modal-open');
                const formsContainer = document.getElementById('auth-forms');
                if (formsContainer) formsContainer.dataset.activeTab = activeTab;
            }

            function openModal() {
                modal.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('modal-open');
                const authTab = document.querySelector('[data-tab="auth"]');
                if (authTab) authTab.click();
            }

            function closeModal() {
                modal.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('modal-open');
            }

            openBtn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', closeModal);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
            });
        }

        const burger = document.querySelector('.burger-menu');
        const navLinks = document.querySelector('.nav_links');
        const menuOverlay = document.querySelector('.menu-overlay');

        if (burger && navLinks && menuOverlay) {
            function closeMenu() {
                burger.classList.remove('active');
                navLinks.classList.remove('active');
                menuOverlay.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }

            function openMenu() {
                burger.classList.add('active');
                navLinks.classList.add('active');
                menuOverlay.classList.add('active');
                document.body.classList.add('no-scroll');
            }

            burger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (navLinks.classList.contains('active')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            menuOverlay.addEventListener('click', closeMenu);

            navLinks.querySelectorAll('.nav_link').forEach(link => {
                link.addEventListener('click', closeMenu);
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
                    closeMenu();
                }
            });
        }
    });
</script>