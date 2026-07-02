<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/header.php';

$payment = null;

/* --------------------------------------------------
   GET PAYMENT ID
-------------------------------------------------- */
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = "Invalid payment method.";
    header("Location: payment-methods.php");
    exit;
}

/* --------------------------------------------------
   FETCH PAYMENT METHOD
-------------------------------------------------- */
try {

    $stmt = $pdo->prepare("
        SELECT *
        FROM payment_methods
        WHERE payment_id = ?
    ");

    $stmt->execute([$id]);

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {

        $_SESSION['error'] = "Payment method not found.";

        header("Location: payment-methods.php");

        exit;

    }

} catch (PDOException $e) {

    $_SESSION['error'] = $e->getMessage();

    header("Location: payment-methods.php");

    exit;

}

/* --------------------------------------------------
   UPDATE
-------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $type = trim($_POST['type'] ?? '');

        $is_active = $_POST['is_active'] ?? 'yes';

        $error_msg = trim($_POST['error_msg'] ?? '');

        if (!in_array($type, ['bank','gift_card','crypto'])) {

            throw new Exception("Invalid payment type.");

        }

        if (!in_array($is_active, ['yes','no'])) {

            throw new Exception("Invalid status.");

        }

        $image = $payment['image_path'];

        /* IMAGE */

        if (!empty($_FILES['payment_image']['name'])) {

            $uploadDir = __DIR__ . '/../uploads/payment-methods/';

            if (!is_dir($uploadDir)) {

                mkdir($uploadDir,0777,true);

            }

            $filename = time().'_'.basename($_FILES['payment_image']['name']);

            move_uploaded_file(

                $_FILES['payment_image']['tmp_name'],

                $uploadDir.$filename

            );

            $image = $filename;

        }

        /* UPDATE */

        $stmt = $pdo->prepare("

            UPDATE payment_methods

            SET

                image_path=?,

                error_msg=?,

                is_active=?,

                type=?

            WHERE payment_id=?

        ");

        $stmt->execute([

            $image,

            $error_msg,

            $is_active,

            $type,

            $id

        ]);

        $_SESSION['success']="Payment method updated successfully.";

        header("Location: payment-methods.php");

        exit;

    }

    catch(Exception $e){

        $_SESSION['error']=$e->getMessage();

        header("Location: manage-payment-methods.php?id=".$id);

        exit;

    }

}

?>

<main style="max-width:700px;margin:2rem auto;padding:0 15px;">

<h1 style="text-align:center;margin-bottom:2rem;">

Edit Payment Method

</h1>

<?php if(!empty($_SESSION['error'])): ?>

<div style="background:#dc2626;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;">

<?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>

</div>

<?php endif; ?>

<?php if(!empty($_SESSION['success'])): ?>

<div style="background:#16a34a;color:#fff;padding:1rem;border-radius:8px;margin-bottom:1rem;">

<?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>

</div>

<?php endif; ?>

<form method="POST"

      enctype="multipart/form-data"

      style="background:var(--card);padding:2rem;border-radius:10px;border:1px solid var(--border);">

<label>

Payment Type

</label>

<select

name="type"

style="width:100%;padding:.8rem;margin-bottom:1rem;">

<option value="bank"

<?= $payment['type']=='bank'?'selected':'' ?>>

Bank

</option>

<option value="gift_card"

<?= $payment['type']=='gift_card'?'selected':'' ?>>

Gift Card

</option>

<option value="crypto"

<?= $payment['type']=='crypto'?'selected':'' ?>>

Crypto

</option>

</select>

<label>

Status

</label>

<select

name="is_active"

style="width:100%;padding:.8rem;margin-bottom:1rem;">

<option value="yes"

<?= $payment['is_active']=='yes'?'selected':'' ?>>

Active

</option>

<option value="no"

<?= $payment['is_active']=='no'?'selected':'' ?>>

Disabled

</option>

</select>

<label>

Disabled Message

</label>

<textarea

name="error_msg"

style="width:100%;height:120px;padding:.8rem;margin-bottom:1rem;resize:vertical;"><?= htmlspecialchars($payment['error_msg']) ?></textarea>

<label>

Current Image

</label>

<div style="margin:15px 0;">

<?php if(!empty($payment['image_path'])): ?>

<img

src="../uploads/payment-methods/<?= htmlspecialchars($payment['image_path']) ?>"

style="width:140px;height:140px;object-fit:contain;border:1px solid #ddd;border-radius:10px;padding:10px;background:#fff;">

<?php else: ?>

<span style="color:#777;">No image uploaded.</span>

<?php endif; ?>

</div>

<label>

Replace Image

</label>

<input

type="file"

name="payment_image"

style="width:100%;margin-bottom:1.5rem;">

<button

type="submit"

class="btn"

style="width:100%;">

<i class="fas fa-save"></i>

Save Changes

</button>

</form>

</main>

<?php require_once __DIR__.'/inc/footer.php'; ?>
