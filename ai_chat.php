<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'ai_helper.php';

session_start();

// Get user message
$input = json_decode(file_get_contents('php://input'), true);
$user_message = trim($input['message'] ?? '');

if (empty($user_message)) {
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Build conversation context
$messages = [
    [
        'role' => 'system',
        'content' => "You are a helpful travel assistant for an Airbnb clone website. Help users find properties, understand booking process, answer questions about locations, prices, amenities, cancellations. Be friendly and concise. Use Indian currency (₹)."
    ],
    [
        'role' => 'user',
        'content' => $user_message
    ]
];

// Get AI response
$ai_response = callOpenAI($messages);

// Save to database if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
saveChatMessage($user_id, $user_message, $ai_response);

// Clean output - ensure no extra whitespace
ob_clean();
echo json_encode(['response' => $ai_response]);
exit;
?>
