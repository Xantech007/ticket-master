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
try {
    $stmt = $pdo->prepare("SELECT * FROM artists WHERE artist_id = ?");
    $stmt->execute([$artist_id]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artist) {
        $_SESSION['error'] = "Artist not found.";
        header("Location: manage-artists.php");
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: manage-artists.php");
    exit;
}
?>

<main style="max-width:1000px;margin:2rem auto;">

<h1 style="text-align:center;margin-bottom:2rem;">Artist Extras</h1>

<!-- ARTIST HEADER CARD -->
<div style="display:flex;align-items:center;gap:20px;background:var(--card);padding:20px;border-radius:12px;border:1px solid var(--border);margin-bottom:2rem;">

    <?php if (!empty($artist['artist_image'])): ?>
        <img src="../uploads/artists/<?= htmlspecialchars($artist['artist_image']) ?>"
             style="width:80px;height:80px;object-fit:cover;border-radius:10px;">
    <?php else: ?>
        <div style="width:80px;height:80px;background:#222;border-radius:10px;"></div>
    <?php endif; ?>

    <div>
        <h2 style="margin:0;">
            <?= htmlspecialchars($artist['artist_name']) ?>
        </h2>
        <p style="color:#888;margin:5px 0 0;">Manage artist modules below</p>
    </div>

</div>

<!-- OPTIONS GRID -->
<div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
">

    <!-- VIP & EXPERIENCE -->
    <a href="manage-vip-exp.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-microphone" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        Manage VIP & Experience
    </a>

    <!-- GALLERY -->
    <a href="manage-gallery.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-images" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        Manage Gallery
    </a>

    <!-- SETLISTS -->
    <a href="manage-setlists.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-music" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        Manage Setlists
    </a>

    <!-- FAQS -->
    <a href="manage-faqs.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-question-circle" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        Manage FAQs
    </a>

    <!-- REVIEWS -->
    <a href="manage-reviews.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-star" style="font-size:20px;margin-bottom:8px;display:block;color:#facc15;"></i>
        Manage Reviews
    </a>

    <!-- FANS ALSO VIEWED -->
    <a href="manage-fans-also-viewed.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-users" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        Fans Also Viewed
    </a>

    <!-- NEWS -->
    <a href="manage-fans-also-viewed.php?artist_id=<?= $artist_id ?>"
       style="background:#111827;padding:20px;border-radius:10px;text-align:center;text-decoration:none;color:#fff;border:1px solid var(--border);">
        <i class="fas fa-news" style="font-size:20px;margin-bottom:8px;display:block;"></i>
        NEWS
    </a>

</div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
