<?php
// Simple analytics tracking
session_start();
require_once 'config.php';

// Track page views
function trackPageView($page) {
    global $pdo;
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO page_views (user_id, page, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $page, $ip, $user_agent]);
    } catch (PDOException $e) {
        // Table might not exist
    }
}
?>
