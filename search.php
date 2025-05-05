<?php
session_start();
require_once 'db.php';

// Проверка авторизации
// Проверка авторизации
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$search_title = '';
$search_priority = '';
$search_status = '';
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['search']) || isset($_GET['priority']) || isset($_GET['status']))) {
    $search_title = trim($_GET['search']);
    $search_priority = $_GET['priority'];
    $search_status = $_GET['status'];

    $query = "SELECT * FROM tasks WHERE user_id = :user_id";
    $params = ['user_id' => $_SESSION['user_id']];

    if (!empty($search_title)) {
        $query .= " AND title LIKE :title";
        $params['title'] = '%' . $search_title . '%';
    }
    if (!empty($search_priority)) {
        $query .= " AND priority = :priority";
        $params['priority'] = $search_priority;
    }
    if (!empty($search_status)) {
        $query .= " AND status = :status";
        $params['status'] = $search_status;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск задач</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <h2>Поиск задач</h2>

    <form method="GET">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label>Название: <input type="text" name="search" value="<?= htmlspecialchars($search_title) ?>"></label><br><br>

        <label>Приоритет:
            <select name="priority">
                <option value="">--Любой--</option>
                <option value="Low" <?= $search_priority === 'Low' ? 'selected' : '' ?>>Низкий</option>
                <option value="Medium" <?= $search_priority === 'Medium' ? 'selected' : '' ?>>Средний</option>
                <option value="High" <?= $search_priority === 'High' ? 'selected' : '' ?>>Высокий</option>
            </select>
        </label><br><br>

        <label>Статус:
            <select name="status">
                <option value="">--Любой--</option>
                <option value="Pending" <?= $search_status === 'Pending' ? 'selected' : '' ?>>В ожидании</option>
                <option value="Completed" <?= $search_status === 'Completed' ? 'selected' : '' ?>>Выполнено</option>
            </select>
        </label><br><br>

        <button type="submit">🔍 Найти</button>
    </form>

    <p><a href="dashboard.php">Назад в панель</a></p>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
        <h3>Результаты поиска:</h3>

        <?php if (empty($results)): ?>
            <p>Ничего не найдено.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Приоритет</th>
                    <th>Дедлайн</th>
                    <th>Статус</th>
                    <th>Создано</th>
                </tr>
                <?php foreach ($results as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
                        <td><?= htmlspecialchars($task['priority']) ?></td>
                        <td><?= htmlspecialchars($task['due_date']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td><?= htmlspecialchars($task['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
