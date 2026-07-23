<?php
$host = 'doguyomayas.beget.app';
$dbname = 'task1_db';
$user = 'task1_db';
$pass = '!89PlNlMvI6h';

$link = mysqli_connect($host, $user, $pass, $dbname);

if (!$link) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

mysqli_set_charset($link, "utf8");

session_start();
?>