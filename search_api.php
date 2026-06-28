<?php
// search_api.php
require_once 'db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// Filter Execution 1: Scan Artist Master Layout Names
$stmt = $pdo->prepare("SELECT id, name AS label, 'artist' AS type FROM artists WHERE name LIKE ? LIMIT 4");
$stmt->execute(["%$query%"]);
$results = array_merge($results, $stmt->fetchAll());

// Filter Execution 2: Scan Unique Active Show Presentations
$stmt = $pdo->prepare("SELECT id, title AS label, 'event' AS type FROM events WHERE title LIKE ? LIMIT 4");
$stmt->execute(["%$query%"]);
$results = array_merge($results, $stmt->fetchAll());

// Filter Execution 3: Scan Geographic Specific Venues
$stmt = $pdo->prepare("SELECT DISTINCT 0 AS id, venue AS label, 'venue' AS type FROM events WHERE venue LIKE ? LIMIT 4");
$stmt->execute(["%$query%"]);
$results = array_merge($results, $stmt->fetchAll());

header('Content-Type: application/json');
echo json_encode($results);
exit;
?>
