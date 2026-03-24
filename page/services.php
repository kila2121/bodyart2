<?php

$allServices = [];
$categories = [];

try {
    if (!$db || !$db->dbs) {
        throw new Exception("Нет подключения к БД");
    }

    $servicesQuery = $db->dbs->query("SELECT s.*,
                COALESCE(g.url, '/public/uploads/gallery_work/default.jpg') as gallery_url,
                COALESCE(g.photo_count, 0) as photos_count
            FROM services s
            LEFT JOIN (
                SELECT 
                    a.id_service,
                    FIRST_VALUE(g.url) OVER (PARTITION BY a.id_service ORDER BY g.is_featured DESC, g.created_at DESC) as url,
                    COUNT(*) OVER (PARTITION BY a.id_service) as photo_count,
                    ROW_NUMBER() OVER (PARTITION BY a.id_service ORDER BY g.is_featured DESC, g.created_at DESC) as rn
                FROM gallery g
                JOIN appointment a ON a.id = g.id_appointment
                WHERE a.id_service IS NOT NULL
            ) g ON s.id = g.id_service AND g.rn = 1
            WHERE s.is_active = 1
            ORDER BY s.category, s.name;");
    $allServices = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);

    $categoriesQuery = $db->dbs->query("SELECT DISTINCT category FROM services ORDER BY category");
    $categories = $categoriesQuery->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log("Ошибка в services.php: " . $e->getMessage());
}

ob_start();
?>
<?php include_once "component/change_theme/changeTheme.php"; ?>
<?php include_once "component/services/hero/hero.php"; ?>
<section class="services-page">
    <div class="page-header">
        <h1>Наши услуги</h1>
    </div>

    <p class="services-description">
        Мы предлагаем широкий спектр услуг по модификации тела.
        Все процедуры выполняются профессиональными мастерами
        с использованием стерильных материалов.
    </p>
    <?php if (!empty($categories)): ?>
        <div class="services-filter" id="services-filter">
            <button class="active" data-filter="all">Все</button>
            <?php foreach ($categories as $category): ?>
                <button data-filter="<?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="services-container">
        <?php
        if (!empty($allServices)) {
            $GLOBALS["AllServices"] = $allServices;
            include_once "component/services/card_services/card.php";
        } else {
            echo "<p>Услуги временно недоступны</p>";
        }
        ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.services-filter button');
        const serviceCards = document.querySelectorAll('.service-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.dataset.filter;

                serviceCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'flex';
                    } else {
                        // Используем data-category атрибут вместо поиска по классу
                        const cardCategory = card.dataset.category;
                        if (cardCategory === filterValue) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            });
        });
    });
</script>

<?php
$content = ob_get_clean();

$template = new Template("BodyArt Studio - Услуги");
$template->addStyle("/styles/page/services.css");
$template->addStyle("/component/services/card_services/card.css");
$template->addStyle("/component/services/hero/hero.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->render($content);
?>