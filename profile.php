<?php
require 'config.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_phone'])) {
        $phone = $_POST['phone'];
        $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
        if ($stmt->execute([$phone, $_SESSION['user_id']])) {
            $_SESSION['user_phone'] = $phone;
            $success = "Phone number updated successfully!";
        } else {
            $error = "Failed to update phone number.";
        }
    }
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Airbnb Clone</title>
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
        
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 24px;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #ebebeb;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            background: #ff385c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            font-weight: 600;
        }
        
        .profile-info h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .profile-info p {
            color: #717171;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff385c;
        }
        
        .btn-save {
            background: #ff385c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-save:hover {
            background: #e31c5f;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-text {
            font-size: 13px;
            color: #717171;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">airbnb</a>
            <div class="nav-links">
                <a href="add-listing.php">Become a host</a>
                <a href="my-bookings.php">My trips</a>
                <a href="ai-assistant.php">🤖 AI Assistant</a>
                <a href="profile.php" style="color: #ff385c;">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($user['name']) ?></h1>
                    <p>Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            
            <?php if($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <div class="info-text">Email cannot be changed</div>
                </div>
                
                <div class="form-group">
                    <label>Phone number</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+91 98765 43210" required>
                    <div class="info-text">📱 We'll send booking confirmations via SMS</div>
                </div>
                
                <button type="submit" name="update_phone" class="btn-save">Update phone number</button>
            </form>
        </div>
    </div>
    
    <?php include 'ai-widget.php'; ?>
</body>
</html>
