<?php
require_once 'config.php';

$error = '';
$success = '';

if (!empty($_POST['login']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $login = mysqli_real_escape_string($link, $_POST['login']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = password_hash(mysqli_real_escape_string($link, $_POST['password']), PASSWORD_DEFAULT);
    
    $check = mysqli_query($link, "SELECT * FROM users WHERE login='$login' OR email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = 'Такой логин или email уже существует';
    } else {
        $query = "INSERT INTO users (login, email, password) VALUES ('$login', '$email', '$password')";
        if (mysqli_query($link, $query)) {
            $success = 'Регистрация успешна! <a href="login.php">Войти</a>';
        } else {
            $error = 'Ошибка регистрации';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Регистрация - Блог</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto;">
            <h1>Блог</h1>
            <h2>Регистрация</h2>
            
            <?php if ($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <p style="color: green;"><?php echo $success; ?></p>
            <?php else: ?>
                <form method="POST">
                    <input type="text" name="login" placeholder="Логин" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Пароль" required>
                    <button type="submit" class="btn">Зарегистрироваться</button>
                </form>
                <p style="margin-top: 15px;">
                    Уже есть аккаунт? <a href="login.php">Войти</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>