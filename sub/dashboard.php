<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_auth'])) { header("Location: login.php"); exit; }

$site_name = getSetting('site_name', $conn);
$uid = $_SESSION['user_id'];
$message_out = '';

// Metadata Profiling Operations Update Flow
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $update = $conn->query("UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id='$uid'");
    if($update) {
        $_SESSION['user_name'] = $name;
        $message_out = "<div class='card' style='background:#D1E7DD; color:#0F5132; padding:12px;'>Identity parameter profile registers updated success.</div>";
    }
}

// Peer Marketplace Resale Entry Creation Process flow
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_transfer'])) {
    $ev_title = $conn->real_escape_string($_POST['event_title']);
    $sec = $conn->real_escape_string($_POST['section']);
    $row = $conn->real_escape_string($_POST['row_num']);
    $seat = $conn->real_escape_string($_POST['seat_num']);
    $price = floatval($_POST['price']);
    
    // Generate clean distinctive uppercase secure readable transfer short code alphanumeric token
    $tx_token = "TX-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
    
    $stmt = $conn->prepare("INSERT INTO transfers (transfer_id, user_id, event_title, section, row_num, seat_num, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissssd", $tx_token, $uid, $ev_title, $sec, $row, $seat, $price);
    if($stmt->execute()) {
        $message_out = "<div class='card' style='background:#D1E7DD; color:#0F5132; padding:12px;'>P2P listing deployment successful! Share target code reference token to buyer tracking indexes: <strong>{$tx_token}</strong></div>";
    }
}

// Read Personal Core Metadata profiles values arrays variables
$u_data = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();

// Pull Custom Inbox Server Messages Injected via System Administrators Terminal Suite
$msg_res = $conn->query("SELECT * FROM messages WHERE user_id='$uid' ORDER BY sent_at DESC");

// Pull Current User P2P asset listing records arrays matrix
$tx_res = $conn->query("SELECT * FROM transfers WHERE user_id='$uid' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Workspace Profile Matrix Hub | <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header style="background:<?php echo getSetting('header_bg', $conn); ?>;">
    <div class="header-top">
        <a href="index.php" class="brand"><?php echo $site_name; ?></a>
        <a href="index.php" class="nav-btn">Return to Showcase Index</a>
    </div>
</header>

<main class="container" style="margin-top:15px;">
    <h2>Welcome back operator entity, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
    <p style="font-size:0.8rem; color:var(--gray); margin-bottom:20px;">Identity mapping unique system access node ID register: #US-100<?php echo $uid; ?></p>

    <?php echo $message_out; ?>

    <div class="card" style="background:#FFF;">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem; margin-bottom:10px;">💌 Command Administrative Network Communications Inbox</h3>
            <?php if($msg_res && $msg_res->num_rows > 0): ?>
                <?php while($m = $msg_res->fetch_assoc()): ?>
                    <div class="inbox-msg">
                        <div style="font-weight:700; color:var(--dark); margin-bottom:2px;">Message Manifest Directive Node:</div>
                        <p style="color:#92400E; font-size:0.9rem;"><?php echo htmlspecialchars($m['message_text']); ?></p>
                        <span style="display:block; font-size:0.7rem; color:var(--gray); margin-top:5px; text-align:right;">Dispatched stamp: <?php echo $m['sent_at']; ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="font-size:0.85rem; color:var(--gray); text-align:center; padding:15px 0;">No direct administrative communications pipelines initialized inside current workspace node framework logs yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem;">🔄 Active Peer-to-Peer Resale Listings Asset Board</h3>
            <form action="dashboard.php" method="POST" style="margin-top:12px; background:#F8FAFC; padding:15px; border-radius:8px; border:1px solid var(--border);">
                <input type="hidden" name="action_create_transfer" value="1">
                <h4 style="font-size:0.9rem; margin-bottom:10px; font-weight:700;">Deploy Secondary Marketplace Asset Node</h4>
                
                <div class="form-group"><label>Event / Performer Name Specification</label><input type="text" name="event_title" class="form-control" placeholder="e.g. BTS World Concert Tour" required></div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                    <div class="form-group"><label>Section Vector</label><input type="text" name="section" class="form-control" placeholder="A1" required></div>
                    <div class="form-group"><label>Row ID</label><input type="text" name="row_num" class="form-control" placeholder="12" required></div>
                    <div class="form-group"><label>Seat Unit</label><input type="text" name="seat_num" class="form-control" placeholder="4" required></div>
                </div>
                <div class="form-group"><label>Target Valuation Transfer Asking Price ($)</label><input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required></div>
                <button type="submit" class="btn" style="padding:10px;">Produce Resale Token Coordinates</button>
            </form>

            <h4 style="font-size:0.9rem; margin-top:20px; margin-bottom:8px; font-weight:700;">Your Asset Nodes Logs</h4>
            <?php if($tx_res && $tx_res->num_rows > 0): ?>
                <div style="display:flex; flex-direction:column; gap:8px;">
                <?php while($t = $tx_res->fetch_assoc()): ?>
                    <div style="background:white; padding:10px; border:1px solid var(--border); border-radius:6px; display:flex; justify-content:space-between; align-items:center; font-size:0.85rem;">
                        <div>
                            <strong><?php echo htmlspecialchars($t['event_title']); ?></strong>
                            <div style="color:var(--gray); font-size:0.75rem;">Vector: Sec <?php echo $t['section']; ?> Row <?php echo $t['row_num']; ?> Seat <?php echo $t['seat_num']; ?></div>
                            <span style="font-family:monospace; background:#F1F5F9; padding:2px 5px; border-radius:3px; font-size:0.75rem; font-weight:700; color:var(--primary);"><?php echo $t['transfer_id']; ?></span>
                        </div>
                        <strong style="color:var(--primary); font-size:1rem;">$<?php echo number_format($t['price'],2); ?></strong>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="font-size:0.75rem; color:var(--gray);">No current asset transfer configurations mapped inside repository logs.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem; margin-bottom:12px;">⚙️ Manage Identity Framework Metadata Parameters</h3>
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="action_update_profile" value="1">
                <div class="form-group">
                    <label>Profile Label Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($u_data['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Communications Email Destination Coordinates</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($u_data['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Active Telephone Dial Vector Line</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($u_data['phone']); ?>" required>
                </div>
                <button type="submit" class="btn btn-secondary" style="padding:10px;">Commit Profile Parameter Changes</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
