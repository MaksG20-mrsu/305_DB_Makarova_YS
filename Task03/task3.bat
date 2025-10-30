#!/bin/bash

echo "инициализация базы данных..."
sqlite3 movies_rating.db < db_init.sql

echo ""
echo "1. составить список фильмов, имеющих хотя бы одну оценку. отсортировать по году и названию, оставить первые 10"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select distinct m.title, m.year
from movies as m
join ratings as r on m.id = r.movie_id
order by m.year, m.title
limit 10;
"
echo ""

echo "2. пользователи, чьи фамилии начинаются на 'a', отсортированные по дате регистрации, первые 5"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select name, register_date
from users
where name like '% A%'
order by register_date
limit 5;
"
echo ""

echo "3. информация о рейтингах: эксперт, фильм, год, оценка, дата (гггг-мм-дд)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select u.name, m.title, m.year, r.rating,
       strftime('%Y-%m-%d', r.timestamp, 'unixepoch')
from ratings as r
join users as u on u.id = r.user_id
join movies as m on m.id = r.movie_id
order by u.name, m.title, r.rating
limit 50;
"
echo ""

echo "4. фильмы с тегами, отсортированные по году, названию и тегу, первые 40"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select m.title, t.tag
from movies as m
join tags as t on m.id = t.movie_id
order by m.year, m.title, t.tag
limit 40;
"
echo ""

echo "5. самые свежие фильмы (по последнему году)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select title, year
from movies
where year = (
  select year
  from movies
  where year > 0
  order by year desc
  limit 1
);
"
echo ""

echo "6. драмы после 2005 года, понравившиеся женщинам (оценка ≥ 4.5)"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
select m.title, m.year, count(r.rating)
from ratings as r
join movies as m on r.movie_id = m.id
join users as u on r.user_id = u.id
where m.genre = 'drama' and m.year > 2005 and u.gender = 'female' and r.rating >= 4.5
group by m.title, m.year
order by m.year, m.title;
"
echo ""

echo "7. анализ регистрации пользователей по годам: максимум и минимум"
echo "--------------------------------------------------"
sqlite3 movies_rating.db -box -echo "
with yearly as (
  select substr(register_date, 1, 4) as year, count(*) as cnt
  from users
  group by year
),
minmax as (
  select min(cnt) as min_cnt, max(cnt) as max_cnt from yearly
)
select y.year, y.cnt
from yearly y, minmax m
where y.cnt = m.min_cnt or y.cnt = m.max_cnt;
"
echo ""
