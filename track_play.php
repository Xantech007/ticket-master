session_start();
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = $_SESSION['user_id'];
$game_id = $_POST['game_id'];

$stmt = $conn->prepare("
    INSERT INTO play_sessions (user_id, game_id, start_time)
    VALUES (?, ?, NOW())
");
$stmt->execute([$user_id, $game_id]);

echo json_encode([
    'status' => 'success',
    'session_id' => $conn->lastInsertId()
]);
