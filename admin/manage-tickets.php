<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__.'/inc/header.php';

/*----------------------------------------------------
GET CONCERT
----------------------------------------------------*/

$concert_id = isset($_GET['concert_id']) ? (int)$_GET['concert_id'] : 0;
$concert = null;

if($concert_id){

    $stmt=$pdo->prepare("
    SELECT
        concerts.*,
        artists.artist_name,
        artists.artist_image
    FROM concerts
    JOIN artists
        ON concerts.artist_id=artists.artist_id
    WHERE concerts.concert_id=?
    ");

    $stmt->execute([$concert_id]);
    $concert=$stmt->fetch(PDO::FETCH_ASSOC);
}

/*----------------------------------------------------
HANDLE POST
----------------------------------------------------*/

if($_SERVER['REQUEST_METHOD']=="POST"){

    try{

        $action=$_POST['action'];
        $concert_id=(int)$_POST['concert_id'];

        if($action=="add"){

            $ticket_name=trim($_POST['ticket_name']);
            $section_name=trim($_POST['section_name']);
            $row_name=trim($_POST['row_name']);
            $seat_name=trim($_POST['seat_name']);
            $price=(float)$_POST['price'];

            if(empty($ticket_name) || empty($section_name) || empty($row_name) || empty($seat_name) || $price<=0){
                throw new Exception("All fields are required.");
            }

            $section_view = null;

            if(isset($_FILES['section_view']) && $_FILES['section_view']['error'] === UPLOAD_ERR_OK){

                $uploadDir = "../uploads/tickets/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = strtolower(pathinfo($_FILES['section_view']['name'], PATHINFO_EXTENSION));
                $filename = uniqid('seat_') . "." . $ext;

                move_uploaded_file($_FILES['section_view']['tmp_name'], $uploadDir . $filename);

                $section_view = $filename;
            }

            $stmt=$pdo->prepare("
                INSERT INTO tickets
                (concert_id, ticket_name, section_name, row_name, seat_name, price, section_view)
                VALUES (?,?,?,?,?,?,?)
            ");

            $stmt->execute([
                $concert_id,
                $ticket_name,
                $section_name,
                $row_name,
                $seat_name,
                $price,
                $section_view
            ]);

            $_SESSION['success']="Ticket added.";
        }

        if($action=="delete"){

            $ticket_id=(int)$_POST['ticket_id'];

            $stmt=$pdo->prepare("DELETE FROM tickets WHERE ticket_id=?");
            $stmt->execute([$ticket_id]);

            $_SESSION['success']="Ticket deleted.";
        }

        header("Location: manage-tickets.php?concert_id=".$concert_id);
        exit;

    }catch(Exception $e){
        $_SESSION['error']=$e->getMessage();
        header("Location: manage-tickets.php?concert_id=".$concert_id);
        exit;
    }
}

/*----------------------------------------------------
FETCH DATA
----------------------------------------------------*/

$stmt=$pdo->query("
SELECT concerts.concert_id, concerts.title, concerts.concert_date, artists.artist_name
FROM concerts
JOIN artists ON concerts.artist_id=artists.artist_id
ORDER BY concerts.concert_date DESC
");

$concerts=$stmt->fetchAll(PDO::FETCH_ASSOC);

$tickets=[];

if($concert_id){
$stmt=$pdo->prepare("SELECT * FROM tickets WHERE concert_id=? ORDER BY price");
$stmt->execute([$concert_id]);
$tickets=$stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main style="max-width:1000px;margin:2rem auto;">

<h1 style="text-align:center;margin-bottom:1rem;">Tickets</h1>

<!-- ALERTS -->
<?php if(isset($_SESSION['success'])): ?>
<div style="background:#238636;color:#fff;padding:15px;border-radius:10px;margin-bottom:1rem;">
<?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div style="background:#f85149;color:#fff;padding:15px;border-radius:10px;margin-bottom:1rem;">
<?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<!-- SELECT CONCERT -->
<?php if(!$concert_id): ?>

<div style="background:var(--card);padding:20px;border-radius:10px;border:1px solid var(--border);max-width:700px;margin:auto;">

<h2 style="margin-bottom:10px;">Select Concert</h2>

<form method="GET">

<select name="concert_id" required onchange="this.form.submit()"
style="width:100%;padding:.8rem;">

<option value="">Choose Concert</option>

<?php foreach($concerts as $c): ?>
<option value="<?= $c['concert_id'] ?>">
<?= htmlspecialchars($c['artist_name']) ?> - <?= htmlspecialchars($c['title']) ?> (<?= $c['concert_date'] ?>)
</option>
<?php endforeach; ?>

</select>

</form>

</div>

</main>
<?php require_once __DIR__.'/inc/footer.php'; ?>
<?php return; endif; ?>

<!-- CONCERT HEADER -->
<div style="display:flex;align-items:center;gap:15px;background:var(--card);padding:15px;border-radius:10px;border:1px solid var(--border);margin-bottom:2rem;">

<img src="../uploads/artists/<?= htmlspecialchars($concert['artist_image']) ?>"
style="width:60px;height:60px;object-fit:cover;border-radius:8px;">

<div>
<h3 style="margin:0;"><?= htmlspecialchars($concert['artist_name']) ?></h3>
<small style="color:#888;">
<?= htmlspecialchars($concert['title']) ?> • <?= htmlspecialchars($concert['concert_date']) ?>
</small>
</div>

</div>

<!-- ADD BUTTON -->
<div style="text-align:right;margin-bottom:1rem;">
<button class="btn" onclick="openModal()">+ Add Ticket</button>
</div>

<!-- TABLE CARD -->
<div style="background:#111827;border:1px solid var(--border);border-radius:10px;overflow:auto;">

<table style="width:100%;border-collapse:collapse;">

<thead>
<tr style="background:#0f172a;">
<th style="padding:12px;text-align:left;">Ticket</th>
<th style="padding:12px;text-align:left;">Section</th>
<th style="padding:12px;text-align:left;">Row</th>
<th style="padding:12px;text-align:left;">Seat</th>
<th style="padding:12px;text-align:left;">Price</th>
<th style="padding:12px;text-align:left;">View</th>
<th style="padding:12px;text-align:left;">Action</th>
</tr>
</thead>

<tbody>

<?php if(empty($tickets)): ?>
<tr>
<td colspan="7" style="padding:20px;text-align:center;color:#888;">
No tickets found.
</td>
</tr>
<?php endif; ?>

<?php foreach($tickets as $ticket): ?>
<tr style="border-top:1px solid var(--border);">

<td style="padding:12px;"><?= htmlspecialchars($ticket['ticket_name']) ?></td>
<td style="padding:12px;"><?= htmlspecialchars($ticket['section_name']) ?></td>
<td style="padding:12px;"><?= htmlspecialchars($ticket['row_name']) ?></td>
<td style="padding:12px;"><?= htmlspecialchars($ticket['seat_name']) ?></td>
<td style="padding:12px;">$<?= number_format($ticket['price'],2) ?></td>

<td style="padding:12px;">
<?php if(!empty($ticket['section_view'])): ?>
<img src="../uploads/tickets/<?= htmlspecialchars($ticket['section_view']) ?>"
style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
<?php else: ?>
<span style="color:#888;">N/A</span>
<?php endif; ?>
</td>

<td style="padding:12px;">

<form method="POST" onsubmit="return confirm('Delete this ticket?');">

<input type="hidden" name="action" value="delete">
<input type="hidden" name="concert_id" value="<?= $concert_id ?>">
<input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">

<button class="btn red" style="padding:6px 10px;font-size:13px;">
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
<div id="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);">

<div style="background:#111827;max-width:600px;margin:5% auto;padding:20px;border-radius:10px;border:1px solid var(--border);">

<h2 style="margin-bottom:10px;">Add Ticket</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="action" value="add">
<input type="hidden" name="concert_id" value="<?= $concert_id ?>">

<input type="text" name="ticket_name" placeholder="Ticket Name" required style="width:100%;padding:.7rem;margin-bottom:10px;">
<input type="text" name="section_name" placeholder="Section" required style="width:100%;padding:.7rem;margin-bottom:10px;">
<input type="text" name="row_name" placeholder="Row" required style="width:100%;padding:.7rem;margin-bottom:10px;">
<input type="text" name="seat_name" placeholder="Seat" required style="width:100%;padding:.7rem;margin-bottom:10px;">
<input type="number" step="0.01" name="price" placeholder="Price" required style="width:100%;padding:.7rem;margin-bottom:10px;">
<input type="file" name="section_view" style="width:100%;padding:.7rem;margin-bottom:10px;">

<button class="btn" style="width:100%;">Save Ticket</button>

</form>

<br>

<button class="btn red" style="width:100%;" onclick="closeModal()">Close</button>

</div>
</div>

<script>
function openModal(){ document.getElementById("modal").style.display="block"; }
function closeModal(){ document.getElementById("modal").style.display="none"; }
</script>

</main>

<?php require_once __DIR__.'/inc/footer.php'; ?>
