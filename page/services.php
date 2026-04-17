<?php

$allServices = [];
$categories = [];

try {
    if (!$db || !$db->dbs) {
        throw new Exception("Нет подключения к БД");
    }
    $allServices = Cache::get('all_services');
    if ($allServices === false) {
        $allServices = $db->dbs->query("SELECT s.*,
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
            ORDER BY s.category, s.name;")->fetchAll(PDO::FETCH_ASSOC);
        Cache::set('all_services', $allServices, 3600);
    }
    $categories = Cache::get('categories_services');
    if ($categories === false) {
        $categories = $db->dbs->query("SELECT DISTINCT category FROM services ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
        Cache::set('categories_services', $categories, 3600);
    }
    $popularServices = Cache::get('popular_services');
    if ($popularServices === false) {
        $popularServices = $db->dbs->query("
                        SELECT 
                        s.id, 
                        s.name, 
                        s.price, 
                        s.duration, 
                        s.category,
                        (
                            SELECT g.url 
                            FROM gallery g
                            LEFT JOIN appointment a ON a.id = g.id_appointment
                            WHERE a.id_service = s.id
                            ORDER BY g.is_featured DESC, g.created_at DESC
                            LIMIT 1
                        ) as gallery_url,
                        COUNT(a.id) as appointments_count
                    FROM services s
                    LEFT JOIN appointment a ON a.id_service = s.id
                    WHERE s.is_active = 1
                    GROUP BY s.id, s.name, s.price, s.duration, s.category
                    ORDER BY appointments_count DESC, s.name
                    LIMIT 4
                    ")->fetchAll(PDO::FETCH_ASSOC);
        Cache::set("popular_services", $popularServices, 3600);
    }

} catch (Exception $e) {
    error_log("Ошибка в services.php: " . $e->getMessage());
}

ob_start();
?>
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
    <div class="services-layout">
        <div class="services-main">
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
        </div>

        <aside class="services-sidebar">
            <div class="sidebar-widget">
                <h3>ПОПУЛЯРНЫЕ УСЛУГИ</h3>
                <ul class="popular-services-list">
                    <?php


                    if (!empty($popularServices)):
                        foreach ($popularServices as $service): ?>
                            <li>
                                <a onclick="openServiceModal(<?= $service['id'] ?>)">
                                    <?= htmlspecialchars($service['name']) ?>
                                    <span><?= htmlspecialchars($service['category']) ?></span>
                                </a>
                            </li>
                        <?php endforeach;
                    else: ?>
                        <li>Скоро здесь появятся популярные услуги</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="sidebar-widget">
                <h3>СОЦСЕТИ</h3>
                <div class="social-links">
                    <a href="https://vk.com/bodyartstudio" target="_blank"><i class="fab fa-vk"></i></a>
                    <a href="https://t.me/bodyartstudio" target="_blank"><i class="fab fa-telegram"></i></a>
                    <a href="https://www.instagram.com/bodyartstudio/" target="_blank"><i
                            class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="sidebar-widget">
                <h3>РЕЖИМ РАБОТЫ</h3>
                <div class="contact-info">
                    <p><i class="far fa-clock"></i> 10:00 – 22:00</p>
                    <p><i class="fas fa-calendar-week"></i> Без выходных</p>
                </div>
            </div>

            <div class="sidebar-widget">
                <h3>КОНТАКТЫ</h3>
                <div class="contact-info">
                    <p><i class="fas fa-phone-alt"></i> +7 (999) 123-45-67</p>
                    <p><i class="fas fa-map-marker-alt"></i> Ставрополь, ул. Ленина, 301</p>
                </div>
            </div>
        </aside>
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
$template->render($content);
?>