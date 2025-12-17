<?php

require_once 'db.php';

$activeGroups = [];
$students = [];
$selectedGroup = null;
$error = null;
$currentYear = date('Y');

try {
    initializeDatabase();
    
    $pdo = getDatabaseConnection();

    $activeGroups = getActiveGroups($pdo);

    if (isset($_GET['group']) && $_GET['group'] !== '') {
        $selectedGroup = $_GET['group'];

        if (!in_array($selectedGroup, $activeGroups, true)) {
            $error = "Неверный номер группы";
            $selectedGroup = null;
        }
    }

    $students = getStudents($pdo, $selectedGroup);
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $error = "Ошибка: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список студентов действующих групп</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 30px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }

        header h1 {
            color:  #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 1.1em;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-info {
            background-color: #e3f2fd;
            color:  #1976d2;
            border-left: 4px solid #1976d2;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius:  8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-form label {
            font-weight:  600;
            color: #555;
            font-size: 1.05em;
        }

        . filter-form select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        . filter-form select:hover {
            border-color: #667eea;
        }

        . filter-form select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-form button,
        .btn-reset {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border:  none;
            border-radius:  6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition:  all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .filter-form button:hover,
        .btn-reset:hover {
            background:  #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-reset {
            background: #6c757d;
        }

        .btn-reset:hover {
            background: #5a6268;
        }

        . results-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f7ff;
            border-radius:  6px;
            border-left: 4px solid #667eea;
        }

        .results-info p {
            margin: 5px 0;
            font-size: 1.05em;
        }

        .results-info strong {
            color: #667eea;
            font-weight:  700;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        . student-table {
            width: 100%;
            border-collapse:  collapse;
            background: white;
        }

        .student-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .student-table th {
            padding: 15px 12px;
            text-align:  left;
            font-weight:  600;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .student-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: all 0.2s ease;
        }

        .student-table tbody tr:hover {
            background-color: #f5f7ff;
        }

        .student-table tbody tr:last-child {
            border-bottom: none;
        }

        .student-table td {
            padding: 12px;
            font-size: 0.95em;
        }

        .group-number {
            font-weight: 700;
            color: #667eea;
            text-align: center;
        }

        .program {
            color: #555;
        }

        .full-name {
            font-weight: 600;
            color: #333;
        }

        .gender {
            text-align: center;
            font-weight: 600;
        }

        .birth-date {
            color: #666;
            white-space: nowrap;
        }

        .card-number {
            font-family: 'Courier New', monospace;
            color: #764ba2;
            font-weight: 600;
            text-align: center;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            padding-top:  20px;
            border-top: 2px solid #e0e0e0;
            color: #666;
        }

        @media (max-width:  768px) {
            .container {
                padding: 15px;
            }
            
            header h1 {
                font-size: 1.8em;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form select,
            .filter-form button,
            .btn-reset {
                width: 100%;
            }
            
            .student-table {
                font-size: 0.85em;
            }
            
            .student-table th,
            .student-table td {
                padding: 8px 6px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Список студентов действующих групп</h1>
            <p class="subtitle">Год окончания обучения: <?= $currentYear ?> и позже</p>
        </header>
        
        <? php if ($error !== null): ?>
            <div class="alert alert-error">
                <? = $error ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-section">
            <form method="get" action="" class="filter-form">
                <label for="group">Фильтр по группе:</label>
                <select name="group" id="group" onchange="this.form.submit()">
                    <option value="">Все группы</option>
                    <? php foreach ($activeGroups as $group): ?>
                        <option value="<?= htmlspecialchars($group) ?>" 
                                <?= ($selectedGroup === $group) ? 'selected' : '' ?>>
                            Группа <?= htmlspecialchars($group) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Применить</button>
                <? php if ($selectedGroup !== null): ?>
                    <a href="?" class="btn-reset">Сбросить фильтр</a>
                <?php endif; ?>
            </form>
        </div>
        
        <? php if (empty($students)): ?>
            <div class="alert alert-info">
                Нет данных для отображения. 
            </div>
        <? php else: ?>
            <div class="results-info">
                <? php if ($selectedGroup !== null): ?>
                    <p>Показаны студенты группы: <strong><?= htmlspecialchars($selectedGroup) ?></strong></p>
                <?php else: ?>
                    <p>Показаны студенты всех действующих групп</p>
                <?php endif; ?>
                <p>Всего студентов: <strong><?= count($students) ?></strong></p>
            </div>
            
            <div class="table-wrapper">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Группа</th>
                            <th>Направление подготовки</th>
                            <th>ФИО</th>
                            <th>Пол</th>
                            <th>Дата рождения</th>
                            <th>Студенческий билет</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="group-number">
                                    <? = htmlspecialchars($student['group_number']) ?>
                                </td>
                                <td class="program">
                                    <? = htmlspecialchars($student['program']) ?>
                                </td>
                                <td class="full-name">
                                    <?= htmlspecialchars($student['full_name']) ?>
                                </td>
                                <td class="gender">
                                    <?= htmlspecialchars($student['gender']) ?>
                                </td>
                                <td class="birth-date">
                                    <?= htmlspecialchars($student['birth_date']) ?>
                                </td>
                                <td class="card-number">
                                    <?= htmlspecialchars($student['student_card_number']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <footer>
            <p>&copy; <? = date('Y') ?> Система управления студентами</p>
        </footer>
    </div>
</body>
</html>