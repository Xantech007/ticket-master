<?php
// admin/manage-news.php
require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

// --------------------------------------------------
// 1. Check if the single news row exists (id = 1)
// --------------------------------------------------
try {
    $stmt = $pdo->prepare("SELECT title FROM news WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $has_news = (bool)$news;
    $current_title = $has_news ? $news['title'] : '';
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $has_news = false;
    $current_title = '';
}

// --------------------------------------------------
// 2. Handle form actions
// --------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add' || $action === 'edit') {
            $title = trim($_POST['title'] ?? '');
            
            if (empty($title)) {
                throw new Exception("News title cannot be empty.");
            }

            if ($action === 'add') {
                // Only allow insert if row doesn't exist
                if ($has_news) {
                    throw new Exception("News entry already exists. Please edit it instead.");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO news (id, title, status, created_at)
                    VALUES (1, ?, 1, NOW())
                ");
                $stmt->execute([$title]);
                
                $message = "News entry created successfully.";
                $has_news = true;
                $current_title = $title;
            } 
            else if ($action === 'edit') {
                // Only allow update if row exists
                if (!$has_news) {
                    throw new Exception("No news entry exists yet. Please create it first.");
                }

                $stmt = $pdo->prepare("
                    UPDATE news 
                    SET title = ?, 
                        updated_at = NOW()   -- if you have this column
                    WHERE id = 1
                ");
                $stmt->execute([$title]);
                
                $message = "News updated successfully.";
                $current_title = $title;
            }
        } 
        else if ($action === 'delete') {
            if (!$has_news) {
                throw new Exception("There is no news to delete.");
            }

            $stmt = $pdo->prepare("DELETE FROM news WHERE id = 1");
            $stmt->execute();
            
            $message = "News entry deleted successfully.";
            $has_news = false;
            $current_title = '';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main>
  <h1 style="text-align:center; margin: 2.5rem 0 2rem;">Manage News (Single Entry)</h1>

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

  <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:2.5rem; max-width:800px; margin:0 auto 4rem;">
    <?php if ($has_news): ?>
      <!-- Edit + Delete mode -->
      <h2 style="margin-bottom:1.8rem; text-align:center; color:#58a6ff;">Current News</h2>
      
      <div style="background:#0d1117; padding:1.5rem; border-radius:8px; margin-bottom:2rem; white-space:pre-wrap; word-break:break-word;">
        <?= htmlspecialchars($current_title) ?>
      </div>

      <h3 style="margin:2rem 0 1.2rem; text-align:center;">Edit News</h3>
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        
        <div style="margin-bottom:1.8rem;">
          <label style="display:block; margin-bottom:0.6rem; font-weight:500;">News Title / Announcement</label>
          <textarea 
            name="title" 
            rows="5" 
            required 
            style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-family:inherit; resize:vertical;"
          ><?= htmlspecialchars($current_title) ?></textarea>
        </div>

        <button type="submit" class="btn" style="width:100%; padding:1rem; margin-bottom:1.5rem;">
          <i class="fas fa-save"></i> Update News
        </button>
      </form>

      <hr style="border-color:var(--border); margin:2.5rem 0;">

      <form method="POST" onsubmit="return confirm('Delete the current news announcement?');">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn red" style="width:100%; padding:1rem;">
          <i class="fas fa-trash-alt"></i> Delete News
        </button>
      </form>

    <?php else: ?>
      <!-- Add mode -->
      <h2 style="margin-bottom:1.8rem; text-align:center;">Create News Announcement</h2>
      
      <p style="text-align:center; color:var(--text-muted); margin-bottom:2rem;">
        There is currently no news entry (ID 1 does not exist).
      </p>

      <form method="POST">
        <input type="hidden" name="action" value="add">
        
        <div style="margin-bottom:1.8rem;">
          <label style="display:block; margin-bottom:0.6rem; font-weight:500;">News Title / Announcement</label>
          <textarea 
            name="title" 
            rows="6" 
            required 
            placeholder="Enter the news text here..." 
            style="width:100%; padding:0.9rem; border:1px solid var(--border); border-radius:6px; background:#0d1117; color:var(--text); font-family:inherit; resize:vertical;"
          ></textarea>
        </div>

        <button type="submit" class="btn" style="width:100%; padding:1rem;">
          <i class="fas fa-plus"></i> Create News
        </button>
      </form>
    <?php endif; ?>
  </div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
