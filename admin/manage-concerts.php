<?php
session_start();

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__.'/inc/header.php';

/*----------------------------------------------------
GET ARTIST
----------------------------------------------------*/

$artist_id = isset($_GET['artist_id']) ? (int)$_GET['artist_id'] : 0;
$artist = null;

/*----------------------------------------------------
FETCH ARTISTS FOR DROPDOWN
----------------------------------------------------*/

$stmt = $pdo->query("
SELECT artist_id,artist_name
FROM artists
ORDER BY artist_name
");

$artists = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*----------------------------------------------------
IF ARTIST SELECTED
----------------------------------------------------*/

if($artist_id){

    $stmt=$pdo->prepare("
    SELECT *
    FROM artists
    WHERE artist_id=?
    ");

    $stmt->execute([$artist_id]);

    $artist=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$artist){
        $_SESSION['error']="Artist not found.";
        header("Location: manage-concerts.php");
        exit;
    }
}

/*----------------------------------------------------
HANDLE POST
----------------------------------------------------*/

if($_SERVER['REQUEST_METHOD']=="POST"){

    try{

        $action=$_POST['action'];

        $artist_id=(int)$_POST['artist_id'];

        if($action=="add"){

            $date=$_POST['concert_date'];
            $day_time=trim($_POST['day_time']);
            $venue=trim($_POST['venue']);
            $location=trim($_POST['location']);
            $title=trim($_POST['title']);

            if(
                empty($date) ||
                empty($day_time) ||
                empty($venue) ||
                empty($location) ||
                empty($title)
            ){
                throw new Exception("All fields are required.");
            }

            $stmt=$pdo->prepare("
            INSERT INTO concerts
            (
                artist_id,
                concert_date,
                day_time,
                venue,
                location,
                title
            )
            VALUES
            (?,?,?,?,?,?)
            ");

            $stmt->execute([
                $artist_id,
                $date,
                $day_time,
                $venue,
                $location,
                $title
            ]);

            $_SESSION['success']="Concert added.";

        }

        if($action=="delete"){

            $concert_id=(int)$_POST['concert_id'];

            $stmt=$pdo->prepare("
            DELETE FROM concerts
            WHERE concert_id=?
            ");

            $stmt->execute([$concert_id]);

            $_SESSION['success']="Concert deleted.";

        }

        header("Location: manage-concerts.php?artist_id=".$artist_id);
        exit;

    }catch(Exception $e){

        $_SESSION['error']=$e->getMessage();

        header("Location: manage-concerts.php?artist_id=".$artist_id);
        exit;

    }

}

/*----------------------------------------------------
FETCH CONCERTS
----------------------------------------------------*/

$concerts=[];

if($artist_id){

$stmt=$pdo->prepare("
SELECT *
FROM concerts
WHERE artist_id=?
ORDER BY concert_date DESC
");

$stmt->execute([$artist_id]);

$concerts=$stmt->fetchAll(PDO::FETCH_ASSOC);

}

?>

<main style="max-width:1100px;margin:auto;">

<h1 style="margin-bottom:25px;">Manage Concerts</h1>

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

<?php if(!$artist){ ?>

<div style="background:var(--card);padding:25px;border-radius:10px;max-width:600px;margin:auto;">

<h2>Select Artist</h2>

<form method="GET">

<select
name="artist_id"
required
style="width:100%;padding:12px;margin:20px 0;">

<option value="">Choose Artist</option>

<?php foreach($artists as $a){ ?>

<option value="<?= $a['artist_id'] ?>">

<?= htmlspecialchars($a['artist_name']) ?>

</option>

<?php } ?>

</select>

<button class="btn" style="width:100%;">

<i class="fas fa-arrow-right"></i>

Continue

</button>

</form>

</div>

<?php return; } ?>

<div style="display:flex;align-items:center;gap:15px;background:var(--card);padding:20px;border-radius:10px;margin-bottom:25px;">

<?php if($artist['artist_image']){ ?>

<img
src="../uploads/artists/<?= htmlspecialchars($artist['artist_image']) ?>"
style="width:70px;height:70px;border-radius:10px;object-fit:cover;">

<?php } ?>

<div>

<h2><?= htmlspecialchars($artist['artist_name']) ?></h2>

<small><?= htmlspecialchars($artist['genre']) ?></small>

</div>

</div>

<div style="text-align:right;margin-bottom:20px;">

<button class="btn" onclick="openModal()">

<i class="fas fa-plus"></i>

Add Concert

</button>

</div>

<div style="overflow:auto;">

<table style="width:100%;border-collapse:collapse;">

<thead>

<tr style="background:#111827;">

<th>Date</th>
<th>Day/Time</th>
<th>Venue</th>
<th>Location</th>
<th>Title</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php if(empty($concerts)){ ?>

<tr>

<td colspan="6" style="padding:20px;text-align:center;">

No concerts found.

</td>

</tr>

<?php } ?>

<?php foreach($concerts as $concert){ ?>

<tr style="border-bottom:1px solid var(--border);">

<td style="padding:12px;">
<?= htmlspecialchars($concert['concert_date']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($concert['day_time']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($concert['venue']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($concert['location']) ?>
</td>

<td style="padding:12px;">
<?= htmlspecialchars($concert['title']) ?>
</td>

<td style="padding:12px;">

<form method="POST" onsubmit="return confirm('Delete concert?');">

<input type="hidden" name="action" value="delete">

<input type="hidden" name="artist_id" value="<?= $artist_id ?>">

<input type="hidden" name="concert_id" value="<?= $concert['concert_id'] ?>">

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

<h2>Add Concert</h2>

<form method="POST">

<input type="hidden" name="action" value="add">

<input type="hidden" name="artist_id" value="<?= $artist_id ?>">

<label>Date</label>
<input type="date" name="concert_date" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Day / Time</label>
<input type="text" name="day_time" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Venue</label>
<input type="text" name="venue" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Location</label>
<input type="text" name="location" required style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Title</label>
<input type="text" name="title" required style="width:100%;padding:10px;margin:10px 0 20px;">

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
