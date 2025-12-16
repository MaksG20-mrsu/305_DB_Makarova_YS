<?php
session_start();
require_once '../src/db.php';
require_once '../src/functions.php';

$pdo = getDbConnection();

$filterGroup = $_GET['group'] ?? '';

$stmt = $pdo->query("
    SELECT DISTINCT g.number 
    FROM groups g
    INNER JOIN students s ON s.group_id = g.id
    ORDER BY CAST(g.number AS INTEGER)
");
$groups = $stmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "
    SELECT 
        s.id,
        s.full_name,
        s.gender,
        strftime('%d.%m.%Y', s.birth_date) AS birth_date,
        s. student_card_number,
        g.number AS group_number,
        g.program,
        g.graduation_year
    FROM students s
    INNER JOIN groups g ON s.group_id = g.id
";

$params = [];
if ($filterGroup !== '') {
    $sql .= " WHERE g.number = :group_number";
    $params['group_number'] = $filterGroup;
}

$sql .= " ORDER BY CAST(g.number AS INTEGER), s.full_name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление студентами</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Управление студентами и экзаменами</h1>
            <p class="subtitle">Информация о составе групп и результатах экзаменов</p>
        </header>

        <? php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="filter-section">
            <form method="get" class="filter-form">
                <label for="group">Фильтр по группе:</label>
                <select name="group" id="group" onchange="this.form.submit()">
                    <option value="">Все группы</option>
                    <? php foreach ($groups as $group): ?>
                        <option value="<?= e($group) ?>" <? = $filterGroup === $group ?  'selected' : '' ?>>
                            Группа <?= e($group) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <? php if ($filterGroup): ?>
                    <a href="index.php" class="btn btn-secondary">Сбросить фильтр</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrapper">
            <? php if (empty($students)): ?>
                <p class="no-data">Нет данных для отображения</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Группа</th>
                            <th>ФИО</th>
                            <th>Пол</th>
                            <th>Дата рождения</th>
                            <th>Студ. билет</th>
                            <th>Направление</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="text-center"><strong><?= e($student['group_number']) ?></strong></td>
                                <td><? = e($student['full_name']) ?></td>
                                <td class="text-center"><?= e($student['gender']) ?></td>
                                <td class="text-center"><?= e($student['birth_date']) ?></td>
                                <td class="text-center"><code><?= e($student['student_card_number']) ?></code></td>
                                <td><? = e($student['program']) ?></td>
                                <td class="actions">
                                    <a href="exam_results.php?student_id=<?= $student['id'] ?>" 
                                       class="btn btn-sm btn-info" title="Результаты экзаменов">
                                        Экзамены
                                    </a>
                                    <a href="student_form.php?id=<?= $student['id'] ?>" 
                                       class="btn btn-sm btn-primary" title="Редактировать">
                                        Редактировать
                                    </a>
                                    <a href="student_delete.php?id=<? = $student['id'] ?>" 
                                       class="btn btn-sm btn-danger" title="Удалить">
                                        Удалить
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="bottom-actions">
            <a href="student_form.php" class="btn btn-success btn-lg">➕ Добавить студента</a>
        </div>

        <footer>
            <p>Всего студентов: <strong><?= count($students) ?></strong></p>
        </footer>
    </div>
</body>
</html>