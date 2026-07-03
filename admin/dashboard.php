<?php
// admin/dashboard.php
require_once __DIR__ . '/inc/header.php';

/* UPDATE WHATSAPP NUMBER */
$success = '';

if(isset($_POST['update_whatsapp'])){

    $whatsapp = trim($_POST['whatsapp']);

    try{

        $stmt = $pdo->prepare("UPDATE admins SET whatsapp=? LIMIT 1");
        $stmt->execute([$whatsapp]);

        $success = "WhatsApp number updated successfully.";

    } catch(PDOException $e){

        $success = "Error: " . $e->getMessage();
    }
}

/* FETCH CURRENT WHATSAPP */
$stmt = $pdo->query("SELECT whatsapp FROM admins LIMIT 1");
$currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

$currentWhatsapp = $currentAdmin['whatsapp'] ?? '';

/* --------------------------------------------------
   DASHBOARD STATS
-------------------------------------------------- */

try {

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM artists");
    $total_artists = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM concerts");
    $total_concerts = (int)$stmt->fetchColumn();

    /* NEW: tickets count */
    $stmt = $pdo->query("SELECT COUNT(*) FROM tickets");
    $total_tickets = (int)$stmt->fetchColumn();

    /* NEW: payment_methods count */
    $stmt = $pdo->query("SELECT COUNT(*) FROM payment_methods");
    $total_payment_methods = (int)$stmt->fetchColumn();

    /* NEW: region_settings count */
    $stmt = $pdo->query("SELECT COUNT(*) FROM region_settings");
    $total_region_settings = (int)$stmt->fetchColumn();
    
} catch (PDOException $e) {

    echo '<div style="background:#f85149;color:#fff;padding:15px;border-radius:8px;margin-bottom:20px;">';
    echo "<strong>Database Error:</strong> " . htmlspecialchars($e->getMessage());
    echo '</div>';

    $total_users = 0;
    $total_artists = 0;
    $total_concerts = 0;
    $total_tickets = 0;
    $total_payments_methods = 0;
    $total_region_settings = 0;
}
?>

<main style="margin-top: 1.5rem;">

<h1 style="text-align:center; margin-bottom:2.5rem; font-size:2.1rem;">
Dashboard Overview
</h1>

<!-- WHATSAPP SETTINGS -->
<div style="
max-width:500px;
margin:0 auto 2rem auto;
background:#161b22;
padding:1.5rem;
border-radius:12px;
border:1px solid #30363d;
">

<h2 style="margin-bottom:1rem; text-align:center;">
  WhatsApp Support Number
</h2>

<?php if(!empty($success)): ?>
  <div style="
    background:#238636;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:1rem;
    text-align:center;
  ">
    <?= htmlspecialchars($success) ?>
  </div>
<?php endif; ?>

<form method="POST">

  <input
    type="text"
    name="whatsapp"
    value="<?= htmlspecialchars($currentWhatsapp) ?>"
    placeholder="Enter WhatsApp Number"
    required
    style="
      width:100%;
      padding:14px;
      border-radius:8px;
      border:1px solid #30363d;
      background:#0d1117;
      color:white;
      margin-bottom:1rem;
      font-size:15px;
    "
  >

  <button
    type="submit"
    name="update_whatsapp"
    style="
      width:100%;
      padding:14px;
      border:none;
      border-radius:8px;
      background:#25D366;
      color:white;
      font-size:15px;
      cursor:pointer;
      font-weight:bold;
    "
  >
    Update WhatsApp
  </button>

</form>

</div>

<!-- STATS -->
<div class="stats-grid">

    <div class="card">
        <div class="card-icon" style="color:#58a6ff;">
            <i class="fas fa-users"></i>
        </div>
        <div class="card-value"><?= number_format($total_users) ?></div>
        <div class="card-label">Users</div>
    </div>

    <div class="card">
        <div class="card-icon" style="color:#ff7b72;">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="card-value"><?= number_format($total_artists) ?></div>
        <div class="card-label">Artists</div>
    </div>

    <div class="card">
        <div class="card-icon" style="color:#3fb950;">
            <i class="fas fa-music"></i>
        </div>
        <div class="card-value"><?= number_format($total_concerts) ?></div>
        <div class="card-label">Concerts</div>
    </div>

    <!-- NEW TICKETS CARD -->
    <div class="card">
        <div class="card-icon" style="color:#d29922;">
            <i class="fas fa-ticket"></i>
        </div>
        <div class="card-value"><?= number_format($total_tickets) ?></div>
        <div class="card-label">Tickets</div>
    </div>

    <!-- NEW TICKETS CARD -->
    <div class="card">
        <div class="card-icon" style="color:#272673;">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="card-value"><?= number_format($total_payment_methods) ?></div>
        <div class="card-label">Payment Methods</div>
    </div>

    <!-- NEW TICKETS CARD -->
    <div class="card">
        <div class="card-icon" style="color:#fff;">
            <i class="fas fa-globe"></i>
        </div>
        <div class="card-value"><?= number_format($total_region_settings) ?></div>
        <div class="card-label">Region Settings</div>
    </div>

</div>

<!-- MANAGEMENT -->
<h2 style="text-align:center; margin:3rem 0 1.8rem; font-size:1.7rem;">
Management Sections
</h2>

<div class="actions-grid">

    <a href="manage-users.php" class="btn">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="manage-artists.php" class="btn">
        <i class="fas fa-microphone"></i> Manage Artists
    </a>

    <a href="manage-concerts.php" class="btn">
        <i class="fas fa-microphone-lines"></i> Manage Concerts
    </a>

    <a href="manage-tickets.php" class="btn">
        <i class="fas fa-ticket"></i> Manage Tickets
    </a>

    <a href="manage-payment-methods.php" class="btn">
        <i class="fas fa-credit-card"></i> Manage Payment Methods
    </a>

    <a href="manage-region-settings.php" class="btn">
        <i class="fas fa-globe"></i> Manage Region Settings
    </a>

</div>

</main>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
