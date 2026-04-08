<?php
require 'config.php';
require_once 'notifications.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;

// Validate inputs
if ($listing_id == 0) {
    die('Invalid listing');
}

if (empty($check_in) || empty($check_out)) {
    die('Please select check-in and check-out dates');
}

$today = date('Y-m-d');
if ($check_in < $today) {
    die('Check-in date cannot be in the past');
}

if ($check_out <= $check_in) {
    die('Check-out must be after check-in');
}

try {
    // Get listing details
    $stmt = $pdo->prepare("SELECT l.*, u.name as host_name, u.phone as host_phone FROM listings l JOIN users u ON l.user_id = u.id WHERE l.id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        die('Listing not found');
    }
    
    if ($guests > $listing['max_guests']) {
        die('Too many guests selected. Maximum is ' . $listing['max_guests']);
    }
    
    // Calculate total
    $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
    $total_price = $nights * $listing['price_per_night'];
    
    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, listing_id, check_in, check_out, guests, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $listing_id, $check_in, $check_out, $guests, $total_price]);
    $booking_id = $pdo->lastInsertId();
    
    // Send notification to user (guest)
    $guest_message = "🎉 Booking Confirmed!\n\n"
        . "Property: {$listing['title']}\n"
        . "Location: {$listing['location']}\n"
        . "Check-in: " . date('M j, Y', strtotime($check_in)) . "\n"
        . "Check-out: " . date('M j, Y', strtotime($check_out)) . "\n"
        . "Guests: $guests\n"
        . "Total: ₹" . number_format($total_price, 2) . "\n\n"
        . "Thank you for booking with Airbnb!";
    
    addNotification($_SESSION['user_id'], 'booking_confirmed', 'Booking Confirmed!', $guest_message);
    
    // Send notification to host
    $host_message = "📅 New Booking Received!\n\n"
        . "Guest: {$_SESSION['user_name']}\n"
        . "Property: {$listing['title']}\n"
        . "Dates: " . date('M j', strtotime($check_in)) . " - " . date('M j, Y', strtotime($check_out)) . "\n"
        . "Guests: $guests\n"
        . "Total: ₹" . number_format($total_price, 2) . "\n\n"
        . "Login to view details.";
    
    addNotification($listing['user_id'], 'new_booking', 'New Booking!', $host_message);
    
    // Redirect to detail page with success message
    header('Location: detail.php?id=' . $listing_id . '&booked=1');
    exit;
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Unable to process booking. Please try again.');
}
?>
