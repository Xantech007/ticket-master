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

$user_id = (int) $_SESSION['user_id'];
$pdo = null;

try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect(); 
    }
} catch (Exception $e) {
    // Suppress configuration errors gracefully
}

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

// Target Initialization without hardcoded fallback elements
$user_profile = [
    'name'    => '',
    'email'   => '',
    'phone'   => '',
    'balance' => 0.00
];

$admin_tickets = [];
$admin_messages = [];
$recent_orders = [];
$transaction_history = [];
$trending_shows = [];

if ($pdo !== null) {
    try {
        // 1. Load User Info & Balance from the users table
        $user_stmt = $pdo->prepare("SELECT full_name, email, phone, balance FROM users WHERE id = ? LIMIT 1");
        $user_stmt->execute([$user_id]);
        $fetched_user = $user_stmt->fetch();
        if ($fetched_user) {
            $user_profile = [
                'name'    => $fetched_user['full_name'] ?? 'User',
                'email'   => $fetched_user['email'] ?? '',
                'phone'   => $fetched_user['phone'] ?? '',
                'balance' => (float) ($fetched_user['balance'] ?? 0.00)
            ];
        }

        // 2. Load Real System Tickets assigned to this user
        $ticket_stmt = $pdo->prepare("SELECT file_path, description FROM tickets WHERE user_id = ? ORDER BY ticket_id DESC");
        $ticket_stmt->execute([$user_id]);
        $admin_tickets = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Load Real Messages/Notices
        $msg_stmt = $pdo->prepare("SELECT message, created_at FROM users WHERE id = ? AND message IS NOT NULL AND message != '' ORDER BY id DESC");
        $msg_stmt->execute([$user_id]);
        $raw_messages = $msg_stmt->fetchAll();
        foreach ($raw_messages as $m_row) {
            $admin_messages[] = [
                'title'      => 'Security Pass Notification',
                'content'    => $m_row['message'],
                'created_at' => $m_row['created_at'] ?? date('Y-m-d H:i:s')
            ];
        }

        // 4. Load Recent Purchase Summary containing explicit concert_id and ticket_id elements
        $order_stmt = $pdo->prepare("
            SELECT 
                o.order_id, 
                o.status AS order_status, 
                o.created_at AS purchase_date,
                o.ticket_id,
                t.concert_id,
                a.artist_name AS show_title,
                c.title AS concert_title,
                t.ticket_name
            FROM orders o
            INNER JOIN tickets t ON o.ticket_id = t.ticket_id
            INNER JOIN concerts c ON t.concert_id = c.concert_id
            INNER JOIN artists a ON c.artist_id = a.artist_id
            WHERE o.user_id = ?
            ORDER BY o.order_id DESC
        ");
        $order_stmt->execute([$user_id]);
        $recent_orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Load Account Ledger Settlements containing deposit_id records
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
            ORDER BY d.deposit_id DESC
        ");
        $tx_stmt->execute([$user_id]);
        $transaction_history = $tx_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Explore Trending Performances - Picked randomly on every individual refresh (Limit capped at 3)
        $trending_stmt = $pdo->prepare("
            SELECT 
                c.concert_id AS id,
                c.title,
                c.location,
                a.artist_name AS artist
            FROM concerts c
            INNER JOIN artists a ON c.artist_id = a.artist_id
            WHERE c.index_type = 'trending'
            ORDER BY RAND() LIMIT 3
        ");
        $trending_stmt->execute();
        $trending_shows = $trending_stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $error_message = "Failed to load dynamic ledger data: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include "../inc/head.php"; ?>
<?php include "../inc/navbar.php"; ?>

<body class="bg-slate-50 text-slate-900 font-sans antialiased">

    <?php include "../inc/header.php"; ?>

    <div id="__next" class="min-h-screen flex flex-col justify-between">
        <main class="max-w-7xl mx-auto w-full px-4 md:px-8 py-10 flex-1">
            
            <div class="space-y-8">
                
                <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-4 mb-4">
                        <i class="fas fa-ticket-alt text-[#024DDF]"></i> Your Available Tickets
                    </h3>
                    <?php if (empty($admin_tickets)): ?>
                        <div class="py-10 text-center bg-slate-50 border border-dashed border-slate-200 rounded-xl">
                            <i class="fas fa-qrcode text-slate-300 text-3xl mb-2 block"></i>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">No active passes inside your ledger vault.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($admin_tickets as $ticket): ?>
                                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm flex flex-col hover:shadow-md transition-all">
                                    <div class="w-full h-40 bg-slate-950 overflow-hidden relative">
                                        <img src="<?php echo htmlspecialchars($ticket['file_path']); ?>" alt="Pass Cover" class="w-full h-full object-cover opacity-90">
                                        <div class="absolute top-3 right-3 bg-[#024DDF] text-white font-mono text-[9px] font-black px-2.5 py-1 rounded shadow-sm tracking-widest">
                                            SECURE PASS
                                        </div>
                                    </div>
                                    <div class="p-4 flex-1 flex flex-col justify-between items-start space-y-3">
                                        <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                            <?php echo htmlspecialchars($ticket['description']); ?>
                                        </p>
                                        <a href="<?php echo htmlspecialchars($ticket['file_path']); ?>" target="_blank" class="w-full text-center text-[10px] font-black bg-slate-900 text-white py-2 rounded-md hover:bg-slate-800 uppercase tracking-widest transition-all flex items-center justify-center gap-2">
                                            <i class="fas fa-external-link-alt text-blue-400"></i> Open Gateway Pass
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="bg-slate-950 text-white border border-slate-900 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 via-transparent to-transparent pointer-events-none"></div>
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-400 flex items-center gap-2 border-b border-slate-800 pb-4 mb-4 relative z-10">
                        <i class="fas fa-bullhorn text-blue-400"></i> Operational Messages & System Notices
                    </h3>
                    <?php if (empty($admin_messages)): ?>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wide py-4 text-center relative z-10">No new structural alerts from management.</p>
                    <?php else: ?>
                        <div class="space-y-4 max-h-[220px] overflow-y-auto pr-2 relative z-10">
                            <?php foreach ($admin_messages as $msg): ?>
                                <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl space-y-2 hover:border-slate-700 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <h5 class="text-xs font-black tracking-tight text-slate-200"><?php echo htmlspecialchars($msg['title']); ?></h5>
                                        <span class="text-[10px] text-slate-500 font-mono"><?php echo htmlspecialchars($msg['created_at']); ?></span>
                                    </div>
                                    <p class="text-[11px] font-medium text-slate-400 leading-relaxed"><?php echo htmlspecialchars($msg['content']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-gradient-to-br from-slate-900 to-slate-850 text-white rounded-2xl p-6 shadow-xl border border-slate-800 flex flex-col justify-between h-48 relative overflow-hidden">
                            <div class="absolute right-0 bottom-0 opacity-5 translate-x-10 translate-y-10">
                                <i class="fas fa-vault text-[12rem]"></i>
                            </div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-black">Available Account Balance</p>
                                    <h2 class="text-3xl font-black mt-2 tracking-tight text-white font-mono">
                                        $<?php echo number_format($user_profile['balance'], 2); ?>
                                    </h2>
                                </div>
                                <div class="bg-blue-600/20 border border-blue-500/30 px-2.5 py-1 rounded text-blue-400 font-mono text-[9px] font-bold uppercase tracking-wider">
                                    USD Asset
                                </div>
                            </div>
                            <div class="border-t border-slate-800/80 pt-4 flex justify-between items-center">
                                <div class="text-left">
                                    <span class="text-[9px] text-slate-500 block uppercase font-bold tracking-wider">Vault ID Code</span>
                                    <span class="text-xs font-mono font-bold text-slate-300">#ACC-<?php echo str_pad($user_id, 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <i class="fas fa-shield-alt text-slate-600 text-lg"></i>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <div class="flex items-center gap-4 border-b border-slate-100 pb-4 mb-6">
                                <div class="w-12 h-12 rounded-xl bg-slate-900 text-white font-black text-sm flex items-center justify-center shadow-inner">
                                    <?php echo strtoupper(substr($user_profile['name'], 0, 2)); ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 tracking-tight"><?php echo htmlspecialchars($user_profile['name']); ?></h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Identity Master Record</p>
                                </div>
                            </div>

                            <?php if (!empty($success_message)): ?>
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-xs p-3 rounded-xl mb-4">
                                    <i class="fas fa-check-circle mr-1"></i> <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($error_message)): ?>
                                <div class="bg-rose-50 border border-rose-200 text-rose-700 font-bold text-xs p-3 rounded-xl mb-4">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <form action="dashboard.php" method="POST" class="space-y-4">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Full Name Signature</label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_profile['name']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Email Communications Address</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider mb-1.5">Phone Contact Line</label>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_profile['phone']); ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold focus:bg-white focus:border-[#024DDF] outline-none transition-all">
                                </div>

                                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-black text-xs uppercase tracking-widest py-3 rounded-xl transition-all">
                                    Commit Record Changes
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-shopping-bag text-[#024DDF]"></i> Recent Purchase Summary
                                </h3>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Displaying Max 3 Records</span>
                            </div>

                            <div class="space-y-3 overflow-y-auto pr-1" style="max-height: 275px;">
                                <?php if (empty($recent_orders)): ?>
                                    <div class="text-center py-8 text-slate-400 font-medium text-xs uppercase tracking-wide">No orders mapped to ledger history.</div>
                                <?php else: ?>
                                    <?php 
                                    $order_counter = 0;
                                    foreach ($recent_orders as $order): 
                                        $order_counter++;
                                    ?>
                                        <div class="bg-white border border-slate-200 rounded-xl p-4 hover:border-slate-300 transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                            <div class="space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-[9px] font-mono font-black text-slate-500 bg-slate-100 border border-slate-200 px-1.5 py-0.5 rounded">
                                                        ORD-<?php echo htmlspecialchars($order['order_id']); ?>
                                                    </span>
                                                    <span class="text-[9px] font-mono font-black text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded">
                                                        CID: <?php echo htmlspecialchars($order['concert_id']); ?>
                                                    </span>
                                                    <span class="text-[9px] font-mono font-black text-purple-600 bg-purple-50 border border-purple-100 px-1.5 py-0.5 rounded">
                                                        TID: <?php echo htmlspecialchars($order['ticket_id']); ?>
                                                    </span>
                                                </div>
                                                <h4 class="text-xs font-black text-slate-900 tracking-tight mt-1"><?php echo htmlspecialchars($order['show_title']); ?></h4>
                                                <p class="text-[11px] text-slate-500 font-medium">
                                                    <?php echo htmlspecialchars($order['concert_title']); ?> • <span class="text-slate-700 font-bold"><?php echo htmlspecialchars($order['ticket_name']); ?></span>
                                                </p>
                                            </div>
                                            <div class="text-left sm:text-right shrink-0">
                                                <span class="text-[10px] font-mono font-black px-2 py-0.5 rounded uppercase <?php echo (strtolower($order['order_status']) === 'confirmed' || strtolower($order['order_status']) === 'completed') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>">
                                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-mono block mt-1"><?php echo date('M d, Y', strtotime($order['purchase_date'])); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                            <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                    <i class="fas fa-receipt text-[#024DDF]"></i> Account Ledger Settlements
                                </h3>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Displaying Max 3 Records</span>
                            </div>

                            <div class="overflow-y-auto pr-1" style="max-height: 250px;">
                                <?php if (empty($transaction_history)): ?>
                                    <div class="text-center py-8 text-slate-400 font-medium text-xs uppercase tracking-wide">No settlements posted to balancing book.</div>
                                <?php else: ?>
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 text-slate-400 uppercase tracking-wider text-[9px] font-black border border-slate-200 rounded-lg">
                                            <tr>
                                                <th class="p-3">Deposit ID</th>
                                                <th class="p-3">Settlement Date</th>
                                                <th class="p-3 text-right">Processed Amount</th>
                                                <th class="p-3 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 font-medium text-slate-600">
                                            <?php 
                                            $tx_counter = 0;
                                            foreach ($transaction_history as $txn): 
                                                $tx_counter++;
                                            ?>
                                                <tr class="hover:bg-slate-50/80 transition-colors">
                                                    <td class="p-3 font-mono font-bold text-slate-900">DEP-<?php echo htmlspecialchars($txn['deposit_id']); ?></td>
                                                    <td class="p-3 text-slate-500 font-mono"><?php echo date('Y-m-d H:i', strtotime($txn['created_at'])); ?></td>
                                                    <td class="p-3 text-right font-black text-[#024DDF] font-mono">$<?php echo number_format($txn['amount'], 2); ?></td>
                                                    <td class="p-3 text-center">
                                                        <span class="font-black tracking-wide uppercase px-2 py-0.5 rounded text-[9px] <?php echo ($txn['status'] === 'confirmed' || $txn['status'] === 'completed' || $txn['status'] === 'Successful') ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>">
                                                            <?php echo htmlspecialchars($txn['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                                <i class="fas fa-bolt text-amber-500"></i> Explore Trending Performances
                            </h3>
                            <?php if (empty($trending_shows)): ?>
                                <div class="bg-slate-50 border border-dashed border-slate-200 rounded-xl p-6 text-center text-xs font-bold text-slate-400 uppercase">
                                    No dynamic events cataloged at this instant.
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <?php foreach ($trending_shows as $show): ?>
                                        <div class="border border-slate-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all flex flex-col justify-between bg-white space-y-3">
                                            <div>
                                                <span class="text-[9px] font-black uppercase tracking-wider text-[#024DDF] block"><?php echo htmlspecialchars($show['artist']); ?></span>
                                                <h4 class="text-xs font-extrabold text-slate-900 truncate mt-0.5" title="<?php echo htmlspecialchars($show['title']); ?>">
                                                    <?php echo htmlspecialchars($show['title']); ?>
                                                </h4>
                                                <p class="text-[10px] font-medium text-slate-400 mt-1 truncate">
                                                    <i class="fas fa-map-marker-alt text-slate-300 mr-1"></i> <?php echo htmlspecialchars($show['location']); ?>
                                                </p>
                                            </div>
                                            <a href="../search.php?q=<?php echo urlencode($show['artist']); ?>" class="w-full text-center text-[9px] font-black text-[#024DDF] bg-blue-50 hover:bg-[#024DDF] hover:text-white py-2 rounded transition-all uppercase tracking-wider block">
                                                Acquire Pass
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </section>

            </div>
            
        </main>

        <?php include "../inc/footer.php"; ?>
    </div>

    <style>
        body { overflow-x: hidden; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</body>
</html>
