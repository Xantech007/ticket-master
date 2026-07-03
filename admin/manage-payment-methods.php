<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

/* --------------------------------------------------
   FETCH PAYMENT METHODS
-------------------------------------------------- */
try {
    $stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY payment_id DESC");
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $payments = [];
}

/* --------------------------------------------------
   HANDLE ACTIONS
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    $action = $_POST['action'] ?? '';

    try {

        /* ---------------- ADD PAYMENT METHOD ---------------- */
        if ($action === 'add') {

            $type      = $_POST['type'] ?? '';
            $error_msg = trim($_POST['error_msg'] ?? '');
            $is_active = $_POST['is_active'] ?? 'yes';

            if (!in_array($type, ['bank', 'gift_card', 'crypto'])) {
                throw new Exception("Invalid payment type.");
            }

            if (!in_array($is_active, ['yes', 'no'])) {
                $is_active = 'yes';
            }

            if (empty($_FILES['image']['name'])) {
                throw new Exception("Please upload an image.");
            }

            $uploadDir = __DIR__ . "/../uploads/payment-methods/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $target = $uploadDir . $imageName;

            move_uploaded_file($_FILES['image']['tmp_name'], $target);

            $stmt = $pdo->prepare("
                INSERT INTO payment_methods
                (image_path, error_msg, is_active, type)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $imageName,
                $error_msg,
                $is_active,
                $type
            ]);

            $_SESSION['success'] = "Payment method added successfully.";

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        /* ---------------- DELETE PAYMENT METHOD ---------------- */
        if ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid payment ID.");
            }

            $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE payment_id=?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Payment method deleted successfully.";

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

    } catch (Exception $e) {

        $_SESSION['error'] = $e->getMessage();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>

<main>

<h1 style="text-align:center;margin:2rem 0;">Manage Payment Methods</h1>

<?php if (!empty($_SESSION['success'])): ?>
<div style="background:#238636;color:#fff;padding:1rem;border-radius:8px;text-align:center;max-width:900px;margin:1rem auto;">
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div style="background:#f85149;color:#fff;padding:1rem;border-radius:8px;text-align:center;max-width:900px;margin:1rem auto;">
    <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="text-align:center;margin-bottom:2rem;">
    <button onclick="openModal()" class="btn">
        + Add Payment Method
    </button>
</div>

<div style="max-width:1100px;margin:0 auto;overflow-x:auto;padding:0 10px;">

<table style="width:100%;border-collapse:collapse;background:var(--card);border:1px solid var(--border);border-radius:10px;min-width:850px;">

<thead>
<tr style="background:#111827;text-align:left;">
    <th style="padding:12px;">Image</th>
    <th style="padding:12px;">Type</th>
    <th style="padding:12px;">Error Message</th>
    <th style="padding:12px;">Status</th>
    <th style="padding:12px;">Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($payments as $payment): ?>

<tr style="border-top:1px solid var(--border);">

<td style="padding:12px;">
    <img src="../uploads/payment-methods/<?= htmlspecialchars($payment['image_path']) ?>"
         style="width:70px;height:50px;object-fit:contain;border-radius:6px;">
</td>

<td style="padding:12px;">
    <?= ucfirst(htmlspecialchars($payment['type'])) ?>
</td>

<td style="padding:12px;max-width:300px;">
    <?= htmlspecialchars(mb_strimwidth($payment['error_msg'],0,60,'...')) ?>
</td>

<td style="padding:12px;">

<?php if($payment['is_active']=='yes'): ?>

<span style="background:#238636;padding:5px 10px;border-radius:20px;color:#fff;">
Active
</span>

<?php else: ?>

<span style="background:#dc3545;padding:5px 10px;border-radius:20px;color:#fff;">
Inactive
</span>

<?php endif; ?>

</td>

<td style="padding:12px;white-space:nowrap;">

<a href="edit-payment-method.php?id=<?= $payment['payment_id'] ?>"
   class="btn green"
   style="padding:6px 10px;font-size:13px;">
    Edit
</a>

<form method="POST"
      style="display:inline-block;margin-left:5px;"
      onsubmit="return confirm('Delete this payment method?');">

<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= $payment['payment_id'] ?>">

<button class="btn red"
        style="padding:6px 10px;font-size:13px;">
Delete
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<!-- MODAL -->

<div id="paymentModal"
style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
background:rgba(0,0,0,.7);overflow-y:auto;padding:20px;">

<div style="
background:#0d1117;
max-width:500px;
margin:40px auto;
padding:2rem;
border-radius:10px;
max-height:90vh;
overflow-y:auto;
">

<h2>Add Payment Method</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="action" value="add">

<label>Type</label>

<select name="type"
style="width:100%;padding:.7rem;margin-bottom:1rem;">

<option value="bank">Bank</option>
<option value="gift_card">Gift Card</option>
<option value="crypto">Crypto</option>

</select>

<label>Error Message</label>

<textarea name="error_msg"
style="width:100%;padding:.7rem;margin-bottom:1rem;"
rows="4"></textarea>

<label>Status</label>

<select name="is_active"
style="width:100%;padding:.7rem;margin-bottom:1rem;">

<option value="yes">Active</option>
<option value="no">Inactive</option>

</select>

<label>Image</label>

<input type="file"
name="image"
required
style="margin-bottom:1rem;">

<button class="btn" style="width:100%;">
Save
</button>

</form>

<br>

<button onclick="closeModal()" class="btn red" style="width:100%;">
Close
</button>

</div>

</div>

<script>

function openModal(){
    document.getElementById('paymentModal').style.display='block';
}

function closeModal(){
    document.getElementById('paymentModal').style.display='none';
}

</script>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
