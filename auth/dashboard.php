<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['auth_error'] = "Please login to access dashboard.";
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
} catch (Exception $e) {}

$success_message = "";
$error_message = "";

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
            $success_message = "Profile configuration layers synced successfully.";
        } catch (Exception $e) {
            $error_message = "Synchronization error: " . $e->getMessage();
        }
    } else {
        $error_message = "Required fields cannot be left empty.";
    }
}

$user_profile = [
    'name'    => 'Jane Doe',
    'email'   => 'janedoe@infinityfreeapp.com',
    'phone'   => '+1 (555) 019-2834',
    'balance' => 0.00
];

$admin_messages = [];
$recent_orders = [];
$transaction_history = [];

$admin_tickets = [
    ['file_path' => 'https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=600&q=80', 'description' => 'VIP Golden Circle Early Entry Pass Package Allocation File Vector. Valid across all standard stadium layout properties. Please save to phone pass wallet storage.']
];

if ($pdo !== null) {
    try {
        $user_stmt = $pdo->prepare("SELECT full_name, email, phone, balance FROM users WHERE id = ? LIMIT 1");
        $user_stmt->execute([$user_id]);
        $fetched_user = $user_stmt->fetch();
        if ($fetched_user) {
            $user_profile = [
                'name'    => $fetched_user['full_name'] ?? 'N/A',
                'email'   => $fetched_user['email'] ?? 'N/A',
                'phone'   => $fetched_user['phone'] ?? 'N/A',
                'balance' => (float) ($fetched_user['balance'] ?? 0.00)
            ];
        }

        $msg_stmt = $pdo->prepare("SELECT message, created_at FROM users WHERE id = ? AND message IS NOT NULL AND message != '' ORDER BY id DESC");
        $msg_stmt->execute([$user_id]);
        $raw_messages = $msg_stmt->fetchAll();
        foreach ($raw_messages as $m_row) {
            $admin_messages[] = [
                'title'      => 'Security Clearance Notice',
                'content'    => $m_row['message'],
                'created_at' => $m_row['created_at'] ?? date('Y-m-d H:i:s')
            ];
        }

        $order_stmt = $pdo->prepare("
            SELECT o.order_id, o.status, o.created_at, a.artist_name, c.title, t.ticket_name, t.section_name, t.row_name, t.seat_name
            FROM orders o
            INNER JOIN tickets t ON o.ticket_id = t.ticket_id
            INNER JOIN concerts c ON t.concert_id = c.concert_id
            INNER JOIN artists a ON c.artist_id = a.artist_id
            WHERE o.user_id = ? ORDER BY o.order_id DESC LIMIT 40
        ");
        $order_stmt->execute([$user_id]);
        foreach ($order_stmt->fetchAll() as $or) {
            $seats = trim(sprintf("%s (Sec %s, Row %s, Seat %s)", $or['ticket_name'], $or['section_name'], $or['row_name'], $or['seat_name']));
            $recent_orders[] = [
                'id'     => 'TM-' . $or['order_id'],
                'title'  => $or['artist_name'],
                'venue'  => $or['title'],
                'seats'  => !empty($seats) ? $seats : 'General Allocation',
                'status' => $or['status'], 
                'date'   => date('M d, Y', strtotime($or['created_at']))
            ];
        }
        
        $tx_stmt = $pdo->prepare("
            SELECT d.deposit_id, d.created_at, d.amount, d.status, p.image_path
            FROM deposits d LEFT JOIN payment_methods p ON d.payment_id = p.payment_id
            WHERE d.user_id = ? ORDER BY d.deposit_id DESC LIMIT 30
        ");
        $tx_stmt->execute([$user_id]);
        foreach ($tx_stmt->fetchAll() as $tx) {
            $transaction_history[] = [
                'ref'    => 'DEP-' . $tx['deposit_id'],
                'date'   => date('Y-m-d', strtotime($tx['created_at'])),
                'method' => !empty($tx['image_path']) ? '../uploads/payment-methods/' . $tx['image_path'] : 'Gateway standard Network',
                'amount' => $tx['amount'],
                'status' => ($tx['status'] === 'confirmed' || $tx['status'] === 'completed' || $tx['status'] === 'Successful') ? 'Successful' : 'Processing'
            ];
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<?php include "../inc/head.php"; ?>
<?php include "../inc/navbar.php"; ?>

<body class="bg-zinc-950 text-zinc-100 font-sans antialiased selection:bg-blue-500 selection:text-white">

    <?php include "../inc/header.php"; ?>

    <div class="sticky top-0 z-40 bg-zinc-900/80 backdrop-blur-md border-b border-zinc-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-8 overflow-x-auto py-4 scrollbar-none snap-x">
                <a href="#manifests-section" class="snap-start shrink-0 text-xs font-semibold tracking-wider text-zinc-400 hover:text-blue-400 transition-colors flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> TICKET MANIFESTS
                </a>
                <a href="#alerts-section" class="snap-start shrink-0 text-xs font-semibold tracking-wider text-zinc-400 hover:text-blue-400 transition-colors flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> ALERTS TERMINAL
                </a>
                <a href="#profile-section" class="snap-start shrink-0 text-xs font-semibold tracking-wider text-zinc-400 hover:text-blue-400 transition-colors flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> SECURITY ACCESS
                </a>
                <a href="#orders-section" class="snap-start shrink-0 text-xs font-semibold tracking-wider text-zinc-400 hover:text-blue-400 transition-colors flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> PRODUCTION ORDERS
                </a>
                <a href="#transactions-section" class="snap-start shrink-0 text-xs font-semibold tracking-wider text-zinc-400 hover:text-blue-400 transition-colors flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> LEDGER BALANCE
                </a>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex flex-col justify-between">
        <main class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 flex-1 space-y-10">
            
            <section id="manifests-section" class="scroll-mt-24 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl relative overflow-hidden shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)]">
                <div class="flex items-center justify-between mb-6 border-b border-zinc-800 pb-4">
                    <div class="space-y-0.5">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-200 flex items-center gap-2">
                            <i class="fas fa-ticket-alt text-blue-500 text-base"></i> Ticket Allocation Manifests
                        </h2>
                        <p class="text-xs text-zinc-500">Active cryptographic vector files uploaded by administrative node root authority.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($admin_tickets as $ticket): ?>
                        <div class="bg-zinc-950/60 border border-zinc-800 rounded-xl overflow-hidden group hover:border-zinc-700 transition-all flex flex-col shadow-inner">
                            <div class="w-full h-44 bg-zinc-900 overflow-hidden relative">
                                <img src="<?php echo htmlspecialchars($ticket['file_path']); ?>" onerror="this.src='https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?auto=format&fit=crop&w=600&q=80';" alt="Allocation Resource Mapping Graph" class="w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-100 transition-all duration-500">
                                <div class="absolute top-3 right-3 bg-blue-500/10 border border-blue-500/30 backdrop-blur-md text-blue-400 font-mono text-[10px] font-bold px-2.5 py-1 rounded-md tracking-wider">
                                    VERIFIED PASS
                                </div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between items-start space-y-4">
                                <p class="text-xs text-zinc-400 leading-relaxed font-medium">
                                    <?php echo htmlspecialchars($ticket['description']); ?>
                                </p>
                                <a href="<?php echo htmlspecialchars($ticket['file_path']); ?>" target="_blank" class="text-[11px] font-bold bg-zinc-800 hover:bg-zinc-700 text-zinc-200 px-4 py-2.5 rounded-lg border border-zinc-700 transition-all uppercase tracking-wider flex items-center gap-2 shadow-sm w-full sm:w-auto justify-center">
                                    <i class="fas fa-download text-blue-400"></i> Fetch Asset Target File
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-4 space-y-8">
                    
                    <div id="alerts-section" class="scroll-mt-24 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)] space-y-4">
                        <div class="border-b border-zinc-800 pb-3 flex justify-between items-center">
                            <h4 class="text-xs font-bold uppercase tracking-widest text-amber-400 flex items-center gap-2">
                                <i class="fas fa-terminal animate-pulse text-amber-500"></i> Signal Alerts Engine
                            </h4>
                            <span class="text-[10px] bg-amber-500/10 border border-amber-500/20 text-amber-400 px-2 py-0.5 rounded font-mono">Live Node</span>
                        </div>
                        <div class="space-y-4 max-h-[280px] overflow-y-auto pr-1">
                            <?php if (!empty($admin_messages)): ?>
                                <?php foreach ($admin_messages as $msg): ?>
                                    <div class="bg-zinc-950/80 border border-zinc-800/80 p-4 rounded-xl space-y-2 hover:border-zinc-700/60 transition-colors">
                                        <span class="text-[9px] text-zinc-500 font-mono block tracking-wider"><i class="far fa-clock mr-1"></i><?php echo htmlspecialchars($msg['created_at']); ?></span>
                                        <h5 class="text-xs font-bold tracking-tight text-zinc-300"><?php echo htmlspecialchars($msg['title']); ?></h5>
                                        <p class="text-[11px] font-medium text-zinc-400 leading-relaxed"><?php echo htmlspecialchars($msg['content']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-6 border border-dashed border-zinc-800 rounded-xl">
                                    <i class="fas fa-shield-alt text-zinc-700 text-lg mb-2 block"></i>
                                    <p class="text-xs text-zinc-600 italic">No system alerts queued.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div id="profile-section" class="scroll-mt-24 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)]">
                        <div class="flex items-center gap-4 border-b border-zinc-800 pb-5 mb-5">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-black text-sm flex items-center justify-center shadow-lg tracking-wider">
                                <?php echo strtoupper(substr($user_profile['name'], 0, 2)); ?>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold text-zinc-200 tracking-tight"><?php echo htmlspecialchars($user_profile['name']); ?></h3>
                                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">Operator Identity Layer</p>
                            </div>
                        </div>

                        <div class="bg-zinc-950 border border-zinc-800 rounded-xl p-4 mb-6 flex justify-between items-center shadow-inner relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase text-zinc-500 tracking-widest block">Liquidity Node Balance</span>
                                <span class="text-2xl font-black text-zinc-100 tracking-tight font-mono">
                                    $<?php echo number_format($user_profile['balance'], 2); ?>
                                </span>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-zinc-900 border border-zinc-800 flex items-center justify-center shadow-sm">
                                <i class="fas fa-wallet text-blue-400 text-sm"></i>
                            </div>
                        </div>

                        <?php if (!empty($success_message)): ?>
                            <div class="bg-emerald-950/80 border border-emerald-800 text-emerald-400 font-medium text-xs p-3.5 rounded-xl mb-4 flex items-center gap-2">
                                <i class="fas fa-check-circle text-emerald-500"></i> <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($error_message)): ?>
                            <div class="bg-rose-950/80 border border-rose-800 text-rose-400 font-medium text-xs p-3.5 rounded-xl mb-4 flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle text-rose-500"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="dashboard.php" method="POST" class="space-y-4">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-zinc-500 tracking-widest mb-2">Signature Identity Token Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_profile['name']); ?>" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-xs font-medium text-zinc-300 focus:bg-zinc-950 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-zinc-500 tracking-widest mb-2">Network Endpoint Routing Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-xs font-medium text-zinc-300 focus:bg-zinc-950 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-zinc-500 tracking-widest mb-2">Comms Gateway Access Channel</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user_profile['phone']); ?>" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-4 py-3 text-xs font-medium text-zinc-300 focus:bg-zinc-950 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all">
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-widest py-3.5 rounded-xl transition-all shadow-md shadow-blue-600/10 active:scale-[0.99]">
                                Update Security Metadata
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-8">
                    
                    <div id="orders-section" class="scroll-mt-24 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)] space-y-6">
                        <div class="flex flex-row justify-between items-center border-b border-zinc-800 pb-4 gap-2">
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-200 flex items-center gap-2">
                                    <i class="fas fa-layer-group text-blue-500"></i> Secured Production Orders & Gate Passes
                                </h3>
                                <p class="text-xs text-zinc-500">Real-time status tracking for active concert access nodes.</p>
                            </div>
                            <?php if (count($recent_orders) > 3): ?>
                                <button type="button" onclick="openOrdersModal()" class="text-[10px] font-bold text-blue-400 hover:text-blue-300 uppercase tracking-widest bg-zinc-950 border border-zinc-800 hover:border-zinc-700 px-4 py-2 rounded-xl transition-all shadow-sm shrink-0">
                                    Manifest Log <i class="fas fa-chevron-right text-[8px] ml-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="space-y-3">
                            <?php if (!empty($recent_orders)): ?>
                                <?php 
                                $limited_orders = array_slice($recent_orders, 0, 3);
                                foreach ($limited_orders as $order): 
                                ?>
                                    <div class="bg-zinc-950/50 border border-zinc-800/80 rounded-xl p-4 hover:border-zinc-700 hover:-translate-y-0.5 transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-mono font-bold text-zinc-400 bg-zinc-900 border border-zinc-800 px-2 py-0.5 rounded"><?php echo htmlspecialchars($order['id']); ?></span>
                                                
                                                <?php 
                                                    $status = strtolower($order['status']);
                                                    if ($status === 'confirmed' || $status === 'completed' || $status === 'success') {
                                                        $badge_cls = 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
                                                    } elseif ($status === 'processing' || $status === 'pending') {
                                                        $badge_cls = 'text-amber-400 bg-amber-500/10 border-amber-500/20 animate-pulse';
                                                    } else {
                                                        $badge_cls = 'text-blue-400 bg-blue-500/10 border-blue-500/20';
                                                    }
                                                ?>
                                                <span class="text-[10px] font-bold px-2 py-0.5 border rounded uppercase tracking-wider <?php echo $badge_cls; ?>">
                                                    <?php echo htmlspecialchars($order['status']); ?>
                                                </span>
                                            </div>
                                            <h4 class="text-sm font-bold text-zinc-200 tracking-tight group-hover:text-blue-400 transition-colors"><?php echo htmlspecialchars($order['title']); ?></h4>
                                            <p class="text-xs text-zinc-500 font-medium flex items-center gap-1.5 flex-wrap">
                                                <i class="fas fa-cube text-zinc-600"></i> <?php echo htmlspecialchars($order['venue']); ?> <span class="text-zinc-700">•</span> <span class="text-zinc-400 font-mono"><?php echo htmlspecialchars($order['seats']); ?></span>
                                            </p>
                                        </div>
                                        <div class="text-left sm:text-right w-full sm:w-auto shrink-0 border-t sm:border-t-0 border-zinc-800/80 pt-3 sm:pt-0 flex sm:flex-col justify-between items-center sm:items-end gap-1">
                                            <span class="text-xs font-bold text-zinc-400 font-mono"><?php echo htmlspecialchars($order['date']); ?></span>
                                            <span class="text-[9px] text-zinc-600 font-mono tracking-widest uppercase hidden sm:inline block">Sync Node</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-10 border border-dashed border-zinc-800 rounded-xl">
                                    <i class="fas fa-box-open text-zinc-700 text-xl mb-2 block"></i>
                                    <p class="text-xs text-zinc-500 italic">No dynamic production access routes found.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="transactions-section" class="scroll-mt-24 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)] space-y-6">
                        <div class="flex flex-row justify-between items-center border-b border-zinc-800 pb-4 gap-2">
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-200 flex items-center gap-2">
                                    <i class="fas fa-receipt text-blue-500"></i> Financial Balance Statements Ledger
                                </h3>
                                <p class="text-xs text-zinc-500">Historical transaction database execution checkpoints.</p>
                            </div>
                            <?php if (count($transaction_history) > 3): ?>
                                <button type="button" onclick="openTxModal()" class="text-[10px] font-bold text-blue-400 hover:text-blue-300 uppercase tracking-widest bg-zinc-950 border border-zinc-800 hover:border-zinc-700 px-4 py-2 rounded-xl transition-all shadow-sm shrink-0">
                                    Full Statements <i class="fas fa-chevron-right text-[8px] ml-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="overflow-x-auto rounded-xl border border-zinc-800 bg-zinc-950/30">
                            <?php if (!empty($transaction_history)): ?>
                                <table class="w-full text-left text-xs font-medium text-zinc-400 border-collapse">
                                    <thead>
                                        <tr class="bg-zinc-950 border-b border-zinc-800 text-zinc-500 text-[10px] font-bold tracking-widest uppercase">
                                            <th class="p-4">Reference Registry</th>
                                            <th class="p-4">Execution Timestamp</th>
                                            <th class="p-4">Access Method Channel</th>
                                            <th class="p-4 text-right">Cumulative Net</th>
                                            <th class="p-4 text-center">Settlement</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-900 font-mono">
                                        <?php 
                                        $limited_tx = array_slice($transaction_history, 0, 3);
                                        foreach ($limited_tx as $txn): 
                                        ?>
                                            <tr class="hover:bg-zinc-900/40 transition-colors">
                                                <td class="p-4 font-bold text-zinc-300"><?php echo htmlspecialchars($txn['ref']); ?></td>
                                                <td class="p-4 text-zinc-500"><?php echo htmlspecialchars($txn['date']); ?></td>
                                                <td class="p-4 text-zinc-400 flex items-center gap-3">
                                                    <?php if (strpos($txn['method'], '../uploads/') === 0): ?>
                                                        <img src="<?php echo htmlspecialchars($txn['method']); ?>" alt="Gateway Protocol Graphic" class="h-4 w-auto object-contain rounded opacity-80 border border-zinc-800 max-w-[55px] filter brightness-90">
                                                    <?php else: ?>
                                                        <span class="text-zinc-500 tracking-tight font-sans text-xs font-medium"><?php echo htmlspecialchars($txn['method']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="p-4 text-right font-bold text-blue-400">
                                                    $<?php echo number_format($txn['amount'], 2); ?>
                                                </td>
                                                <td class="p-4 text-center">
                                                    <span class="font-bold tracking-wider uppercase px-2.5 py-1 border rounded text-[9px] <?php echo ($txn['status'] === 'Successful') ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-amber-500/20 bg-amber-500/10 text-amber-400'; ?>">
                                                        <?php echo htmlspecialchars($txn['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-10">
                                    <i class="fas fa-file-invoice-dollar text-zinc-700 text-xl mb-2 block"></i>
                                    <p class="text-xs text-zinc-500 italic">No balance shifts synced to current portfolio address.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
            
        </main>

        <?php include "../inc/footer.php"; ?>
    </div>

    <div id="ordersHistoryModal" class="hidden fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div class="bg-zinc-900 border border-zinc-800 w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col transform scale-95 transition-all duration-300 max-h-[80vh] overflow-hidden shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)]">
            <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4 bg-zinc-950/30">
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-200 flex items-center gap-2">
                        <i class="fas fa-history text-blue-500"></i> Archival Dynamic Production Manifest Ledger
                    </h3>
                    <p class="text-xs text-zinc-500">Comprehensive node arrays allocated to authenticated signature context.</p>
                </div>
                <button onclick="closeOrdersModal()" class="text-zinc-500 hover:text-zinc-300 w-8 h-8 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 transition-colors flex items-center justify-center">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6 space-y-3 flex-1 bg-zinc-950/10 border-b border-zinc-800">
                <?php foreach ($recent_orders as $order): ?>
                    <div class="bg-zinc-950/60 border border-zinc-800/80 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 group hover:border-zinc-700 transition-colors">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-mono font-bold text-zinc-400 bg-zinc-900 border border-zinc-800 px-2 py-0.5 rounded"><?php echo htmlspecialchars($order['id']); ?></span>
                                <?php 
                                    $status = strtolower($order['status']);
                                    $badge_cls = ($status === 'confirmed' || $status === 'completed' || $status === 'success') ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : (($status === 'processing' || $status === 'pending') ? 'text-amber-400 bg-amber-500/10 border-amber-500/20' : 'text-blue-400 bg-blue-500/10 border-blue-500/20');
                                ?>
                                <span class="text-[10px] font-bold px-2 py-0.5 border rounded uppercase tracking-wider <?php echo $badge_cls; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-zinc-200 tracking-tight group-hover:text-blue-400 transition-colors"><?php echo htmlspecialchars($order['title']); ?></h4>
                            <p class="text-xs text-zinc-500 font-medium">
                                <i class="fas fa-cube text-zinc-600 mr-1"></i> <?php echo htmlspecialchars($order['venue']); ?> • <span class="font-mono text-zinc-400"><?php echo htmlspecialchars($order['seats']); ?></span>
                            </p>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto shrink-0 border-t sm:border-t-0 border-zinc-800 pt-3 sm:pt-0">
                            <span class="text-xs font-bold text-zinc-400 font-mono"><?php echo htmlspecialchars($order['date']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="txHistoryModal" class="hidden fixed inset-0 z-50 bg-zinc-950/80 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300 opacity-0">
        <div class="bg-zinc-900 border border-zinc-800 w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col transform scale-95 transition-all duration-300 max-h-[80vh] overflow-hidden shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05)]">
            <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4 bg-zinc-950/30">
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-200 flex items-center gap-2">
                        <i class="fas fa-history text-blue-500"></i> Vault Registry Statements Ledger Log
                    </h3>
                    <p class="text-xs text-zinc-500">Cryptographic audit pipeline mapping database records.</p>
                </div>
                <button onclick="closeTxModal()" class="text-zinc-500 hover:text-zinc-300 w-8 h-8 rounded-lg bg-zinc-950 border border-zinc-800 hover:border-zinc-700 transition-colors flex items-center justify-center">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6 flex-1 bg-zinc-950/10">
                <div class="rounded-xl border border-zinc-800 bg-zinc-950/50 overflow-hidden">
                    <table class="w-full text-left text-xs font-medium text-zinc-400 border-collapse">
                        <thead>
                            <tr class="bg-zinc-950 border-b border-zinc-800 text-zinc-500 text-[10px] font-bold tracking-widest uppercase">
                                <th class="p-4">Reference Registry</th>
                                <th class="p-4">Execution Timestamp</th>
                                <th class="p-4">Access Method Channel</th>
                                <th class="p-4 text-right">Cumulative Net</th>
                                <th class="p-4 text-center">Settlement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-900 font-mono">
                            <?php foreach ($transaction_history as $txn): ?>
                                <tr class="hover:bg-zinc-900/40 transition-colors">
                                    <td class="p-4 font-bold text-zinc-300"><?php echo htmlspecialchars($txn['ref']); ?></td>
                                    <td class="p-4 text-zinc-500"><?php echo htmlspecialchars($txn['date']); ?></td>
                                    <td class="p-4 text-zinc-400 flex items-center gap-3">
                                        <?php if (strpos($txn['method'], '../uploads/') === 0): ?>
                                            <img src="<?php echo htmlspecialchars($txn['method']); ?>" alt="Method Protocol Graphic" class="h-4 w-auto object-contain rounded opacity-80 border border-zinc-800 max-w-[55px]">
                                        <?php else: ?>
                                            <span class="text-zinc-500 font-sans text-xs font-medium"><?php echo htmlspecialchars($txn['method']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right font-bold text-blue-400">
                                        $<?php echo number_format($txn['amount'], 2); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="font-bold tracking-wider uppercase px-2.5 py-1 border rounded text-[9px] <?php echo ($txn['status'] === 'Successful') ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' : 'border-amber-500/20 bg-amber-500/10 text-amber-400'; ?>">
                                            <?php echo htmlspecialchars($txn['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        function animateModalIn(el) {
            el.classList.remove('hidden');
            setTimeout(() => {
                el.classList.remove('opacity-0');
                el.querySelector('.transform').classList.remove('scale-95');
            }, 20);
        }

        function animateModalOut(el) {
            el.classList.add('opacity-0');
            el.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                el.classList.add('hidden');
            }, 300);
        }

        function openOrdersModal() { animateModalIn(document.getElementById('ordersHistoryModal')); }
        function closeOrdersModal() { animateModalOut(document.getElementById('ordersHistoryModal')); }
        function openTxModal() { animateModalIn(document.getElementById('txHistoryModal')); }
        function closeTxModal() { animateModalOut(document.getElementById('txHistoryModal')); }
    </script>

    <style>
        body { overflow-x: hidden; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #09090b; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>
</body>
</html>
