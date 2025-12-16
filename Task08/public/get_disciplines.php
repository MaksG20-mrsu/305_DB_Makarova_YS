<?php
require_once '../src/db.php';
require_once '../src/functions.php';

header('Content-Type: application/json');

$studentId = $_GET['student_id'] ?? null;
$examDate = $_GET['exam_date'] ?? null;

if (!$studentId || !$examDate) {
    echo json_encode([]);
    exit;
}

$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT g.program, g.graduation_year
    FROM students s
    INNER JOIN groups g ON s.group_id = g.id
    WHERE s.id = ?
");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    echo json_encode([]);
    exit;
}

$courseYear = calculateCourseYearAtDate($student['graduation_year'], $examDate);

$stmt = $pdo->prepare("
    SELECT id, name, course_year
    FROM disciplines
    WHERE program = ? AND course_year <= ?
    ORDER BY course_year, name
");
$stmt->execute([$student['program'], $courseYear]);
$disciplines = $stmt->fetchAll();

echo json_encode($disciplines);