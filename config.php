<?php
// تنظیمات اتصال به دیتابیس — مقادیر پیش‌فرض XAMPP
$DB_HOST = 'localhost';
$DB_NAME = 'koleidor_factions';
$DB_USER = 'root';
$DB_PASS = '';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die('خطا در اتصال به دیتابیس: ' . $conn->connect_error . ' — مطمئن شوید Apache و MySQL در XAMPP روشن هستند و database.sql را ایمپورت کرده‌اید.');
}

$conn->set_charset('utf8mb4');
