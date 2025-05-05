<?php
session_start();
require_once 'db.php';


require_once 'functions.php';
checkAuth();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Удаляем задачу, проверяя владельца
$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
$stmt->execute(['id' => $task_id, 'user_id' => $user_id]);

header('Location: dashboard.php');
exit;
