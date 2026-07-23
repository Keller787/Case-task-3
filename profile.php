<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_res = mysqli_query($link, $user_query);
$user = mysqli_fetch_assoc($user_res);

$posts_query = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC";
$posts_res = mysqli_query($link, $posts_query);

$followers_query = "SELECT u.* FROM users u 
                    JOIN subscriptions s ON s.follower_id = u.id 
                    WHERE s.following_id = $user_id";
$followers_res = mysqli_query($link, $followers_query);

$following_query = "SELECT u.* FROM users u 
                    JOIN subscriptions s ON s.following_id = u.id 
                    WHERE s.follower_id = $user_id";
$following_res = mysqli_query($link, $following_query);

$all_users_query = "SELECT * FROM users WHERE id != $user_id";
$all_users_res = mysqli_query($link, $all_users_query);

// Функция получения тегов поста
function getPostTags($link, $post_id) {
    $tags_query = "SELECT t.name FROM tags t 
                   JOIN post_tags pt ON pt.tag_id = t.id 
                   WHERE pt.post_id = $post_id";
    $tags_res = mysqli_query($link, $tags_query);
    $tags = [];
    while ($tag = mysqli_fetch_assoc($tags_res)) {
        $tags[] = $tag['name'];
    }
    return $tags;
}

// Получаем список ID на которых подписан
$sub_ids = [];
$sub_check = mysqli_query($link, "SELECT following_id FROM subscriptions WHERE follower_id = $user_id");
while ($row = mysqli_fetch_assoc($sub_check)) {
    $sub_ids[] = $row['following_id'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Профиль - Блог</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo">Блог</a>
            <nav>
                <a href="index.php">Главная</a>
                <a href="create_post.php">Новый пост</a>
                <a href="profile.php">Профиль</a>
                <a href="logout.php">Выход</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="card">
            <h2><?php echo $user['login']; ?></h2>
            <p><?php echo $user['email']; ?></p>
            <p>Зарегистрирован: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <div class="card">
            <h3>Мои посты (<?php echo mysqli_num_rows($posts_res); ?>)</h3>
            <?php if (mysqli_num_rows($posts_res) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts_res)): 
                    $tags = getPostTags($link, $post['id']);
                ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                        <h4><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h4>
                        <div class="meta">
                            <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                            <?php if ($post['is_private']): ?>
                                <span style="color: red;">[Приватный]</span>
                            <?php else: ?>
                                <span style="color: green;">[Публичный]</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($tags)): ?>
                            <div style="margin: 5px 0;">
                                <?php foreach ($tags as $tag): ?>
                                    <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="tag">#<?php echo $tag; ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm">Редактировать</a>
                        <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пост?')">Удалить</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>У вас нет постов. <a href="create_post.php">Создать первый пост</a></p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h3>Подписчики (<?php echo mysqli_num_rows($followers_res); ?>)</h3>
            <?php while ($follower = mysqli_fetch_assoc($followers_res)): ?>
                <span class="tag"><?php echo $follower['login']; ?></span>
            <?php endwhile; ?>
        </div>
        
        <div class="card">
            <h3>Подписки (<?php echo mysqli_num_rows($following_res); ?>)</h3>
            <?php while ($following = mysqli_fetch_assoc($following_res)): ?>
                <span class="tag"><?php echo $following['login']; ?></span>
            <?php endwhile; ?>
        </div>
        
        <div class="card">
            <h3>Другие пользователи</h3>
            <?php while ($u = mysqli_fetch_assoc($all_users_res)): 
                $is_sub = in_array($u['id'], $sub_ids);
            ?>
                <div class="user-item">
                    <span>
                        <?php echo $u['login']; ?>
                        <?php if ($is_sub): ?>
                            <span style="color: green; font-size: 12px;">(подписан)</span>
                        <?php endif; ?>
                    </span>
                    <?php if ($is_sub): ?>
                        <a href="subscribe.php?action=unsubscribe&user_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger">Отписаться</a>
                    <?php else: ?>
                        <a href="subscribe.php?action=subscribe&user_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-success">Подписаться</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>