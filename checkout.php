<?php
session_start();

require_once 'config/db.php';

// ---------------------------------------------
// AUTH CHECK (MATCHING YOUR SYSTEM)
// ---------------------------------------------
if (!isset($_SESSION['user_id'])) {

    $_SESSION['auth_error'] = "Please login to continue checkout.";
    $_SESSION['redirect_after_auth'] = $_SERVER['REQUEST_URI'];

    header("Location: auth.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Enable error reporting (dev only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ---------------------------------------------
// GET ORDER IDS FROM SESSION
// ---------------------------------------------
if (
    !isset($_SESSION['checkout_order_ids']) ||
    !is_array($_SESSION['checkout_order_ids']) ||
    empty($_SESSION['checkout_order_ids'])
) {
    die("Invalid order reference.");
}

$order_ids = array_map('intval', $_SESSION['checkout_order_ids']);

// ---------------------------------------------
// DB CONNECTION
// ---------------------------------------------
$pdo = null;

try {
    if (class_exists('Database')) {
        $pdo = (new Database())->connect();
    }
} catch (Exception $e) {
    die("Database connection failed.");
}

// ---------------------------------------------
// FETCH ORDERS (SECURED)
// ---------------------------------------------
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));

$stmt = $pdo->prepare("
    SELECT o.*, t.ticket_name, t.price, c.title
    FROM orders o
    JOIN tickets t ON o.ticket_id = t.ticket_id
    JOIN concerts c ON t.concert_id = c.concert_id
    WHERE o.order_id IN ($placeholders)
");

$stmt->execute($order_ids);
$all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$all_orders) {
    die("Order not found.");
}

// ---------------------------------------------
// SECURITY CHECK: ENSURE OWNERSHIP
// ---------------------------------------------
foreach ($all_orders as $order) {
    if ((int)$order['user_id'] !== $user_id) {
        die("Unauthorized access: order does not belong to this user.");
    }
}

// ---------------------------------------------
// USER COUNTRY
// ---------------------------------------------
$stmt = $pdo->prepare("
    SELECT country
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_country = $user['country'] ?? '';

// ---------------------------------------------
// REGION SETTINGS
// ---------------------------------------------
$stmt = $pdo->prepare("
    SELECT currency, exchange_rates
    FROM region_settings
    WHERE country = ?
    LIMIT 1
");

$stmt->execute([$user_country]);

$region = $stmt->fetch(PDO::FETCH_ASSOC);

$currency = $region['currency'] ?? 'USD';

$exchange_rate = (float)($region['exchange_rates'] ?? 1);

$symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'NGN' => '₦',
    'CAD' => 'C$',
    'AUD' => 'A$'
];

$curr_meta = [
    'symbol' => $symbols[$currency] ?? '$',
    'rate'   => $exchange_rate
];

$exchange_rates = [
    $currency => $curr_meta
];

// ---------------------------------------------
// SUPPORT CONTACTS
// ---------------------------------------------
$support = [
    'email' => '',
    'telegram' => '',
    'whatsapp' => ''
];

$stmt = $pdo->query("
    SELECT
        email,
        telegram,
        whatsapp
    FROM admins
    LIMIT 1
");

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    $support = $admin;
}

// Now safe to continue using filtered orders
$order_items = $all_orders;

// ---------------------------------------------
// TOTAL CALCULATION
// ---------------------------------------------
$total_amount = 0;
$order_title = $order_items[0]['title'] ?? 'Order';

foreach ($order_items as $item) {
    $total_amount += (float)$item['price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include "inc/head.php"; ?>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
<?php include "inc/header.php"; ?>

<?php
// ---------------------------------------------------------------------
// FIX: MULTI ORDER SUPPORT (from save_order.php redirect)
// ---------------------------------------------------------------------

// Load DB
$pdo = (new Database())->connect();

// Fetch ALL orders
$placeholders = implode(',', array_fill(0, count($order_ids), '?'));

$stmt = $pdo->prepare("
    SELECT o.*, t.ticket_name, t.price, c.title
    FROM orders o
    JOIN tickets t ON o.ticket_id = t.ticket_id
    JOIN concerts c ON t.concert_id = c.concert_id
    WHERE o.order_id IN ($placeholders)
    AND o.user_id = ?
");

$stmt->execute(array_merge($order_ids, [$user_id]));
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$order_items) {
    die("Order not found.");
}

// ---------------------------------------------------------------------
// AGGREGATE TOTAL (NEW FIX)
// ---------------------------------------------------------------------
$total_amount = 0;
$order_title = $order_items[0]['title'] ?? 'Concert Order';

foreach ($order_items as $item) {
    $total_amount += $item['price'];
}
?>

<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

    <div class="flex flex-col sm:flex-row justify-between items-center bg-white border border-gray-200 p-6 rounded-2xl shadow-sm mb-8 gap-4">
    
        <div>
            <h1 class="text-xl font-black tracking-tight text-gray-900 uppercase">
                Secure Transaction Gateway
            </h1>
    
            <p class="text-xs text-gray-400 font-semibold mt-0.5">
                Order Batch Reference:
                <span class="font-mono text-gray-600">
                    #TM-<?php echo implode('-', $order_ids); ?>
                </span>
            </p>
        </div>
    
        <div class="relative inline-block text-left group">
    
            <button class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-sm inline-flex items-center gap-2">
                <i class="fas fa-headset text-amber-400"></i>
                Contact Live Support
                <i class="fas fa-chevron-down text-[10px]"></i>
            </button>
    
            <div class="absolute right-0 w-52 bg-white border border-gray-200 rounded-xl shadow-xl py-1 mt-1 hidden group-hover:block z-50">
    
                <a href="https://wa.me/<?php echo urlencode($support['whatsapp']); ?>"
                   target="_blank"
                   class="flex items-center gap-2 px-4 py-2 text-xs font-bold hover:bg-green-50">
    
                    <i class="fab fa-whatsapp"></i>
                    WhatsApp
                </a>
    
                <a href="https://t.me/<?php echo urlencode($support['telegram']); ?>"
                   target="_blank"
                   class="flex items-center gap-2 px-4 py-2 text-xs font-bold hover:bg-sky-50">
    
                    <i class="fab fa-telegram-plane"></i>
                    Telegram
                </a>
    
                <a href="mailto:<?php echo htmlspecialchars($support['email']); ?>"
                   class="flex items-center gap-2 px-4 py-2 text-xs font-bold hover:bg-blue-50">
    
                    <i class="fas fa-envelope"></i>
                    Email
                </a>
    
            </div>
    
        </div>
    
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- LEFT PAYMENT PANEL (UNCHANGED UI) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Currency Switch -->
            <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
                <h3 class="text-xs font-black uppercase text-gray-400 mb-3">
                    Select Currency
                </h3>

                <div class="grid grid-cols-3 gap-2">
                    <?php foreach ($exchange_rates as $key => $rates): ?>
                        <a href="checkout.php?currency=<?php echo urlencode($key); ?>">
                           class="px-4 py-3 border text-center rounded-xl font-black text-xs uppercase
                           <?php echo $currency === $key ? 'border-[#024DDF] bg-blue-50 text-[#024DDF]' : 'border-gray-200 text-gray-600'; ?>">
                            <?php echo $key; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Form -->
            <form action="checkout.php?currency=<?php echo urlencode($currency); ?>"
                  method="POST"
                  enctype="multipart/form-data">

                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="giftcard">

                <!-- PAYMENT UI (UNCHANGED) -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

                    <div class="p-6 space-y-3">

                        <p class="text-xs font-medium text-gray-500">
                            Choose payment method for batch checkout
                        </p>

                        <!-- Giftcard / Crypto / Bank tabs remain unchanged -->
                        <!-- (KEEP YOUR ORIGINAL UI HERE EXACTLY) -->

                    </div>
                </div>

                <!-- Upload Section (UNCHANGED) -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 mt-6">
                    <button type="submit"
                            class="w-full bg-slate-900 hover:bg-black text-white font-black text-xs uppercase py-4 rounded-xl">
                        Finalize Batch Payment
                    </button>
                </div>

            </form>
        </div>

        <!-- RIGHT SUMMARY PANEL (FIXED) -->
        <div class="lg:col-span-5 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">

            <h3 class="text-xs font-black uppercase text-gray-400 mb-4">
                Order Summary (Batch)
            </h3>

            <div class="space-y-3">
                <?php foreach ($order_items as $item): ?>
                    <div class="border-b pb-3">
                        <p class="text-xs font-black text-gray-900 uppercase">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </p>
                        <p class="text-[10px] text-gray-500">
                            <?php echo htmlspecialchars($item['ticket_name']); ?>
                        </p>
                        <p class="text-xs font-mono text-gray-700">
                            $<?php echo number_format($item['price'], 2); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-t mt-4 pt-4 flex justify-between">
                <span class="text-xs font-black">TOTAL</span>
                <span class="text-lg font-black text-[#024DDF]">
                    <?php echo $curr_meta['symbol'] . number_format($total_amount * $curr_meta['rate'], 2); ?>
                </span>
            </div>

        </div>

    </div>
</div>

<?php include "inc/footer.php"; ?>
</body>
</html>
