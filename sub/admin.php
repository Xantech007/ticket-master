<?php
session_start();
include 'db.php';
$site_name = getSetting('site_name', $conn);
$is_admin = isset($_SESSION['admin_auth_state']) && $_SESSION['admin_auth_state'] === true;
$msg_out = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Secure Master Cryptographic Terminal Challenge Gate Pass Check
    if($action === 'admin_auth_login') {
        $pass_token = $_POST['admin_pass_key'] ?? '';
        if($pass_token === getSetting('admin_password', $conn)) {
            $_SESSION['admin_auth_state'] = true;
            header("Location: admin.php");
            exit;
        } else {
            $msg_out = "<p style='color:red; font-size:0.85rem; font-weight:700;'>Terminal Access Authorization Failure: Secret coordinate parameter mismatch.</p>";
        }
    }

    if(isset($_SESSION['admin_auth_state']) && $_SESSION['admin_auth_state'] === true) {
        
        // Dynamic Global Global Copywriting Content Override Engine Task Matrix Channel
        if ($action === 'commit_settings') {
            foreach($_POST['setting'] as $k => $v) {
                $sk = $conn->real_escape_string($k);
                $sv = $conn->real_escape_string($v);
                $conn->query("UPDATE site_settings SET meta_value='$sv' WHERE meta_key='$sk'");
            }
            
            // Header Logo File Process Wrapper Node Module Hook for Device Photo Roll uploads
            if(!empty($_FILES["upload_logo_file"]["name"])) {
                if(!is_dir('uploads')) { mkdir('uploads', 0755, true); }
                $logo_path = "uploads/logo_" . time() . "_" . basename($_FILES["upload_logo_file"]["name"]);
                if(move_uploaded_file($_FILES["upload_logo_file"]["tmp_name"], $logo_path)) {
                    $conn->query("UPDATE site_settings SET meta_value='$logo_path' WHERE meta_key='header_logo'");
                }
            }

            // Hero Canvas Canvas Asset File image processor wrapper node
            if(!empty($_FILES["upload_hero_file"]["name"])) {
                if(!is_dir('uploads')) { mkdir('uploads', 0755, true); }
                $hero_path = "uploads/hero_" . time() . "_" . basename($_FILES["upload_hero_file"]["name"]);
                if(move_uploaded_file($_FILES["upload_hero_file"]["tmp_name"], $hero_path)) {
                    $conn->query("UPDATE site_settings SET meta_value='$hero_path' WHERE meta_key='hero_img'");
                }
            }
            $msg_out = "<div class='card' style='background:#D1E7DD; color:#0F5132; padding:12px;'>Global layout property settings and file uploads executed successfully.</div>";
        }

        // Live Event Package Dynamic Injection Node Module Task Channel
        if ($action === 'inject_event') {
            $title = $conn->real_escape_string($_POST['title']);
            $venue = $conn->real_escape_string($_POST['venue']);
            $edate = $conn->real_escape_string($_POST['event_date']);
            $cat = $conn->real_escape_string($_POST['category']);
            $p_vip = floatval($_POST['price_vip']);
            $p_reg = floatval($_POST['price_regular']);
            $keys = $conn->real_escape_string($_POST['search_keywords']);
            $desc = $conn->real_escape_string($_POST['description']);

            $main_img_path = "uploads/default_event.jpg";
            $seat_img_path = "uploads/default_seating.jpg";

            if(!is_dir('uploads')) { mkdir('uploads', 0755, true); }

            if(!empty($_FILES["img_main"]["name"])) {
                $main_img_path = "uploads/m_" . time() . "_" . basename($_FILES["img_main"]["name"]);
                move_uploaded_file($_FILES["img_main"]["tmp_name"], $main_img_path);
            }
            if(!empty($_FILES["img_seat"]["name"])) {
                $seat_img_path = "uploads/s_" . time() . "_" . basename($_FILES["img_seat"]["name"]);
                move_uploaded_file($_FILES["img_seat"]["tmp_name"], $seat_img_path);
            }

            $stmt = $conn->prepare("INSERT INTO events (title, description, venue, event_date, category, price_vip, price_regular, image_main, image_seating, search_keywords) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssddsss", $title, $desc, $venue, $edate, $cat, $p_vip, $p_reg, $main_img_path, $seat_img_path, $keys);
            $stmt->execute();
            $msg_out = "<div class='card' style='background:#D1E7DD; color:#0F5132; padding:12px;'>Show allocation card deployed live to Showcase Inventory Grid indexes.</div>";
        }

        // CRM Direct Custom Personal Notifications Dispatch Task Channel
        if ($action === 'dispatch_crm_msg') {
            $user_target_id = intval($_POST['user_target_id']);
            $msg_body = $conn->real_escape_string($_POST['message_body_text']);

            $stmt = $conn->prepare("INSERT INTO messages (user_id, message_text) VALUES (?, ?)");
            $stmt->bind_param("is", $user_target_id, $msg_body);
            if($stmt->execute()) {
                $msg_out = "<div class='card' style='background:#D1E7DD; color:#0F5132; padding:12px;'>Message directive token injected successfully into Target Account Dashboard inbox panel.</div>";
            }
        }
    }
}

if(!$is_admin):
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Authorize Administrative Terminal Signature</title><link rel="stylesheet" href="styles.css"></head>
<body style="background:#021526; display:flex; align-items:center; height:100vh; justify-content:center; padding:15px;">
<div class="card" style="width:100%; max-width:380px; background:white; padding:25px;">
    <h3 style="font-weight:800; text-align:center; color:var(--dark);">Administrative Control Suite</h3>
    <p style="text-align:center; font-size:0.75rem; color:var(--gray); margin-bottom:15px;">Secure structural operational access key required below.</p>
    <?php echo $msg_out; ?>
    <form action="admin.php" method="POST">
        <input type="hidden" name="action" value="admin_auth_login">
        <div class="form-group">
            <label>Master Passphrase Vault Key</label>
            <input type="password" name="admin_pass_key" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn" style="background:#021526;">Authorize System Control</button>
    </form>
</div>
</body></html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master System Administrative Control Hub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header style="background:#0F172A; border-bottom:3px solid var(--accent);"><div class="header-top"><span class="brand">👑 System Central Core</span><a href="index.php" class="nav-btn">Monitor Front End Live</a></div></header>

<main class="container" style="margin-top:20px;">
    <h2>Global Operations Control Panel Suite</h2>
    <p style="font-size:0.8rem; color:var(--gray); margin-bottom:20px;">Viewport interface optimized for execution from high performance smartphone arrays logs.</p>

    <?php echo $msg_out; ?>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem;">1. Absolute Global Layout Content & UI Settings Engine</h3>
            <form action="admin.php" method="POST" enctype="multipart/form-data" style="margin-top:12px;">
                <input type="hidden" name="action" value="commit_settings">
                
                <div class="form-group"><label>Platform Title Identity Branding Name Token</label><input type="text" name="setting[site_name]" value="<?php echo getSetting('site_name', $conn); ?>" class="form-control"></div>
                <div class="form-group"><label>Header Bar HEX Color Vector</label><input type="text" name="setting[header_bg]" value="<?php echo getSetting('header_bg', $conn); ?>" class="form-control" placeholder="#0A0E1A"></div>
                <div class="form-group"><label>Replace Header Branding Brand Logo Image (Camera Roll File)</label><input type="file" name="upload_logo_file" accept="image/*" class="form-control"></div>
                <div class="form-group"><label>Top Header Micro Ticker Alert Notification Ribbon Text Message</label><input type="text" name="setting[alert_text]" value="<?php echo getSetting('alert_text', $conn); ?>" class="form-control"></div>
                <hr style="margin:15px 0; border:0; border-top:1px solid var(--border);">
                
                <div class="form-group"><label>Hero Showcase Title Billboard Banner Header Text</label><input type="text" name="setting[hero_title]" value="<?php echo getSetting('hero_title', $conn); ?>" class="form-control"></div>
                <div class="form-group"><label>Hero Subtext Descriptive Segment Elements</label><input type="text" name="setting[hero_subtitle]" value="<?php echo getSetting('hero_subtitle', $conn); ?>" class="form-control"></div>
                <div class="form-group"><label>Replace Hero Canvas Canvas Graphic Background Asset</label><input type="file" name="upload_hero_file" accept="image/*" class="form-control"></div>
                <hr style="margin:15px 0; border:0; border-top:1px solid var(--border);">
                
                <div class="form-group"><label>About Narrative Headline Widget Text</label><input type="text" name="setting[about_title]" value="<?php echo getSetting('about_title', $conn); ?>" class="form-control"></div>
                <div class="form-group"><label>About Narrative Long Body Story Text Paragraph Block Context</label><textarea name="setting[about_text]" class="form-control" rows="3"><?php echo getSetting('about_text', $conn); ?></textarea></div>
                <hr style="margin:15px 0; border:0; border-top:1px solid var(--border);">
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div class="form-group"><label>FAQ 1 Question Row</label><input type="text" name="setting[faq_1_q]" value="<?php echo getSetting('faq_1_q', $conn); ?>" class="form-control"></div>
                    <div class="form-group"><label>FAQ 1 Expandable Dropdown Answer Text</label><input type="text" name="setting[faq_1_a]" value="<?php echo getSetting('faq_1_a', $conn); ?>" class="form-control"></div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div class="form-group"><label>FAQ 2 Question Row</label><input type="text" name="setting[faq_2_q]" value="<?php echo getSetting('faq_2_q', $conn); ?>" class="form-control"></div>
                    <div class="form-group"><label>FAQ 2 Expandable Dropdown Answer Text</label><input type="text" name="setting[faq_2_a]" value="<?php echo getSetting('faq_2_a', $conn); ?>" class="form-control"></div>
                </div>
                <hr style="margin:15px 0; border:0; border-top:1px solid var(--border);">
                <div class="form-group"><label>Corporate Copyright Footer Info Stamp Summary</label><input type="text" name="setting[footer_copyright]" value="<?php echo getSetting('footer_copyright', $conn); ?>" class="form-control"></div>
                
                <button type="submit" class="btn">Deploy Global Interface Revisions</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem;">2. Inject Fresh Multi-Image Dynamic Live Show Record Node</h3>
            <form action="admin.php" method="POST" enctype="multipart/form-data" style="margin-top:12px;">
                <input type="hidden" name="action" value="inject_event">
                
                <div class="form-group"><label>Event Title Label Designation</label><input type="text" name="title" class="form-control" placeholder="e.g. BTS MAP OF THE SOUL WORLD TOUR" required></div>
                <div class="form-group"><label>Target Venue Arena / Stadium Matrix Location Coordinates</label><input type="text" name="venue" class="form-control" placeholder="e.g. MetLife Stadium, New Jersey" required></div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div class="form-group"><label>Execution Date Timeline Vector</label><input type="datetime-local" name="event_date" class="form-control" required></div>
                    <div class="form-group"><label>Operational Classification Stream Category</label>
                        <select name="category" class="form-control"><option value="Concerts">Concerts</option><option value="Sports">Sports</option><option value="Theater">Theater</option></select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div class="form-group"><label>VIP Ticket Tier Baseline Unit Valuation ($)</label><input type="number" step="0.01" name="price_vip" class="form-control" placeholder="350.00" required></div>
                    <div class="form-group"><label>Standard Ticket Tier Unit Valuation ($)</label><input type="number" step="0.01" name="price_regular" class="form-control" placeholder="120.00" required></div>
                </div>

                <div class="form-group"><label>Smart Search Parser Alternative Sub Keywords / Tags (Comma separated)</label><input type="text" name="search_keywords" class="form-control" placeholder="bts, kpop, banga, army, metlife, live stadium, 2026"></div>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div class="form-group"><label>Upload Main Poster Graphics Card</label><input type="file" name="img_main" accept="image/*" class="form-control"></div>
                    <div class="form-group"><label>Upload Seating Plan Layout Scheme Map</label><input type="file" name="img_seat" accept="image/*" class="form-control"></div>
                </div>

                <div class="form-group"><label>Extended Context Event Summary Description Meta Block</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                
                <button type="submit" class="btn" style="background:#0F172A;">Deploy Live Show Package Card Asset</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="section-title" style="font-size:1.1rem;">3. Interactive CRM Directory Node & Direct User Messaging Engine</h3>
            <p style="font-size:0.8rem; color:var(--gray); margin-bottom:12px;">Review complete listed logs registries of matching registered user entities and push targeted message directives instantly.</p>
            
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php 
                $u_res = $conn->query("SELECT id, name, email, phone FROM users ORDER BY id DESC");
                if($u_res && $u_res->num_rows > 0) {
                    while($usr = $u_res->fetch_assoc()) {
                        echo "
                        <div style='background:#F8FAFC; border:1px solid var(--border); padding:15px; border-radius:8px;'>
                            <div style='font-size:0.85rem; display:flex; flex-direction:column; gap:2px; margin-bottom:10px;'>
                                <span style='font-weight:700; color:var(--dark);'>Profile Name: ".htmlspecialchars($usr['name'])." (ID: #US-100".$usr['id'].")</span>
                                <span style='color:var(--gray);'>Mail Coordinates: ".htmlspecialchars($usr['email'])."</span>
                                <span style='color:var(--gray);'>Tel Channel Line: ".htmlspecialchars($usr['phone'])."</span>
                            </div>
                            
                            <form action='admin.php' method='POST' style='border-top:1px dashed var(--border); padding-top:10px;'>
                                <input type=" . '"hidden"' . " name='action' value='dispatch_crm_msg'>
                                <input type=" . '"hidden"' . " name='user_target_id' value='".$usr['id']."'>
                                <div class='form-group' style='margin-bottom:6px;'>
                                    <input type='text' name='message_body_text' class='form-control' placeholder='Type secure customized statement note to user workspace notification feed...' style='padding:8px; font-size:0.8rem;' required>
                                </div>
                                <button type='submit' class='btn' style='padding:5px 10px; font-size:0.75rem; background:var(--accent); color:var(--dark); width:max-content;'>Inject Inbox Directive Token</button>
                            </form>
                        </div>";
                    }
                } else {
                    echo "<p style='font-size:0.8rem; color:var(--gray); text-align:center;'>No registered consumer entities detected mapped inside server database storage records yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</main>
</body>
</html>
