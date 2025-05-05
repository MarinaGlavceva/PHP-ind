<?php
require_once 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка заполненности полей
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Все поля обязательны для заполнения.";
    }

    // Проверка формата email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный формат email.";
    }

    // Проверка совпадения паролей
    if ($password !== $confirm_password) {
        $errors[] = "Пароли не совпадают.";
    }

    // Проверка минимальной длины пароля
    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов.";
    }

    // Если ошибок нет
    if (empty($errors)) {
        // Проверяем, существует ли пользователь
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);

        if ($stmt->fetch()) {
            $errors[] = "Пользователь с таким логином или email уже существует.";
        } else {
            // Хешируем пароль и вставляем пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password
            ]);

            $success = "Регистрация успешна! Теперь вы можете войти.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <h2>Регистрация</h2>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color: green;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label>Логин: <input type="text" name="username" required></label><br><br>
        <label>Email: <input type="email" name="email" required></label><br><br>
        <label>Пароль: <input type="password" name="password" required></label><br><br>
        <label>Подтвердите пароль: <input type="password" name="confirm_password" required></label><br><br>
        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</body>
</html>
