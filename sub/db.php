# 2. DB.PHP
<?php
// Core Infrastructure Database Connector Config Node
$host = "sql207.infinityfree.com";
$user = "if0_42273705";
$pass = "MWJvmCfpNDKo";
$dbname = "if0_42273705_ticket";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database engine core connection breakdown: " . $conn->connect_error);
}

// Fetch Global Key-Value Site Text Configurations
function getSetting($key, $conn) {
    $stmt = $conn->prepare("SELECT meta_value FROM site_settings WHERE meta_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? htmlspecialchars($res['meta_value']) : '';
}
?>
