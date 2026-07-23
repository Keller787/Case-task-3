<?php
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

if (!empty($_POST['title']) && !empty($_POST['content'])) {
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $content = mysqli_real_escape_string($link, $_POST['content']);
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    $query = "INSERT INTO posts (user_id, title, content, is_private) 
              VALUES ($user_id, '$title', '$content', $is_private)";
    
    if (mysqli_query($link, $query)) {
        $post_id = mysqli_insert_id($link);
        
        // Добавляем теги
        if (!empty($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            foreach ($tags as $tag_name) {
                $tag_name = trim($tag_name);
                if ($tag_name) {
                    // Проверяем существование тега
                    $tag_query = "SELECT id FROM tags WHERE name = '$tag_name'";
                    $tag_res = mysqli_query($link, $tag_query);
                    if (mysqli_num_rows($tag_res) > 0) {
                        $tag = mysqli_fetch_assoc($tag_res);
                        $tag_id = $tag['id'];
                    } else {
                        $insert_tag = "INSERT INTO tags (name) VALUES ('$tag_name')";
                        mysqli_query($link, $insert_tag);
                        $tag_id = mysqli_insert_id($link);
                    }
                    // Связываем пост с тегом
                    $link_query = "INSERT INTO post_tags (post_id, tag_id) VALUES ($post_id, $tag_id)";
                    mysqli_query($link, $link_query);
                }
            }
        }
        
        $message = '<p style="color: green;">Пост создан! <a href="post.php?id='.$post_id.'">Посмотреть</a></p>';
    } else {
        $message = '<p style="color: red;">Ошибка создания поста</p>';
    }
}

// Получаем существующие теги для подсказки
$existing_tags_query = "SELECT name FROM tags ORDER BY name";
$existing_tags_res = mysqli_query($link, $existing_tags_query);
$existing_tags = [];
while ($tag = mysqli_fetch_assoc($existing_tags_res)) {
    $existing_tags[] = $tag['name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Создать пост - Блог</title>
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
    
    <div class="container mb-20">
        <div class="card" style="max-width: 700px; margin: 0 auto;">
            <h2>Создать новый пост</h2>
            
            <?php echo $message; ?>
            
            <?php if (!empty($existing_tags)): ?>
                <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                    <strong>Существующие теги:</strong>
                    <?php foreach ($existing_tags as $tag): ?>
                        <span class="tag">#<?php echo $tag; ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="title" placeholder="Заголовок" required>
                <textarea name="content" placeholder="Текст поста" required></textarea>
                <input type="text" name="tags" placeholder="Теги через запятую (например: php, mysql, blog)">
                <label>
                    <input type="checkbox" name="is_private" value="1"> Приватный пост (только по запросу)
                </label>
                <button type="submit" class="btn">Опубликовать</button>
            </form>
        </div>
    </div>
</body>
</html>