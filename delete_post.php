<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id) {
    $query = "DELETE FROM posts WHERE id = $post_id AND user_id = $user_id";
    mysqli_query($link, $query);
}

header('Location: profile.php');
exit;
?>