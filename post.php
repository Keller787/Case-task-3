<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$post_id) {
    header('Location: index.php');
    exit;
}

$post_query = "SELECT p.*, u.login FROM posts p 
               JOIN users u ON p.user_id = u.id 
               WHERE p.id = $post_id";
$post_res = mysqli_query($link, $post_query);
$post = mysqli_fetch_assoc($post_res);

if (!$post) {
    header('Location: index.php');
    exit;
}

// проверка доступа к приватным постам
$can_view = false;

// Если пост публичный - могут все
if ($post['is_private'] == 0) {
    $can_view = true;
}

// Если пост приватный
if ($post['is_private'] == 1) {
    // 2.1 Автор может смотреть свой пост
    if ($post['user_id'] == $user_id) {
        $can_view = true;
    }
    
    // Подписчик может смотреть пост
    $check_sub = "SELECT * FROM subscriptions 
                  WHERE follower_id = $user_id 
                  AND following_id = {$post['user_id']}";
    $sub_res = mysqli_query($link, $check_sub);
    if (mysqli_num_rows($sub_res) > 0) {
        $can_view = true;
    }
}

// Если нет доступа - редирект
if (!$can_view) {
    header('Location: index.php');
    exit;
}

// Добавление комментария
if (!empty($_POST['comment'])) {
    $comment = mysqli_real_escape_string($link, $_POST['comment']);
    $query = "INSERT INTO comments (post_id, user_id, content) VALUES ($post_id, $user_id, '$comment')";
    mysqli_query($link, $query);
    header("Location: post.php?id=$post_id");
    exit;
}

$comments_query = "SELECT c.*, u.login FROM comments c 
                   JOIN users u ON c.user_id = u.id 
                   WHERE c.post_id = $post_id 
                   ORDER BY c.created_at ASC";
$comments_res = mysqli_query($link, $comments_query);

// Теги поста
$tags_query = "SELECT t.name FROM tags t 
               JOIN post_tags pt ON pt.tag_id = t.id 
               WHERE pt.post_id = $post_id";
$tags_res = mysqli_query($link, $tags_query);
$tags = [];
while ($tag = mysqli_fetch_assoc($tags_res)) {
    $tags[] = $tag['name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo $post['title']; ?> - Блог</title>
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
        <div class="card" style="<?php echo $post['is_private'] ? 'border-left: 4px solid #f39c12;' : 'border-left: 4px solid #27ae60;'; ?>">
            <h2><?php echo $post['title']; ?></h2>
            <div class="meta">
                <?php echo $post['login']; ?> | 
                <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                <?php if ($post['is_private']): ?>
                    <span style="color: #f39c12; font-weight: bold;">Приватный (доступен подписчикам)</span>
                <?php else: ?>
                    <span style="color: #27ae60;">Публичный</span>
                <?php endif; ?>
            </div>
            
            <!-- Отображение тегов -->
            <?php if (!empty($tags)): ?>
                <div style="margin: 10px 0;">
                    <strong>Теги:</strong>
                    <?php foreach ($tags as $tag): ?>
                        <a href="index.php?tag=<?php echo urlencode($tag); ?>" class="tag">#<?php echo $tag; ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="margin: 20px 0; line-height: 1.8;">
                <?php echo nl2br($post['content']); ?>
            </div>
            
            <?php if ($post['user_id'] == $user_id): ?>
                <a href="edit_post.php?id=<?php echo $post_id; ?>" class="btn btn-sm">Редактировать</a>
                <a href="delete_post.php?id=<?php echo $post_id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пост?')">Удалить</a>
            <?php endif; ?>
        </div>
        
        <!-- Комментарии -->
        <div class="card">
            <h3>Комментарии (<?php echo mysqli_num_rows($comments_res); ?>)</h3>
            
            <?php if (mysqli_num_rows($comments_res) > 0): ?>
                <?php while ($comment = mysqli_fetch_assoc($comments_res)): ?>
                    <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <strong><?php echo $comment['login']; ?></strong>
                        <span style="color: #777; font-size: 12px;">
                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                        </span>
                        <p><?php echo nl2br($comment['content']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Пока нет комментариев</p>
            <?php endif; ?>
            
            <form method="POST" style="margin-top: 15px;">
                <textarea name="comment" placeholder="Написать комментарий..." required></textarea>
                <button type="submit" class="btn btn-sm">Отправить</button>
            </form>
        </div>
    </div>
</body>
</html>