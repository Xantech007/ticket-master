<?php
// event.php
require_once 'db.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$headline_title = "Scheduled Engagements";
$events = [];

if ($type === 'artist') {
    $stmt = $pdo->prepare("SELECT e.*, a.name AS artist_name FROM events e JOIN artists a ON e.artist_id = a.id WHERE e.artist_id = ? ORDER BY e.event_date ASC");
    $stmt->execute([$id]);
    $events = $stmt->fetchAll();
    if(!empty($events)) $headline_title = "Tour Run: " . htmlspecialchars($events[0]['artist_name']);
} elseif ($type === 'venue') {
    $stmt = $pdo->prepare("SELECT e.*, a.name AS artist_name FROM events e JOIN artists a ON e.artist_id = a.id WHERE e.venue = ? ORDER BY e.event_date ASC");
    $stmt->execute([$query]);
    $events = $stmt->fetchAll();
    $headline_title = "Production at: " . htmlspecialchars($query);
} elseif ($type === 'event') {
    header("Location: section_selection.php?event_id=" . $id);
    exit;
} else {
    $stmt = $pdo->query("SELECT e.*, a.name AS artist_name FROM events e JOIN artists a ON e.artist_id = a.id ORDER BY e.event_date ASC");
    $events = $stmt->fetchAll();
    $headline_title = "All Registered Event Listings";
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include "inc/head.php"; ?>
<body class="bg-gray-50 text-gray-900">
<?php include "inc/navbar1.php"; ?>
<?php include "inc/navbar2.php"; ?>

<div class="max-w-6xl mx-auto px-4 py-12">
    <h2 class="text-3xl font-black text-gray-900 mb-8 border-b-4 border-blue-600 pb-3 inline-block"><?= $headline_title; ?></h2>
    
    <?php if(empty($events)): ?>
        <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-100 text-center text-gray-500">
            No live bookings or shows correspond with your selection parameters currently.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($events as $e): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 flex flex-col justify-between">
                    <div class="p-6">
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md">Verified Asset</span>
                        <h3 class="text-xl font-bold text-gray-900 mt-3"><?= htmlspecialchars($e['artist_name']); ?></h3>
                        <p class="text-sm font-semibold text-gray-700 mt-1"><?= htmlspecialchars($e['title']); ?></p>
                        <p class="text-xs text-gray-500 mt-4"><i class="fas fa-building"></i> <?= htmlspecialchars($e['venue']); ?></p>
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-clock"></i> <?= date('F d, Y @ h:i A', strtotime($e['event_date'])); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 border-t border-gray-100">
                        <a href="section_selection.php?event_id=<?= $e['id']; ?>" class="block w-full text-center bg-[#024DDF] hover:bg-blue-800 text-white font-bold py-2.5 px-4 rounded-lg transition-colors text-sm shadow-sm">
                            Analyze Available Seats
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "inc/footer.php"; ?>
