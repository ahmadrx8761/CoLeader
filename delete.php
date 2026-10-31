<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
}

header('Location: index.php?msg=deleted');
exit;
