<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/inc/header.php';

/* --------------------------------------------------
   GET ARTIST
-------------------------------------------------- */

$artist_id = (int)($_GET['artist_id'] ?? 0);

if ($artist_id <= 0) {
    $_SESSION['error'] = "Invalid artist.";
    header("Location: manage-artists.php");
    exit;
}

/* --------------------------------------------------
   FETCH ARTIST
-------------------------------------------------- */

$stmt = $pdo->prepare("SELECT * FROM artists WHERE artist_id=?");
$stmt->execute([$artist_id]);
$artist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artist) {
    $_SESSION['error'] = "Artist not found.";
    header("Location: manage-artists.php");
    exit;
}

/* --------------------------------------------------
   HANDLE ACTIONS
-------------------------------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $action = $_POST['action'] ?? '';

        /* ---------------- ADD ---------------- */

        if ($action == 'add') {

            $name = trim($_POST['v_artist_name']);
            $link = trim($_POST['v_artist_link']);

            if ($name == '' || $link == '') {
                throw new Exception("Please complete all required fields.");
            }

            $imageName = '';

            if (!empty($_FILES['v_artist_image']['name'])) {

                $uploadDir = __DIR__ . "/../uploads/fans-also-viewed/";

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageName = time() . "_" . basename($_FILES['v_artist_image']['name']);

                move_uploaded_file(
                    $_FILES['v_artist_image']['tmp_name'],
                    $uploadDir . $imageName
                );
            }

            $stmt = $pdo->prepare("
                INSERT INTO fans_also_viewed
                (artist_id,v_artist_name,v_artist_image,v_artist_link)
                VALUES (?,?,?,?)
            ");

            $stmt->execute([
                $artist_id,
                $name,
                $imageName,
                $link
            ]);

            $_SESSION['success'] = "Artist added successfully.";

            header("Location: manage-fans-also-viewed.php?artist_id=".$artist_id);
            exit;
        }

        /* ---------------- DELETE ---------------- */

        if ($action == 'delete') {

            $fav_id = (int)$_POST['fav_id'];

            $stmt = $pdo->prepare("
                SELECT v_artist_image
                FROM fans_also_viewed
                WHERE fav_id=?
            ");

            $stmt->execute([$fav_id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['v_artist_image']) {

                $file = __DIR__ . "/../uploads/fans-also-viewed/" . $row['v_artist_image'];

                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $stmt = $pdo->prepare("
                DELETE FROM fans_also_viewed
                WHERE fav_id=?
            ");

            $stmt->execute([$fav_id]);

            $_SESSION['success'] = "Artist deleted.";

            header("Location: manage-fans-also-viewed.php?artist_id=".$artist_id);
            exit;
        }

    } catch(Exception $e){

        $_SESSION['error'] = $e->getMessage();

        header("Location: manage-fans-also-viewed.php?artist_id=".$artist_id);
        exit;
    }

}

/* --------------------------------------------------
   FETCH DATA
-------------------------------------------------- */

$stmt = $pdo->prepare("
SELECT *
FROM fans_also_viewed
WHERE artist_id=?
ORDER BY fav_id DESC
");

$stmt->execute([$artist_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<main style="max-width:1100px;margin:auto;">

<h1 style="margin-bottom:20px;">Manage Fans Also Viewed</h1>

<div style="display:flex;align-items:center;gap:15px;background:var(--card);padding:20px;border-radius:10px;margin-bottom:25px;">

<?php if($artist['artist_image']){ ?>

<img src="../uploads/artists/<?= htmlspecialchars($artist['artist_image']) ?>"
style="width:70px;height:70px;border-radius:10px;object-fit:cover;">

<?php } ?>

<div>

<h2 style="margin:0;">
<?= htmlspecialchars($artist['artist_name']) ?>
</h2>

<small>Fans Also Viewed</small>

</div>

</div>

<?php if(isset($_SESSION['success'])){ ?>

<div style="background:#238636;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">
<?= $_SESSION['success']; unset($_SESSION['success']); ?>
</div>

<?php } ?>

<?php if(isset($_SESSION['error'])){ ?>

<div style="background:#f85149;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">
<?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>

<?php } ?>

<div style="text-align:right;margin-bottom:20px;">

<button class="btn" onclick="openModal()">
<i class="fas fa-plus"></i>
Add Artist
</button>

</div>

<div style="overflow:auto;">

<table style="width:100%;border-collapse:collapse;">

<thead>

<tr style="background:#111827;">

<th style="padding:12px;">Image</th>
<th style="padding:12px;">Artist</th>
<th style="padding:12px;">Link</th>
<th style="padding:12px;">Action</th>

</tr>

</thead>

<tbody>

<?php if(empty($items)){ ?>

<tr>
<td colspan="4" style="padding:20px;text-align:center;">
No records found.
</td>
</tr>

<?php } ?>

<?php foreach($items as $row){ ?>

<tr style="border-bottom:1px solid var(--border);">

<td style="padding:12px;">

<?php if($row['v_artist_image']){ ?>

<img src="../uploads/fans-also-viewed/<?= htmlspecialchars($row['v_artist_image']) ?>"
style="width:60px;height:60px;border-radius:8px;object-fit:cover;">

<?php } ?>

</td>

<td style="padding:12px;">
<?= htmlspecialchars($row['v_artist_name']) ?>
</td>

<td style="padding:12px;max-width:300px;word-break:break-word;">
<a href="<?= htmlspecialchars($row['v_artist_link']) ?>"
target="_blank">
<?= htmlspecialchars($row['v_artist_link']) ?>
</a>
</td>

<td style="padding:12px;">

<form method="POST"
onsubmit="return confirm('Delete this artist?');">

<input type="hidden"
name="action"
value="delete">

<input type="hidden"
name="fav_id"
value="<?= $row['fav_id'] ?>">

<button class="btn red">
<i class="fas fa-trash"></i>
Delete
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<!-- ADD MODAL -->

<div id="addModal"
style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.75);">

<div style="background:#111827;max-width:600px;margin:4% auto;padding:25px;border-radius:10px;">

<h2>Add Related Artist</h2>

<form method="POST" enctype="multipart/form-data">

<input type="hidden"
name="action"
value="add">

<label>Artist Name</label>

<input
type="text"
name="v_artist_name"
required
style="width:100%;padding:10px;margin:10px 0 20px;">

<label>Artist Image</label>

<input
type="file"
name="v_artist_image"
accept="image/*"
style="width:100%;margin:10px 0 20px;">

<label>Artist Link</label>

<input
type="url"
name="v_artist_link"
required
placeholder="https://example.com"
style="width:100%;padding:10px;margin:10px 0 20px;">

<button class="btn" style="width:100%;">
<i class="fas fa-save"></i>
Save
</button>

</form>

<br>

<button
class="btn red"
style="width:100%;"
onclick="closeModal()">

Close

</button>

</div>

</div>

<script>

function openModal(){
    document.getElementById('addModal').style.display='block';
}

function closeModal(){
    document.getElementById('addModal').style.display='none';
}

</script>

</main>

<?php require_once __DIR__.'/inc/footer.php'; ?>
