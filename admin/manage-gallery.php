<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/header.php';

/* --------------------------------------------------
   GET ARTIST ID
-------------------------------------------------- */
$artist_id = (int)($_GET['artist_id'] ?? 0);

if ($artist_id <= 0) {
    $_SESSION['error'] = "Invalid artist ID.";
    header("Location: manage-artists.php");
    exit;
}

/* --------------------------------------------------
   FETCH ARTIST
-------------------------------------------------- */
$stmt = $pdo->prepare("SELECT * FROM artists WHERE artist_id = ?");
$stmt->execute([$artist_id]);
$artist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$artist) {
    $_SESSION['error'] = "Artist not found.";
    header("Location: manage-artists.php");
    exit;
}

/* --------------------------------------------------
   FETCH GALLERY (YOUTUBE LINKS)
-------------------------------------------------- */
$stmt = $pdo->prepare("SELECT * FROM gallery WHERE artist_id = ? ORDER BY gallery_id DESC");
$stmt->execute([$artist_id]);
$gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* --------------------------------------------------
   HANDLE ADD / DELETE
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $action = $_POST['action'] ?? '';

        /* ---------------- ADD YOUTUBE LINK ---------------- */
        if ($action === 'upload') {

            $media_title = trim($_POST['media_title'] ?? '');
            $link = trim($_POST['youtube_media_link'] ?? '');
            
            if ($media_title === '') {
                throw new Exception("Media title is required.");
            }
            
            if ($link === '') {
                throw new Exception("YouTube link is required.");
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO gallery (
                    artist_id,
                    media_title,
                    youtube_media_link,
                    media_type
                )
                VALUES (?, ?, ?, 'youtube')
            ");
            
            $stmt->execute([
                $artist_id,
                $media_title,
                $link
            ]);

            $_SESSION['success'] = "YouTube video added successfully.";
            header("Location: manage-gallery.php?artist_id=" . $artist_id);
            exit;
        }

        /* ---------------- DELETE ---------------- */
        if ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            $stmt = $pdo->prepare("DELETE FROM gallery WHERE gallery_id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Media deleted.";
            header("Location: manage-gallery.php?artist_id=" . $artist_id);
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: manage-gallery.php?artist_id=" . $artist_id);
        exit;
    }
}

/* --------------------------------------------------
   EXTRACT YOUTUBE ID
-------------------------------------------------- */
function getYoutubeId($url) {
    preg_match('/(youtu\.be\/|v=)([^&]+)/', $url, $matches);
    return $matches[2] ?? '';
}
?>

<main style="max-width:1000px;margin:2rem auto;">

<h1 style="text-align:center;margin-bottom:1rem;">Gallery (YouTube)</h1>

<!-- ARTIST HEADER -->
<div style="display:flex;align-items:center;gap:15px;background:var(--card);padding:15px;border-radius:10px;border:1px solid var(--border);margin-bottom:2rem;">

    <img src="../uploads/artists/<?= htmlspecialchars($artist['artist_image']) ?>"
         style="width:60px;height:60px;object-fit:cover;border-radius:8px;">

    <div>
        <h3 style="margin:0;"><?= htmlspecialchars($artist['artist_name']) ?></h3>
        <small style="color:#888;">Manage YouTube Gallery</small>
    </div>

</div>

<!-- ADD YOUTUBE LINK -->
<div style="background:#111827;padding:20px;border-radius:10px;margin-bottom:2rem;">

<form method="POST">

    <input type="hidden" name="action" value="upload">

    <label style="display:block;margin-bottom:8px;">
        Media Title
    </label>

    <input
        type="text"
        name="media_title"
        placeholder="e.g. Live in Lagos 2025"
        required
        style="width:100%;padding:.7rem;margin-bottom:15px;">

    <label style="display:block;margin-bottom:8px;">
        YouTube Video Link
    </label>

    <input
        type="url"
        name="youtube_media_link"
        placeholder="https://www.youtube.com/watch?v=XXXX"
        required
        style="width:100%;padding:.7rem;margin-bottom:15px;">

    <button class="btn" style="width:100%;">
        <i class="fas fa-plus"></i> Add Video
    </button>

</form>

</div>

<!-- GRID -->
<?php if (empty($gallery)): ?>
<p style="text-align:center;color:#888;">No videos found.</p>
<?php endif; ?>

<div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
">

<?php foreach ($gallery as $media): ?>

<?php $ytId = getYoutubeId($media['youtube_media_link']); ?>

<div style="background:#111827;border:1px solid var(--border);border-radius:10px;overflow:hidden;">

    <!-- THUMBNAIL -->
    <?php if ($ytId): ?>
        <img src="https://img.youtube.com/vi/<?= $ytId ?>/hqdefault.jpg"
             style="width:100%;height:160px;object-fit:cover;">
    <?php endif; ?>

    <div style="padding:15px;">
   
        <h3 style="margin:0 0 10px;font-size:16px;">
           <?= htmlspecialchars($media['media_title']) ?>
        </h3>
   
        <a href="<?= htmlspecialchars($media['youtube_media_link']) ?>"
           target="_blank"
           style="color:#58a6ff;text-decoration:none;font-size:14px;word-break:break-all;">
            <i class="fab fa-youtube"></i> Watch on YouTube
        </a>

        <form method="POST"
              onsubmit="return confirm('Delete this video?');"
              style="margin-top:10px;">

            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $media['gallery_id'] ?>">

            <button class="btn red" style="width:100%;">Delete</button>
        </form>

    </div>

</div>

<?php endforeach; ?>

</div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
