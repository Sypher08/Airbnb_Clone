<?php require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_phone'] = $user['phone'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Airbnb Clone</title>
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
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ff385c;
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
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #717171;
        }
        
        .register-link a {
            color: #ff385c;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>Welcome back</h2>
        <p class="auth-subtitle">Log in to your account</p>
        
        <?php if(isset($_GET['registered'])) echo "<div class='success'>✓ Registration successful! Please login.</div>"; ?>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-auth">Log in</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
    </div>
</body>
</html>
