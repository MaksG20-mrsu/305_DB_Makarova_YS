<?php

define('DB_FILE', __DIR__ . '/students.db');

function getDatabaseConnection(): PDO
{
    try {
        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO:: ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        throw new PDOException("Database connection failed: " . $e->getMessage());
    }
}

function initializeDatabase(): void
{
    $pdo = getDatabaseConnection();
    
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            number TEXT NOT NULL UNIQUE,
            program TEXT NOT NULL,
            graduation_year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            gender TEXT NOT NULL CHECK(gender IN ('М', 'Ж')),
            birth_date DATE NOT NULL,
            student_card_number TEXT NOT NULL UNIQUE,
            group_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
        )
    ");
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM groups");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        
        $pdo->exec("
            DROP TABLE IF EXISTS students;
            DROP TABLE IF EXISTS groups;
        ");
        
        $pdo->exec("
            INSERT INTO groups (number, program, graduation_year) VALUES
            ('1', 'Программная инженерия', 2025),
            ('2', 'Программная инженерия', 2026)
        ");
        
        $pdo->exec("
            INSERT INTO students (full_name, gender, birth_date, student_card_number, group_id) VALUES
            ('Зубков Роман Сергеевич', 'М', '2005-09-20', '31', 1),
            ('Иванов Максим Александрович', 'М', '2005-11-09', '12', 1),
            ('Ивенин Артём Андреевич', 'М', '2005-12-25', '67', 1),
            ('Казейкин Иван Иванович', 'М', '2006-03-12', '90', 2),
            ('Колыганов Александр Павлович', 'М', '2005-04-18', '34', 2),
            ('Кочнев Артем Алексеевич', 'М', '2005-05-29', '89', 1),
            ('Логунов Илья Сергеевич', 'М', '2005-02-01', '56', 1),
            ('Макарова Юлия Сергеевна', 'Ж', '2005-05-07', '23', 1),
            ('Маклаков Сергей Александрович', 'М', '2005-03-13', '11', 2),
            ('Маскинскова Наталья Сергеевна', 'Ж', '2005-10-28', '61', 1),
            ('Мукасеев Дмитрий Александрович', 'М', '2005-11-19', '55', 1),
            ('Наумкин Владислав Валерьевич', 'М', '2005-12-12', '67', 1),
            ('Паркаев Василий Александрович', 'М', '2005-10-16', '19', 2),
            ('Полковников Дмитрий Александрович', 'М', '2006-01-10', '27', 2),
            ('Пузаков Дмитрий Александрович', 'М', '2005-07-20', '20', 2),
            ('Пшеницына Полина Алексеевна', 'Ж', '2005-09-23', '63', 2),
            ('Пяткин Игорь Алексеевич', 'М', '2005-02-09', '33', 2),
            ('Рыбаков Евгений Геннадьевич', 'М', '2005-03-03', '37', 1),
            ('Рыжкин Владислав Дмитриевич', 'М', '2005-08-07', '83', 2),
            ('Рябченко Александра Станиславовна', 'Ж', '2005-09-30', '15', 1),
            ('Снегирев Данил Александрович', 'М', '2005-11-26', '99', 2),
            ('Тульсков Илья Андреевич', 'М', '2005-10-20', '48', 2),
            ('Фирстов Артём Александрович', 'М', '2005-12-10', '49', 2),
            ('Четайкин Владислав Александрович', 'М', '2005-08-26', '51', 2),
            ('Шарунов Максим Игоревич', 'М', '2005-04-24', '21', 2),
            ('Шушев Денис Сергеевич', 'М', '2005-08-15', '78', 1)
        ");
    }
}

function getActiveGroups(PDO $pdo): array
{
    $currentYear = (int)date('Y');
    
    $sql = "SELECT DISTINCT g.number 
            FROM groups g
            INNER JOIN students s ON s.group_id = g.id
            WHERE g.graduation_year >= : current_year
            ORDER BY CAST(g.number AS INTEGER)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['current_year' => $currentYear]);
    
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getStudents(PDO $pdo, ? string $groupNumber = null): array
{
    $currentYear = (int)date('Y');
    
    $sql = "SELECT 
                g.number AS group_number,
                g. program,
                s.full_name,
                s.gender,
                strftime('%d.%m.%Y', s.birth_date) AS birth_date,
                s.student_card_number
            FROM students s
            INNER JOIN groups g ON s.group_id = g.id
            WHERE g.graduation_year >= :current_year";
    
    $params = ['current_year' => $currentYear];
    
    if ($groupNumber !== null) {
        $sql .= " AND g.number = : group_number";
        $params['group_number'] = $groupNumber;
    }
    
    $sql .= " ORDER BY CAST(g.number AS INTEGER), s.full_name";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function getLastName(string $fullName): string
{
    $parts = explode(' ', trim($fullName));
    return $parts[0] ?? '';
}

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    try {
        initializeDatabase();
        echo "Database initialized successfully!\n";
        echo "Database file: " . DB_FILE . "\n";
        
        $pdo = getDatabaseConnection();
        $groupsCount = $pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn();
        $studentsCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
        
        echo "\nStatistics:\n";
        echo "- Groups: $groupsCount\n";
        echo "- Students: $studentsCount\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}