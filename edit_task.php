<?php
session_start();
require_once 'db.php';

// Проверка авторизации
require_once 'functions.php';
checkAuth();


if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Получаем задачу
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $task_id, 'user_id' => $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("Задача не найдена или нет доступа.");
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    if (empty($title) || empty($description) || empty($priority) || empty($due_date) || empty($status)) {
        $errors[] = "Все поля обязательны для заполнения.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE tasks SET title = :title, description = :description, priority = :priority, due_date = :due_date, status = :status WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due_date' => $due_date,
            'status' => $status,
            'id' => $task_id,
            'user_id' => $user_id
        ]);
        $success = "Задача успешно обновлена!";
        // Обновляем $task для отображения новых значений
        $task['title'] = $title;
        $task['description'] = $description;
        $task['priority'] = $priority;
        $task['due_date'] = $due_date;
        $task['status'] = $status;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать задачу</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <h2>Редактировать задачу</h2>

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
        <div style="color: green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label>Название задачи: <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required></label><br><br>
        
        <label>Описание задачи:<br>
            <textarea name="description" rows="5" cols="40" required><?= htmlspecialchars($task['description']) ?></textarea>
        </label><br><br>
        
        <label>Приоритет:
            <select name="priority" required>
                <option value="Low" <?= $task['priority'] == 'Low' ? 'selected' : '' ?>>Низкий</option>
                <option value="Medium" <?= $task['priority'] == 'Medium' ? 'selected' : '' ?>>Средний</option>
                <option value="High" <?= $task['priority'] == 'High' ? 'selected' : '' ?>>Высокий</option>
            </select>
        </label><br><br>
        
        <label>Дедлайн: <input type="date" name="due_date" value="<?= htmlspecialchars($task['due_date']) ?>" required></label><br><br>
        
        <label>Статус:
            <select name="status" required>
                <option value="Pending" <?= $task['status'] == 'Pending' ? 'selected' : '' ?>>В ожидании</option>
                <option value="Completed" <?= $task['status'] == 'Completed' ? 'selected' : '' ?>>Выполнено</option>
            </select>
        </label><br><br>
        
        <button type="submit">Сохранить изменения</button>
    </form>

    <p><a href="dashboard.php">Назад</a></p>
</body>
</html>
