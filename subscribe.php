<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$target_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($target_id && $target_id != $user_id) {
    // Проверяем, существует ли пользователь
    $check_user = mysqli_query($link, "SELECT id FROM users WHERE id = $target_id");
    if (mysqli_num_rows($check_user) > 0) {
        if ($action == 'subscribe') {
            // Проверяем, не подписан ли уже
            $check_sub = "SELECT * FROM subscriptions WHERE follower_id = $user_id AND following_id = $target_id";
            $check_res = mysqli_query($link, $check_sub);
            if (mysqli_num_rows($check_res) == 0) {
                $query = "INSERT INTO subscriptions (follower_id, following_id) VALUES ($user_id, $target_id)";
                if (mysqli_query($link, $query)) {
                    // Успешно подписались
                }
            }
        } elseif ($action == 'unsubscribe') {
            $query = "DELETE FROM subscriptions WHERE follower_id = $user_id AND following_id = $target_id";
            mysqli_query($link, $query);
        }
    }
}

header('Location: profile.php');
exit;
?>