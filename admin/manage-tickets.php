<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__.'/inc/header.php';

/*----------------------------------------------------
GET ARTIST
----------------------------------------------------*/

$concert_id = isset($_GET['concert_id']) ? (int)$_GET['concert_id'] : 0;
$concert = null;

/*----------------------------------------------------
GET CONCERT
----------------------------------------------------*/
$stmt = $pdo->prepare("
SELECT
    concerts.*,
    artists.artist_name,
    artists.artist_image
FROM concerts
JOIN artists
ON concerts.artist_id=artists.artist_id
WHERE concert_id=?
");

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

            if(
            
            empty($ticket_name) ||
            
            empty($section_name) ||
            
            empty($row_name) ||
            
            empty($seat_name) ||
            
            $price<=0
            
            ){
            
            throw new Exception("All fields are required.");
            
            }

            $seat_view = null;
            
            if (
                isset($_FILES['seat_view']) &&
                $_FILES['seat_view']['error'] === UPLOAD_ERR_OK
            ) {
            
                $uploadDir = "../uploads/tickets/";
            
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
            
                $ext = strtolower(pathinfo($_FILES['seat_view']['name'], PATHINFO_EXTENSION));
            
                $filename = uniqid('seat_') . "." . $ext;
            
                move_uploaded_file(
                    $_FILES['seat_view']['tmp_name'],
                    $uploadDir . $filename
                );
            
                $seat_view = $filename;
            }

            $stmt=$pdo->prepare("
            INSERT INTO tickets
            (
            concert_id,
            ticket_name,
            section_name,
            row_name,
            seat_name,
            price,
            seat_view
            )
            VALUES
            (?,?,?,?,?,?,?)
            ");

            $stmt->execute([
                $concert_id,
                $ticket_name,
                $section_name,
                $row_name,
                $seat_name,
                $price,
                $seat_view
            ]);

            $_SESSION['success']="Ticket added.";

        }

        if($action=="delete"){

            $ticket_id=(int)$_POST['ticket_id'];

            $stmt=$pdo->prepare("
            DELETE FROM tickets
            WHERE ticket_id=?
            ");

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
FETCH CONCERTS
----------------------------------------------------*/

$concerts=[];

$stmt=$pdo->query("
SELECT
    concerts.concert_id,
    concerts.title,
    concerts.concert_date,
    artists.artist_name
FROM concerts
JOIN artists
ON concerts.artist_id=artists.artist_id
ORDER BY concerts.concert_date DESC
");

$concerts=$stmt->fetchAll(PDO::FETCH_ASSOC);

$tickets=[];

if($concert_id){

$stmt=$pdo->prepare("
SELECT *
FROM tickets
WHERE concert_id=?
ORDER BY price
");

$stmt->execute([$concert_id]);

$tickets=$stmt->fetchAll(PDO::FETCH_ASSOC);

}

?>

<main style="max-width:1100px;margin:auto;">

<h1 style="margin-bottom:25px;">Manage Tickets</h1>

<?php
if(isset($_SESSION['success'])){
?>
<div style="background:#238636;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">
<?= $_SESSION['success']; unset($_SESSION['success']);?>
</div>
<?php
}
?>

<?php
if(isset($_SESSION['error'])){
?>
<div style="background:#f85149;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">
<?= $_SESSION['error']; unset($_SESSION['error']);?>
</div>
<?php
}
?>

<?php if(!$concert_id){ ?>

<div style="background:var(--card);padding:25px;border-radius:10px;max-width:700px;margin:auto;">

<h2>Select Concert</h2>

<form method="GET">

<select
name="concert_id"
required
onchange="this.form.submit()"
style="width:100%;padding:12px;">

<option value="">Choose Concert</option>

<?php foreach($concerts as $c){ ?>

<option value="<?= $c['concert_id'] ?>">

<?= htmlspecialchars($c['artist_name']) ?>

-

<?= htmlspecialchars($c['title']) ?>

(

<?= $c['concert_date'] ?>

)

</option>

<?php } ?>

</select>

</form>

</div>

<?php return; } ?>

<div style="display:flex;align-items:center;gap:15px;background:var(--card);padding:20px;border-radius:10px;margin-bottom:25px;">

<?php if($concert['artist_image']){ ?>

<img
src="../uploads/artists/<?= htmlspecialchars($concert['artist_image']) ?>"
style="width:70px;height:70px;border-radius:10px;object-fit:cover;">

<?php } ?>

<div>

<h2><?= htmlspecialchars($concert['artist_name']) ?></h2>

<small>

<?= htmlspecialchars($concert['title']) ?>
<br>
<?= htmlspecialchars($concert['concert_date']) ?>

</small>

</div>

</div>

<div style="text-align:right;margin-bottom:20px;">

<button class="btn" onclick="openModal()">

<i class="fas fa-plus"></i>

Add Ticket

</button>

</div>

<div style="overflow:auto;">

<table style="width:100%;border-collapse:collapse;">

<thead>

<tr style="background:#111827;">

<th>Ticket</th>
<th>Section</th>
<th>Row</th>
<th>Seat</th>
<th>Price</th>
<th>Seat View</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php if(empty($tickets)){ ?>

<tr>

<td colspan="7" style="padding:20px;text-align:center;">

No tickets found.

</td>

</tr>

<?php } ?>

<?php foreach($tickets as $ticket){ ?>

<tr style="border-bottom:1px solid var(--border);">

<td style="padding:12px;">
<?= htmlspecialchars($ticket['ticket_name']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($ticket['section_name']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($ticket['row_name']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($ticket['seat_name']) ?>
</td>

<td style="padding:12px;">
$<?= number_format($ticket['price'],2) ?>
</td>

<td style="padding:12px;">
    <?php if (!empty($ticket['seat_view'])): ?>
        <img
        src="../uploads/tickets/<?= htmlspecialchars($ticket['seat_view']) ?>"
        style="width:55px;height:55px;object-fit:cover;border-radius:8px;">
    <?php else: ?>
        <span style="color:#888;">N/A</span>
    <?php endif; ?>
</td>


<td style="padding:12px;">

<form method="POST" onsubmit="return confirm('Delete concert?');">

<input type="hidden" name="action" value="delete">

<input type="hidden" name="concert_id" value="<?= $concert_id ?>">

<input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">

<button class="btn red">

<i class="fas fa-trash"></i>

Delete

</button>

</form>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div id="modal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.7);">

<div style="background:#111827;max-width:600px;margin:5% auto;padding:25px;border-radius:10px;">

<h2>Add Ticket</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="action" value="add">

<input type="hidden" name="concert_id" value="<?= $concert_id ?>">

<label>Ticket Name</label>
<input type="text" name="ticket_name" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Section</label>
<input type="text" name="section_name" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Row</label>
<input type="text" name="row_name" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Seat</label>
<input type="text" name="seat_name" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Price</label>
<input type="number" step="0.01" name="price" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Image</label>
<input type="file" name="seat_view" required style="width:100%;padding:10px;margin:10px 0 20px;">

<button class="btn" style="width:100%;">

<i class="fas fa-save"></i>

Save Concert

</button>

</form>

<br>

<button class="btn red" style="width:100%;" onclick="closeModal()">

Close

</button>

</div>

</div>

<script>

function openModal(){
document.getElementById("modal").style.display="block";
}

function closeModal(){
document.getElementById("modal").style.display="none";
}

</script>

</main>

<?php require_once __DIR__.'/inc/footer.php'; ?>
