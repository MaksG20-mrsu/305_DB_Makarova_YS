<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions.php';

$pdo = getDbConnection();

$studentId = $_GET['student_id'] ?? null;

if (!$studentId) {
    setFlashMessage('error', 'ID студента не указан');
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT s.*, g.number AS group_number, g.program, g.graduation_year
    FROM students s
    INNER JOIN groups g ON s.group_id = g.id
    WHERE s.id = ? 
");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    setFlashMessage('error', 'Студент не найден');
    redirect('index.php');
}

$stmt = $pdo->prepare("
    SELECT 
        er.id,
        er.exam_date,
        er.grade,
        d.name AS discipline_name,
        d.course_year,
        strftime('%d. %m.%Y', er. exam_date) AS formatted_date
    FROM exam_results er
    INNER JOIN disciplines d ON er.discipline_id = d.id
    WHERE er.student_id = ?
    ORDER BY er.exam_date DESC
");
$stmt->execute([$studentId]);
$examResults = $stmt->fetchAll();

$currentCourse = calculateCourseYear($student['graduation_year']);

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты экзаменов</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Результаты экзаменов</h1>
            <p class="subtitle"><a href="index.php">← Вернуться к списку студентов</a></p>
        </header>

        <? php if ($flash): ?>
            <div class="alert alert-<? = e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="card student-info">
            <h2>Информация о студенте</h2>
            <table class="info-table">
                <tr>
                    <th>ФИО:</th>
                    <td><?= e($student['full_name']) ?></td>
                </tr>
                <tr>
                    <th>Группа:</th>
                    <td><?= e($student['group_number']) ?></td>
                </tr>
                <tr>
                    <th>Направление:</th>
                    <td><?= e($student['program']) ?></td>
                </tr>
                <tr>
                    <th>Год выпуска:</th>
                    <td><?= e($student['graduation_year']) ?></td>
                </tr>
                <tr>
                    <th>Текущий курс:</th>
                    <td><strong><?= $currentCourse ?> курс</strong></td>
                </tr>
            </table>
        </div>

        <div class="section-header">
            <h2>Результаты экзаменов</h2>
            <a href="exam_form.php? student_id=<?= $studentId ?>" class="btn btn-success">
                Добавить результат экзамена
            </a>
        </div>

        <div class="table-wrapper">
            <? php if (empty($examResults)): ?>
                <p class="no-data">У студента пока нет результатов экзаменов</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Дата экзамена</th>
                            <th>Дисциплина</th>
                            <th>Курс</th>
                            <th>Оценка</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($examResults as $result): ?>
                            <tr>
                                <td class="text-center"><?= e($result['formatted_date']) ?></td>
                                <td><?= e($result['discipline_name']) ?></td>
                                <td class="text-center"><?= e($result['course_year']) ?> курс</td>
                                <td class="text-center">
                                    <span class="grade grade-<?= $result['grade'] ?>">
                                        <?= e($result['grade']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="exam_form.php?id=<?= $result['id'] ?>&student_id=<?= $studentId ?>" 
                                       class="btn btn-sm btn-primary" title="Редактировать">
                                        Редактировать
                                    </a>
                                    <a href="exam_delete.php?id=<?= $result['id'] ?>&student_id=<?= $studentId ?>" 
                                       class="btn btn-sm btn-danger" title="Удалить">
                                        Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php
                $totalExams = count($examResults);
                $avgGrade = array_sum(array_column($examResults, 'grade')) / $totalExams;
                $excellentCount = count(array_filter($examResults, fn($r) => $r['grade'] == 5));
                $goodCount = count(array_filter($examResults, fn($r) => $r['grade'] == 4));
                ?>

                <div class="statistics">
                    <h3>Статистика</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Всего экзаменов:</span>
                            <span class="stat-value"><?= $totalExams ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Средний балл:</span>
                            <span class="stat-value"><?= number_format($avgGrade, 2) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Отличных оценок:</span>
                            <span class="stat-value"><?= $excellentCount ? ></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Хороших оценок:</span>
                            <span class="stat-value"><?= $goodCount ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>