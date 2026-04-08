<?php
require 'config.php';
require_once 'notifications.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    die('Invalid listing ID');
}

try {
    // Get listing with host info
    $stmt = $pdo->prepare("
        SELECT l.*, u.name as host_name, u.email as host_email, u.phone as host_phone,
               u.created_at as host_joined
        FROM listings l 
        JOIN users u ON l.user_id = u.id 
        WHERE l.id = ?
    ");
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        die('Listing not found');
    }
    
    // Get reviews
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.listing_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$id]);
    $reviews = $stmt->fetchAll();
    
    $reviewsCount = count($reviews);
    $avgRating = $reviewsCount > 0 ? array_sum(array_column($reviews, 'rating')) / $reviewsCount : $listing['rating'];
    $avgRating = number_format($avgRating, 2);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Unable to load property details. Please try again later.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($listing['title']) ?> - Airbnb Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: #ffffff;
            color: #222222;
        }
        
        /* Navbar Styles */
        .navbar {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1000;
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
        
        /* Main Container */
        .detail-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }
        
        /* Header Section */
        .detail-header {
            margin-bottom: 24px;
        }
        
        .detail-title {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #222;
        }
        
        .detail-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #ebebeb;
        }
        
        .detail-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .detail-rating a {
            color: #222;
            text-decoration: underline;
        }
        
        .detail-actions {
            display: flex;
            gap: 16px;
        }
        
        .detail-actions a {
            text-decoration: none;
            color: #222;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Image Gallery */
        .detail-gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin: 24px 0;
            border-radius: 20px;
            overflow: hidden;
        }
        
        .detail-main-image {
            grid-row: span 2;
            min-height: 400px;
        }
        
        .detail-main-image img,
        .detail-small-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .detail-small-images {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        /* Two Column Layout */
        .detail-two-column {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 80px;
            margin-top: 48px;
        }
        
        /* Left Column - Property Info */
        .property-info {
            flex: 1;
        }
        
        /* Host Section */
        .host-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #ebebeb;
        }
        
        .host-info h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .host-details {
            color: #717171;
            font-size: 14px;
        }
        
        .host-avatar {
            width: 56px;
            height: 56px;
            background: #ff385c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
        }
        
        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin: 24px 0;
        }
        
        .feature-card {
            display: flex;
            gap: 16px;
            padding: 16px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
        }
        
        .feature-icon {
            font-size: 24px;
        }
        
        .feature-text h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .feature-text p {
            font-size: 14px;
            color: #717171;
        }
        
        /* Description */
        .description-section {
            margin: 32px 0;
        }
        
        .description-section h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .description-text {
            line-height: 1.6;
            color: #222;
            white-space: pre-line;
        }
        
        /* Amenities */
        .amenities-section {
            margin: 32px 0;
        }
        
        .amenities-section h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .amenities-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .amenity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #ebebeb;
            font-size: 14px;
        }
        
        /* Reviews Section */
        .reviews-section {
            margin: 32px 0;
        }
        
        .reviews-section h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 24px;
        }
        
        .review-card {
            padding: 20px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .reviewer-name {
            font-weight: 600;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .review-date {
            font-size: 12px;
            color: #717171;
            margin-bottom: 8px;
        }
        
        .review-comment {
            line-height: 1.5;
            color: #222;
        }
        
        /* Right Column - Booking Card */
        .booking-card {
            position: sticky;
            top: 100px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
            background: white;
        }
        
        .booking-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .booking-price .price {
            font-size: 22px;
            font-weight: 600;
        }
        
        .booking-price .price span {
            font-size: 16px;
            font-weight: 400;
        }
        
        .rating-summary {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .booking-form {
            margin: 16px 0;
        }
        
        .date-picker {
            border: 1px solid #ebebeb;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        
        .date-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        
        .date-field {
            padding: 16px;
            border-right: 1px solid #ebebeb;
        }
        
        .date-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .date-field input,
        .guest-select select {
            width: 100%;
            border: none;
            padding: 8px 0;
            font-family: inherit;
            font-size: 14px;
            background: transparent;
        }
        
        .guest-select {
            padding: 16px;
            border-top: 1px solid #ebebeb;
        }
        
        .guest-select label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .guest-select select {
            width: 100%;
            border: none;
            padding: 8px 0;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-reserve {
            width: 100%;
            background: #ff385c;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            margin: 16px 0;
        }
        
        .btn-reserve:hover {
            background: #e31c5f;
        }
        
        .no-charge {
            text-align: center;
            font-size: 12px;
            color: #717171;
            margin-bottom: 24px;
        }
        
        .price-breakdown {
            margin: 16px 0;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 12px 0;
            font-size: 14px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #ebebeb;
            font-weight: 600;
            font-size: 16px;
        }
        
        hr {
            margin: 24px 0;
            border: none;
            border-top: 1px solid #ebebeb;
        }
        
        /* Success Message */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .success-message a {
            color: #155724;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .detail-two-column {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .detail-gallery {
                grid-template-columns: 1fr;
            }
            
            .detail-small-images {
                display: none;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .amenities-grid {
                grid-template-columns: 1fr;
            }
            
            .booking-card {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">airbnb</a>
            <div class="nav-links">
                <a href="add-listing.php">Become a host</a>
                <?php if(isLoggedIn()): ?>
                    <a href="my-bookings.php">My trips</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="detail-container">
        <!-- Success Message -->
        <?php if(isset($_GET['booked'])): ?>
            <div class="success-message">
                <span>✓ Booking confirmed successfully!</span>
                <a href="my-bookings.php">View my trips →</a>
            </div>
        <?php endif; ?>
        
        <!-- Header -->
        <div class="detail-header">
            <h1 class="detail-title"><?= htmlspecialchars($listing['title']) ?></h1>
            
            <div class="detail-meta">
                <div class="detail-rating">
                    <span>⭐</span>
                    <span><?= $avgRating ?></span>
                    <span>·</span>
                    <a href="#"><?= $reviewsCount ?> reviews</a>
                    <span>·</span>
                    <a href="#"><?= htmlspecialchars($listing['location']) ?></a>
                </div>
                <div class="detail-actions">
                    <a href="#">🔗 Share</a>
                    <a href="#">❤️ Save</a>
                </div>
            </div>
        </div>
        
        <!-- Image Gallery -->
        <div class="detail-gallery">
            <div class="detail-main-image">
                <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>">
            </div>
            <div class="detail-small-images">
                <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="Property image 1">
                <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="Property image 2">
                <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="Property image 3">
                <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="Property image 4">
            </div>
        </div>
        
        <!-- Two Column Layout -->
        <div class="detail-two-column">
            <!-- Left Column -->
            <div class="property-info">
                <!-- Host Section -->
                <div class="host-section">
                    <div class="host-info">
                        <h2>Entire home hosted by <?= htmlspecialchars($listing['host_name']) ?></h2>
                        <div class="host-details">
                            <?= $listing['max_guests'] ?> guests · 
                            <?= $listing['bedrooms'] ?> bedroom · 
                            <?= $listing['beds'] ?> beds · 
                            <?= $listing['bathrooms'] ?> bathroom
                        </div>
                    </div>
                    <div class="host-avatar">
                        <?= strtoupper(substr($listing['host_name'], 0, 1)) ?>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🅿️</div>
                        <div class="feature-text">
                            <h4>Free parking</h4>
                            <p>Free parking available on premises</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <div class="feature-text">
                            <h4>Great communication</h4>
                            <p>Quick responses to all messages</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">✅</div>
                        <div class="feature-text">
                            <h4>Self check-in</h4>
                            <p>Check yourself in with the smartlock</p>
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🧹</div>
                        <div class="feature-text">
                            <h4>Enhanced Clean</h4>
                            <p>Committed to Airbnb's cleaning process</p>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="description-section">
                    <h3>About this place</h3>
                    <div class="description-text">
                        <?= nl2br(htmlspecialchars($listing['description'])) ?>
                    </div>
                </div>
                
                <!-- Amenities -->
                <div class="amenities-section">
                    <h3>What this place offers</h3>
                    <div class="amenities-grid">
                        <div class="amenity-item">📶 Wifi</div>
                        <div class="amenity-item">🧺 Washer</div>
                        <div class="amenity-item">🌳 Backyard</div>
                        <div class="amenity-item">💨 Hair dryer</div>
                        <div class="amenity-item">🚗 Free parking</div>
                        <div class="amenity-item">☕ Breakfast</div>
                        <div class="amenity-item">🧳 Luggage dropoff</div>
                        <div class="amenity-item">🛋️ Patio or balcony</div>
                        <div class="amenity-item">❄️ Air conditioning</div>
                        <div class="amenity-item">📺 TV</div>
                    </div>
                </div>
                
                <!-- Reviews -->
                <?php if($reviewsCount > 0): ?>
                <div class="reviews-section">
                    <h3><?= $reviewsCount ?> reviews</h3>
                    <?php foreach(array_slice($reviews, 0, 5) as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <span class="reviewer-name"><?= htmlspecialchars($review['user_name']) ?></span>
                                <div class="review-rating">⭐ <?= number_format($review['rating'], 1) ?></div>
                            </div>
                            <div class="review-date"><?= date('M Y', strtotime($review['created_at'])) ?></div>
                            <div class="review-comment"><?= htmlspecialchars($review['comment']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - Booking Card -->
            <div class="booking-card">
                <div class="booking-price">
                    <div class="price">₹<?= number_format($listing['price_per_night']) ?> <span>night</span></div>
                    <div class="rating-summary">⭐ <?= $avgRating ?></div>
                </div>
                
                <form action="book.php" method="POST" id="bookingForm">
                    <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                    
                    <div class="booking-form">
                        <div class="date-picker">
                            <div class="date-row">
                                <div class="date-field">
                                    <label>CHECK-IN</label>
                                    <input type="date" name="check_in" id="check_in" required>
                                </div>
                                <div class="date-field">
                                    <label>CHECKOUT</label>
                                    <input type="date" name="check_out" id="check_out" required>
                                </div>
                            </div>
                            <div class="guest-select">
                                <label>GUESTS</label>
                                <select name="guests" id="guests">
                                    <?php for($i=1; $i<=min(16, $listing['max_guests']); $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> <?= $i == 1 ? 'guest' : 'guests' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-reserve">Reserve</button>
                        <div class="no-charge">You won't be charged yet</div>
                    </div>
                </form>
                
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>₹<?= number_format($listing['price_per_night']) ?> x <span id="nightsCount">0</span> nights</span>
                        <span id="subtotal">₹0</span>
                    </div>
                    <div class="price-row">
                        <span>Airbnb service fee</span>
                        <span>₹14</span>
                    </div>
                    <div class="total-row">
                        <strong>Total</strong>
                        <strong id="total">₹0</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const pricePerNight = <?= $listing['price_per_night'] ?>;
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const guests = document.getElementById('guests');
        
        // Set min dates
        const today = new Date().toISOString().split('T')[0];
        checkIn.min = today;
        checkOut.min = today;
        
        function updatePricing() {
            let nights = 0;
            if (checkIn.value && checkOut.value) {
                const start = new Date(checkIn.value);
                const end = new Date(checkOut.value);
                if (end > start) {
                    nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                }
            }
            
            const subtotal = nights * pricePerNight;
            const total = subtotal + 14;
            
            document.getElementById('nightsCount').innerText = nights;
            document.getElementById('subtotal').innerText = '₹' + subtotal.toLocaleString('en-IN');
            document.getElementById('total').innerText = '₹' + total.toLocaleString('en-IN');
        }
        
        checkIn.addEventListener('change', updatePricing);
        checkOut.addEventListener('change', updatePricing);
        guests.addEventListener('change', updatePricing);
        updatePricing();
    </script>
</body>
</html>
