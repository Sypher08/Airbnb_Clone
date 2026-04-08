<?php 
require 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO listings (user_id, title, description, location, price_per_night, max_guests, bedrooms, beds, bathrooms, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['location'],
            $_POST['price'],
            $_POST['max_guests'],
            $_POST['bedrooms'],
            $_POST['beds'],
            $_POST['bathrooms'],
            $_POST['image_url']
        ]);
        header('Location: index.php?added=1');
        exit;
    } catch (PDOException $e) {
        $error = "Failed to add listing: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Host - Airbnb Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
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
        }
        
        .nav-links a {
            text-decoration: none;
            color: #222;
            font-size: 14px;
        }
        
        .form-container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .form-container h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #222;
        }
        
        .form-container p {
            color: #717171;
            margin-bottom: 32px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #222;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff385c;
            box-shadow: 0 0 0 2px rgba(255,56,92,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            width: 100%;
            background: #ff385c;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            margin-top: 16px;
        }
        
        .btn-submit:hover {
            background: #e31c5f;
        }
        
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .form-container {
                margin: 20px;
                padding: 24px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">airbnb</a>
            <div class="nav-links">
                <a href="add-listing.php" style="color: #ff385c;">Become a host</a>
                <a href="my-bookings.php">My trips</a>
                <a href="ai-assistant.php">🤖 AI Assistant</a>
                <a href="profile.php">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="form-container">
        <h1>List your space</h1>
        <p>Share your property with travelers worldwide</p>
        
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Property title</label>
                <input type="text" name="title" placeholder="e.g., Cozy Beachfront Villa" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" placeholder="Describe your property..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="City, Country" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Price per night (₹)</label>
                    <input type="number" name="price" placeholder="₹" required>
                </div>
                
                <div class="form-group">
                    <label>Max guests</label>
                    <input type="number" name="max_guests" placeholder="Max guests" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Bedrooms</label>
                    <input type="number" name="bedrooms" placeholder="Bedrooms" required>
                </div>
                
                <div class="form-group">
                    <label>Beds</label>
                    <input type="number" name="beds" placeholder="Beds" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Bathrooms</label>
                    <input type="number" step="0.5" name="bathrooms" placeholder="Bathrooms" required>
                </div>
                
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image_url" placeholder="https://example.com/image.jpg" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Create listing</button>
        </form>
    </div>
    
    <?php include 'ai-widget.php'; ?>
</body>
</html>
