PRAGMA foreign_keys = ON;

-- Макарова Юлия
INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Макарова Юлия', 'makarova.yulia@student.ru', 'female', datetime('now', 'localtime'), 'student');

-- Кочнев Артём
INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Кочнев Артём', 'kochnev.artem@student.ru', 'male', datetime('now', 'localtime'), 'student');

-- Логунов Илья
INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Логунов Илья', 'logunov.ilya@student.ru', 'male', datetime('now', 'localtime'), 'student');

-- Маклаков Сергей
INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Маклаков Сергей', 'maklakov.sergei@student.ru', 'male', datetime('now', 'localtime'), 'student');

-- Маскинскова Наталья
INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Маскинскова Наталья', 'maskinskova.natalia@student.ru', 'female', datetime('now', 'localtime'), 'student');

-- 2. Добавление трех новых фильмов разных жанров

-- Фильм 1: Побег из Шоушенка (1994) - Drama, Crime
INSERT INTO movies (title, year)
VALUES ('The Shawshank Redemption (1994)', 1994);

-- Связываем фильм с жанрами (используем last_insert_rowid() для получения ID только что добавленного фильма)
INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'The Shawshank Redemption (1994)' AND year = 1994),
    (SELECT id FROM genres WHERE name = 'Drama')
);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'The Shawshank Redemption (1994)' AND year = 1994),
    (SELECT id FROM genres WHERE name = 'Crime')
);

-- Фильм 2: Тайна Коко (2017) - Animation, Adventure, Family
INSERT INTO movies (title, year)
VALUES ('Coco (2017)', 2017);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Coco (2017)' AND year = 2017),
    (SELECT id FROM genres WHERE name = 'Animation')
);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Coco (2017)' AND year = 2017),
    (SELECT id FROM genres WHERE name = 'Adventure')
);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Coco (2017)' AND year = 2017),
    (SELECT id FROM genres WHERE name = 'Family')
);

-- Фильм 3: Индиана Джонс: В поисках утраченного ковчега (1981) - Action, Adventure
INSERT INTO movies (title, year)
VALUES ('Raiders of the Lost Ark (1981)', 1981);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Raiders of the Lost Ark (1981)' AND year = 1981),
    (SELECT id FROM genres WHERE name = 'Action')
);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Raiders of the Lost Ark (1981)' AND year = 1981),
    (SELECT id FROM genres WHERE name = 'Adventure')
);

-- 3. Добавление трех отзывов

-- Отзыв на Побег из Шоушенка (рейтинг 5/5)
INSERT INTO reviews (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'makarova.yulia@student.ru'),
    (SELECT id FROM movies WHERE title = 'The Shawshank Redemption (1994)' AND year = 1994),
    5.0,
    strftime('%s', 'now')
);

-- Отзыв на Тайна Коко (рейтинг 4.8/5)
INSERT INTO reviews (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'makarova.yulia@student.ru'),
    (SELECT id FROM movies WHERE title = 'Coco (2017)' AND year = 2017),
    4.8,
    strftime('%s', 'now')
);

-- Отзыв на Индиана Джонс (рейтинг 4.5/5)
INSERT INTO reviews (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'makarova.yulia@student.ru'),
    (SELECT id FROM movies WHERE title = 'Raiders of the Lost Ark (1981)' AND year = 1981),
    4.5,
    strftime('%s', 'now')
);