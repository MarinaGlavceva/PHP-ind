<?php
session_start();
require_once 'db.php';

// Проверка авторизации
require_once 'functions.php';
checkAuth();


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получение задач пользователя
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель пользователя</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
    <p>Ваша роль: <?= htmlspecialchars($_SESSION['role']) ?></p>

    <p>
    <a href="create_task.php">➕ Создать новую задачу</a> |
    <a href="search.php">🔍 Поиск задач</a> |
    <a href="logout.php">🚪 Выйти</a>
</p>

    <h3>Ваши задачи:</h3>

    <?php if (empty($tasks)): ?>
        <p>У вас пока нет задач.</p>
    <?php else: ?>
        <?php
// Получаем статистику
$stmt = $pdo->prepare("SELECT 
    COUNT(*) AS total, 
    SUM(status = 'Completed') AS completed, 
    SUM(status = 'Pending') AS pending 
    FROM tasks WHERE user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h3>Статистика:</h3>
<ul>
    <li>Всего задач: <?= $stats['total'] ?></li>
    <li>Выполнено: <?= $stats['completed'] ?: 0 ?></li>
    <li>В ожидании: <?= $stats['pending'] ?: 0 ?></li>
</ul>

        <table>
            <tr>
                <th>Название</th>
                <th>Описание</th>
                <th>Приоритет</th>
                <th>Дедлайн</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><?= htmlspecialchars($task['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
                    <td><?= htmlspecialchars($task['priority']) ?></td>
                    <td><?= htmlspecialchars($task['due_date']) ?></td>
                    <td><?= htmlspecialchars($task['status']) ?></td>
                    <td>
                        <a href="edit_task.php?id=<?= $task['id'] ?>">✏️ Редактировать</a> |
                        <a href="delete_task.php?id=<?= $task['id'] ?>" onclick="return confirm('Точно удалить эту задачу?');">🗑️ Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
