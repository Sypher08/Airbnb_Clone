<?php 
require 'config.php';

// Build search query
$where = [];
$params = [];

// Search by location
if (!empty($_GET['location'])) {
    $where[] = "location LIKE ?";
    $params[] = "%{$_GET['location']}%";
}

// Price range
if (!empty($_GET['min_price'])) {
    $where[] = "price_per_night >= ?";
    $params[] = (int)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where[] = "price_per_night <= ?";
    $params[] = (int)$_GET['max_price'];
}

// Guests
if (!empty($_GET['guests'])) {
    $where[] = "max_guests >= ?";
    $params[] = (int)$_GET['guests'];
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM listings $whereClause ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airbnb Clone - Find your stay</title>
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
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #ebebeb;
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
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
        
        .search-section {
            background: white;
            padding: 20px 0;
            border-bottom: 1px solid #ebebeb;
        }
        
        .search-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        .search-form {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .search-group {
            flex: 1;
            min-width: 150px;
        }
        
        .search-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #222;
        }
        
        .search-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            font-size: 14px;
        }
        
        .search-btn {
            background: #ff385c;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .filters-bar {
            background: white;
            padding: 16px 0;
            border-bottom: 1px solid #ebebeb;
        }
        
        .filters-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .filter-chip {
            padding: 8px 16px;
            border: 1px solid #ebebeb;
            border-radius: 30px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            color: #222;
            display: inline-block;
        }
        
        .main-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
        }
        
        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .listing-card {
            text-decoration: none;
            color: inherit;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s;
        }
        
        .listing-card:hover {
            transform: translateY(-4px);
        }
        
        .listing-image {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 12px;
        }
        
        .listing-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .guest-favorite-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .listing-info {
            padding: 12px 0;
        }
        
        .listing-title {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .listing-location {
            font-size: 13px;
            color: #717171;
            margin-bottom: 4px;
        }
        
        .listing-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 8px 0;
            font-size: 13px;
        }
        
        .listing-price {
            font-weight: 600;
            font-size: 15px;
        }
        
        .results-count {
            margin-bottom: 24px;
            font-size: 14px;
            color: #717171;
        }
        
        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            .search-form {
                flex-direction: column;
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
    
    <div class="search-section">
        <div class="search-container">
            <form method="GET" class="search-form">
                <div class="search-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="Search destinations" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                </div>
                <div class="search-group">
                    <label>Check in</label>
                    <input type="date" name="check_in" value="<?= htmlspecialchars($_GET['check_in'] ?? '') ?>">
                </div>
                <div class="search-group">
                    <label>Check out</label>
                    <input type="date" name="check_out" value="<?= htmlspecialchars($_GET['check_out'] ?? '') ?>">
                </div>
                <div class="search-group">
                    <label>Guests</label>
                    <input type="number" name="guests" placeholder="1 guest" value="<?= htmlspecialchars($_GET['guests'] ?? '') ?>">
                </div>
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>
    </div>
    
    <div class="filters-bar">
        <div class="filters-container">
            <a href="?guest_favorite=1" class="filter-chip">⭐ Guest favourite</a>
            <a href="?instant_book=1" class="filter-chip">⚡ Instant Book</a>
            <a href="?free_cancellation=1" class="filter-chip">✅ Free cancellation</a>
        </div>
    </div>
    
    <div class="main-container">
        <div class="sidebar">
            <h3>Filters</h3>
        </div>
        
        <div>
            <div class="results-count">
                <?= count($listings) ?> homes found
            </div>
            
            <div class="listings-grid">
                <?php foreach($listings as $listing): ?>
                    <a href="detail.php?id=<?= $listing['id'] ?>" class="listing-card">
                        <div class="listing-image">
                            <img src="<?= htmlspecialchars($listing['image_url']) ?>" alt="<?= htmlspecialchars($listing['title']) ?>">
                            <?php if($listing['is_guest_favorite'] ?? false): ?>
                                <div class="guest-favorite-badge">⭐ Guest favourite</div>
                            <?php endif; ?>
                        </div>
                        <div class="listing-info">
                            <div class="listing-title"><?= htmlspecialchars($listing['title']) ?></div>
                            <div class="listing-location"><?= htmlspecialchars($listing['location']) ?></div>
                            <div class="listing-rating">
                                <span>⭐</span>
                                <span><?= number_format($listing['rating'] ?? 4.5, 2) ?></span>
                                <span>(<?= $listing['review_count'] ?? 0 ?>)</span>
                            </div>
                            <div class="listing-price">
                                ₹<?= number_format($listing['price_per_night']) ?> <span>night</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php include "ai-widget.php"; ?>
</body>
</html>
