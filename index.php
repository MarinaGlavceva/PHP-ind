<?php
session_start();

// Если пользователь уже авторизован, отправляем его на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'db.php';
$stmt = $pdo->query("SELECT title, description FROM tasks ORDER BY created_at DESC LIMIT 5");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница - Дневник задач</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">🏠 Главная</a>
        <a href="login.php">🔑 Войти</a>
        <a href="register.php">📝 Регистрация</a>
    </div>

    <h2>Добро пожаловать в Дневник задач ✅</h2>
    <p>Наше приложение поможет вам:</p>
    <ul>
        <li>📌 Создавать задачи</li>
        <li>✅ Отслеживать их выполнение</li>
        <li>🔍 Искать и управлять задачами</li>
    </ul>
    <h3>Последние задачи:</h3>
<?php if ($tasks): ?>
    <ul>
    <?php foreach ($tasks as $task): ?>
        <li><strong><?= htmlspecialchars($task['title']) ?>:</strong> <?= htmlspecialchars($task['description']) ?></li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Задач пока нет.</p>
<?php endif; ?>


    <p>Для начала работы войдите в свой аккаунт или создайте новый:</p>

    <a href="login.php" class="btn">🔑 Войти</a>
    <a href="register.php" class="btn">📝 Зарегистрироваться</a>
</body>
</html>

