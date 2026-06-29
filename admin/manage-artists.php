<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

/* --------------------------------------------------
   FETCH ALL ARTISTS
-------------------------------------------------- */
try {
    $stmt = $pdo->query("SELECT * FROM artists ORDER BY id DESC");
    $artists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $artists = [];
}

/* --------------------------------------------------
   HANDLE ACTIONS
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    $action = $_POST['action'] ?? '';

    try {

        /* ---------------- ADD ARTIST ---------------- */
        if ($action === 'add') {

            $artist_name = trim($_POST['artist_name'] ?? '');
            $vip_exp     = trim($_POST['vip_exp'] ?? '');
            $reviews     = trim($_POST['reviews'] ?? '');
            $about       = trim($_POST['about'] ?? '');
            $faqs        = trim($_POST['faqs'] ?? '');

            if ($artist_name === '') {
                throw new Exception("Artist name is required.");
            }

            $stmt = $pdo->prepare("
                INSERT INTO artists (artist_name, vip_exp, reviews, about, faqs)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $artist_name,
                $vip_exp,
                $reviews,
                $about,
                $faqs
            ]);

            $id = $pdo->lastInsertId();

            $message = "Artist added successfully.";

            // Redirect to concerts page for this artist
            header("Location: manage-concerts.php?artist_id=" . $id);
            exit;
        }

        /* ---------------- DELETE ARTIST ---------------- */
        if ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid artist ID.");
            }

            $stmt = $pdo->prepare("DELETE FROM artists WHERE id = ?");
            $stmt->execute([$id]);

            $message = "Artist deleted successfully.";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage Artists</h1>

  <?php if ($message): ?>
    <div style="background:#238636; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:800px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#f85149; color:white; padding:1.2rem; border-radius:8px; margin-bottom:2rem; text-align:center; max-width:800px; margin-left:auto; margin-right:auto;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- ADD ARTIST FORM -->
  <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2.5rem; max-width:800px; margin:0 auto 4rem;">

    <h2 style="margin-bottom:1.8rem; text-align:center;">Add New Artist</h2>

    <form method="POST">

      <input type="hidden" name="action" value="add">

      <div style="margin-bottom:1.2rem;">
        <label>Artist Name</label>
        <input type="text" name="artist_name" required
          style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);">
      </div>

      <div style="margin-bottom:1.2rem;">
        <label>VIP Experience</label>
        <textarea name="vip_exp" rows="3"
          style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);"></textarea>
      </div>

      <div style="margin-bottom:1.2rem;">
        <label>Reviews</label>
        <textarea name="reviews" rows="3"
          style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);"></textarea>
      </div>

      <div style="margin-bottom:1.2rem;">
        <label>About</label>
        <textarea name="about" rows="4"
          style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);"></textarea>
      </div>

      <div style="margin-bottom:1.8rem;">
        <label>FAQs</label>
        <textarea name="faqs" rows="4"
          style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text);"></textarea>
      </div>

      <button type="submit" class="btn" style="width:100%; padding:1rem;">
        <i class="fas fa-plus"></i> Add Artist
      </button>

    </form>
  </div>

  <!-- ARTISTS LIST -->
  <div style="max-width:900px; margin:0 auto 4rem;">

    <h2 style="text-align:center; margin-bottom:2rem;">Existing Artists</h2>

    <?php if (empty($artists)): ?>
      <p style="text-align:center; color:var(--text-muted);">No artists found.</p>
    <?php endif; ?>

    <?php foreach ($artists as $artist): ?>

      <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2rem; margin-bottom:1.5rem;">

        <h3 style="color:#58a6ff; margin-bottom:1rem;">
          <?= htmlspecialchars($artist['artist_name']) ?>
        </h3>

        <p><strong>VIP Experience:</strong><br><?= nl2br(htmlspecialchars($artist['vip_exp'])) ?></p>
        <p><strong>Reviews:</strong><br><?= nl2br(htmlspecialchars($artist['reviews'])) ?></p>
        <p><strong>About:</strong><br><?= nl2br(htmlspecialchars($artist['about'])) ?></p>
        <p><strong>FAQs:</strong><br><?= nl2br(htmlspecialchars($artist['faqs'])) ?></p>

        <div style="margin-top:1.5rem; display:flex; gap:10px; flex-wrap:wrap;">

          <a href="manage-concerts.php?artist_id=<?= $artist['id'] ?>"
             class="btn green">
            <i class="fas fa-music"></i> Manage Concerts
          </a>

          <form method="POST" onsubmit="return confirm('Delete this artist?');" style="display:inline;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $artist['id'] ?>">

            <button type="submit" class="btn red">
              <i class="fas fa-trash"></i> Delete
            </button>
          </form>

        </div>

      </div>

    <?php endforeach; ?>

  </div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
