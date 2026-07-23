<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем список подписок
$subs_query = "SELECT following_id FROM subscriptions WHERE follower_id = $user_id";
$subs_res = mysqli_query($link, $subs_query);
$subs = [];
while ($row = mysqli_fetch_assoc($subs_res)) {
    $subs[] = $row['following_id'];
}

// Фильтр по тегам
$tag_filter = '';
$tag_where = '';
if (!empty($_GET['tag'])) {
    $tag_name = mysqli_real_escape_string($link, $_GET['tag']);
    $tag_filter = $tag_name;
    $tag_where = "AND p.id IN (SELECT post_id FROM post_tags pt JOIN tags t ON pt.tag_id = t.id WHERE t.name = '$tag_name')";
}

// посты от подписок только приватные
$feed_posts = [];
if (!empty($subs)) {
    $subs_list = implode(',', $subs);
    
    // Получаем только приватные посты от подписок (is_private = 1)
    $feed_query = "SELECT p.*, u.login FROM posts p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.user_id IN ($subs_list) 
                  AND p.is_private = 1 
                  $tag_where
                  ORDER BY p.created_at DESC 
                  LIMIT 50";
    $feed_res = mysqli_query($link, $feed_query);
    while ($post = mysqli_fetch_assoc($feed_res)) {
        $feed_posts[] = $post;
    }
}

// все публичные посты
$posts_query = "SELECT p.*, u.login FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.is_private = 0 
                $tag_where
                ORDER BY p.created_at DESC";
$posts_res = mysqli_query($link, $posts_query);

// Получаем все теги для вывода
$all_tags_query = "SELECT DISTINCT t.name, t.id FROM tags t 
                   JOIN post_tags pt ON pt.tag_id = t.id 
                   ORDER BY t.name";
$all_tags_res = mysqli_query($link, $all_tags_query);

// Функция для получения тегов поста
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

// Получаем список пользователей, на которых подписан
$subscribed_users = [];
if (!empty($subs)) {
    $subs_list = implode(',', $subs);
    $users_query = "SELECT id, login FROM users WHERE id IN ($subs_list)";
    $users_res = mysqli_query($link, $users_query);
    while ($u = mysqli_fetch_assoc($users_res)) {
        $subscribed_users[] = $u['login'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Блог - Главная</title>
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
        <h1>Лента</h1>
        
        <!-- Теги для фильтрации -->
        <div class="card">
            <h3>Фильтр по тегам</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a href="index.php" class="btn btn-sm <?php echo empty($tag_filter) ? 'btn' : ''; ?>" style="<?php echo empty($tag_filter) ? '' : 'background: #ddd; color: #333;'; ?>">
                    Все посты
                </a>
                <?php while ($tag = mysqli_fetch_assoc($all_tags_res)): ?>
                    <a href="index.php?tag=<?php echo urlencode($tag['name']); ?>" 
                       class="btn btn-sm <?php echo $tag_filter == $tag['name'] ? 'btn' : ''; ?>"
                       style="<?php echo $tag_filter == $tag['name'] ? '' : 'background: #e9ecef; color: #333;'; ?>">
                        #<?php echo $tag['name']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Приватные посты от подписок -->
        <div class="card" style="border-left: 4px solid #f39c12;">
            <h3>Приватные посты от подписок (<?php echo count($feed_posts); ?>)</h3>
            
            <?php if (!empty($subscribed_users)): ?>
                <div style="margin: 5px 0 15px 0; padding: 8px; background: #e8f4fd; border-radius: 5px;">
                    <small>Вы подписаны на: <?php echo implode(', ', $subscribed_users); ?></small>
                </div>
            <?php endif; ?>
            
            <?php if (count($feed_posts) > 0): ?>
                <?php foreach ($feed_posts as $post): ?>
                    <div style="padding: 15px 0; border-bottom: 1px solid #eee;">
                        <h4><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h4>
                        <div class="meta">
                            <?php echo $post['login']; ?> | 
                            <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                            <span style="color: #f39c12; font-weight: bold;"> Приватный</span>
                        </div>
                        <p><?php echo mb_substr($post['content'], 0, 150); ?>...</p>
                        <?php 
                        $tags = getPostTags($link, $post['id']);
                        if (!empty($tags)): 
                        ?>
                            <div style="margin-top: 5px;">
                                <?php foreach ($tags as $tag): ?>
                                    <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="tag">#<?php echo $tag; ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!empty($subs)): ?>
                    <p>У пользователей, на которых вы подписаны, пока нет приватных постов.</p>
                    <p style="font-size: 13px; color: #777;">Приватные посты видны только подписчикам.</p>
                <?php else: ?>
                    <p>Вы ни на кого не подписаны. <a href="profile.php">Найти пользователей</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Все публичные посты -->
        <h2>Все публичные посты <?php echo !empty($tag_filter) ? 'с тегом #' . $tag_filter : ''; ?></h2>
        <?php if (mysqli_num_rows($posts_res) > 0): ?>
            <?php while ($post = mysqli_fetch_assoc($posts_res)): ?>
                <div class="card" style="border-left: 4px solid #27ae60;">
                    <h3><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h3>
                    <div class="meta">
                        <?php echo $post['login']; ?> | 
                        <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                        <span style="color: #27ae60;">Публичный</span>
                    </div>
                    <p><?php echo mb_substr($post['content'], 0, 200); ?>...</p>
                    <?php 
                    $tags = getPostTags($link, $post['id']);
                    if (!empty($tags)): 
                    ?>
                        <div style="margin-top: 5px;">
                            <?php foreach ($tags as $tag): ?>
                                <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="tag">#<?php echo $tag; ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Нет публичных постов <?php echo !empty($tag_filter) ? 'с тегом #' . $tag_filter : ''; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>