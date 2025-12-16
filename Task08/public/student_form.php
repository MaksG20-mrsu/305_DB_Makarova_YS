<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions. php';

$pdo = getDbConnection();

$id = $_GET['id'] ??  null;
$isEdit = $id !== null;

$student = null;
$errors = [];

$stmt = $pdo->query("SELECT id, number, program, graduation_year FROM groups ORDER BY CAST(number AS INTEGER)");
$groups = $stmt->fetchAll();

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        setFlashMessage('error', 'Студент не найден');
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $birthDate = $_POST['birth_date'] ?? '';
    $studentCardNumber = trim($_POST['student_card_number'] ?? '');
    $groupId = $_POST['group_id'] ?? '';
    
    // Validation
    if (empty($fullName)) {
        $errors[] = 'ФИО обязательно для заполнения';
    }
    
    if (! in_array($gender, ['М', 'Ж'])) {
        $errors[] = 'Укажите пол';
    }
    
    if (empty($birthDate)) {
        $errors[] = 'Дата рождения обязательна';
    }
    
    if (empty($studentCardNumber)) {
        $errors[] = 'Номер студенческого билета обязателен';
    }
    
    if (empty($groupId)) {
        $errors[] = 'Выберите группу';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE student_card_number = ?  AND id != ?");
        $stmt->execute([$studentCardNumber, $id ??  0]);
        if ($stmt->fetch()) {
            $errors[] = 'Студент с таким номером билета уже существует';
        }
    }
    
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE students 
                    SET full_name = ?, gender = ?, birth_date = ?, 
                        student_card_number = ?, group_id = ? 
                    WHERE id = ? 
                ");
                $stmt->execute([$fullName, $gender, $birthDate, $studentCardNumber, $groupId, $id]);
                setFlashMessage('success', 'Студент успешно обновлён');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO students (full_name, gender, birth_date, student_card_number, group_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$fullName, $gender, $birthDate, $studentCardNumber, $groupId]);
                setFlashMessage('success', 'Студент успешно добавлен');
            }
            
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

$formData = $_POST ??  $student ??  [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактирование' : 'Добавление' ?> студента</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><? = $isEdit ? 'Редактирование студента' : '➕ Добавление студента' ?></h1>
            <p class="subtitle"><a href="index.php">← Вернуться к списку</a></p>
        </header>

        <? php if (! empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Ошибки: </strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label for="full_name">ФИО <span class="required">*</span></label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       value="<?= e($formData['full_name'] ?? '') ?>" 
                       required
                       placeholder="Иванов Иван Иванович">
            </div>

            <div class="form-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" 
                               name="gender" 
                               value="М" 
                               <?= ($formData['gender'] ?? '') === 'М' ? 'checked' : '' ?> 
                               required>
                        Мужской
                    </label>
                    <label class="radio-label">
                        <input type="radio" 
                               name="gender" 
                               value="Ж" 
                               <?= ($formData['gender'] ?? '') === 'Ж' ? 'checked' : '' ?> 
                               required>
                        Женский
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="birth_date">Дата рождения <span class="required">*</span></label>
                <input type="date" 
                       id="birth_date" 
                       name="birth_date" 
                       value="<?= e($formData['birth_date'] ?? '') ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="student_card_number">Номер студенческого билета <span class="required">*</span></label>
                <input type="text" 
                       id="student_card_number" 
                       name="student_card_number" 
                       value="<?= e($formData['student_card_number'] ?? '') ?>" 
                       required
                       placeholder="12345">
            </div>

            <div class="form-group">
                <label for="group_id">Группа <span class="required">*</span></label>
                <select id="group_id" name="group_id" required>
                    <option value="">-- Выберите группу --</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= $group['id'] ?>" 
                                <?= ($formData['group_id'] ?? '') == $group['id'] ? 'selected' : '' ?>>
                            Группа <?= e($group['number']) ?> - <?= e($group['program']) ? > 
                            (выпуск <?= e($group['graduation_year']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <?= $isEdit ? 'Сохранить изменения' : '➕ Добавить студента' ?>
                </button>
                <a href="index.php" class="btn btn-secondary btn-lg">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>