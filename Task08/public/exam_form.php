<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions. php';

$pdo = getDbConnection();

$id = $_GET['id'] ?? null;
$studentId = $_GET['student_id'] ?? null;
$isEdit = $id !== null;

$examResult = null;
$errors = [];

if (! $studentId && !$isEdit) {
    setFlashMessage('error', 'ID студента не указан');
    redirect('index.php');
}

if ($studentId) {
    $stmt = $pdo->prepare("
        SELECT s.*, g.number AS group_number, g.program, g.graduation_year
        FROM students s
        INNER JOIN groups g ON s.group_id = g.id
        WHERE s. id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
    
    if (!$student) {
        setFlashMessage('error', 'Студент не найден');
        redirect('index.php');
    }
}

if ($isEdit) {
    $stmt = $pdo->prepare("
        SELECT er.*, s.id AS student_id, s.full_name, g.program, g.graduation_year
        FROM exam_results er
        INNER JOIN students s ON er.student_id = s.id
        INNER JOIN groups g ON s. group_id = g.id
        WHERE er.id = ? 
    ");
    $stmt->execute([$id]);
    $examResult = $stmt->fetch();
    
    if (!$examResult) {
        setFlashMessage('error', 'Результат экзамена не найден');
        redirect('index.php');
    }
    
    $studentId = $examResult['student_id'];
    $student = [
        'id' => $examResult['student_id'],
        'full_name' => $examResult['full_name'],
        'program' => $examResult['program'],
        'graduation_year' => $examResult['graduation_year']
    ];
}

$allStudents = [];
if (!$isEdit && ! $studentId) {
    $stmt = $pdo->query("
        SELECT s.id, s.full_name, g.number AS group_number, g.program, g.graduation_year
        FROM students s
        INNER JOIN groups g ON s.group_id = g.id
        ORDER BY s.full_name
    ");
    $allStudents = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedStudentId = $_POST['student_id'] ?? $studentId;
    $disciplineId = $_POST['discipline_id'] ?? '';
    $examDate = $_POST['exam_date'] ?? '';
    $grade = $_POST['grade'] ?? '';
    
    if (empty($selectedStudentId)) {
        $errors[] = 'Выберите студента';
    }
    
    if (empty($disciplineId)) {
        $errors[] = 'Выберите дисциплину';
    }
    
    if (empty($examDate)) {
        $errors[] = 'Укажите дату экзамена';
    }
    
    if (empty($grade) || ! in_array($grade, ['2', '3', '4', '5'])) {
        $errors[] = 'Выберите оценку (от 2 до 5)';
    }
    
    if (empty($errors) && strtotime($examDate) > time()) {
        $errors[] = 'Дата экзамена не может быть в будущем';
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE exam_results 
                    SET discipline_id = ?, exam_date = ?, grade = ?  
                    WHERE id = ? 
                ");
                $stmt->execute([$disciplineId, $examDate, $grade, $id]);
                setFlashMessage('success', 'Результат экзамена успешно обновлён');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO exam_results (student_id, discipline_id, exam_date, grade)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$selectedStudentId, $disciplineId, $examDate, $grade]);
                setFlashMessage('success', 'Результат экзамена успешно добавлен');
            }
            
            redirect('exam_results.php?student_id=' .  $selectedStudentId);
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

$formData = $_POST ??  $examResult ??  [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><? = $isEdit ? 'Редактирование' : 'Добавление' ?> результата экзамена</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function loadDisciplines() {
            const studentId = document.getElementById('student_id').value;
            const examDate = document.getElementById('exam_date').value;
            const disciplineSelect = document.getElementById('discipline_id');
            
            if (!studentId || !examDate) {
                return;
            }
            
            fetch(`get_disciplines.php?student_id=${studentId}&exam_date=${examDate}`)
                .then(response => response.json())
                .then(data => {
                    disciplineSelect.innerHTML = '<option value="">-- Выберите дисциплину --</option>';
                    data.forEach(discipline => {
                        const option = document.createElement('option');
                        option. value = discipline.id;
                        option.textContent = `${discipline.name} (${discipline.course_year} курс)`;
                        disciplineSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= $isEdit ? 'Редактирование результата экзамена' : 'Добавление результата экзамена' ?></h1>
            <p class="subtitle">
                <a href="<? = $studentId ? 'exam_results. php?student_id=' . $studentId : 'index.php' ?>">
                    ← Вернуться назад
                </a>
            </p>
        </header>

        <? php if (! empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <? php if ($studentId && isset($student)): ?>
            <div class="card student-info">
                <h3>Студент: <?= e($student['full_name']) ?></h3>
                <p>Направление: <?= e($student['program']) ?></p>
                <p>Год выпуска: <?= e($student['graduation_year']) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <? php if (! $studentId || !empty($allStudents)): ?>
                <div class="form-group">
                    <label for="student_id">Студент <span class="required">*</span></label>
                    <select id="student_id" name="student_id" required onchange="loadDisciplines()">
                        <option value="">-- Выберите студента --</option>
                        <?php foreach ($allStudents as $s): ?>
                            <option value="<?= $s['id'] ?>" 
                                    data-program="<?= e($s['program']) ?>"
                                    data-graduation="<?= e($s['graduation_year']) ?>"
                                    <?= ($formData['student_id'] ??  '') == $s['id'] ? 'selected' : '' ?>>
                                <?= e($s['full_name']) ?> (группа <?= e($s['group_number']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="exam_date">Дата экзамена <span class="required">*</span></label>
                <input type="date" 
                       id="exam_date" 
                       name="exam_date" 
                       value="<?= e($formData['exam_date'] ?? '') ?>" 
                       max="<?= date('Y-m-d') ?>"
                       required
                       onchange="loadDisciplines()">
                <small class="form-text">Дата не может быть в будущем</small>
            </div>

            <div class="form-group">
                <label for="discipline_id">Дисциплина <span class="required">*</span></label>
                <select id="discipline_id" name="discipline_id" required>
                    <option value="">-- Сначала выберите дату экзамена --</option>
                    <?php
                    if ($isEdit || !empty($formData['exam_date'])) {
                        $dateForDisciplines = $formData['exam_date'] ?? $examResult['exam_date'];
                        $studentForDisciplines = $student ??  null;
                        
                        if ($studentForDisciplines && $dateForDisciplines) {
                            $courseYear = calculateCourseYearAtDate(
                                $studentForDisciplines['graduation_year'], 
                                $dateForDisciplines
                            );
                            
                            $stmt = $pdo->prepare("
                                SELECT id, name, course_year
                                FROM disciplines
                                WHERE program = ?  AND course_year <= ?
                                ORDER BY course_year, name
                            ");
                            $stmt->execute([$studentForDisciplines['program'], $courseYear]);
                            $disciplines = $stmt->fetchAll();
                            
                            foreach ($disciplines as $discipline):
                    ?>
                                <option value="<?= $discipline['id'] ? >"
                                        <? = ($formData['discipline_id'] ?? '') == $discipline['id'] ? 'selected' : '' ?>>
                                    <?= e($discipline['name']) ?> (<?= e($discipline['course_year']) ? > курс)
                                </option>
                    <?php 
                            endforeach;
                        }
                    }
                    ?>
                </select>
                <small class="form-text">Доступны дисциплины, которые студент изучал на момент сдачи экзамена</small>
            </div>

            <div class="form-group">
                <label for="grade">Оценка <span class="required">*</span></label>
                <select id="grade" name="grade" required>
                    <option value="">-- Выберите оценку --</option>
                    <option value="5" <?= ($formData['grade'] ?? '') == '5' ? 'selected' : '' ?>>5 (Отлично)</option>
                    <option value="4" <?= ($formData['grade'] ?? '') == '4' ? 'selected' : '' ?>>4 (Хорошо)</option>
                    <option value="3" <?= ($formData['grade'] ?? '') == '3' ? 'selected' :  '' ?>>3 (Удовлетворительно)</option>
                    <option value="2" <?= ($formData['grade'] ?? '') == '2' ? 'selected' : '' ? >>2 (Неудовлетворительно)</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $isEdit ? 'Сохранить изменения' : '➕ Добавить результат' ?>
                </button>
                <a href="<?= $studentId ? 'exam_results. php?student_id=' . $studentId : 'index.php' ?>" 
                   class="btn btn-secondary btn-lg">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>