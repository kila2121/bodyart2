<?php

$allMasters = [];
$spec = [];

try {
    if (!$db || !$db->dbs) {
        throw new Exception("Нет подключения к БД");
    }

    $mastersQuery = $db->dbs->query("SELECT * FROM master ORDER BY spec, fio");
    $allMasters = $mastersQuery->fetchAll(PDO::FETCH_ASSOC);

    $categoriesQuery = $db->dbs->query("SELECT DISTINCT spec FROM master ORDER BY spec");
    $spec = $categoriesQuery->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log("Ошибка в masters.php: " . $e->getMessage());
}

ob_start();
?>
<?php include_once "component/change_theme/changeTheme.php"; ?>
<?php include_once "component/masters/hero/hero.php"; ?>
<section class="masters-page">
    <div class="page-header">
        <h1>Наши мастера</h1>
    </div>

    <p class="masters-description">
        В нашей студии работают профессиональные мастера с многолетним опытом.
        Каждый специалист имеет свою уникальную специализацию и стиль работы.
    </p>

    <?php if (!empty($spec)): ?>
        <div class="masters-filter" id="masters-filter">
            <button class="active" data-filter="all">Все</button>
            <?php foreach ($spec as $sp): ?>
                <button data-filter="<?php echo htmlspecialchars($sp); ?>">
                    <?php echo htmlspecialchars($sp); ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="masters-container">
        <?php
        if (!empty($allMasters)) {
            $GLOBALS['mastersData'] = $allMasters;
            include_once "component/masters/card_masters/card.php";
        } else {
            echo "<p>Мастера временно недоступны</p>";
        }
        ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.masters-filter button');
        const masterCards = document.querySelectorAll('.master-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.dataset.filter;

                masterCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'flex';
                    } else {
                        const specElement = card.querySelector('.master-specialization');
                        if (specElement) {
                            let specText = specElement.textContent;
                            specText = specText.replace('Специализация:', '').trim();

                            if (specText === filterValue) {
                                card.style.display = 'flex';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    }
                });
            });
        });
    });
</script>

<?php
$content = ob_get_clean();

$template = new Template("BodyArt Studio - Мастера");
$template->addStyle("/styles/page/masters.css");
$template->addStyle("/component/masters/hero/hero.css");
$template->addStyle("/component/masters/card_masters/card.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->render($content);
?>