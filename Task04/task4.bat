#!/bin/bash
chcp 65001

echo "Создание и заполнение базы данных..."
sqlite3 movies_rating.db < db_init.sql
echo ""

echo "1. Найти все пары пользователей, оценивших один и тот же фильм (первые 100 записей)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT 
    u1.name AS user1_name,
    u2.name AS user2_name,
    m.title AS movie_title
FROM ratings r1
JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
JOIN users u1 ON r1.user_id = u1.id
JOIN users u2 ON r2.user_id = u2.id
JOIN movies m ON r1.movie_id = m.id
LIMIT 100;"
echo ""

echo "2. Найти 10 самых старых оценок от разных пользователей"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT 
    m.title AS movie_title,
    u.name AS user_name,
    r.rating,
    DATE(r.timestamp, 'unixepoch') AS review_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
WHERE r.id IN (
    SELECT MIN(id)
    FROM ratings
    GROUP BY user_id
)
ORDER BY r.timestamp
LIMIT 10;"
echo ""

echo "3. Фильмы с максимальным и минимальным средним рейтингом"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH avg_ratings AS (
    SELECT 
        movie_id,
        AVG(rating) AS avg_rating
    FROM ratings
    GROUP BY movie_id
),
max_min_ratings AS (
    SELECT 
        MAX(avg_rating) AS max_rating,
        MIN(avg_rating) AS min_rating
    FROM avg_ratings
)
SELECT 
    m.title,
    m.year,
    ROUND(ar.avg_rating, 2) AS avg_rating,
    CASE 
        WHEN ar.avg_rating = mmr.max_rating THEN 'Да'
        WHEN ar.avg_rating = mmr.min_rating THEN 'Нет'
    END AS \"Рекомендуем\"
FROM avg_ratings ar
JOIN movies m ON ar.movie_id = m.id
CROSS JOIN max_min_ratings mmr
WHERE ar.avg_rating = mmr.max_rating OR ar.avg_rating = mmr.min_rating
ORDER BY m.year, m.title;"
echo ""

echo "4. Количество оценок и средняя оценка от мужчин (2011-2014)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT 
    COUNT(r.rating) AS count_ratings,
    ROUND(AVG(r.rating), 2) AS avg_rating
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE u.gender = 'male'
    AND CAST(STRFTIME('%Y', r.timestamp, 'unixepoch') AS INTEGER) BETWEEN 2011 AND 2014;"
echo ""

echo "5. Топ-20 фильмов со средней оценкой и количеством пользователей"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT 
    m.title,
    m.year,
    ROUND(AVG(r.rating), 2) AS avg_rating,
    COUNT(DISTINCT r.user_id) AS user_count
FROM movies m
JOIN ratings r ON m.id = r.movie_id
GROUP BY m.id, m.title, m.year
ORDER BY m.year, m.title
LIMIT 20;"
echo ""

echo "6. Самый распространенный жанр фильма"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE split_genres(movie_id, genre, rest) AS (
    SELECT 
        id,
        CASE 
            WHEN INSTR(genres, '|') > 0 THEN SUBSTR(genres, 1, INSTR(genres, '|') - 1)
            ELSE genres
        END,
        CASE 
            WHEN INSTR(genres, '|') > 0 THEN SUBSTR(genres, INSTR(genres, '|') + 1)
            ELSE NULL
        END
    FROM movies
    WHERE genres IS NOT NULL
    
    UNION ALL
    
    SELECT 
        movie_id,
        CASE 
            WHEN INSTR(rest, '|') > 0 THEN SUBSTR(rest, 1, INSTR(rest, '|') - 1)
            ELSE rest
        END,
        CASE 
            WHEN INSTR(rest, '|') > 0 THEN SUBSTR(rest, INSTR(rest, '|') + 1)
            ELSE NULL
        END
    FROM split_genres
    WHERE rest IS NOT NULL
)
SELECT 
    TRIM(genre) AS genre,
    COUNT(*) AS movie_count
FROM split_genres
WHERE genre IS NOT NULL AND genre != ''
GROUP BY TRIM(genre)
ORDER BY movie_count DESC
LIMIT 1;"
echo ""

echo "7. 10 последних зарегистрированных пользователей"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "SELECT 
    name || '|' || register_date AS user_info
FROM users
ORDER BY register_date DESC
LIMIT 10;"
echo ""

echo "8. Дни недели для дней рождения (пример с 2000-2025)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "WITH RECURSIVE birthday_years AS (
    SELECT 
        2000 AS year,
        DATE('2005-10-17') AS birthday_dategit 
    
    UNION ALL
    
    SELECT 
        year + 1,
        DATE((year + 1) || '-10-17')
    FROM birthday_years
    WHERE year < 2025
)
SELECT 
    year,
    CASE CAST(STRFTIME('%w', birthday_date) AS INTEGER)
        WHEN 0 THEN 'Воскресенье'
        WHEN 1 THEN 'Понедельник'
        WHEN 2 THEN 'Вторник'
        WHEN 3 THEN 'Среда'
        WHEN 4 THEN 'Четверг'
        WHEN 5 THEN 'Пятница'
        WHEN 6 THEN 'Суббота'
    END AS day_of_week,
    birthday_date AS date
FROM birthday_years
ORDER BY year;"
echo ""

echo "Все задания выполнены!"