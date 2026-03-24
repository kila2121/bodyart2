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
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="<?php echo $this->baseUrl . "styles/global_style.css" ?>">
            <link rel="stylesheet" href="<?php echo $this->baseUrl . "component/message/message.css" ?>">
            <?php foreach ($this->styles as $style): ?>
                <link rel="stylesheet" href="<?php echo $style; ?>">
            <?php endforeach; ?>
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
                <script src="<?php echo $script; ?>"></script>
            <?php endforeach; ?>
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