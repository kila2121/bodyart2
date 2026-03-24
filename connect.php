<?php
session_start();
header('Content-type: text/html; charset=utf-8');
setlocale(LC_ALL, 'ru_RU.utf8');
$ini_fields = parse_ini_file('./.gitignore/config.ini', true);
define("DB_HOST", $ini_fields['database']['host']);
define("DB_NAME", $ini_fields['database']['base']);
define("DB_USER", $ini_fields['database']['user']);
define("DB_PASW", $ini_fields['database']['pass']);
require_once "classes/pdo_create.php";
require_once "classes/csrf.php";
$db = new pdo_create(DB_USER, DB_HOST, DB_PASW, DB_NAME);
