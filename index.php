<?php require_once "connect.php" ?>
<?php require_once "action.php" ?>
<?php include_once "classes/template.php";
include_once "classes/cache.php";

if (isset($_REQUEST["page"])) {
    $page = $_REQUEST["page"];
    if ($page == "index") {
        header("Location: /index.php");
        exit();
    } else {
        if (isset($_REQUEST["id"])) {
            $_GET['id'] = $_REQUEST["id"];
        }
        if (!in_array($page, ['admin', 'details_master', 'gallery', 'masterProfile', 'masters', 'services', 'user'])) {
            header("Location: /index.php");
            exit();
        }
        include_once "page/" . $page . ".php";
        exit();
    }
}

ob_start();
?>
<?php include_once "component/index_component/hero_section/hero.php"; ?>

<div class="home-content">
    <?php include_once "component/change_theme/changeTheme.php"; ?>

    <?php include_once "component/index_component/advantages/advantages.php"; ?>

    <?php include_once "component/index_component/aboutUs/aboutUs.php"; ?>

    <?php include_once "component/index_component/popularServices/popularServices.php"; ?>

    <?php include_once "component/index_component/reviews/reviews.php"; ?>

    <?php include_once "component/index_component/goAppointment/goAppointment.php"; ?>

    <?php include_once "component/index_component/contact_section/contact_section.php"; ?>
</div>

<?php
$content = ob_get_clean();
$template = new Template("BodyArt Studio");
$template->addStyle("/component/index_component/hero_section/hero.css");
$template->addStyle("/component/index_component/advantages/advantages.css");
$template->addStyle("/component/index_component/aboutUs/aboutUs.css");
$template->addStyle("/component/index_component/popularServices/popularServices.css");
$template->addStyle("/component/index_component/reviews/reviews.css");
$template->addStyle("/component/index_component/goAppointment/goAppointment.css");
$template->addStyle("/component/index_component/contact_section/contact_section.css");
$template->addStyle("/component/change_theme/changeTheme.css");
$template->addStyle("/component/modal_window/style_reg_form.css");
$template->addStyle("/component/modal_window/appointment_form.css");
$template->addStyle("/styles/page/index.css");
$template->render($content);
?>