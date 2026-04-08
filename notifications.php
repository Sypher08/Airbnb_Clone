<?php
require_once 'config.php';

// Add a notification for a user
function addNotification($user_id, $type, $title, $message) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $title, $message]);
        
        // For critical notifications, also send SMS if phone exists
        if ($type == 'booking_confirmed' || $type == 'booking_cancelled') {
            sendSMSNotification($user_id, $message);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Failed to add notification: " . $e->getMessage());
        return false;
    }
}

// Get unread notifications count
function getUnreadCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

// Get user notifications
function getUserNotifications($user_id, $limit = 20) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

// Mark notification as read
function markAsRead($notification_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notification_id, $user_id]);
}

// Mark all notifications as read
function markAllAsRead($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

// Send SMS notification (simulated - you can integrate with actual SMS API)
function sendSMSNotification($user_id, $message) {
    global $pdo;
    
    // Get user's phone number
    $stmt = $pdo->prepare("SELECT phone, name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && !empty($user['phone'])) {
        // Log SMS attempt
        $stmt = $pdo->prepare("INSERT INTO sms_logs (user_id, phone, message, status) VALUES (?, ?, ?, 'sent')");
        $stmt->execute([$user_id, $user['phone'], $message]);
        
        // In production, integrate with SMS API like Twilio, MSG91, etc.
        // Example with MSG91 (India):
        // $sms_url = "https://api.msg91.com/api/v5/flow/";
        // $curl = curl_init();
        // curl_setopt_array($curl, [
        //     CURLOPT_URL => $sms_url,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_POST => true,
        //     CURLOPT_POSTFIELDS => json_encode([
        //         'mobiles' => $user['phone'],
        //         'message' => $message,
        //         'sender' => 'AIRBNB'
        //     ]),
        //     CURLOPT_HTTPHEADER => ['authkey: YOUR_API_KEY', 'Content-Type: application/json']
        // ]);
        // curl_exec($curl);
        
        return true;
    }
    return false;
}

// Delete old notifications (older than 30 days)
function cleanupOldNotifications() {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    return $stmt->execute();
}
?>
