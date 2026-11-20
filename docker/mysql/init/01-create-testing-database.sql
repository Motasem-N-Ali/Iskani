-- Runs once, when the MySQL data volume is first created.
-- The image creates `iskani` (MYSQL_DATABASE) by itself; the test database it does not.
CREATE DATABASE IF NOT EXISTS `iskani_testing`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- MYSQL_USER only receives rights on `iskani`, so grant the test database explicitly.
GRANT ALL PRIVILEGES ON `iskani_testing`.* TO 'iskani'@'%';
FLUSH PRIVILEGES;
