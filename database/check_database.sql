-- ============================================
-- СКРИПТЫ ПРОВЕРКИ И ОПТИМИЗАЦИИ БАЗЫ ДАННЫХ
-- Проект: Кинотеатр на колёсах
-- Дата: 15 марта 2026
-- ============================================

-- 1. ПРОВЕРКА СУЩЕСТВУЮЩИХ ТАБЛИЦ
-- ============================================
SHOW TABLES;

-- 2. ПРОВЕРКА СТРУКТУРЫ ТАБЛИЦ
-- ============================================
DESCRIBE users;
DESCRIBE movies;
DESCRIBE cities;
DESCRIBE votes;
DESCRIBE routes;
DESCRIBE settings;
DESCRIBE two_factor_codes;

-- 3. ПРОВЕРКА ВНЕШНИХ КЛЮЧЕЙ (FOREIGN KEYS)
-- ============================================
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 4. ПРОВЕРКА ИНДЕКСОВ
-- ============================================
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX,
    NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME, INDEX_NAME;

-- 5. ДОБАВЛЕНИЕ ОТСУТСТВУЮЩИХ ИНДЕКСОВ
-- ============================================

-- Индексы для таблицы users
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_email (email);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_city_id (city_id);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_users_role (role);

-- Индексы для таблицы movies
ALTER TABLE movies ADD INDEX IF NOT EXISTS idx_movies_city_id (city_id);
ALTER TABLE movies ADD INDEX IF NOT EXISTS idx_movies_rating (rating);
ALTER TABLE movies ADD INDEX IF NOT EXISTS idx_movies_created_at (created_at);

-- Индексы для таблицы cities
ALTER TABLE cities ADD INDEX IF NOT EXISTS idx_cities_name (name);
ALTER TABLE cities ADD INDEX IF NOT EXISTS idx_cities_lat_lng (lat, lng);

-- Индексы для таблицы votes
ALTER TABLE votes ADD INDEX IF NOT EXISTS idx_votes_user_id (user_id);
ALTER TABLE votes ADD INDEX IF NOT EXISTS idx_votes_movie_id (movie_id);
ALTER TABLE votes ADD INDEX IF NOT EXISTS idx_votes_city_id (city_id);
ALTER TABLE votes ADD INDEX IF NOT EXISTS idx_votes_created_at (created_at);

-- Индексы для таблицы routes
ALTER TABLE routes ADD INDEX IF NOT EXISTS idx_routes_city_id (city_id);
ALTER TABLE routes ADD INDEX IF NOT EXISTS idx_routes_movie_id (movie_id);
ALTER TABLE routes ADD INDEX IF NOT EXISTS idx_routes_show_date (show_date);

-- 6. ОПТИМИЗАЦИЯ ТАБЛИЦ
-- ============================================
OPTIMIZE TABLE users;
OPTIMIZE TABLE movies;
OPTIMIZE TABLE cities;
OPTIMIZE TABLE votes;
OPTIMIZE TABLE routes;
OPTIMIZE TABLE settings;
OPTIMIZE TABLE two_factor_codes;

-- 7. АНАЛИЗ ТАБЛИЦ
-- ============================================
ANALYZE TABLE users;
ANALYZE TABLE movies;
ANALYZE TABLE cities;
ANALYZE TABLE votes;
ANALYZE TABLE routes;
ANALYZE TABLE settings;
ANALYZE TABLE two_factor_codes;

-- 8. ПРОВЕРКА ЦЕЛОСТНОСТИ ДАННЫХ
-- ============================================

-- Проверка на наличие сиротских записей в votes
SELECT COUNT(*) as orphan_votes FROM votes v
LEFT JOIN users u ON v.user_id = u.id
WHERE u.id IS NULL;

-- Проверка на наличие сиротских записей в movies
SELECT COUNT(*) as orphan_movies FROM movies m
LEFT JOIN cities c ON m.city_id = c.id
WHERE c.id IS NULL AND m.city_id IS NOT NULL;

-- Проверка на наличие сиротских записей в routes
SELECT COUNT(*) as orphan_routes FROM routes r
LEFT JOIN cities c ON r.city_id = c.id
WHERE c.id IS NULL;

-- 9. СТАТИСТИКА ПО ТАБЛИЦАМ
-- ============================================
SELECT 
    TABLE_NAME,
    TABLE_ROWS as 'Количество записей',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as 'Размер (MB)',
    ROUND(DATA_FREE / 1024 / 1024, 2) as 'Свободное место (MB)'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_ROWS DESC;

-- 10. ПРОВЕРКА ДУБЛИКАТОВ
-- ============================================

-- Дубликаты email в users
SELECT email, COUNT(*) as count FROM users
GROUP BY email HAVING count > 1;

-- Дубликаты названий городов
SELECT name, COUNT(*) as count FROM cities
GROUP BY name HAVING count > 1;

-- Дубликаты названий фильмов
SELECT title, COUNT(*) as count FROM movies
GROUP BY title HAVING count > 1;

-- 11. СОЗДАНИЕ РЕЗЕРВНОЙ КОПИИ (для MySQL)
-- ============================================
-- Выполняется через консоль:
-- mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

-- 12. ВОССТАНОВЛЕНИЕ ИЗ РЕЗЕРВНОЙ КОПИИ
-- ============================================
-- Выполняется через консоль:
-- mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

-- 13. НАСТРОЙКА АВТОИНКРЕМЕНТА
-- ============================================
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE movies AUTO_INCREMENT = 1;
ALTER TABLE cities AUTO_INCREMENT = 1;
ALTER TABLE votes AUTO_INCREMENT = 1;
ALTER TABLE routes AUTO_INCREMENT = 1;

-- 14. ПРОВЕРКА КОДИРОВКИ
-- ============================================
SELECT 
    TABLE_NAME,
    TABLE_COLLATION
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE();

-- Должно быть: utf8mb4_unicode_ci

-- 15. ИСПРАВЛЕНИЕ КОДИРОВКИ (если нужно)
-- ============================================
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE movies CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE cities CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE votes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE routes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 16. СОЗДАНИЕ ПРЕДСТАВЛЕНИЙ ДЛЯ СТАТИСТИКИ
-- ============================================

-- Представление: статистика по городам
CREATE OR REPLACE VIEW city_stats AS
SELECT 
    c.id,
    c.name,
    COUNT(DISTINCT v.id) as votes_count,
    COUNT(DISTINCT m.id) as movies_count,
    COALESCE(SUM(v.expected_attendees), 0) as expected_attendees
FROM cities c
LEFT JOIN votes v ON c.id = v.city_id
LEFT JOIN movies m ON c.id = m.city_id
GROUP BY c.id, c.name;

-- Представление: рейтинг фильмов
CREATE OR REPLACE VIEW movie_rating AS
SELECT 
    m.id,
    m.title,
    m.genre,
    COUNT(v.id) as votes_count,
    COALESCE(SUM(v.expected_attendees), 0) as expected_attendees,
    m.rating
FROM movies m
LEFT JOIN votes v ON m.id = v.movie_id
GROUP BY m.id, m.title, m.genre, m.rating
ORDER BY m.rating DESC;

-- 17. ПРОВЕРКА ТРИГГЕРОВ
-- ============================================
SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    EVENT_OBJECT_TABLE,
    ACTION_TIMING
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE();

-- 18. ПРОВЕРКА ХРАНИМЫХ ПРОЦЕДУР
-- ============================================
SELECT 
    ROUTINE_NAME,
    ROUTINE_TYPE,
    DATA_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE();

-- 19. МОНИТОРИНГ АКТИВНОСТИ (для MySQL 5.7+)
-- ============================================
SELECT * FROM sys.session;
SELECT * FROM sys.processlist;

-- 20. ОЧИСТКА СТАРЫХ ДАННЫХ (опционально)
-- ============================================

-- Удаление старых сессий (если используется таблица sessions)
-- DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY);

-- Удаление старых токенов 2FA
-- DELETE FROM two_factor_codes WHERE created_at < NOW() - INTERVAL 1 DAY;

-- ============================================
-- ЗАВЕРШЕНИЕ ПРОВЕРКИ
-- ============================================
SELECT 'Проверка базы данных завершена успешно!' as Status;
