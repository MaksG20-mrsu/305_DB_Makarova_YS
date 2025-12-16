<?php

require_once 'db.php';

function displayTable(array $data): void
{
    if (empty($data)) {
        echo "Нет данных для отображения.\n";
        return;
    }
    
    $headers = [
        'Группа',
        'Направление подготовки',
        'ФИО',
        'Пол',
        'Дата рождения',
        'Студ. билет'
    ];
    
    $widths = array_map('mb_strlen', $headers);
    
    foreach ($data as $row) {
        $values = array_values($row);
        foreach ($values as $index => $value) {
            $widths[$index] = max($widths[$index], mb_strlen($value ??  ''));
        }
    }
    
    echo "┌";
    foreach ($widths as $index => $width) {
        echo str_repeat("─", $width + 2);
        echo ($index < count($widths) - 1) ? "┬" : "┐";
    }
    echo "\n";
    
    echo "│";
    foreach ($headers as $index => $header) {
        echo " " . mb_str_pad($header, $widths[$index]) . " │";
    }
    echo "\n";
    
    echo "├";
    foreach ($widths as $index => $width) {
        echo str_repeat("─", $width + 2);
        echo ($index < count($widths) - 1) ? "┼" : "┤";
    }
    echo "\n";
    
    foreach ($data as $row) {
        echo "│";
        $values = array_values($row);
        foreach ($values as $index => $value) {
            echo " " . mb_str_pad($value ??  '', $widths[$index]) . " │";
        }
        echo "\n";
    }
    
    echo "└";
    foreach ($widths as $index => $width) {
        echo str_repeat("─", $width + 2);
        echo ($index < count($widths) - 1) ? "┴" : "┘";
    }
    echo "\n";
}

function mb_str_pad(string $str, int $length): string
{
    $diff = $length - mb_strlen($str);
    if ($diff <= 0) {
        return $str;
    }
    return $str . str_repeat(' ', $diff);
}

function readInput(string $prompt): string
{
    echo $prompt;
    $input = fgets(STDIN);
    return $input !== false ? trim($input) : '';
}

function isValidGroupNumber(string $input, array $validGroups): bool
{
    return empty($input) || in_array($input, $validGroups, true);
}

try {
    initializeDatabase();
    
    $pdo = getDatabaseConnection();
    
    echo "   Список студентов действующих групп\n";
    
    $activeGroups = getActiveGroups($pdo);
    
    if (empty($activeGroups)) {
        echo "В базе данных нет действующих групп.\n";
        exit(0);
    }
    
    echo "Действующие группы:  " . implode(", ", $activeGroups) . "\n\n";
    
    $selectedGroup = null;
    $maxAttempts = 3;
    $attempt = 0;
    
    while ($attempt < $maxAttempts) {
        $input = readInput("Введите номер группы для фильтрации (или нажмите Enter для вывода всех): ");
        
        if (isValidGroupNumber($input, $activeGroups)) {
            $selectedGroup = empty($input) ? null : $input;
            break;
        }
        
        $attempt++;
        echo "Ошибка:  Неверный номер группы. ";
        
        if ($attempt < $maxAttempts) {
            echo "Попробуйте еще раз.\n";
        } else {
            echo "Превышено количество попыток.  Будут выведены все группы.\n";
            $selectedGroup = null;
        }
    }
    
    echo "\n";
    
    $students = getStudents($pdo, $selectedGroup);
    
    if ($selectedGroup !== null) {
        echo "Студенты группы:  $selectedGroup\n\n";
    } else {
        echo "Студенты всех действующих групп\n\n";
    }
    
    displayTable($students);
    
    echo "\n";
    echo "Всего студентов: " . count($students) . "\n";
    
} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}