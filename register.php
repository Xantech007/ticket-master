<?php
session_start();
require_once "config/db.php";

$pdo = (new Database())->connect();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$full_name = trim($_POST["full_name"]);
$email = trim($_POST["email"]);
$country = trim($_POST["country"]);
$country_code = trim($_POST["country_code"]);
$phone = trim($_POST["phone"]);
$password = $_POST["password"];
$confirm = $_POST["confirm_password"];

/* -------------------------
   VALIDATION
--------------------------*/
if (!$full_name || !$email || !$country || !$phone || !$password) {
    die("All fields are required");
}

if ($password !== $confirm) {
    die("Passwords do not match");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email");
}

/* -------------------------
   CHECK EXISTING USER
--------------------------*/
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    die("Email already exists");
}

/* -------------------------
   HASH PASSWORD
--------------------------*/
$hash = password_hash($password, PASSWORD_BCRYPT);

/* -------------------------
   INSERT USER
--------------------------*/
$stmt = $pdo->prepare("
    INSERT INTO users 
    (full_name, email, country, country_code, phone, password_hash)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $full_name,
    $email,
    $country,
    $country_code,
    $phone,
    $hash
]);

$user_id = $pdo->lastInsertId();

/* -------------------------
   AUTO LOGIN
--------------------------*/
$_SESSION["user_id"] = $user_id;
$_SESSION["email"] = $email;

/* -------------------------
   REDIRECT LOGIC (RETURN TO PREVIOUS PAGE)
--------------------------*/
$redirect = $_SESSION["redirect_after_auth"] ?? "booking.php";
unset($_SESSION["redirect_after_auth"]);

header("Location: $redirect");
exit;
