<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions.php';

$pdo = getDbConnection();

$id = $_GET['id'] ?? null;

if (! $id) {
    setFlashMessage('error', 'ID студента не указан');
    redirect('index. php');
}

$stmt = $pdo->prepare("
    SELECT s.*, g.number AS group_number 
    FROM students s 
    INNER JOIN groups g ON s. group_id = g.id
    WHERE s.id = ? 
");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('error', 'Студент не найден');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Студент успешно удалён');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление студента</title>
    <link rel="stylesheet" href="styles. css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Удаление студента</h1>
            <p class="subtitle"><a href="index.php">← Вернуться к списку</a></p>
        </header>

        <div class="alert alert-warning">
            <strong>Внимание! </strong> Вы действительно хотите удалить студента?
        </div>

        <div class="card">
            <table class="info-table">
                <tr>
                    <th>ФИО: </th>
                    <td><? = e($student['full_name']) ?></td>
                </tr>
                <tr>
                    <th>Группа:</th>
                    <td><?= e($student['group_number']) ?></td>
                </tr>
                <tr>
                    <th>Пол:</th>
                    <td><?= e($student['gender']) ?></td>
                </tr>
                <tr>
                    <th>Дата рождения:</th>
                    <td><?= e($student['birth_date']) ?></td>
                </tr>
                <tr>
                    <th>Студ. билет:</th>
                    <td><?= e($student['student_card_number']) ?></td>
                </tr>
            </table>
        </div>

        <form method="post" class="form">
            <div class="form-actions">
                <button type="submit" name="confirm" value="1" class="btn btn-danger btn-lg">
                    Да, удалить
                </button>
                <a href="index.php" class="btn btn-secondary btn-lg">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>