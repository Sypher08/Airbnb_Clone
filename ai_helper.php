<?php
require_once 'config.php';

function callOpenAI($messages) {
    // Get API key from environment
    $openai_api_key = getenv('OPENAI_API_KEY');
    
    if (empty($openai_api_key)) {
        // Fallback to simple rule-based AI
        return simpleAIResponse($messages);
    }
    
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 500
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'];
    } else {
        error_log("OpenAI API error: " . $response);
        return simpleAIResponse($messages);
    }
}

function simpleAIResponse($messages) {
    global $pdo;
    
    // Get the last user message
    $lastMessage = end($messages);
    $query = strtolower($lastMessage['content']);
    
    // Property count query
    if (strpos($query, 'property') !== false || strpos($query, 'listing') !== false || strpos($query, 'how many') !== false) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM listings");
        $total = $stmt->fetchColumn();
        return "🏠 We have $total amazing properties available! Use the search bar to find your perfect stay by location, dates, and number of guests.";
    }
    
    // Location-based query
    $locations = ['pune', 'mumbai', 'delhi', 'goa', 'manali', 'kathmandu', 'pokhara', 'bangalore', 'chennai', 'kolkata'];
    foreach ($locations as $loc) {
        if (strpos($query, $loc) !== false) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count, MIN(price_per_night) as min_price, MAX(price_per_night) as max_price FROM listings WHERE LOWER(location) LIKE ?");
            $stmt->execute(["%$loc%"]);
            $stats = $stmt->fetch();
            if ($stats && $stats['count'] > 0) {
                return "🏠 We have {$stats['count']} properties in " . ucfirst($loc) . ". Prices range from ₹" . number_format($stats['min_price']) . " to ₹" . number_format($stats['max_price']) . " per night. Use the search bar to see all options!";
            } else {
                return "I couldn't find properties in " . ucfirst($loc) . ". Try searching for nearby cities or different destinations like Pune, Mumbai, Goa, or Manali.";
            }
        }
    }
    
    // Booking questions
    if (strpos($query, 'book') !== false || strpos($query, 'reserve') !== false) {
        return "📅 To book a property:\n\n1️⃣ Find a property you like\n2️⃣ Click 'View details'\n3️⃣ Select your check-in/check-out dates\n4️⃣ Enter number of guests\n5️⃣ Click 'Reserve'\n\nYou'll need to be logged in to complete the booking. After booking, you'll receive a confirmation notification.";
    }
    
    // Price questions
    if (strpos($query, 'price') !== false || strpos($query, 'cost') !== false) {
        $stmt = $pdo->query("SELECT AVG(price_per_night) as avg_price, MIN(price_per_night) as min_price, MAX(price_per_night) as max_price FROM listings");
        $stats = $stmt->fetch();
        return "💰 Price Information:\n\n• Average price: ₹" . number_format($stats['avg_price']) . " per night\n• Cheapest: ₹" . number_format($stats['min_price']) . " per night\n• Most expensive: ₹" . number_format($stats['max_price']) . " per night\n\nUse the price filter to find properties within your budget!";
    }
    
    // Cancellation questions
    if (strpos($query, 'cancel') !== false) {
        return "❌ Cancellation Policy:\n\n• Most properties offer free cancellation within 48 hours of booking\n• Check the property details for specific terms\n• Full refund if cancelled before check-in (varies by property)\n• Service fees may apply for late cancellations\n\nAlways review the cancellation policy before booking!";
    }
    
    // Amenities questions
    if (strpos($query, 'amenities') !== false || strpos($query, 'wifi') !== false || strpos($query, 'parking') !== false || strpos($query, 'pool') !== false) {
        return "🛋️ Common Amenities:\n\n• 📶 Free WiFi\n• 🚗 Free Parking\n• 🍳 Kitchen\n• 🧺 Washer/Dryer\n• ❄️ Air Conditioning\n• 📺 Smart TV\n• 🔥 Heating\n• 💨 Hair Dryer\n• ☕ Coffee Maker\n\nEach property lists its specific amenities on the detail page!";
    }
    
    // Help questions
    if (strpos($query, 'help') !== false || strpos($query, 'support') !== false || strpos($query, 'what can you') !== false) {
        return "🤖 I can help you with:\n\n• 🔍 Finding properties in specific locations\n• 📅 Understanding the booking process\n• 💰 Checking prices and availability\n• 🛋️ Learning about amenities\n• ❌ Cancellation policies\n• 📱 Account and profile management\n\nWhat would you like to know more about?";
    }
    
    // Greetings
    if (strpos($query, 'hello') !== false || strpos($query, 'hi') !== false || strpos($query, 'hey') !== false) {
        return "👋 Hello! I'm your AI travel assistant. I can help you find properties, check prices, suggest destinations, and answer questions about bookings. What can I help you with today?";
    }
    
    // Top properties
    if (strpos($query, 'top') !== false || strpos($query, 'best') !== false || strpos($query, 'popular') !== false) {
        $stmt = $pdo->query("SELECT title, location, price_per_night, rating FROM listings ORDER BY rating DESC LIMIT 3");
        $top = $stmt->fetchAll();
        $response = "⭐ Top Rated Properties:\n\n";
        foreach ($top as $property) {
            $response .= "• {$property['title']} - {$property['location']}\n  ₹{$property['price_per_night']}/night | Rating: {$property['rating']}⭐\n\n";
        }
        return $response;
    }
    
    // Default response
    return "🤖 I'm here to help! You can ask me about:\n\n🏠 Properties and listings\n📅 Booking process\n💰 Prices and fees\n📍 Locations (Pune, Mumbai, Goa, Manali, etc.)\n🛋️ Amenities and features\n❌ Cancellation policies\n⭐ Top rated properties\n\nWhat would you like to know?";
}

// Save chat history
function saveChatMessage($user_id, $message, $response) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_logs (user_id, user_message, ai_response) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $message, $response]);
    } catch (PDOException $e) {
        error_log("Failed to save chat: " . $e->getMessage());
    }
}
?>
