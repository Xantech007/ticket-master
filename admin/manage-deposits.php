<?php
// admin/manage-deposits.php
require_once __DIR__ . '/inc/header.php';

$message='';
$error='';

/* UPDATE STATUS */

if($_SERVER['REQUEST_METHOD']==='POST'){

$deposit_id=(int)($_POST['deposit_id'] ?? 0);
$new_status=(int)($_POST['status'] ?? -1);

if($deposit_id<=0 || !in_array($new_status,[0,1,2])){
$error="Invalid request";
}else{

try{

$pdo->beginTransaction();

$stmt=$pdo->prepare("SELECT user_id,amount,status FROM deposits WHERE id=?");
$stmt->execute([$deposit_id]);
$deposit=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$deposit){
throw new Exception("Deposit not found.");
}

$current_status=$deposit['status'];

/* HANDLE BALANCE ADJUSTMENT */

if($current_status!=1 && $new_status==1){

$pdo->prepare("
UPDATE users 
SET balance=balance+?
WHERE id=?
")->execute([$deposit['amount'],$deposit['user_id']]);

}

if($current_status==1 && $new_status!=1){

$pdo->prepare("
UPDATE users
SET balance=balance-?
WHERE id=?
")->execute([$deposit['amount'],$deposit['user_id']]);

}

$stmt=$pdo->prepare("
UPDATE deposits
SET status=?,updated_at=NOW()
WHERE id=?
");

$stmt->execute([$new_status,$deposit_id]);

$pdo->commit();

$message="Deposit #{$deposit_id} updated successfully.";

}catch(Exception $e){

$pdo->rollBack();
$error=$e->getMessage();

}

}

}


/* LOAD ALL DEPOSITS */

try{

$stmt=$pdo->query("
SELECT
d.id,
d.amount,
d.paid_amount,
d.paid_currency,
d.proof,
d.status,
d.created_at,

u.email,
u.phone,

COALESCE(pm.name,'Unknown Method') AS method_name

FROM deposits d
LEFT JOIN users u ON d.user_id=u.id
LEFT JOIN payment_methods pm ON d.method_id=pm.id

ORDER BY d.created_at DESC
");

$deposits=$stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(PDOException $e){

$error="Failed to load deposits";
$deposits=[];

}

?>

<main>

<h1 style="text-align:center;margin:2.5rem 0 2rem;">
Manage Deposits
</h1>


<?php if($message): ?>
<div style="background:#238636;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin-left:auto;margin-right:auto;">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>


<?php if($error): ?>
<div style="background:#f85149;color:white;padding:1.2rem;border-radius:8px;margin-bottom:2rem;text-align:center;max-width:900px;margin-left:auto;margin-right:auto;">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>


<?php if(empty($deposits)): ?>

<p style="text-align:center;color:var(--text-muted);font-size:1.1rem;padding:3rem 1rem;">
No deposits found.
</p>

<?php else: ?>


<div style="overflow-x:auto;margin:0 auto;max-width:100%;">

<table style="
width:100%;
max-width:1200px;
margin:0 auto 3rem;
border-collapse:separate;
border-spacing:0 12px;
">

<thead>

<tr style="background:#1f2937;color:#e6edf3;">

<th style="padding:1.2rem;">ID</th>
<th style="padding:1.2rem;">User</th>
<th style="padding:1.2rem;">Method</th>
<th style="padding:1.2rem;">Amount</th>
<th style="padding:1.2rem;">Paid</th>
<th style="padding:1.2rem;">Proof</th>
<th style="padding:1.2rem;">Status</th>
<th style="padding:1.2rem;">Date</th>
<th style="padding:1.2rem;">Update</th>

</tr>

</thead>


<tbody>

<?php foreach($deposits as $dep): ?>

<tr style="background:var(--card);box-shadow:0 2px 8px rgba(0,0,0,0.3);">

<td style="padding:1.2rem;text-align:center;">
<?= $dep['id'] ?>
</td>


<td style="padding:1.2rem;">
<?= htmlspecialchars($dep['email'] ?? '—') ?>
<br>
<small style="color:var(--text-muted)">
<?= htmlspecialchars($dep['phone'] ?? '—') ?>
</small>
</td>


<td style="padding:1.2rem;text-align:center;">
<?= htmlspecialchars($dep['method_name']) ?>
</td>


<td style="padding:1.2rem;text-align:right;font-weight:600;">
$<?= number_format($dep['amount'],2) ?>
</td>


<td style="padding:1.2rem;text-align:right;">

<?php if($dep['paid_amount']): ?>

<?= number_format($dep['paid_amount'],2)." ".htmlspecialchars($dep['paid_currency']) ?>

<?php else: ?>

—

<?php endif; ?>

</td>


<td style="padding:1.2rem;text-align:center;">

<?php if(!empty($dep['proof'])): ?>

<?php $proof="../".$dep['proof']; ?>

<img src="<?= $proof ?>"
style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid var(--border);cursor:pointer;"
onclick="openPreview('<?= $proof ?>')">

<?php else: ?>

<span style="color:var(--text-muted)">No proof</span>

<?php endif; ?>

</td>


<td style="padding:1.2rem;text-align:center;">

<?php

if($dep['status']==0) echo "Pending";
elseif($dep['status']==1) echo "Approved";
else echo "Rejected";

?>

</td>


<td style="padding:1.2rem;text-align:center;">
<?= date("Y-m-d H:i",strtotime($dep['created_at'])) ?>
</td>


<td style="padding:1.2rem;text-align:center;">

<form method="POST">

<input type="hidden" name="deposit_id" value="<?= $dep['id'] ?>">

<select name="status" style="padding:0.4rem;border-radius:6px;margin-right:6px;">

<option value="0" <?= $dep['status']==0?'selected':'' ?>>Pending</option>
<option value="1" <?= $dep['status']==1?'selected':'' ?>>Approved</option>
<option value="2" <?= $dep['status']==2?'selected':'' ?>>Rejected</option>

</select>

<button class="btn" style="padding:0.4rem 0.9rem;font-size:0.9rem;">
Update
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>



<div id="previewModal"
style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.94);z-index:2000;align-items:center;justify-content:center;">

<img id="previewImage"
style="max-width:95%;max-height:90vh;border-radius:12px;">

</div>


</main>



<script>

function openPreview(src){

document.getElementById("previewImage").src=src;
document.getElementById("previewModal").style.display="flex";

}

document.getElementById("previewModal").onclick=function(){

this.style.display="none";

}

</script>


<?php require_once __DIR__.'/inc/footer.php'; ?>
