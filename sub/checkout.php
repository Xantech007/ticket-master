<?php
session_start();
include 'db.php';
$site_name = getSetting('site_name', $conn);

if($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: index.php"); exit; }

$item_type = $_POST['item_type'] ?? '';
$target_id = $_POST['target_id'] ?? '';
$qty_vip = intval($_POST['qty_tier_vip'] ?? 0);
$qty_reg = intval($_POST['qty_tier_reg'] ?? 0);

if($qty_vip === 0 && $qty_reg === 0) { die("Please update your asset assignment vectors to choose at least 1 item allocation token."); }

$summary_title = '';
$subtotal = 0;
$breakdown_string = '';

if ($item_type === 'p2p') {
    $stmt = $conn->prepare("SELECT * FROM transfers WHERE transfer_id = ? AND status='available'");
    $stmt->bind_param("s", $target_id);
    $stmt->execute();
    $t_row = $stmt->get_result()->fetch_assoc();
    if(!$t_row) { die("Market asset matching signature reference token tracking invalid."); }
    $summary_title = "P2P Ownership Transfer Request Handover";
    $subtotal = $t_row['price'];
    $breakdown_string = "1x Peer Resale Ticket (Event: ".$t_row['event_title']." | Sec: ".$t_row['section']." Row: ".$t_row['row_num']." Seat: ".$t_row['seat_num'].")";
} else {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $e_row = $stmt->get_result()->fetch_assoc();
    if(!$e_row) { die("Standard target indexing signature matrix data mapping error."); }
    $summary_title = $e_row['title'];
    
    $parts = [];
    if($qty_vip > 0) {
        $subtotal += ($qty_vip * $e_row['price_vip']);
        $parts[] = "{$qty_vip}x VIP Lounge Suite Tickets";
    }
    if($qty_reg > 0) {
        $subtotal += ($qty_reg * $e_row['price_regular']);
        $parts[] = "{$qty_reg}x Standard Main Bowl Tickets";
    }
    $breakdown_string = implode(' + ', $parts) . " at Venue destination: " . $e_row['venue'];
}

// Fixed Professional Itemized Platform Service Charge Broker Fee Scalar Parameter
$service_broker_fee = 14.50;
$grand_total = $subtotal + $service_broker_fee;

// Generate Professional High Entropy Anti Spam Order ID Tracker Token
$order_id = "MT-" . date('dM') . "-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 5));

// Produce Dynamic Context Custom Handover Message String Array Vector Packages
$whatsapp_payload_msg = "Hello Support Center Network. I want to finalize verification protocols for Ticket Manifest Order ID Reference Block Token: {$order_id}. \\n\\nDetails Allocation Context:\\n- Platform Event: {$summary_title}\\n- Assets Assigned: {$breakdown_string}\\n- Subtotal Base Amount: ${$subtotal}\\n- Platform Clearing Fee: ${$service_broker_fee}\\n- Definitive Grand Total Settlement Target: ${$grand_total}\\n\\nPlease forward system clearance wallet processing parameters coordinates instantly.";

$whatsapp_destination_receiver_phone = "447412364559";
$final_api_gateway_route_url = "https://wa.me/" . $whatsapp_destination_receiver_phone . "?text=" . urlencode($whatsapp_payload_msg);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Summary Ledger Cart | <?php echo $site_name; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header style="background:<?php echo getSetting('header_bg', $conn); ?>;">
    <div class="header-top"><a href="index.php" class="brand"><?php echo $site_name; ?></a></div>
</header>

<main class="container" style="margin-top:20px;">
    <div class="card" style="padding:20px; background:white;">
        <div class="badge" style="background:#D1E7DD; color:#0F5132;">ORDER GENERATED KEY ASSIGNED</div>
        <h2 style="margin-top:5px; font-size:1.3rem;">Verify Operational Summary Details</h2>
        <hr style="margin:12px 0; border:0; border-top:1px solid var(--border);">
        
        <div style="font-size:0.9rem; margin-bottom:15px; display:flex; justify-content:space-between;">
            <span style="color:var(--gray);">Order ID Reference Tag:</span>
            <strong style="color:var(--dark); font-family:monospace; font-size:1rem;"><?php echo $order_id; ?></strong>
        </div>

        <div style="background:#F8FAFC; padding:15px; border-radius:8px; border:1px solid var(--border); margin-bottom:20px;">
            <h4 style="font-weight:700; color:var(--dark);"><?php echo htmlspecialchars($summary_title); ?></h4>
            <p style="font-size:0.85rem; color:var(--gray); margin-top:4px;"><?php echo htmlspecialchars($breakdown_string); ?></p>
        </div>

        <div style="font-size:0.9rem; display:flex; flex-direction:column; gap:8px; border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:15px;">
            <div style="display:flex; justify-content:space-between;"><span style="color:var(--gray);">Base Subtotal Valuation Matrix:</span><span>$<?php echo number_format($subtotal,2); ?></span></div>
            <div style="display:flex; justify-content:space-between;"><span style="color:var(--gray);">Itemized Platform Operations Clearence Fee:</span><span>$<?php echo number_format($service_broker_fee,2); ?></span></div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <span style="font-weight:700; font-size:1.05rem;">Grand Total Settlement Sum:</span>
            <span style="font-size:1.4rem; font-weight:900; color:var(--primary);">$<?php echo number_format($grand_total,2); ?></span>
        </div>

        <p style="font-size:0.75rem; color:var(--gray); text-align:center; margin-bottom:15px; line-height:1.4;">Tapping the verification execution link terminal below wraps your secure transaction cookies parameters and instantly initializes handovers onto the support chat box app layer seamlessly.</p>
        
        <a href="<?php echo $final_api_gateway_route_url; ?>" class="btn">Execute Validation Checkout via WhatsApp</a>
        <a href="index.php" class="btn btn-secondary" style="margin-top:10px;">Cancel and Empty Cart</a>
    </div>
</main>
</body>
</html>
