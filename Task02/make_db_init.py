import os
import pandas as pd


# Конфигурационные параметры
DATA_FOLDER = 'dataset'
SQL_OUTPUT_FILE = 'db_init.sql'
DATABASE_NAME = 'db_init.db'

def create_database_script():
    """
    Генерирует SQL-скрипт для инициализации базы данных фильмов
    на основе CSV файлов в указанной директории
    """
    with open(SQL_OUTPUT_FILE, 'w', encoding='utf-8') as sql_file:
        # Удаляем существующие таблицы (если есть)
        sql_file.write("-- Удаление старых таблиц\n")
        sql_file.write("DROP TABLE IF EXISTS movies;\n")
        sql_file.write("DROP TABLE IF EXISTS ratings;\n")
        sql_file.write("DROP TABLE IF EXISTS tags;\n")
        sql_file.write("DROP TABLE IF EXISTS users;\n\n")
        
        # Создаем таблицу фильмов
        sql_file.write("-- Таблица с информацией о фильмах\n")
        sql_file.write("""CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT,
    year INTEGER,
    genres TEXT
);\n\n""")
        
        # Обрабатываем данные о фильмах
        movies_data = pd.read_csv(os.path.join(DATA_FOLDER, 'movies.csv'))
        sql_file.write("-- Вставка данных о фильмах\n")
        for index, record in movies_data.iterrows():
            # Экранируем апострофы в названиях
            safe_title = record['title'].replace("'", "''")
            sql_file.write(f"INSERT INTO movies (id, title, year, genres) VALUES ({record['movieId']}, '{safe_title}', {record.get('year', 'NULL')}, '{record['genres']}');\n")
        
        # Создаем таблицу рейтингов
        sql_file.write("\n-- Таблица с оценками пользователей\n")
        sql_file.write("""CREATE TABLE ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    movie_id INTEGER,
    rating REAL,
    timestamp INTEGER
);\n\n""")
        
        # Обрабатываем рейтинги
        ratings_data = pd.read_csv(os.path.join(DATA_FOLDER, 'ratings.csv'))
        sql_file.write("-- Вставка данных об оценках\n")
        for index, record in ratings_data.iterrows():
            sql_file.write(f"INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ({record['userId']}, {record['movieId']}, {record['rating']}, {record['timestamp']});\n")
        
        # Создаем таблицу тегов
        sql_file.write("\n-- Таблица с тегами фильмов\n")
        sql_file.write("""CREATE TABLE tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    movie_id INTEGER,
    tag TEXT,
    timestamp INTEGER
);\n\n""")
        
        # Обрабатываем теги
        tags_data = pd.read_csv(os.path.join(DATA_FOLDER, 'tags.csv'))
        sql_file.write("-- Вставка данных о тегах\n")
        for index, record in tags_data.iterrows():
            safe_tag = str(record['tag']).replace("'", "''")
            sql_file.write(f"INSERT INTO tags (user_id, movie_id, tag, timestamp) VALUES ({record['userId']}, {record['movieId']}, '{safe_tag}', {record['timestamp']});\n")
        
        # Создаем таблицу пользователей
        sql_file.write("\n-- Таблица с информацией о пользователях\n")
        sql_file.write("""CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT,
    email TEXT,
    gender TEXT,
    register_date TEXT,
    occupation TEXT
);\n\n""")
        
        # Пытаемся загрузить данные пользователей (может отсутствовать)
        try:
            users_data = pd.read_csv(
                os.path.join(DATA_FOLDER, 'users.dat'), 
                sep='::', 
                engine='python', 
                names=['id', 'name', 'email', 'gender', 'register_date', 'occupation']
            )
            sql_file.write("-- Вставка данных о пользователях\n")
            for index, record in users_data.iterrows():
                sql_file.write(f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ({record['id']}, '{record['name']}', '{record['email']}', '{record['gender']}', '{record['register_date']}', '{record['occupation']}');\n")
        except FileNotFoundError:
            print(f"Внимание: файл 'users.dat' не найден в папке '{DATA_FOLDER}'. Таблица пользователей останется пустой.")

# Точка входа в программу
if __name__ == '__main__':
    # Проверяем существование папки с данными
    if not os.path.exists(DATA_FOLDER):
        print(f"Ошибка: Папка '{DATA_FOLDER}' не найдена. Убедитесь, что она существует и содержит необходимые файлы данных.")
    else:
        create_database_script()
        print(f"SQL-скрипт '{SQL_OUTPUT_FILE}' создан успешно!")