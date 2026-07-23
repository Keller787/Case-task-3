<?php
require_once 'config.php';

$error = '';

if (!empty($_POST['login']) && !empty($_POST['password'])) {
    $login = mysqli_real_escape_string($link, $_POST['login']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    $query = "SELECT * FROM users WHERE login='$login'";
    $res = mysqli_query($link, $query);
    $user = mysqli_fetch_assoc($res);
    
    if (!empty($user) && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login'] = $user['login'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Вход - Блог</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <h1>Блог</h1>
            <h2>Вход</h2>
            
            <?php if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <form method="POST">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" class="btn">Войти</button>
            </form>
            
            <p style="margin-top: 15px;">
                Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </p>
        </div>
    </div>
</body>
</html>