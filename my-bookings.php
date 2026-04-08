<?php 
require 'config.php';
requireLogin();

// Get user's bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.*, l.title, l.image_url, l.location, l.rating 
        FROM bookings b 
        JOIN listings l ON b.listing_id = l.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My trips - Airbnb Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f7f7f7;
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #ebebeb;
            padding: 16px 0;
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ff385c;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #222;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            color: #ff385c;
        }
        
        .trips-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        
        .trips-header {
            margin-bottom: 32px;
        }
        
        .trips-header h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .trips-header p {
            color: #717171;
        }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .booking-content {
            display: flex;
            gap: 24px;
            padding: 20px;
        }
        
        .booking-image {
            width: 200px;
            height: 150px;
            flex-shrink: 0;
        }
        
        .booking-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .booking-details {
            flex: 1;
        }
        
        .booking-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #222;
        }
        
        .booking-location {
            color: #717171;
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .booking-info {
            background: #f7f7f7;
            padding: 12px;
            border-radius: 12px;
            margin: 12px 0;
        }
        
        .booking-info div {
            margin: 4px 0;
            font-size: 14px;
        }
        
        .booking-price {
            font-size: 18px;
            font-weight: 600;
            color: #222;
            margin-top: 12px;
        }
        
        .status-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 16px;
        }
        
        .empty-state p {
            color: #717171;
            margin-bottom: 24px;
        }
        
        .btn-explore {
            display: inline-block;
            background: #ff385c;
            color: white;
            padding: 12px 32px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .btn-explore:hover {
            background: #e31c5f;
        }
        
        @media (max-width: 768px) {
            .booking-content {
                flex-direction: column;
            }
            .booking-image {
                width: 100%;
                height: 200px;
            }
            .trips-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">airbnb</a>
            <div class="nav-links">
                <a href="add-listing.php">Become a host</a>
                <a href="my-bookings.php" style="color: #ff385c; font-weight: 600;">My trips</a>
                <a href="ai-assistant.php">🤖 AI Assistant</a>
                <a href="profile.php">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="trips-container">
        <div class="trips-header">
            <h1>My trips</h1>
            <p>View and manage your bookings</p>
        </div>
        
        <?php if(empty($bookings)): ?>
            <div class="empty-state">
                <h3>No trips booked yet</h3>
                <p>Explore properties and book your next adventure!</p>
                <a href="index.php" class="btn-explore">Start exploring →</a>
            </div>
        <?php else: ?>
            <?php foreach($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-content">
                        <div class="booking-image">
                            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($booking['title']) ?>">
                        </div>
                        <div class="booking-details">
                            <h2 class="booking-title"><?= htmlspecialchars($booking['title']) ?></h2>
                            <div class="booking-location">📍 <?= htmlspecialchars($booking['location']) ?></div>
                            
                            <div class="booking-info">
                                <div>📅 Check-in: <strong><?= date('F j, Y', strtotime($booking['check_in'])) ?></strong></div>
                                <div>📅 Check-out: <strong><?= date('F j, Y', strtotime($booking['check_out'])) ?></strong></div>
                                <div>👥 Guests: <strong><?= $booking['guests'] ?> <?= $booking['guests'] == 1 ? 'guest' : 'guests' ?></strong></div>
                            </div>
                            
                            <div class="booking-price">
                                Total: <strong>₹<?= number_format($booking['total_price'], 2) ?></strong>
                            </div>
                            <span class="status-badge">✓ Confirmed</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'ai-widget.php'; ?>
</body>
</html>
