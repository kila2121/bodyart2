<?php

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$cacheKey = 'master_' . $id;

$master = Cache::get($cacheKey);
if ($master === false) {
    try {
        $stmt = $db->dbs->prepare("SELECT * FROM master WHERE id = ?");
        $stmt->execute([$id]);
        $master = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Ошибка получения мастера: " . $e->getMessage());
    }
    Cache::set($cacheKey, $master, 3600);
}


if (!$master) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Мастер не найден</h1>";
    exit;
}

$works = [];
try {
    $stmt = $db->dbs->prepare("
        SELECT g.* FROM gallery g
        LEFT JOIN appointment a ON a.id = g.id_appointment
        WHERE a.id_master = ?
        ORDER BY g.is_featured DESC, g.created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$id]);
    $works = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ошибка получения работ мастера: " . $e->getMessage());
}

$reviews = [];
try {
    $stmt = $db->dbs->prepare("
        SELECT r.*, u.login, u.fio, u.avatar_url 
        FROM reviews r
        LEFT JOIN user u ON r.id_user = u.id
        LEFT JOIN appointment a ON a.id = r.id_appointment
        WHERE a.id_master = ? AND r.is_approved = 1
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ошибка получения отзывов: " . $e->getMessage());
}

$masterServices = [];
try {
    $stmt = $db->dbs->prepare("
        SELECT s.* FROM services s
        WHERE s.is_active = 1
        AND EXISTS (
            SELECT 1 FROM appointment a
            WHERE a.id_service = s.id AND a.id_master = ?
        )
        ORDER BY s.category, s.name
        LIMIT 4;");
    $stmt->execute([$id]);
    $masterServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Ошибка получения услуг мастера: " . $e->getMessage());
}

$worksCount = 0;
try {
    $stmt = $db->dbs->prepare("
        SELECT COUNT(*) as count FROM gallery g
        LEFT JOIN appointment a ON a.id = g.id_appointment
        WHERE a.id_master = ?
    ");
    $stmt->execute([$id]);
    $worksCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (Exception $e) {
    error_log("Ошибка подсчета работ: " . $e->getMessage());
}

ob_start();
?>

<section class="master-detail">
    <?php include_once "component/details_master_component/hero/hero.php"; ?>

    <div class="master-content">
        <div class="master-content-inner">
            <div class="content-columns">
                <div class="content-column left-column">
                    <?php include_once "component/details_master_component/aboutMaster/aboutMaster.php"; ?>

                    <?php include_once "component/details_master_component/contact/contact.php"; ?>
                </div>

                <div class="content-column right-column">
                    <?php if (!empty($works)): ?>
                        <?php include_once "component/details_master_component/master_work/master_work.php"; ?>
                    <?php endif; ?>

                    <?php if (!empty($reviews)):
                        include_once "component/details_master_component/reviews/reviews.php";
                    else: ?>
                        <div class="master-section reviews-section">
                            <h2 class="section-heading">
                                <i class="fas fa-star"></i>
                                Отзывы
                            </h2>
                            <p class="no-reviews-text">У мастера пока нет отзывов</p>
                        </div>
                    <?php endif; ?>

                    <?php include_once "component/details_master_component/sticky-action/sticky-action.php"; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
$template = new Template("BodyArt Studio - " . htmlspecialchars($master['fio']));
$template->addStyle("/styles/page/master_detail.css");
$template->addStyle("/component/details_master_component/aboutMaster/aboutMaster.css");
$template->addStyle("/component/details_master_component/contact/contact.css");
$template->addStyle("/component/details_master_component/master_work/master_work.css");
$template->addStyle("/component/details_master_component/reviews/reviews.css");
$template->addStyle("/component/details_master_component/hero/hero.css");
$template->addStyle("/component/details_master_component/sticky-action/sticky-action.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->render($content);
?>