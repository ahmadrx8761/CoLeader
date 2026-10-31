-- دیتابیس مدیریت اعضای فکشن‌ها
-- این فایل را در phpMyAdmin ایمپورت کنید یا از طریق کنسول MySQL اجرا کنید.

CREATE DATABASE IF NOT EXISTS koleidor_factions
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE koleidor_factions;

CREATE TABLE IF NOT EXISTS factions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL DEFAULT '#334155',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faction_id INT NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  rank_title VARCHAR(100) DEFAULT NULL,
  member_code VARCHAR(50) DEFAULT NULL,
  status ENUM('فعال','غیرفعال') NOT NULL DEFAULT 'فعال',
  joined_date DATE DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (faction_id) REFERENCES factions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO factions (slug, name, color, sort_order) VALUES
  ('medic',    'مدیک',       '#1F7A5C', 1),
  ('pd',       'پلیس (PD)',  '#1D4E89', 2),
  ('mechanic', 'مکانیک',     '#B8601B', 3),
  ('taxi',     'تاکسی',      '#C9A227', 4),
  ('army',     'آرمی',       '#4B5563', 5),
  ('fbi',      'اف‌بی‌آی',    '#1C2433', 6);
