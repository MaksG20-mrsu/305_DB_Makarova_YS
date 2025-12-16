<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions.php';

$pdo = getDbConnection();

$id = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;

if (!$id) {
    setFlashMessage('error', 'ID результата экзамена не указан');
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT er.*, d.name AS discipline_name, s.full_name
    FROM exam_results er
    INNER JOIN disciplines d ON er. discipline_id = d.id
    INNER JOIN students s ON er.student_id = s.id
    WHERE er.id = ? 
");
$stmt->execute([$id]);
$examResult = $stmt->fetch();

if (!$examResult) {
    setFlashMessage('error', 'Результат экзамена не найден');
    redirect('index.php');
}

$studentId = $studentId ?? $examResult['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM exam_results WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('success', 'Результат экзамена успешно удалён');
        } catch (PDOException $e) {
            setFlashMessage('error', 'Ошибка при удалении: ' . $e->getMessage());
        }
    }
    redirect('exam_results.php?student_id=' .  $studentId);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удаление результата экзамена</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Удаление результата экзамена</h1>
            <p class="subtitle">
                <a href="exam_results.php?student_id=<?= $studentId ?>">← Вернуться к результатам</a>
            </p>
        </header>

        <div class="alert alert-warning">
            <strong>Внимание! </strong> Вы действительно хотите удалить результат экзамена? 
        </div>

        <div class="card">
            <table class="info-table">
                <tr>
                    <th>Студент: </th>
                    <td><? = e($examResult['full_name']) ?></td>
                </tr>
                <tr>
                    <th>Дисциплина:</th>
                    <td><?= e($examResult['discipline_name']) ?></td>
                </tr>
                <tr>
                    <th>Дата экзамена:</th>
                    <td><?= e(date('d. m.Y', strtotime($examResult['exam_date']))) ?></td>
                </tr>
                <tr>
                    <th>Оценка:</th>
                    <td><span class="grade grade-<?= $examResult['grade'] ? >"><?= e($examResult['grade']) ?></span></td>
                </tr>
            </table>
        </div>

        <form method="post" class="form">
            <div class="form-actions">
                <button type="submit" name="confirm" value="1" class="btn btn-danger btn-lg">
                    Да, удалить
                </button>
                <a href="exam_results.php?student_id=<?= $studentId ?>" class="btn btn-secondary btn-lg">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</body>
</html>