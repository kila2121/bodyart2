<!-- Hero секция -->
<?php
$stats = Cache::get('hero_stats');

if ($stats === false) {
    $happyUsers = $db->dbs->query("SELECT COUNT(DISTINCT id_user) FROM reviews WHERE rating IN (4, 5)")->fetchColumn();
    $completeWork = $db->dbs->query("SELECT COUNT(*) FROM appointment WHERE status='completed'")->fetchColumn();
    $avarageRaiting = $db->dbs->query("SELECT ROUND(AVG(rating), 1) FROM reviews WHERE is_approved = 1")->fetchColumn();
    $competedUsers = $db->dbs->query("SELECT COUNT(DISTINCT id_user) FROM appointment WHERE status IN ('completed', 'confirmed')")->fetchColumn();
    $stats = [$happyUsers, $completeWork, $avarageRaiting, $competedUsers];
    Cache::set('hero_stats', $stats, 3600);
}

list($happyUsers, $completeWork, $avarageRaiting, $competedUsers) = $stats;

?>
<div class="hero">
    <div class="hero_content">
        <div class="head">
            <h1 class="title">
                <span class="title_line">Превратите своё тело</span>
                <span class="title_line">в <span class="title_highlight">живое произведение</span></span>
                <span class="title_line">искусства</span>
            </h1>
            <p class="subtitle">
                В <span class="subtitle_highlight">BodyArt Studio</span> мы создаём уникальные татуировки и
                модификации,
                которые отражают вашу индивидуальность и становятся частью вашей истории
            </p>
        </div>

        <div class="grid_container">
            <div class="card">
                <i class="fas fa-user-check icons"></i>
                <h3>Бесплатная консультация</h3>
                <p>Обсудим вашу идею и создадим индивидуальный эскиз без предоплаты</p>
            </div>

            <div class="card">
                <i class="fas fa-shield-alt icons"></i>
                <h3>100% безопасность</h3>
                <p>Стерильные условия, одноразовые инструменты и сертифицированные материалы</p>
            </div>

            <div class="card">
                <i class="fas fa-gem icons"></i>
                <h3>Пожизненная гарантия</h3>
                <p>Бесплатная коррекция в течение первого года и поддержка на протяжении всей жизни</p>
            </div>

            <div class="card">
                <i class="fas fa-star icons"></i>
                <h3>ТОП-мастера</h3>
                <p>Профессионалы с 5+ лет опыта, победители международных конкурсов</p>
            </div>
        </div>

        <div class="trust">
            <div class="trust_item">
                <div class="trust_icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="trust_info">
                    <span class="trust_number">
                        <?= $happyUsers ?>
                    </span>
                    <span class="trust_label">Довольных клиентов</span>
                </div>
            </div>

            <div class="trust_item">
                <div class="trust_icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="trust_info">
                    <span class="trust_number"><?= $avarageRaiting ?></span>
                    <span class="trust_label">Рейтинг</span>
                </div>
            </div>

            <div class="trust_item">
                <div class="trust_icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="trust_info">
                    <span class="trust_number"><?= $completeWork ?></span>
                    <span class="trust_label">Выполненных работ</span>
                </div>
            </div>

            <div class="trust_item">
                <div class="trust_icon">
                    <i class="fas fa-medal"></i>
                </div>
                <div class="trust_info">
                    <span class="trust_number"><?= $competedUsers ?></span>
                    <span class="trust_label">Обслуженных людей</span>
                </div>
            </div>
        </div>

        <a href="#aboutUs" class="scroll_link">
            <div class="scroll_indicator">
                <div class="scroll_arrow">
                    <i class="fas fa-angle-down icons2"></i>
                    <i class="fas fa-angle-down icons2"></i>
                    <i class="fas fa-angle-down icons2"></i>
                </div>
                <span class="scroll_text">Листайте вниз, чтобы узнать больше</span>
            </div>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scroll = document.querySelector('.scroll_indicator')
        if (scroll) {
            scroll.addEventListener('click', function () {
                const advantages = document.querySelector('.advantages');
                if (advantages) {
                    advantages.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        }
    })
</script>