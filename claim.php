session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

// Get pending balance
$stmt = $conn->prepare("SELECT pending_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$amount = $user['pending_balance'];

if ($amount <= 0) {
    echo json_encode(['status' => 'empty']);
    exit;
}

// Move to main balance
$stmt = $conn->prepare("
    UPDATE users 
    SET balance = balance + ?, pending_balance = 0
    WHERE id = ?
");
$stmt->execute([$amount, $user_id]);

// Mark sessions as claimed
$stmt = $conn->prepare("
    UPDATE play_sessions 
    SET claimed = 1
    WHERE user_id = ? AND claimed = 0
");
$stmt->execute([$user_id]);

echo json_encode([
    'status' => 'success',
    'amount' => $amount
]);
