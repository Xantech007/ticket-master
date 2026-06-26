<?php
/* SESSION SAFE START */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/database.php";
include "inc/countries.php";

/* CONNECT DB */
$db = new Database();
$conn = $db->connect();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT full_name, username, email, phone, gender, address, country FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/* SAFE DEFAULTS */
$user = array_merge([
    'full_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'gender' => '',
    'address' => '',
    'country' => ''
], $user ?: []);

/* UPDATE PROFILE */
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $phone     = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $gender    = $_POST['gender'] ?? null;
    $address   = trim($_POST['address']);
    $country   = $_POST['country'] ?? null;

    try {
        $stmt = $conn->prepare("
            UPDATE users 
            SET full_name=?, username=?, email=?, phone=?, gender=?, address=?, country=?
            WHERE id=?
        ");
        $stmt->execute([
            $full_name, $username, $email, $phone, $gender, $address, $country, $user_id
        ]);

        $success = "Profile updated successfully!";

        /* REFRESH USER DATA */
        $stmt = $conn->prepare("SELECT full_name, username, email, phone, gender, address, country FROM users WHERE id=?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user = array_merge([
            'full_name' => '', 'username' => '', 'email' => '', 'phone' => '',
            'gender' => '', 'address' => '', 'country' => ''
        ], $user ?: []);

    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
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
    max-width: 900px;
    margin: auto;
    padding: 15px;
}

.profile-box {
    background: #fff;
    padding: 30px 25px;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    margin-top: 30px;
}

.profile-box h2 {
    margin-bottom: 25px;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* SUCCESS / ERROR MESSAGES */
.success {
    background: #d1fae5;
    color: #10b981;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}
.error {
    background: #fee2e2;
    color: #ef4444;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* FORM STYLING */
.form-group {
    margin-bottom: 22px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 14px 16px;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #00aaff;
    box-shadow: 0 0 0 4px rgba(0, 170, 255, 0.12);
}

/* BUTTONS */
.btn {
    padding: 14px 26px;
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
.btn-dark {
    background: #1f2937;
    color: #fff;
}
.btn-dark:hover {
    background: #111827;
}

/* Mobile Optimizations */
@media (max-width: 768px) {
    .profile-box {
        padding: 25px 20px;
        margin-top: 20px;
    }
    .profile-box h2 {
        font-size: 26px;
    }
    .btn {
        width: 100%;
        justify-content: center;
        padding: 16px;
    }
    .btn-dark {
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
    <div class="profile-box">

        <!-- Success message from password change -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success">
                <i class="fa-solid fa-check-circle"></i> 
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <i class="fa-solid fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <h2><i class="fa-solid fa-user" style="color:#00aaff;"></i> My Profile</h2>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php if($user['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if($user['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Other" <?php if($user['gender'] === 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Country</label>
                <select name="country">
                    <option value="">Select Country</option>
                    <?php foreach($countries as $c): ?>
                        <option value="<?php echo htmlspecialchars($c); ?>" 
                            <?php if($user['country'] === $c) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <br>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Save Changes
            </button>

            <a href="change-password.php" class="btn btn-dark" style="margin-left:12px;">
                <i class="fa-solid fa-key"></i> Change Password
            </a>
        </form>
    </div>
</div>

<?php include "inc/footer.php"; ?>
