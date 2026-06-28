<?php
// section_selection.php
require_once 'db.php';
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

$stmt = $pdo->prepare("SELECT e.*, a.name AS artist_name FROM events e JOIN artists a ON e.artist_id = a.id WHERE e.id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("<div class='p-12 text-center font-bold text-red-600'>Target Event context not synchronized inside data framework.</div>");
}

$secStmt = $pdo->prepare("SELECT * FROM sections WHERE event_id = ?");
$secStmt->execute([$event_id]);
$sections = $secStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php include "inc/head.php"; ?>
<body class="bg-gray-100 text-gray-900">
<?php include "inc/navbar1.php"; ?>
<?php include "inc/navbar2.php"; ?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 p-8 text-white">
            <h2 class="text-3xl font-black"><?= htmlspecialchars($event['artist_name']); ?></h2>
            <p class="text-lg font-medium opacity-90 mt-1"><?= htmlspecialchars($event['title']); ?></p>
            <div class="flex flex-wrap gap-4 mt-4 text-xs font-semibold opacity-75">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue']); ?></span>
                <span><i class="fas fa-calendar-day"></i> <?= date('l, M d, Y', strtotime($event['event_date'])); ?></span>
            </div>
        </div>

        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 text-center">Stadium Structural Map Layout Blueprint</h3>
            <img src="uploads/<?= htmlspecialchars($event['stadium_map']); ?>" onerror="this.src='https://picsum.photos/id/101/800/400';" class="max-w-full h-auto mx-auto rounded-xl shadow-md border border-gray-200" alt="Stadium Map Grid">
        </div>

        <div class="p-6 space-y-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Select Pricing Tier or Section Block</h3>
            
            <?php if(empty($sections)): ?>
                <p class="text-center text-gray-400 py-6">No sections allocated for this event venue execution framework yet.</p>
            <?php else: ?>
                <?php foreach($sections as $sec): ?>
                    <div class="border border-gray-200 rounded-xl p-5 shadow-sm bg-white hover:border-blue-400 transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-3 mb-4">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($sec['section_name']); ?></h4>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">
                                    <?= ($sec['is_ga'] == 1) ? 'General Admission Placement' : 'Assigned Seating Grid Structure'; ?>
                                </span>
                            </div>
                            <?php if($sec['is_ga'] == 1): ?>
                                <span class="text-xl font-black text-blue-600">$<?= number_format($sec['ga_price'], 2); ?> <span class="text-xs text-gray-400 font-normal">/ ticket</span></span>
                            <?php endif; ?>
                        </div>

                        <?php if($sec['is_ga'] == 1): ?>
                            <form action="cart_stub.php" method="POST" class="flex flex-wrap items-center gap-4">
                                <input type="hidden" name="section_id" value="<?= $sec['id']; ?>">
                                <input type="hidden" name="booking_mode" value="ga">
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-bold text-gray-500 uppercase">Quantity:</label>
                                    <input type="number" name="ticket_quantity" min="1" max="<?= $sec['ga_available_tickets']; ?>" value="1" class="w-20 border border-gray-300 rounded-lg p-2 font-bold text-center outline-none focus:border-blue-600">
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold text-sm px-6 py-2.5 rounded-lg shadow transition-colors ml-auto">
                                    Add Tickets to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-xs text-gray-400 font-bold uppercase mb-3">Click on an available grid identifier sequence to select:</p>
                                <?php
                                $seatStmt = $pdo->prepare("SELECT * FROM seats WHERE section_id = ? ORDER BY row_name, CAST(seat_number AS UNSIGNED)");
                                $seatStmt->execute([$sec['id']]);
                                $seats = $seatStmt->fetchAll();

                                $currentRow = '';
                                if(empty($seats)) {
                                    echo "<p class='text-xs text-gray-400 italic'>No individual seat coordinates appended to this sector layout block yet.</p>";
                                } else {
                                    foreach($seats as $seat) {
                                        if($currentRow !== $seat['row_name']) {
                                            $currentRow = $seat['row_name'];
                                            echo "<div class='my-2 flex flex-wrap items-center gap-1 text-sm text-gray-700'><span class='w-16 block font-black text-xs text-gray-500 uppercase'>Row ".htmlspecialchars($currentRow).":</span>";
                                        }
                                        
                                        if($seat['is_booked'] == 1) {
                                            echo "<span class='bg-red-200 text-red-700 text-xs px-2 py-1 rounded cursor-not-allowed font-bold opacity-60' title='Reserved/Sold out'>".$seat['seat_number']."</span>";
                                        } else {
                                            echo "<a href='cart_stub.php?booking_mode=assigned&seat_id=".$seat['id']."' class='bg-green-100 hover:bg-blue-600 hover:text-white text-green-800 text-xs px-2 py-1 rounded font-bold transition-all shadow-sm' title='Secure Seat | Price: $".$seat['price']."'>".$seat['seat_number']."</a>";
                                        }
                                    }
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
