<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$db = new Database();
$conn = $db->connect();

$settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

$site_name = $settings['site_name'];
$currency = $settings['currency'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($site_name); ?></title>

<link rel="icon" type="image/png" href="/assets/favicon.png">
<link rel="shortcut icon" href="/assets/favicon.png">

<link rel="stylesheet" href="/assets/css/theme.css">
</head>
<body>
