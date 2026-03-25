<?php
$stats = Cache::get('aboutUs_stats');

if ($stats === false) {
    $yearWork = $db->dbs->query("SELECT YEAR(CURDATE()) - YEAR(MIN(date_reg)) as years_working FROM user")->fetchColumn();
    $mastersCount = $db->dbs->query("SELECT COUNT(*) FROM master WHERE is_Active = 1")->fetchColumn();
    $countComletedWork = $db->dbs->query("SELECT COUNT(*) FROM appointment WHERE status = 'completed'")->fetchColumn();

    $stats = [$yearWork, $mastersCount, $countComletedWork];
    Cache::set("aboutUs_stats", $stats, 3600);
}

list($yearWork, $mastersCount, $countComletedWork) = $stats;

?>
<section class="about-studio">
    <div class="about-content">
        <h2 class="section-title">О студии BodyArt</h2>
        <p class="about-text">
            Мы открылись в 2016 году с простой целью — создавать качественное искусство на теле в комфортной и
            безопасной атмосфере.
            За это время мы выполнили более 3000 работ, воспитали 10 мастеров и стали одной из самых рекомендуемых
            студий в городе.
        </p>
        <p class="about-text">
            Наша философия — индивидуальный подход к каждому клиенту. Мы не работаем по шаблонам, каждый эскиз создаётся
            с нуля
            специально для вас. Используем только премиальные материалы и следим за трендами индустрии.
        </p>

        <div class="stats-row">
            <div class="stat-block">
                <span class="stat-number"><?= $yearWork ?></span>
                <span class="stat-label">лет работы</span>
            </div>
            <div class="stat-block">
                <span class="stat-number"><?= $countComletedWork ?></span>
                <span class="stat-label">работ</span>
            </div>
            <div class="stat-block">
                <span class="stat-number"><?= $mastersCount ?></span>
                <span class="stat-label">мастеров</span>
            </div>
        </div>
    </div>

    <div class="about-image">
        <img src="/public/studio-interior.jpg" alt="Интерьер студии" loading="lazy">
    </div>
</section>