<?php
/* SESSION SAFE START */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* HANDLE PASSWORD CHANGE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } 
        elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } 
        elseif ($new_password !== $confirm_password) {
            $error = "New password and confirmation do not match.";
        } 
        else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);

            // Set success message and redirect
            $_SESSION['success_message'] = "Password changed successfully!";
            header("Location: profile.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "An error occurred: " . $e->getMessage();
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

.container {
    max-width: 620px;
    margin: auto;
    padding: 15px;
}

.password-box {
    background: #fff;
    padding: 35px 25px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    margin-top: 30px;
}

.password-box h2 {
    margin-bottom: 30px;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1f2937;
}

/* ERROR MESSAGE */
.error {
    background: #fee2e2;
    color: #ef4444;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* FORM STYLING */
.form-group {
    margin-bottom: 24px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}
.form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #00aaff;
    box-shadow: 0 0 0 4px rgba(0, 170, 255, 0.12);
}

/* BUTTONS */
.btn {
    padding: 15px 28px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}
.btn-primary {
    background: #00aaff;
    color: #fff;
}
.btn-primary:hover {
    background: #0088cc;
}
.btn-secondary {
    background: #1f2937;
    color: #fff;
    text-decoration: none;
}
.btn-secondary:hover {
    background: #111827;
}

/* Mobile Optimizations */
@media (max-width: 768px) {
    .password-box {
        padding: 28px 20px;
        margin-top: 20px;
    }
    .password-box h2 {
        font-size: 26px;
    }
    .btn {
        width: 100%;
        justify-content: center;
        padding: 16px;
    }
    .btn-secondary {
        margin-top: 12px;
        margin-left: 0 !important;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 12px;
    }
}
</style>

<div class="container">
    <div class="password-box">
        <h2>
            <i class="fa-solid fa-key" style="color:#00aaff;"></i> 
            Change Password
        </h2>

        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" 
                       id="current_password" 
                       name="current_password" 
                       required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       minlength="6" 
                       required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       minlength="6" 
                       required>
            </div>

            <br><br>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Password
            </button>

            <a href="profile.php" class="btn btn-secondary" style="margin-left: 12px;">
                <i class="fa-solid fa-arrow-left"></i> Cancel
            </a>
        </form>
    </div>
</div>

<?php include "inc/footer.php"; ?>
