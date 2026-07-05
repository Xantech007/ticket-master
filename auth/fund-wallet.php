<?php
session_start();
require_once '../config/db.php';

// ---------------------------------------------
// AUTH CHECK
// ---------------------------------------------
if (!isset($_SESSION['user_id'])) {
    $_SESSION['auth_error'] = "Please login to fund your account wallet.";
    $_SESSION['redirect_after_auth'] = $_SERVER['REQUEST_URI'];
    header("Location: auth");
    exit;
}
$user_id = (int) $_SESSION['user_id'];

// ---------------------------------------------
// DATABASE CONNECTION
// ---------------------------------------------
try {
    $pdo = (new Database())->connect();
} catch (Exception $e) {
    die("Database connection failed.");
}

// Fetch user localization properties
$stmt = $pdo->prepare("SELECT country, balance FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_country = trim($user['country'] ?? '');
$current_balance = (float)($user['balance'] ?? 0.00);

// Get currency adjustment variables
$displayCurrency = isset($_GET['currency']) ? trim(strtoupper($_GET['currency'])) : 'USD';

$stmt = $pdo->prepare("SELECT exchange_rates FROM region_settings WHERE country = ? LIMIT 1");
$stmt->execute([$user_country]);
$region = $stmt->fetch(PDO::FETCH_ASSOC);
$localRate = (float)($region['exchange_rates'] ?? 1);

$displayRate = ($displayCurrency === 'USD') ? 1 : $localRate;
$symbols = ['USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'NGN'=>'₦', 'CAD'=>'C$', 'AUD'=>'A$', 'KES'=>'KSh', 'ZAR'=>'R', 'GHS'=>'GH₵'];
$displaySymbol = $symbols[$displayCurrency] ?? '$';

// ---------------------------------------------
// FETCH FILTERED PAYMENT METHODS (redirect_link = 'no')
// ---------------------------------------------
$stmt = $pdo->prepare("SELECT payment_id, name, image_path, type FROM payment_methods WHERE is_active = 'yes' AND redirect_link = 'no'");
$stmt->execute();
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------
// FORMS SUBMISSION HANDLING
// ---------------------------------------------
$errors = [];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deposit_amount = (float)($_POST['deposit_amount'] ?? 0);
    $selected_payment_id = (int)($_POST['payment_id'] ?? 0);

    if ($deposit_amount <= 0) {
        $errors[] = "Please enter a valid deposit amount greater than 0.";
    }
    
    // Verify method exists and complies with scope rules
    $stmt = $pdo->prepare("SELECT type FROM payment_methods WHERE payment_id = ? AND is_active = 'yes' AND redirect_link = 'no' LIMIT 1");
    $stmt->execute([$selected_payment_id]);
    $method_meta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$method_meta) {
        $errors[] = "Invalid or inaccessible funding method selected.";
    }

    if (empty($errors)) {
        $payment_type = $method_meta['type'];
        $submittedData = [];

        // Map dynamic form array fields depending on payment type context
        if ($payment_type === 'gift_card') {
            $submittedData['card_code'] = trim($_POST['card_code'] ?? '');
            $submittedData['card_pin'] = trim($_POST['card_pin'] ?? '');
            if (empty($submittedData['card_code'])) $errors[] = "Gift card voucher tracking code is required.";
        } elseif ($payment_type === 'bank') {
            $submittedData['sender_bank'] = trim($_POST['sender_bank'] ?? '');
            $submittedData['sender_account_name'] = trim($_POST['sender_account_name'] ?? '');
            if (empty($submittedData['sender_account_name'])) $errors[] = "Originating sender account name is required.";
        } elseif ($payment_type === 'crypto') {
            $submittedData['txid_hash'] = trim($_POST['txid_hash'] ?? '');
            $submittedData['sender_wallet'] = trim($_POST['sender_wallet'] ?? '');
            if (empty($submittedData['txid_hash'])) $errors[] = "Transaction blockchain hash tracking key is required.";
        } elseif ($payment_type === 'e_pay') {
            $submittedData['sender_email_phone'] = trim($_POST['sender_email_phone'] ?? '');
            $submittedData['transaction_reference'] = trim($_POST['transaction_reference'] ?? '');
            if (empty($submittedData['transaction_reference'])) $errors[] = "E-Pay unique transaction reference is required.";
        }

        // Handle Proof File Upload Document pipeline
        $uploadedFilePath = null;
        if (empty($errors)) {
            if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['proof_file']['tmp_name'];
                $fileName = $_FILES['proof_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $uploadFileDir = 'uploads/proofs/';
                    
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                        $uploadedFilePath = "uploads/proofs/" . $newFileName;
                    } else {
                        $errors[] = 'System migration constraints blocked moving the verification receipt.';
                    }
                } else {
                    $errors[] = 'Invalid file extension rules. Allowed formats: ' . implode(', ', $allowedExtensions);
                }
            } else {
                $errors[] = 'A transaction screenshot image or receipt attachment document is required.';
            }
        }

        // Commit execution parameters cleanly into system deposits engine logs
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // 'order_ids' is flagged with 'wallet_funding' to differentiate it from basic concert ticket processing sequences
                $insertStmt = $pdo->prepare("
                    INSERT INTO deposits (user_id, payment_id, order_ids, amount, currency, payment_type, submitted_details, proof_file, status) 
                    VALUES (?, ?, 'wallet_funding', ?, ?, ?, ?, ?, 'pending')
                ");

                $insertStmt->execute([
                    $user_id,
                    $selected_payment_id,
                    $deposit_amount,
                    $displayCurrency,
                    $payment_type,
                    json_encode($submittedData),
                    $uploadedFilePath
                ]);

                $pdo->commit();
                $_SESSION['flash_success'] = "Wallet funding application logged successfully! Once confirmed, your balance will reflect.";
                header("Location: dashboard");
                exit;

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = "Failed protecting database parameters inside allocation cycle: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include "../inc/head.php"; ?>
<?php include "../inc/navbar.php"; ?>
<body class="bg-gradient-to-br from-slate-100 via-white to-slate-200 min-h-screen" onload="toggleGatewayInputs()">
<?php include "../inc/header.php"; ?>

<div class="max-w-4xl mx-auto px-5 py-10">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 p-4 text-red-700 rounded shadow-md">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-800 to-slate-900 p-8 text-white flex justify-between items-center">
            <div>
                <span class="text-xs uppercase font-bold tracking-widest bg-white/20 px-3 py-1 rounded-full">Account Credit Hub</span>
                <h1 class="text-3xl font-black mt-2">Fund Wallet Balance</h1>
            </div>
            <div class="text-right">
                <span class="text-xs block opacity-70 uppercase tracking-wide">Current Balance</span>
                <span class="text-2xl font-black"><?= $displaySymbol . number_format($current_balance, 2) . ' ' . $displayCurrency ?></span>
            </div>
        </div>

        <div class="p-8">
            <?php if (!empty($errors)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 text-red-700 rounded-r-xl">
                    <p class="font-bold">Errors encountered during submission process:</p>
                    <ul class="list-disc pl-5 text-sm mt-1">
                        <?php foreach($errors as $error) echo "<li>".htmlspecialchars($error)."</li>"; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
                    <label class="block text-xs uppercase font-bold text-slate-500 mb-2">Specify Funding Amount (<?= $displayCurrency ?>)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-bold text-slate-400"><?= $displaySymbol ?></span>
                        <input type="number" step="0.01" min="1" name="deposit_amount" required 
                               class="w-full bg-white border border-slate-300 rounded-xl pl-10 pr-4 py-3.5 font-black text-lg focus:outline-none focus:border-indigo-600 tracking-wide" 
                               placeholder="0.00" value="<?= isset($_POST['deposit_amount']) ? htmlspecialchars($_POST['deposit_amount']) : '' ?>">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs uppercase font-bold text-slate-500">Choose Settlement Method</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($payment_methods as $index => $method): ?>
                            <label class="border border-slate-200 rounded-2xl p-4 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition peer">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_id" value="<?= $method['payment_id'] ?>" 
                                           data-type="<?= $method['type'] ?>" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500" 
                                           <?= ($index === 0) ? 'checked' : '' ?> onchange="toggleGatewayInputs()">
                                    <span class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($method['name']) ?></span>
                                </div>
                                <img src="uploads/payment-methods/<?= htmlspecialchars($method['image_path']) ?>" 
                                     alt="Gateway Logo" class="h-8 w-auto object-contain">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="dynamic-metadata-fields" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hidden space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider pb-2 border-b">Transactional Fields</h3>
                    
                    <div id="field-group-gift_card" class="gateway-fields hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Voucher Claim Reference Code</label>
                            <input type="text" name="card_code" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none font-mono" placeholder="XXXX-XXXX-XXXX">
                        </div>
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Voucher Pin Profile Code (Optional)</label>
                            <input type="text" name="card_pin" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none font-mono" placeholder="1234">
                        </div>
                    </div>

                    <div id="field-group-bank" class="gateway-fields hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Your Executing Bank Title</label>
                            <input type="text" name="sender_bank" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none" placeholder="e.g. Barclays Bank">
                        </div>
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Transfer Remitter Account Name</label>
                            <input type="text" name="sender_account_name" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none" placeholder="John Doe">
                        </div>
                    </div>

                    <div id="field-group-crypto" class="gateway-fields hidden space-y-4">
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Transaction Link Block Hash / TXID</label>
                            <input type="text" name="txid_hash" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none font-mono" placeholder="0x3fca91...">
                        </div>
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">External Outgoing Wallet Remit Address (Optional)</label>
                            <input type="text" name="sender_wallet" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none font-mono" placeholder="Source network address">
                        </div>
                    </div>

                    <div id="field-group-e_pay" class="gateway-fields hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Your Registered Identifier Name/Email</label>
                            <input type="text" name="sender_email_phone" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none" placeholder="alias@example.com">
                        </div>
                        <div>
                            <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Merchant Settlement Reference Code</label>
                            <input type="text" name="transaction_reference" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:outline-none font-mono" placeholder="REF-88910">
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs uppercase font-bold text-slate-500 mb-1">Upload Receipt Attachment / Proof of Remittance</label>
                        <input type="file" name="proof_file" required class="w-full border border-dashed border-slate-300 bg-slate-50/50 rounded-xl px-4 py-4 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-700 file:text-white hover:file:bg-slate-800 cursor-pointer">
                        <small class="text-slate-400 block mt-1">Accepted validation structural file patterns: JPEG, PNG, PDF.</small>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-2">
                    <a href="dashboard" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-6 py-3.5 rounded-xl text-center transition text-sm">
                        Return to Dashboard
                    </a>
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-black px-6 py-3.5 rounded-xl shadow-lg hover:shadow-xl transition text-center tracking-wide text-sm">
                        Submit Funding Documentation <i class="fas fa-upload ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleGatewayInputs() {
    const activeRadio = document.querySelector('input[name="payment_id"]:checked');
    const panel = document.getElementById('dynamic-metadata-fields');
    
    // Hide all programmatic tracking containers
    document.querySelectorAll('.gateway-fields').forEach(div => div.classList.add('hidden'));
    
    if (activeRadio) {
        const selectedType = activeRadio.getAttribute('data-type');
        const activeContainer = document.getElementById('field-group-' + selectedType);
        
        panel.classList.remove('hidden');
        if (activeContainer) {
            activeContainer.classList.remove('hidden');
        }
    }
}
</script>

<?php include "../inc/footer.php"; ?>
</body>
</html>
