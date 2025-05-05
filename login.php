<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Проверка заполненности полей
    if (empty($username) || empty($password)) {
        $errors[] = "Все поля обязательны для заполнения.";
    }

    if (empty($errors)) {
        // Проверяем наличие пользователя
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Логин успешен
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Редирект на панель управления
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = "Неверный логин или пароль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <h2>Вход</h2>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label>Логин: <input type="text" name="username" required></label><br><br>
        <label>Пароль: <input type="password" name="password" required></label><br><br>
        <button type="submit">Войти</button>
    </form>

    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    <p><a href="forgot_password.php">🔄 Забыли пароль?</a></p>

</body>
</html>
