<?php
session_start();
require_once 'db.php';

if (!isAdmin()) {
    die('Доступ запрещён');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Обработка смены роли
if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $new_role = $_GET['change_role'] === 'user' ? 'user' : 'admin';
    
    // Не разрешаем изменять самого себя
    if ($user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute(['role' => $new_role, 'id' => $user_id]);
    }
    header('Location: admin.php');
    exit;
}

// Получаем всех пользователей
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем все задачи
$stmt = $pdo->query("
    SELECT tasks.*, users.username 
    FROM tasks 
    JOIN users ON tasks.user_id = users.id 
    ORDER BY tasks.created_at DESC
");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Админ-панель</h2>

    <p><a href="dashboard.php">Назад в панель</a> | <a href="logout.php">Выйти</a></p>

    <h3>Список пользователей:</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Логин</th>
            <th>Email</th>
            <th>Роль</th>
            <th>Действие</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td>
                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin.php?change_role=user&id=<?= $user['id'] ?>">Сделать user</a>
                        <?php else: ?>
                            <a href="admin.php?change_role=admin&id=<?= $user['id'] ?>">Сделать admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        (вы)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Все задачи:</h3>
    <table>
        <tr>
            <th>Название</th>
            <th>Пользователь</th>
            <th>Описание</th>
            <th>Приоритет</th>
            <th>Дедлайн</th>
            <th>Статус</th>
            <th>Создано</th>
        </tr>
        <?php foreach ($tasks as $task): ?>
            <tr>
                <td><?= htmlspecialchars($task['title']) ?></td>
                <td><?= htmlspecialchars($task['username']) ?></td>
                <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
                <td><?= htmlspecialchars($task['priority']) ?></td>
                <td><?= htmlspecialchars($task['due_date']) ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td><?= htmlspecialchars($task['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
