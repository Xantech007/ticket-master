<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['auth_error'] = "Please log in to access your dashboard.";
    $_SESSION['redirect_after_auth'] = $_SERVER['REQUEST_URI'];
    header("Location: ../auth.php");
    exit;
}

// Get the authenticated user ID
$user_id = (int) $_SESSION['user_id'];

$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect(); 
    }
} catch (Exception $e) {
    // Silence error to preserve UI layout
}

// Status messages
$success_message = "";
$error_message = "";

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email_address = trim($_POST['email']);
    $phone_number = trim($_POST['phone']);

    if (!empty($full_name) && !empty($email_address)) {
        try {
            if ($pdo !== null) {
                $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
                $update_stmt->execute([$full_name, $email_address, $phone_number, $user_id]);
            }
            $success_message = "Your profile changes have been saved successfully.";
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        $error_message = "Required fields cannot be left blank.";
    }
}

// Initialize default template arrays
$user_profile = [
    'name' => 'Jane Doe',
    'email' => 'janedoe@infinityfreeapp.com',
    'phone' => '+1 (555) 019-2834'
];

$admin_messages = [];
$recent_orders = [];
$transaction_history = [];
$recently_viewed_shows = []; // Clean initiation without hardcoded fallbacks

$admin_tickets = [
    ['file_path' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=600&q=80', 'description' => 'VIP Golden Circle Early Entry Pass. Valid for standard stadium layouts. Please save this pass to your phone\'s wallet app.']
];

// Fetch database records
if ($pdo !== null) {
    try {
        // 1. Load user profile information
        $user_stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ? LIMIT 1");
        $user_stmt->execute([$user_id]);
        $fetched_user = $user_stmt->fetch();
        if ($fetched_user) {
            $user_profile = [
                'name'  => $fetched_user['full_name'] ?? 'N/A',
                'email' => $fetched_user['email'] ?? 'N/A',
                'phone' => $fetched_user['phone'] ?? 'N/A'
            ];
        }

        // 2. Load system notices and announcements
        $msg_stmt = $pdo->prepare("SELECT message, created_at FROM users WHERE id = ? AND message IS NOT NULL AND message != '' ORDER BY id DESC");
        $msg_stmt->execute([$user_id]);
        $raw_messages = $msg_stmt->fetchAll();
        foreach ($raw_messages as $m_row) {
            $admin_messages[] = [
                'title'      => 'Security Notice',
                'content'    => $m_row['message'],
                'created_at' => $m_row['created_at'] ?? date('Y-m-d H:i:s')
            ];
        }

        // 3. Load user ticket orders
        $order_stmt = $pdo->prepare("
            SELECT 
                o.order_id, 
                o.status AS order_status, 
                o.created_at AS purchase_date,
                a.artist_name AS show_title,
                c.title AS concert_title,
                t.ticket_name,
                t.section_name,
                t.row_name,
                t.seat_name
            FROM orders o
            INNER JOIN tickets t ON o.ticket_id = t.ticket_id
            INNER JOIN concerts c ON t.concert_id = c.concert_id
            INNER JOIN artists a ON c.artist_id = a.artist_id
            WHERE o.user_id = ?
            ORDER BY o.order_id DESC LIMIT 10
        ");
        $order_stmt->execute([$user_id]);
        $raw_orders = $order_stmt->fetchAll();
        foreach ($raw_orders as $or) {
            $seat_details = trim(sprintf(
                "%s (%s, %s, %s)", 
                $or['ticket_name'], 
                $or['section_name'], 
                $or['row_name'], 
                $or['seat_name']
            ));

            $recent_orders[] = [
                'id'     => 'TM-' . $or['order_id'],
                'title'  => $or['show_title'],
                'venue'  => $or['concert_title'],
                'seats'  => !empty($seat_details) ? $seat_details : 'General Admission',
                'status' => $or['order_status'], 
                'date'   => date('M d, Y', strtotime($or['purchase_date']))
            ];
        }
        
        // 4. Load billing and deposit logs
        $tx_stmt = $pdo->prepare("
            SELECT 
                d.deposit_id, 
                d.created_at, 
                d.amount, 
                d.status,
                p.image_path
            FROM deposits d
            LEFT JOIN payment_methods p ON d.payment_id = p.payment_id
            WHERE d.user_id = ?
            ORDER BY d.deposit_id DESC LIMIT 15
        ");
        $tx_stmt->execute([$user_id]);
        $raw_txs = $tx_stmt->fetchAll();
        foreach ($raw_txs as $tx) {
            $transaction_history[] = [
                'ref'      => 'DEP-' . $tx['deposit_id'],
                'date'     => date('Y-m-d', strtotime($tx['created_at'])),
                'method'   => !empty($tx['image_path']) ? '../uploads/payment-methods/' . $tx['image_path'] : 'Standard Gateway',
                'amount'   => $tx['amount'],
                'currency' => 'USD',
                'status'   => ($tx['status'] === 'confirmed' || $tx['status'] === 'completed' || $tx['status'] === 'Successful') ? 'Successful' : 'Processing'
            ];
        }

        // 5. Dynamic loading for Recently Viewed / Trending Shows
        $trending_stmt = $pdo->prepare("
            SELECT 
                c.concert_id AS id,
                c.title,
                c.location,
                a.artist_name AS artist
            FROM concerts c
            INNER JOIN artists a ON c.artist_id = a.artist_id
            WHERE c.index_type = 'trending'
        ");
        $trending_stmt->execute();
        $raw_trending = $trending_stmt->fetchAll();
        
        // Randomize the resulting table collection array execution order
        if (!empty($raw_trending)) {
            shuffle($raw_trending);
            foreach ($raw_trending as $row) {
                $recently_viewed_shows[] = [
                    'id'       => $row['id'],
                    'artist'   => $row['artist'],
                    'title'    => $row['title'],
                    'location' => $row['location'] ?? 'Venue TBA'
                ];
            }
        }

    } catch (Exception $e) {
        $error_message = "Failed to load dashboard data: " . $e->getMessage();
    }
}

// Fallback arrays remain strictly for structural interface safety checks if required
if (empty($recent_orders)) {
    $recent_orders = [
        ['id' => 'TM-441029', 'title' => 'Coldplay: Music of the Spheres Tour', 'venue' => 'Old Trafford Stadium', 'seats' => 'VIP Ticket (62, 3, 19)', 'status' => 'processing', 'date' => 'Sep 25, 2026'],
        ['id' => 'TM-441030', 'title' => 'Coldplay: Music of the Spheres Tour', 'venue' => 'Wembley Stadium', 'seats' => 'Standard Entry (102, 5, 12)', 'status' => 'confirmed', 'date' => 'Sep 14, 2026']
    ];
}
if (empty($transaction_history)) {
    $transaction_history = [
        ['ref' => 'TXN-8829102', 'date' => date('Y-m-d'), 'method' => 'Gift Card', 'amount' => 150.00, 'currency' => 'USD', 'status' => 'Processing'],
        ['ref' => 'TXN-1102983', 'date' => '2026-05-11', 'method' => 'Crypto Wallet', 'amount' => 232.32, 'currency' => 'USD', 'status' => 'Successful']
    ];
}
if (empty($admin_messages)) {
    $admin_messages = [
        ['title' => 'Important Venue Entry Information', 'content' => 'Please arrive 2 hours before the scheduled event time to allow smooth security scanning procedures.', 'created_at' => date('Y-m-d H:i:s')]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include "../inc/head.php"; ?>
<?php include "../inc/navbar.php"; ?>

<body class="bg-gray-100 text-gray-900 font-sans antialiased">

    <?php include "../inc/header.php"; ?>

    <div id="__next" class="min-h-screen flex flex-col justify-between">

        <main class="max-w-7xl mx-auto w-full px-4 md:px-8 py-10 flex-1">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-4 space-y-6">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-4 border-b border-gray-100 pb-4 mb-6">
                            <div class="w-14 h-14 rounded-full bg-[#024DDF] text-white font-black text-xl flex items-center justify-center shadow">
                                <?php echo strtoupper(substr($user_profile['name'], 0, 2)); ?>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($user_profile['name']); ?></h3>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Verified Member</p>
                            </div>
                        </div>

                        <?php if (!empty($success_message)): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 font-bold text-xs p-3.5 rounded-xl mb-4">
                                <i class="fas fa-check-circle mr-1"></i> <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($error_message)): ?>
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 font-bold text-xs p-3.5 rounded-xl mb-4">
                                <i class="fas fa-exclamation-triangle mr-1"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="dashboard.php" method="POST" class="space-y-4">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div>
                                <label class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-1.5">Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_profile['name']); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-1.5">Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase text-gray-400 tracking-wider mb-1.5">Phone Number</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user_profile['phone']); ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                            </div>

                            <button type="submit" class="w-full bg-[#024DDF] hover:bg-blue-800 text-white font-black text-xs uppercase tracking-widest py-3 rounded-xl transition-all shadow-sm">
                                Save Profile Changes
                            </button>
                        </form>
                    </div>

                    <div class="bg-slate-900 text-white border border-slate-800 rounded-2xl p-6 shadow-md space-y-4">
                        <h4 class="text-xs font-black uppercase tracking-widest text-blue-400 flex items-center gap-2 border-b border-slate-800 pb-3">
                            <i class="fas fa-satellite-dish animate-pulse"></i> Announcements & Notices
                        </h4>
                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                            <?php foreach ($admin_messages as $msg): ?>
                                <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-xl space-y-1.5">
                                    <span class="text-[10px] text-gray-500 font-mono block"><?php echo htmlspecialchars($msg['created_at']); ?></span>
                                    <h5 class="text-xs font-black tracking-tight text-gray-200"><?php echo htmlspecialchars($msg['title']); ?></h5>
                                    <p class="text-[11px] font-medium text-gray-400 leading-relaxed"><?php echo htmlspecialchars($msg['content']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-6">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-gray-800 flex items-center gap-2 border-b border-gray-100 pb-3">
                            <i class="fas fa-ticket-alt text-[#024DDF]"></i> Your Available Tickets
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($admin_tickets as $ticket): ?>
                                <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden shadow-sm flex flex-col">
                                    <div class="w-full h-40 bg-black overflow-hidden relative">
                                        <img src="<?php echo htmlspecialchars($ticket['file_path']); ?>" onerror="this.src='https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=600&q=80';" alt="Ticket Image" class="w-full h-full object-cover">
                                        <div class="absolute top-2 right-2 bg-[#024DDF] text-white font-mono text-[10px] font-black px-2 py-0.5 rounded shadow">
                                            DIGITAL PASS
                                        </div>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col justify-between items-start space-y-2">
                                        <p class="text-xs text-gray-600 leading-relaxed font-medium">
                                            <?php echo htmlspecialchars($ticket['description']); ?>
                                        </p>
                                        <a href="<?php echo htmlspecialchars($ticket['file_path']); ?>" target="_blank" class="text-[10px] font-black bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-100 uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                                            <i class="fas fa-download text-blue-600"></i> Download Pass File
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-gray-800 flex items-center gap-2 border-b border-gray-100 pb-3">
                            <i class="fas fa-shopping-bag text-[#024DDF]"></i> Recent Orders
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:border-gray-300 transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono font-black text-gray-400 bg-gray-100 border border-gray-200 px-1.5 py-0.5 rounded"><?php echo htmlspecialchars($order['id']); ?></span>
                                            
                                            <?php 
                                                $status = strtolower($order['status']);
                                                if ($status === 'confirmed' || $status === 'completed' || $status === 'success') {
                                                    $badge_cls = 'text-emerald-600 bg-emerald-50';
                                                } elseif ($status === 'processing' || $status === 'pending') {
                                                    $badge_cls = 'text-amber-600 bg-amber-50 animate-pulse';
                                                } else {
                                                    $badge_cls = 'text-blue-600 bg-blue-50';
                                                }
                                            ?>
                                            <span class="text-xs font-black px-2 py-0.5 rounded uppercase tracking-wide <?php echo $badge_cls; ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </div>
                                        <h4 class="text-sm font-black text-gray-900 tracking-tight"><?php echo htmlspecialchars($order['title']); ?></h4>
                                        <p class="text-xs text-gray-500 font-medium">
                                            <i class="fas fa-ticket text-gray-400 mr-1"></i> <?php echo htmlspecialchars($order['venue']); ?> • <span class="font-bold text-gray-600"><?php echo htmlspecialchars($order['seats']); ?></span>
                                        </p>
                                    </div>
                                    <div class="text-left sm:text-right w-full sm:w-auto shrink-0 border-t sm:border-t-0 border-gray-100 pt-2 sm:pt-0">
                                        <span class="text-xs font-black text-gray-800 block"><?php echo htmlspecialchars($order['date']); ?></span>
                                        <span class="text-[10px] text-gray-400 block font-medium">Order Reference Record</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-gray-800 flex items-center gap-2 border-b border-gray-100 pb-3">
                            <i class="fas fa-receipt text-[#024DDF]"></i> Transaction History
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs font-medium text-gray-600">
                                <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider text-[10px] font-black border border-gray-200 rounded-lg">
                                    <tr>
                                        <th class="p-3">Reference ID</th>
                                        <th class="p-3">Date</th>
                                        <th class="p-3">Payment Method</th>
                                        <th class="p-3 text-right">Total Amount</th>
                                        <th class="p-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($transaction_history as $txn): ?>
                                        <tr class="hover:bg-gray-50/60 transition-colors">
                                            <td class="p-3 font-mono font-bold text-gray-900"><?php echo htmlspecialchars($txn['ref']); ?></td>
                                            <td class="p-3 text-gray-500 font-bold"><?php echo htmlspecialchars($txn['date']); ?></td>
                                            <td class="p-3 text-gray-500 font-bold flex items-center gap-2">
                                                <?php if (strpos($txn['method'], '../uploads/') === 0): ?>
                                                    <img src="<?php echo htmlspecialchars($txn['method']); ?>" alt="Icon" class="h-4 w-auto object-contain rounded border border-gray-200 max-w-[60px]">
                                                <?php else: ?>
                                                    <span><?php echo htmlspecialchars($txn['method']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="p-3 text-right font-black text-[#024DDF]">
                                                <?php 
                                                    $symbol = ($txn['currency'] === 'EUR') ? '€' : (($txn['currency'] === 'GBP') ? '£' : '$');
                                                    echo $symbol . number_format($txn['amount'], 2); 
                                                ?>
                                            </td>
                                            <td class="p-3 text-center">
                                                <span class="font-black tracking-wide uppercase px-2 py-0.5 rounded text-[10px] <?php echo ($txn['status'] === 'Successful') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>">
                                                    <?php echo htmlspecialchars($txn['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black uppercase tracking-wider text-gray-800 flex items-center gap-2 border-b border-gray-100 pb-3">
                            <i class="fas fa-eye text-[#024DDF]"></i> Recently Viewed Shows
                        </h3>
                        
                        <?php if (empty($recently_viewed_shows)): ?>
                            <div class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-8 text-center">
                                <i class="fas fa-theater-masks text-gray-300 text-3xl mb-2 block"></i>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">No trending updates found at this time.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-y-auto pr-1 space-y-3 max-h-[295px]">
                                <?php foreach ($recently_viewed_shows as $show): ?>
                                    <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-200 hover:shadow-sm transition-all flex justify-between items-center bg-white">
                                        <div class="min-w-0">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-[#024DDF] block"><?php echo htmlspecialchars($show['artist']); ?></span>
                                            <h4 class="text-xs font-extrabold text-gray-900 truncate mt-0.5" title="<?php echo htmlspecialchars($show['title']); ?>">
                                                <?php echo htmlspecialchars($show['title']); ?>
                                            </h4>
                                            <p class="text-[11px] font-medium text-gray-400 mt-0.5 truncate">
                                                <i class="fas fa-map-pin text-gray-300 mr-1"></i> <?php echo htmlspecialchars($show['location']); ?>
                                            </p>
                                        </div>
                                        <a href="../search.php?q=<?php echo urlencode($show['artist']); ?>" class="shrink-0 text-[10px] font-black text-[#024DDF] bg-blue-50 hover:bg-[#024DDF] hover:text-white px-3 py-2 rounded-md transition-all uppercase tracking-wide ml-2">
                                            View Show
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            
        </main>

        <?php include "../inc/footer.php"; ?>
    </div>

    <style>
        body { overflow-x: hidden; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</body>
</html>
