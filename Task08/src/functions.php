<?php

function calculateCourseYear(int $graduationYear): int
{
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('m');
    
    if ($currentMonth >= 9) {
        $currentAcademicYear = $currentYear;
    } else {
        $currentAcademicYear = $currentYear - 1;
    }
    
    $courseYear = 4 - ($graduationYear - $currentAcademicYear - 1);
    
    return max(1, min(4, $courseYear));
}

function calculateCourseYearAtDate(int $graduationYear, string $date): int
{
    $dateObj = new DateTime($date);
    $year = (int)$dateObj->format('Y');
    $month = (int)$dateObj->format('m');
    
    if ($month >= 9) {
        $academicYear = $year;
    } else {
        $academicYear = $year - 1;
    }
    
    $courseYear = 4 - ($graduationYear - $academicYear - 1);
    
    return max(1, min(4, $courseYear));
}

function e(? string $string): string
{
    return htmlspecialchars($string ??  '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function getFlashMessage(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function setFlashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}