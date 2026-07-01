<?php
// checkout.php - Premium Responsive Platform Checkout Portal Ledger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

// Mock active session user identifier if auth context isn't fully initialized
$user_id = 1; 

$pdo = null;
try {
    if (class_exists('Database')) {
        $dbInstance = new Database();
        $pdo = $dbInstance->connect(); 
    }
} catch (Exception $e) {
    // Structural database connection error fail-safes
}

// -------------------------------------------------------------------------
// DATA LAYER FETCHES (Support Channels, Crypto Addresses, Cards, and Order Items)
// -------------------------------------------------------------------------
// Default fallbacks if administration data tables do not have values yet
$support = ['whatsapp' => '+1555000000', 'telegram' => 'PlatformSupportTextBot', 'email' => 'support@ticketmaster.xo.je'];
$cryptos = [];
$giftcards = [];
$order_item = ['title' => 'The Eras World Tour Live Finale Pass', 'base_price' => 150.00, 'qty' => 1];

if ($pdo !== null) {
    try {
        // Fetch support contacts from admins table
        $stmt = $pdo->query("SELECT email, telegram, whatsapp FROM admins LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $support['email'] = $admin['email'];
            $support['telegram'] = $admin['telegram'];
            $support['whatsapp'] = $admin['whatsapp'];
        }
        // Fetch Administrative Enabled Payment Vectors
        $cryptos = $pdo->query("SELECT * FROM payment_crypto WHERE is_active = 1 ORDER BY id DESC")->fetchAll();
        $giftcards = $pdo->query("SELECT * FROM payment_giftcards WHERE is_active = 1 ORDER BY id DESC")->fetchAll();
    } catch (Exception $e) {
        // Fallback array mock settings population if tables don't exist yet
        if (empty($cryptos)) {
            $cryptos = [
                ['id' => 1, 'crypto_name' => 'Bitcoin', 'network' => 'BTC / SegWit Network', 'address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'],
                ['id' => 2, 'crypto_name' => 'Ethereum', 'network' => 'ERC-20 Mainnet', 'address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e']
            ];
        }
        if (empty($giftcards)) {
            $giftcards = [
                ['id' => 1, 'card_name' => 'Apple Store Card', 'face_value' => 100.00],
                ['id' => 2, 'card_name' => 'Razer Gold Pass', 'face_value' => 50.00]
            ];
        }
    }
}

// --- Dynamic Currency Conversion Multipliers Matrix Ledger ---
$currency = isset($_GET['currency']) ? $_GET['currency'] : 'USD';
$exchange_rates = ['USD' => ['symbol' => '$', 'rate' => 1.0], 'EUR' => ['symbol' => '€', 'rate' => 0.92], 'GBP' => ['symbol' => '£', 'rate' => 0.78]];
$curr_meta = isset($exchange_rates[$currency]) ? $exchange_rates[$currency] : $exchange_rates['USD'];

$converted_total = $order_item['base_price'] * $curr_meta['rate'];

// -------------------------------------------------------------------------
// FORM SUBMISSION HANDLER PIPELINE (Saves orders to User Dashboard)
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null) {
    try {
        $payment_method = $_POST['payment_method'];
        $meta_description = isset($_POST['user_description']) ? trim($_POST['user_description']) : '';
        $uploaded_images_urls = [];

        // Handle uploaded image verification receipts files block array
        if (isset($_FILES['receipt_images'])) {
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

        // Commit order entry parameters to the DB ledger architecture safely
        $stmt = $pdo->prepare("INSERT INTO user_orders (user_id, item_title, amount, currency, payment_method, proof_images, description, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'processing', NOW())");
        $stmt->execute([$user_id, $order_item['title'], $converted_total, $currency, $payment_method, $images_json, $meta_description]);

        echo "<script>alert('Receipt submitted! Your order is now processing pending administrator confirmation.'); window.location.href='dashboard.php';</script>";
        exit;
    } catch (Exception $e) {
        $error_log = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include "inc/head.php"; ?>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <?php include "inc/header.php"; ?>

    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        
        <div class="flex flex-col sm:flex-row justify-between items-center bg-white border border-gray-200 p-6 rounded-2xl shadow-sm mb-8 gap-4">
            <div>
                <h1 class="text-xl font-black tracking-tight text-gray-900 uppercase">Secure Transaction Gateway</h1>
                <p class="text-xs text-gray-400 font-semibold mt-0.5">Order Token Index Tracking Session Ref: #TM-<?php echo rand(10000,99999); ?></p>
            </div>
            
            <div class="relative inline-block text-left group">
                <button class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all shadow-sm inline-flex items-center gap-2">
                    <i class="fas fa-headset text-amber-400"></i> Contact Live Support Desk <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
                <div class="absolute right-0 w-52 bg-white border border-gray-200 rounded-xl shadow-xl py-1 mt-1 hidden group-hover:block z-50">
                    <a href="https://wa.me/<?php echo $support['whatsapp']; ?>?text=Hello%20Support,%20I%20am%20checking%20out%20for%20<?php echo urlencode($order_item['title']); ?>.%" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                        <i class="fab fa-whatsapp text-base"></i> WhatsApp Live Desk
                    </a>
                    <a href="https://t.me/<?php echo $support['telegram']; ?>?start=order_info" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-sky-50 hover:text-sky-600 transition-colors">
                        <i class="fab fa-telegram-plane text-base"></i> Telegram Channel
                    </a>
                    <a href="mailto:<?php echo $support['email']; ?>?subject=Order%20Payment%20Assistance" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-blue-50 hover:text-[#024DDF] transition-colors">
                        <i class="fas fa-envelope text-base"></i> Email Matrix Ticket
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white border border-gray-200 p-6 rounded-2xl shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">Select Operating Settlement Currency</h3>
                    <div class="grid grid-cols-3 gap-2">
                        <?php foreach ($exchange_rates as $key => $rates): ?>
                            <a href="checkout.php?currency=<?php echo $key; ?>" class="px-4 py-3 border text-center rounded-xl font-black text-xs transition-all uppercase tracking-wider <?php echo $currency === $key ? 'border-[#024DDF] bg-blue-50 text-[#024DDF]' : 'border-gray-200 hover:bg-gray-50 text-gray-600'; ?>">
                                <?php echo $key; ?> (<?php echo $rates['symbol']; ?>)
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form action="checkout.php?currency=<?php echo $currency; ?>" method="POST" enctype="multipart/form-data" id="masterPaymentForm">
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="giftcard">
                    
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-6">
                        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Choose Funding Destination Channel</h3>
                        </div>
                        <div class="grid grid-cols-3 border-b border-gray-200 font-black text-xs uppercase tracking-tight text-center">
                            <button type="button" onclick="switchGatewayTab('giftcard')" id="btn-tab-giftcard" class="py-4 border-b-2 border-[#024DDF] text-[#024DDF] transition-all flex flex-col sm:flex-row items-center justify-center gap-1.5 bg-blue-50/40">
                                <i class="fas fa-ticket-alt"></i> Gift Card
                            </button>
                            <button type="button" onclick="switchGatewayTab('crypto')" id="btn-tab-crypto" class="py-4 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition-all flex flex-col sm:flex-row items-center justify-center gap-1.5">
                                <i class="fab fa-bitcoin"></i> Crypto Web3
                            </button>
                            <button type="button" onclick="switchGatewayTab('bank')" id="btn-tab-bank" class="py-4 border-b-2 border-transparent text-gray-400 hover:text-gray-700 transition-all flex flex-col sm:flex-row items-center justify-center gap-1.5">
                                <i class="fas fa-university"></i> Bank Wire
                            </button>
                        </div>

                        <div class="p-6">
                            <div id="panel-gateway-giftcard" class="space-y-4">
                                <p class="text-xs font-medium text-gray-500 leading-relaxed">Select from our verified administrative support card matrix clear lists to compute exchange allocations:</p>
                                <div class="space-y-2">
                                    <?php foreach ($giftcards as $card): ?>
                                        <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50/80 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <input type="radio" name="selected_card_id" value="<?php echo $card['id']; ?>" class="w-4 h-4 text-[#024DDF] focus:ring-0" checked>
                                                <span class="text-xs font-black text-gray-800 uppercase tracking-tight"><?php echo htmlspecialchars($card['card_name']); ?></span>
                                            </div>
                                            <span class="font-mono text-xs font-black text-slate-700">
                                                Value: <?php echo $curr_meta['symbol'] . number_format($card['face_value'] * $curr_meta['rate'], 2); ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div id="panel-gateway-crypto" class="space-y-4 hidden">
                                <p class="text-xs font-medium text-gray-500 leading-relaxed">Send the exact total translation token equivalent amount directly to any secure node address parameter specified here:</p>
                                <div class="space-y-3">
                                    <?php foreach ($cryptos as $index => $coin): ?>
                                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 space-y-2">
                                            <div class="flex justify-between items-center border-b border-gray-100 pb-1.5">
                                                <span class="text-xs font-black text-gray-900 uppercase tracking-wide"><i class="fas fa-coins text-amber-500 mr-1"></i> <?php echo htmlspecialchars($coin['crypto_name']); ?></span>
                                                <span class="text-[10px] bg-slate-200 text-slate-800 px-2 py-0.5 rounded font-black uppercase tracking-wider"><?php echo htmlspecialchars($coin['network']); ?></span>
                                            </div>
                                            <div class="flex items-center gap-2 bg-white border border-gray-200 px-3 py-2 rounded-lg justify-between">
                                                <span id="crypto-addr-<?php echo $index; ?>" class="font-mono text-[11px] text-gray-600 truncate block font-bold tracking-tight select-all"><?php echo htmlspecialchars($coin['address']); ?></span>
                                                <button type="button" onclick="navigator.clipboard.writeText('<?php echo $coin['address']; ?>'); alert('Address clipped successfully!');" class="text-gray-400 hover:text-blue-600 transition-colors text-xs p-1"><i class="far fa-copy"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div id="panel-gateway-bank" class="space-y-4 hidden text-center py-6">
                                <div class="w-12 h-12 bg-blue-50 text-[#024DDF] rounded-full flex items-center justify-center mx-auto mb-3 text-lg"><i class="fas fa-comments-dollar"></i></div>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight">Direct Wire Assistance Verification</h4>
                                <p class="text-xs text-gray-500 max-w-sm mx-auto leading-relaxed font-medium">To complete secure manual settlement through your local bank accounts, connect directly with our active support coordinator desk.</p>
                                <a href="https://wa.me/<?php echo $support['whatsapp']; ?>?text=I%20want%20to%20pay%20via%20Bank%20Transfer%20for%20<?php echo urlencode($order_item['title']); ?>%20amounting%20to%20<?php echo $curr_meta['symbol'].number_format($converted_total,2); ?>" target="_blank" class="mt-2 inline-flex items-center gap-2 px-5 py-3 bg-[#024DDF] hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-md">
                                    <i class="fab fa-whatsapp"></i> Initialize Support Desk Wire Integration
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id="progressiveUploadSection" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm mb-6 space-y-4">
                        
                        <div id="initialTriggerBlock" class="text-center py-2">
                            <button type="button" onclick="revealVerificationUploadsPipeline()" class="w-full bg-[#024DDF] hover:bg-blue-800 text-white font-black text-xs uppercase tracking-widest py-4 rounded-xl transition-all shadow-md tracking-wider">
                                <i class="fas fa-check-circle mr-1"></i> I Have Paid / Transferred Fund Asset
                            </button>
                        </div>

                        <div id="hiddenUploadBlock" class="hidden animate-[fadeIn_0.3s_ease-out] space-y-4 border-t border-gray-100 pt-4">
                            <div class="bg-emerald-50/60 border border-emerald-100 p-4 rounded-xl flex items-start gap-2.5">
                                <i class="fas fa-info-circle text-emerald-600 mt-0.5 text-sm"></i>
                                <p class="text-[11px] text-emerald-800 font-medium leading-normal">Asset ledger marked as sent. To secure transaction indexing slots, drop image receipts confirmation documents array modules below.</p>
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-wide mb-1">Upload Receipt Images (Multiple Allowed)</label>
                                <input type="file" name="receipt_images[]" id="fileUploadInput" class="w-full text-xs font-bold text-gray-500 bg-gray-50 border border-gray-200 file:border-0 file:bg-slate-900 file:text-white file:px-4 file:py-2.5 file:rounded-xl file:text-xs file:font-black file:uppercase file:tracking-wider file:mr-4 file:cursor-pointer rounded-xl pr-3 cursor-pointer" multiple>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase text-gray-400 tracking-wide mb-1">Optional Execution Context / Description Notes</label>
                                <textarea name="user_description" rows="3" placeholder="Enter Transaction ID reference points, physical location, or serial card codes..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs font-medium outline-none focus:ring-2 focus:ring-blue-100 focus:border-[#024DDF] transition-all"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-black text-xs uppercase tracking-widest py-3.5 rounded-xl transition-all shadow-sm">
                                Finalize Order Placement Submission
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-5 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden sticky top-6">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-400">Order Manifest Items</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start justify-between gap-4 border-b border-gray-100 pb-4">
                        <div>
                            <h4 class="text-xs font-black text-gray-900 uppercase leading-snug"><?php echo htmlspecialchars($order_item['title']); ?></h4>
                            <span class="text-[10px] text-gray-400 font-bold block mt-1 uppercase">Standard Entry Access Configuration Ledger</span>
                        </div>
                        <span class="text-xs font-bold text-gray-400 font-mono">x<?php echo $order_item['qty']; ?></span>
                    </div>
                    
                    <div class="space-y-2 text-xs text-gray-500 font-semibold">
                        <div class="flex justify-between items-center">
                            <span>Base Order Subtotal:</span>
                            <span class="font-mono text-gray-700"><?php echo $curr_meta['symbol'] . number_format($converted_total, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Transactional Infrastructure Provisioning:</span>
                            <span class="font-mono text-emerald-600 uppercase font-black text-[10px] bg-emerald-50 px-2 py-0.5 rounded">Free Allocation</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-200 pt-4 flex justify-between items-baseline">
                        <span class="text-xs font-black uppercase text-gray-900 tracking-tight">Total Settlement Sum:</span>
                        <span class="text-xl font-black font-mono text-[#024DDF]">
                            <?php echo $curr_meta['symbol'] . number_format($converted_total, 2); ?> <span class="text-[11px] font-sans font-black text-gray-400"><?php echo $currency; ?></span>
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function switchGatewayTab(gatewayType) {
            // Document active payment method value pointer references
            document.getElementById('selectedPaymentMethod').value = gatewayType;

            // Reset dynamic tab active color elements mappings
            const formats = ['giftcard', 'crypto', 'bank'];
            formats.forEach(type => {
                const btn = document.getElementById('btn-tab-' + type);
                const panel = document.getElementById('panel-gateway-' + type);
                
                if (type === gatewayType) {
                    btn.classList.add('border-[#024DDF]', 'text-[#024DDF]', 'bg-blue-50/40');
                    btn.classList.remove('border-transparent', 'text-gray-400');
                    panel.classList.remove('hidden');
                } else {
                    btn.classList.remove('border-[#024DDF]', 'text-[#024DDF]', 'bg-blue-50/40');
                    btn.classList.add('border-transparent', 'text-gray-400');
                    panel.classList.add('hidden');
                }
            });

            // Prevent required checking dependencies conflicts during Bank Wire redirections
            const fileInput = document.getElementById('fileUploadInput');
            if (gatewayType === 'bank') {
                fileInput.removeAttribute('required');
                document.getElementById('progressiveUploadSection').classList.add('opacity-40', 'pointer-events-none');
            } else {
                document.getElementById('progressiveUploadSection').classList.remove('opacity-40', 'pointer-events-none');
            }
        }

        function revealVerificationUploadsPipeline() {
            // Hide trigger node block container layout element
            document.getElementById('initialTriggerBlock').classList.add('hidden');
            
            // Unmask structural file input and text verification blocks
            document.getElementById('hiddenUploadBlock').classList.remove('hidden');
            
            // Set input file node configuration requirements dynamically on deployment mapping
            if (document.getElementById('selectedPaymentMethod').value !== 'bank') {
                document.getElementById('fileUploadInput').setAttribute('required', 'required');
            }
        }
    </script>

    <?php include "inc/footer.php"; ?>
</body>
</html>
