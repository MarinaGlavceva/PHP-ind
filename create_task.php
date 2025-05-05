<?php
session_start();
require_once 'db.php';

// Проверка авторизации
require_once 'functions.php';
checkAuth();


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = '';

// Папка для загрузки файлов
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    // Проверка заполненности полей
    if (empty($title) || empty($description) || empty($priority) || empty($due_date) || empty($status)) {
        $errors[] = "Все поля обязательны для заполнения.";
    }

    // Проверка загрузки файла (по желанию)
    $uploadedFile = null;
    if (!empty($_FILES['file']['name'])) {
        $fileName = basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Ограничения типов файлов (пример: pdf, jpg, png)
        $allowedTypes = ['pdf', 'jpg', 'png', 'jpeg'];
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Разрешены только файлы: pdf, jpg, png, jpeg.";
        } else {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $uploadedFile = $fileName;
            } else {
                $errors[] = "Не удалось загрузить файл.";
            }
        }
    }

    if (empty($errors)) {
        // Вставка задачи в базу
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date, status) 
                               VALUES (:user_id, :title, :description, :priority, :due_date, :status)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due_date' => $due_date,
            'status' => $status
        ]);

        $success = "Задача успешно создана!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создать задачу</title>
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <h2>Создание новой задачи</h2>

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

    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <label>Название задачи: <input type="text" name="title" required></label><br><br>
        
        <label>Описание задачи:<br>
            <textarea name="description" rows="5" cols="40" required></textarea>
        </label><br><br>
        
        <label>Приоритет:
            <select name="priority" required>
                <option value="Low">Низкий</option>
                <option value="Medium" selected>Средний</option>
                <option value="High">Высокий</option>
            </select>
        </label><br><br>
        
        <label>Дедлайн: <input type="date" name="due_date" required></label><br><br>
        
        <label>Статус:
            <select name="status" required>
                <option value="Pending" selected>В ожидании</option>
                <option value="Completed">Выполнено</option>
            </select>
        </label><br><br>
        
        <label>Прикрепить файл (pdf/jpg/png): <input type="file" name="file"></label><br><br>
        
        <button type="submit">Создать задачу</button>
    </form>

    <p><a href="dashboard.php">Назад в панель</a></p>
</body>
</html>
