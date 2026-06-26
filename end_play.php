session_start();
require_once "config/database.php";

if (!isset($_POST['session_id'])) exit;

$session_id = $_POST['session_id'];

// Get session + game reward
$stmt = $conn->prepare("
    SELECT ps.*, g.reward_per_min 
    FROM play_sessions ps
    JOIN games g ON g.id = ps.game_id
    WHERE ps.id = ?
");
$stmt->execute([$session_id]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session || $session['end_time']) exit;

// Calculate duration
$start = strtotime($session['start_time']);
$end = time();

$duration = $end - $start; // seconds
$minutes = $duration / 60;

// Calculate earnings
$earned = $minutes * $session['reward_per_min'];

// Update session
$stmt = $conn->prepare("
    UPDATE play_sessions 
    SET end_time = NOW(),
        duration = ?,
        earned = ?
    WHERE id = ?
");
$stmt->execute([$duration, $earned, $session_id]);

// Add to pending balance
$stmt = $conn->prepare("
    UPDATE users 
    SET pending_balance = pending_balance + ?
    WHERE id = ?
");
$stmt->execute([$earned, $session['user_id']]);
