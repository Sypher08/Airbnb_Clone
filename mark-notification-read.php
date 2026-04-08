<?php
require 'config.php';
require_once 'notifications.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    markAsRead($_POST['id'], $_SESSION['user_id']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
