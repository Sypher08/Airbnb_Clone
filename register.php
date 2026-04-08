<?php require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $password]);
        header('Location: login.php?registered=1');
        exit;
    } catch(PDOException $e) {
        $error = "Email already exists!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Airbnb Clone</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .auth-container h2 {
            font-size: 28px;
            margin-bottom: 8px;
            color: #222;
        }
        
        .auth-subtitle {
            color: #717171;
            margin-bottom: 32px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: #222;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ebebeb;
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff385c;
            box-shadow: 0 0 0 2px rgba(255,56,92,0.1);
        }
        
        .btn-auth {
            width: 100%;
            background: #ff385c;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 12px;
        }
        
        .btn-auth:hover {
            background: #e31c5f;
        }
        
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #717171;
        }
        
        .login-link a {
            color: #ff385c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .phone-hint {
            font-size: 12px;
            color: #717171;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Create account</h2>
        <p class="auth-subtitle">Join the Airbnb community</p>
        
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full name</label>
                <input type="text" name="name" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            
            <div class="form-group">
                <label>Phone number</label>
                <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                <div class="phone-hint">📱 We'll send booking confirmations via SMS</div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>
            
            <button type="submit" class="btn-auth">Sign up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>
