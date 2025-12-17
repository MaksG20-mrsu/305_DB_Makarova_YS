<?php

define('DB_FILE', __DIR__ . '/data/students. db');

if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS disciplines (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            program TEXT NOT NULL,
            course_year INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS exam_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id INTEGER NOT NULL,
            discipline_id INTEGER NOT NULL,
            exam_date DATE NOT NULL,
            grade INTEGER NOT NULL CHECK(grade >= 2 AND grade <= 5),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE
        )
    ");
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM groups");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
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
        
        $pdo->exec("
            INSERT INTO disciplines (name, program, course_year) VALUES
            -- 1 курс
            ('Математический анализ', 'Программная инженерия', 1),
            ('Основы программирования', 'Программная инженерия', 1),
            ('Алгоритмы и структуры данных', 'Программная инженерия', 1),
            ('Дискретная математика', 'Программная инженерия', 1),
            ('Английский язык', 'Программная инженерия', 1),
            -- 2 курс
            ('Объектно-ориентированное программирование', 'Программная инженерия', 2),
            ('Базы данных', 'Программная инженерия', 2),
            ('Операционные системы', 'Программная инженерия', 2),
            ('Теория вероятностей', 'Программная инженерия', 2),
            ('Компьютерные сети', 'Программная инженерия', 2),
            -- 3 курс
            ('Проектирование программных систем', 'Программная инженерия', 3),
            ('Веб-технологии', 'Программная инженерия', 3),
            ('Тестирование программного обеспечения', 'Программная инженерия', 3),
            ('Управление проектами', 'Программная инженерия', 3),
            ('Искусственный интеллект', 'Программная инженерия', 3),
            -- 4 курс
            ('Архитектура программных систем', 'Программная инженерия', 4),
            ('Машинное обучение', 'Программная инженерия', 4),
            ('Безопасность информационных систем', 'Программная инженерия', 4),
            ('Распределенные системы', 'Программная инженерия', 4)
        ");
        
        $pdo->exec("
            INSERT INTO exam_results (student_id, discipline_id, exam_date, grade) VALUES
            -- Иванов Максим (id=2, группа 1, выпуск 2025 - сейчас 4 курс)
            (2, 1, '2022-01-15', 5),
            (2, 2, '2022-01-20', 5),
            (2, 3, '2022-06-10', 4),
            (2, 6, '2023-01-18', 5),
            (2, 7, '2023-06-15', 5),
            (2, 11, '2024-01-20', 4),
            (2, 12, '2024-06-18', 5),
            -- Макарова Юлия (id=8, группа 1)
            (8, 1, '2022-01-15', 4),
            (8, 2, '2022-01-20', 5),
            (8, 4, '2022-06-12', 5),
            (8, 6, '2023-01-18', 4),
            (8, 7, '2023-06-15', 5),
            -- Казейкин Иван (id=4, группа 2, выпуск 2026 - сейчас 3 курс)
            (4, 1, '2023-01-15', 4),
            (4, 2, '2023-01-20', 4),
            (4, 3, '2023-06-10', 5),
            (4, 6, '2024-01-18', 5),
            (4, 7, '2024-06-15', 4),
            -- Пшеницына Полина (id=16, группа 2)
            (16, 1, '2023-01-15', 5),
            (16, 2, '2023-01-20', 5),
            (16, 4, '2023-06-12', 5)
        ");
    }
    
    echo "База данных успешно инициализирована!\n";
    echo "Файл базы данных: " .  DB_FILE . "\n\n";
    
    $groupsCount = $pdo->query("SELECT COUNT(*) FROM groups")->fetchColumn();
    $studentsCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $disciplinesCount = $pdo->query("SELECT COUNT(*) FROM disciplines")->fetchColumn();
    $examsCount = $pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn();
    
    echo "Статистика:\n";
    echo "   - Групп: $groupsCount\n";
    echo "   - Студентов: $studentsCount\n";
    echo "   - Дисциплин: $disciplinesCount\n";
    echo "   - Результатов экзаменов:  $examsCount\n";
    
} catch (Exception $e) {
    echo "Ошибка:  " . $e->getMessage() . "\n";
    exit(1);
}