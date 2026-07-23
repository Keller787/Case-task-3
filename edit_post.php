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

$check_query = "SELECT * FROM posts WHERE id = $post_id AND user_id = $user_id";
$check_res = mysqli_query($link, $check_query);
$post = mysqli_fetch_assoc($check_res);

if (!$post) {
    header('Location: index.php');
    exit;
}

$message = '';

if (!empty($_POST['title']) && !empty($_POST['content'])) {
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $content = mysqli_real_escape_string($link, $_POST['content']);
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    $query = "UPDATE posts SET title = '$title', content = '$content', is_private = $is_private WHERE id = $post_id";
    
    if (mysqli_query($link, $query)) {
        $message = '<p style="color: green;">Пост обновлен! <a href="post.php?id='.$post_id.'">Посмотреть</a></p>';
    } else {
        $message = '<p style="color: red;">Ошибка обновления</p>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактировать пост</title>
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
            <h2>Редактировать пост</h2>
            
            <?php echo $message; ?>
            
            <form method="POST">
                <input type="text" name="title" value="<?php echo $post['title']; ?>" required>
                <textarea name="content" required><?php echo $post['content']; ?></textarea>
                <label>
                    <input type="checkbox" name="is_private" value="1" <?php echo $post['is_private'] ? 'checked' : ''; ?>> Приватный пост
                </label>
                <button type="submit" class="btn">Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>