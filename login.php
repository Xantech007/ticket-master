<?php
/* SESSION SAFE START */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT id, password, balance FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['balance'] = $user['balance'] ?? 0;
            
            header("Location: index.php");
            exit;
        } else {
            $message = '<div class="error">
                <i class="fa-solid fa-circle-exclamation"></i> Invalid email or password
            </div>';
        }
    } else {
        $message = '<div class="error">
            <i class="fa-solid fa-circle-exclamation"></i> All fields are required
        </div>';
    }
}
?>

<?php include "inc/header.php"; ?>
<?php include "inc/navbar.php"; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Mobile-First Design */
* {
    box-sizing: border-box;
}

.auth-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    padding: 20px 15px;
}

.auth-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    padding: 35px 25px;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* Logo */
.logo img {
    width: 68px;
    height: 68px;
    margin-bottom: 15px;
}

/* Title */
.auth-card h2 {
    font-size: 28px;
    margin-bottom: 6px;
    color: #1f2937;
}

.subtitle {
    color: #64748b;
    font-size: 15.5px;
    margin-bottom: 25px;
}

/* Error Message */
.error {
    background: #fee2e2;
    color: #ef4444;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-align: left;
}

/* Input Groups */
.input-group {
    position: relative;
    margin-bottom: 18px;
}
.input-group i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 18px;
    z-index: 2;
}
.input-group input {
    width: 100%;
    padding: 14px 14px 14px 48px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fff;
}
.input-group input:focus {
    outline: none;
    border-color: #00aaff;
    box-shadow: 0 0 0 4px rgba(0, 170, 255, 0.12);
}

/* Button */
.btn {
    width: 100%;
    padding: 15px;
    margin-top: 10px;
    border: none;
    border-radius: 10px;
    background: #00aaff;
    color: #fff;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn:hover {
    background: #0088cc;
    transform: translateY(-2px);
}

/* Footer Text */
.bottom-text {
    margin-top: 25px;
    font-size: 15px;
    color: #64748b;
}
.bottom-text a {
    color: #00aaff;
    font-weight: 500;
    text-decoration: none;
}
.bottom-text a:hover {
    text-decoration: underline;
}

/* Mobile Optimizations */
@media (max-width: 480px) {
    .auth-card {
        padding: 30px 20px;
        border-radius: 14px;
    }
    .auth-card h2 {
        font-size: 26px;
    }
    .input-group input {
        font-size: 16px; /* Prevents zoom on iOS */
        padding: 16px 16px 16px 50px;
    }
    .btn {
        padding: 16px;
        font-size: 17px;
    }
}
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo -->
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo">
        </div>

        <h2>Welcome Back</h2>
        <div class="subtitle">Login to continue playing & earning real money</div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="input-group">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-right-to-bracket"></i> 
                Sign In
            </button>
        </form>

        <div class="bottom-text">
            Don’t have an account? 
            <a href="register.php">Create Free Account</a>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
