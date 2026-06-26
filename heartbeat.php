<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) exit;

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];
$session_id = $_POST['session_id'] ?? null;

if (!$session_id) exit;

// Get session + reward
$stmt = $conn->prepare("
    SELECT ps.*, g.reward_per_min 
    FROM play_sessions ps
    JOIN games g ON g.id = ps.game_id
    WHERE ps.id = ? AND ps.user_id = ?
");
$stmt->execute([$session_id, $user_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session || $session['end_time']) exit;

// Calculate time since last ping
$last = $session['last_ping'] 
    ? strtotime($session['last_ping']) 
    : strtotime($session['start_time']);

$now = time();
$seconds = $now - $last;

// Prevent abuse (max 15s per ping)
$seconds = min($seconds, 15);

$minutes = $seconds / 60;
$earned = $minutes * $session['reward_per_min'];

// Update session
$stmt = $conn->prepare("
    UPDATE play_sessions 
    SET earned = earned + ?, 
        last_ping = NOW()
    WHERE id = ?
");
$stmt->execute([$earned, $session_id]);

// Add to pending balance
$stmt = $conn->prepare("
    UPDATE users 
    SET pending_balance = pending_balance + ?
    WHERE id = ?
");
$stmt->execute([$earned, $user_id]);

echo json_encode([
    'earned' => $earned
]);
