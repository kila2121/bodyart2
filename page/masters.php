<?php

$allMasters = [];
$spec = [];

try {
    if (!$db || !$db->dbs) {
        throw new Exception("Нет подключения к БД");
    }

    $allMasters = Cache::get('all_masters');
    if ($allMasters === false) {
        $allMasters = $db->dbs->query("SELECT 
                                        m.*,
                                        COUNT(a.id) AS orders_count
                                    FROM master m
                                    LEFT JOIN appointment a ON a.id_master = m.id 
                                        AND a.status IN ('completed', 'confirmed', 'pending')
                                    GROUP BY m.id
                                    ORDER BY orders_count DESC, m.spec, m.fio")->fetchAll(PDO::FETCH_ASSOC);
        Cache::set('all_masters', $allMasters, 3600);
    }

    $spec = Cache::get('spec_masters');
    if ($spec === false) {
        $spec = $db->dbs->query("SELECT DISTINCT spec FROM master ORDER BY spec")->fetchAll(PDO::FETCH_COLUMN);
        Cache::set('spec_masters', $spec, 3600);
    }

} catch (Exception $e) {
    error_log("Ошибка в masters.php: " . $e->getMessage());
}


ob_start();
?>
<?php include_once "component/masters/hero/hero.php"; ?>
<section class="masters-page">
    <div class="page-header">
        <h1>Наши мастера</h1>
    </div>

    <p class="masters-description">
        В нашей студии работают профессиональные мастера с многолетним опытом.
        Каждый специалист имеет свою уникальную специализацию и стиль работы.
    </p>
    <div class="masters-layout">
        <div class="masters-main">
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
        </div>
        <aside class="masters-sidebar">
            <div class="sidebar-widget">
                <h3>ПОПУЛЯРНЫЕ МАСТЕРА</h3>
                <ul class="popular-masters-list">
                    <?php
                    $popularMasters = array_slice($allMasters, 0, 5);
                    foreach ($popularMasters as $master): ?>
                        <li>
                            <a href="/index.php?page=details_master&id=<?= $master['id'] ?>">
                                <?= htmlspecialchars($master['fio']) ?>
                                <span><?= htmlspecialchars($master['spec']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-widget">
                <h3>СОЦСЕТИ</h3>
                <div class="social-links">
                    <a href="#" target="_blank"><i class="fab fa-vk"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-telegram"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
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
                    <p><i class="fas fa-map-marker-alt"></i> г. Москва</p>
                </div>
            </div>
        </aside>
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
$template->addScript("/script/function.js");
$template->render($content);
?>