<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $errors[] = "Введите email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email.";
    }

    if (empty($errors)) {
        // Проверяем, существует ли пользователь с таким email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Генерируем новый пароль
            $new_password = bin2hex(random_bytes(4)); // 8 символов
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Обновляем пароль в БД
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([
                'password' => $hashed_password,
                'id' => $user['id']
            ]);

            $success = "Ваш новый пароль: <strong>$new_password</strong>. Пожалуйста, войдите с новым паролем.";
        } else {
            $errors[] = "Пользователь с таким email не найден.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">🏠 Главная</a>
        <a href="login.php">🔑 Войти</a>
        <a href="register.php">📝 Регистрация</a>
    </div>

    <h2>Восстановление пароля</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Email:
            <input type="email" name="email" required>
        </label>
        <button type="submit">🔄 Сбросить пароль</button>
    </form>
</body>
</html>
