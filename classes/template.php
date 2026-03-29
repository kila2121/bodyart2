<?php
class Template
{
    private $title;
    private $styles = [];
    private $scripts = [];
    private $baseUrl;
    private $basePath;

    public function __construct($title = 'BodyArt Studio')
    {
        $this->title = $title;

        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        $this->baseUrl = '/';
        $this->basePath = $_SERVER['DOCUMENT_ROOT'];
    }

    public function addStyle($style)
    {
        $this->styles[] = $style;
        return $this;
    }

    public function addScript($script)
    {
        $this->scripts[] = $script;
        return $this;
    }

    public function render($content)
    {
        $hasFormError = isset($_SESSION['form_error']) ? 'true' : 'false';
        $activeTab = $_SESSION['active_tab'] ?? 'reg';
        $formData = $_SESSION['form_data'] ?? [];
        ?>
        <!DOCTYPE html>
        <html lang="ru">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
            <title><?php echo htmlspecialchars($this->title); ?></title>

            <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
            <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

            <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            </noscript>

            <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2"
                as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-brands-400.woff2"
                as="font" type="font/woff2" crossorigin>

            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/hero.webp')): ?>
                <link rel="preload" as="image" href="/public/hero.webp" fetchpriority="high">
            <?php else: ?>
                <link rel="preload" as="image" href="/public/hero.jpg" fetchpriority="high">
            <?php endif; ?>

            <link rel="preload" href="<?= $this->baseUrl . "styles/global_style.css" ?>" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="<?= $this->baseUrl . "styles/global_style.css" ?>">
            </noscript>

            <?php foreach ($this->styles as $style): ?>
                <link rel="preload" href="<?= $style ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
                <noscript>
                    <link rel="stylesheet" href="<?= $style ?>">
                </noscript>
            <?php endforeach; ?>

            <link rel="preload" href="<?= $this->baseUrl . "component/message/message.css" ?>" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="<?= $this->baseUrl . "component/message/message.css" ?>">
            </noscript>

            <link rel="preload" href="<?= $this->baseUrl . "component/modal_window/style_reg_form.css" ?>" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="<?= $this->baseUrl . "component/modal_window/style_reg_form.css" ?>">
            </noscript>

            <link rel="preload" href="<?= $this->baseUrl . "component/modal_window/appointment_form.css" ?>" as="style"
                onload="this.onload=null;this.rel='stylesheet'">
            <noscript>
                <link rel="stylesheet" href="<?= $this->baseUrl . "component/modal_window/appointment_form.css" ?>">
            </noscript>
        </head>

        <body data-form-error="<?= $hasFormError ?>" data-active-tab="<?= $activeTab ?>">
            <div class=" layout">
                <?php
                include $this->basePath . "/layout/header.php";
                ?>
                <?php include $this->basePath . "/component/message/message.php"; ?>
                <main class="main">
                    <?php echo $content; ?>
                </main>
                <?php
                include $this->basePath . "/layout/footer.php";
                ?>
            </div>

            <?php foreach ($this->scripts as $script): ?>
                <script src="<?= $script ?>" defer></script>
            <?php endforeach; ?>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    window.showMessage = function (type = "info", message) {
                        let icon = '';
                        let borderColor = '';

                        switch (type) {
                            case 'success':
                                icon = '<i class="fas fa-check-circle"></i>';
                                borderColor = '#10b981';
                                break;
                            case 'error':
                                icon = '<i class="fas fa-exclamation-triangle"></i>';
                                borderColor = '#ef4444';
                                break;
                            case 'info':
                            default:
                                icon = '<i class="fas fa-info-circle"></i>';
                                borderColor = 'var(--accent-color)';
                                break;
                        }

                        let container = document.querySelector('.toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.className = 'toast-container';
                            document.body.appendChild(container);
                        }

                        // Создаём toast в стиле message.css
                        const toast = document.createElement('div');
                        toast.className = `toast ${type}`;
                        toast.innerHTML = `
                <div class="toast-header">
                    <button class="btn-close">&times;</button>
                </div>
                <div class="toast-body">
                    ${icon}
                    <span class="toast-message">${message}</span>
                </div>
            `;

                        container.appendChild(toast);

                        // Кнопка закрытия
                        const closeBtn = toast.querySelector('.btn-close');
                        closeBtn.addEventListener('click', () => {
                            toast.classList.add('hide');
                            setTimeout(() => toast.remove(), 300);
                        });

                        // Авто-закрытие через 4 секунды
                        setTimeout(() => {
                            if (toast && toast.parentNode) {
                                toast.classList.add('hide');
                                setTimeout(() => toast.remove(), 300);
                            }
                        }, 4000);
                    };
                });
            </script>
        </body>

        </html>
        <?php
    }

    public function renderFile($filePath)
    {
        if (!file_exists($filePath)) {
            die("Файл не найден: " . $filePath);
        }

        ob_start();
        include $filePath;
        $content = ob_get_clean();
        $this->render($content);
    }
}
?>