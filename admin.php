<?php
// admin.php
session_start();
require_once 'db.php';

// Authentication Layer Processing Execution Loop
if (!isset($_SESSION['admin_session_active'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_token_action'])) {
        $input_user = trim($_POST['admin_user_field']);
        $input_pass = trim($_POST['admin_pass_field']);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$input_user]);
        $profile = $stmt->fetch();
        
        if ($profile && password_verify($input_pass, $profile['password'])) {
            $_SESSION['admin_session_active'] = true;
            $_SESSION['admin_user_string'] = $profile['username'];
            header("Location: admin.php");
            exit;
        } else {
            $error_prompt = "Access Denied: Invalid Administrative Authentication Signature.";
        }
    }
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Control Center Authentication</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-900 flex items-center justify-center min-h-screen font-sans">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden p-8">
            <h2 class="text-2xl font-black text-slate-900 text-center uppercase tracking-tight">System Guard</h2>
            <p class="text-xs text-gray-400 text-center mt-1 mb-6 font-mono">Secure Administration Node Login</p>
            
            <?php if(isset($error_prompt)): ?>
                <div class="bg-red-50 text-red-700 text-xs font-semibold p-3 rounded-lg border border-red-200 mb-4"><?= $error_prompt; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="auth_token_action" value="1">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Username:</label>
                    <input type="text" name="admin_user_field" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm outline-none focus:border-blue-600 font-semibold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Password:</label>
                    <input type="password" name="admin_pass_field" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm outline-none focus:border-blue-600 font-semibold">
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-4 rounded-lg text-xs uppercase tracking-widest transition-colors shadow">
                    Verify Administrative Access
                </button>
            </form>
        </div>
    </body>
    </html>
<?php 
    exit; 
} // Close Session Check Block Context

// Process administrative update requests
$toast_notification = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crud_action_token'])) {
    $token = $_POST['crud_action_token'];
    
    if ($token === 'new_artist') {
        $name = trim($_POST['art_name']);
        $file_reference = 'default_artist.jpg';
        if (!empty($_FILES['art_img']['name'])) {
            $file_reference = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES['art_img']['name']);
            move_uploaded_file($_FILES['art_img']['tmp_name'], 'uploads/' . $file_reference);
        }
        $stmt = $pdo->prepare("INSERT INTO artists (name, image) VALUES (?, ?)");
        $stmt->execute([$name, $file_reference]);
        $toast_notification = "Profile Registry Updated: Onboarded artist metadata structure.";
    }
    
    if ($token === 'new_event') {
        $art_id = (int)$_POST['ev_artist'];
        $title = trim($_POST['ev_title']);
        $venue = trim($_POST['ev_venue']);
        $date = $_POST['ev_date'];
        $map_reference = 'default_map.jpg';
        
        if (!empty($_FILES['ev_map']['name'])) {
            $map_reference = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES['ev_map']['name']);
            move_uploaded_file($_FILES['ev_map']['tmp_name'], 'uploads/' . $map_reference);
        }
        $stmt = $pdo->prepare("INSERT INTO events (artist_id, title, venue, event_date, stadium_map) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$art_id, $title, $venue, $date, $map_reference]);
        $toast_notification = "Show Published: Live schedule matrix expanded.";
    }
    
    if ($token === 'new_allocation') {
        $ev_id = (int)$_POST['alloc_event'];
        $sec_name = trim($_POST['alloc_name']);
        $schema_type = $_POST['alloc_type'];
        
        if ($schema_type == '1') {
            $ga_val = floatval($_POST['ga_price_field']);
            $ga_pool = (int)$_POST['ga_qty_field'];
            $stmt = $pdo->prepare("INSERT INTO sections (event_id, section_name, is_ga, ga_price, ga_available_tickets) VALUES (?, ?, 1, ?, ?)");
            $stmt->execute([$ev_id, $sec_name, $ga_val, $ga_pool]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO sections (event_id, section_name, is_ga) VALUES (?, ?, 0)");
            $stmt->execute([$ev_id, $sec_name]);
            $section_scope_id = $pdo->lastInsertId();
            
            $row_tag = trim($_POST['row_tag_field']);
            $range_start = (int)$_POST['seat_start_num'];
            $range_end = (int)$_POST['seat_end_num'];
            $seat_val = floatval($_POST['seat_price_field']);
            
            for ($x = $range_start; $x <= $range_end; $x++) {
                $seatInsert = $pdo->prepare("INSERT INTO seats (section_id, row_name, seat_number, price) VALUES (?, ?, ?, ?)");
                $seatInsert->execute([$section_scope_id, $row_tag, $x, $seat_val]);
            }
        }
        $toast_notification = "Inventory Matrix Generated: Allocation units locked to tracking logs.";
    }

    if ($token === 'update_user_credentials') {
        $target_user_id = (int)$_POST['user_target_id'];
        $updated_username = trim($_POST['updated_username']);
        $updated_email = trim($_POST['updated_email']);
        $updated_role = $_POST['updated_role'];
        
        if (!empty($_POST['updated_password'])) {
            $hashed_password = password_hash(trim($_POST['updated_password']), PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$updated_username, $updated_email, $hashed_password, $updated_role, $target_user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$updated_username, $updated_email, $updated_role, $target_user_id]);
        }
        $toast_notification = "User Record Updated: System credentials updated.";
    }

    if ($token === 'resolve_transfer') {
        $transfer_id = (int)$_POST['transfer_scope_id'];
        $resolution = $_POST['status_resolution'];
        $stmt = $pdo->prepare("UPDATE ticket_transfers SET status = ? WHERE id = ?");
        $stmt->execute([$resolution, $transfer_id]);
        $toast_notification = "Market Transfer Resolved: Request state changed to $resolution.";
    }
}

// Global Ledger Data Pull Loops
$artists_ledger = $pdo->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
$events_ledger = $pdo->query("SELECT e.*, a.name AS artist_name FROM events e JOIN artists a ON e.artist_id = a.id ORDER BY e.event_date DESC")->fetchAll();
$users_ledger = $pdo->query("SELECT id, username, email, role FROM users ORDER BY id ASC")->fetchAll();
$transfers_ledger = $pdo->query("SELECT t.*, u.username FROM ticket_transfers t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Administrative Interface Node</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col md:flex-row min-h-screen font-sans">

    <div class="w-full md:w-64 bg-slate-900 text-white flex flex-col justify-between p-6 shrink-0 shadow-2xl">
        <div>
            <div class="flex items-center gap-3 border-b border-slate-800 pb-4 mb-6">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <div>
                    <h2 class="font-black tracking-tight text-sm uppercase">PookaAlex Operator</h2>
                    <p class="text-[10px] text-gray-400 font-mono">Console active state</p>
                </div>
            </div>
            <nav class="space-y-1">
                <a href="#artists-panel" class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-lg bg-slate-800 text-white transition-all"><i class="fas fa-users w-4"></i> Artists & Programs</a>
                <a href="#inventory-panel" class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-lg text-gray-400 hover:bg-slate-800 hover:text-white transition-all"><i class="fas fa-layer-group w-4"></i> Seat Allocator</a>
                <a href="#users-panel" class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-lg text-gray-400 hover:bg-slate-800 hover:text-white transition-all"><i class="fas fa-user-shield w-4"></i> System Users</a>
                <a href="#transfers-panel" class="flex items-center gap-3 text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-lg text-gray-400 hover:bg-slate-800 hover:text-white transition-all"><i class="fas fa-exchange-alt w-4"></i> Marketplace Log</a>
            </nav>
        </div>
        <div class="border-t border-slate-800 pt-4 mt-6">
            <a href="?terminate_session=1" class="flex items-center justify-center gap-2 w-full bg-red-900 hover:bg-red-800 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-colors"><i class="fas fa-power-off"></i> Exit Console</a>
        </div>
    </div>

    <div class="flex-1 p-6 md:p-10 max-h-screen overflow-y-auto">
        <?php if(!empty($toast_notification)): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded-r-xl shadow-sm font-semibold text-sm mb-8 flex items-center gap-3"><i class="fas fa-check-circle text-green-500 text-lg"></i> <?= $toast_notification; ?></div>
        <?php endif; ?>

        <div class="mb-10">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Administrative Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Configure user lists, allocate seating grids, set pricing variables, and manage active market transfers.</p>
        </div>

        <div id="artists-panel" class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6">
                <div class="border-b border-gray-100 pb-3 mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Onboard Artist Profile</h3>
                    <p class="text-xs text-gray-400">Initialize a new global artist profile context framework.</p>
                </div>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="crud_action_token" value="new_artist">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Artist / Performer Name:</label>
                        <input type="text" name="art_name" required placeholder="E.g., Billie Eilish, Coldplay" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Profile Photo Upload Asset:</label>
                        <input type="file" name="art_img" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl transition-all shadow">Save Profile</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6">
                <div class="border-b border-gray-100 pb-3 mb-6">
                    <h3 class="text-lg font-bold text-slate-900">Schedule Multiple Event Presentation Runs</h3>
                    <p class="text-xs text-gray-400">Link multiple distinct venue dates to a registered artist profile template context.</p>
                </div>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="crud_action_token" value="new_event">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Target Artist Context Profile:</label>
                            <select name="ev_artist" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600 bg-white font-medium">
                                <?php foreach($artists_ledger as $art): ?>
                                    <option value="<?= $art['id']; ?>"><?= htmlspecialchars($art['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Tour / Presentation Title Name:</label>
                            <input type="text" name="ev_title" required placeholder="E.g., Next Generation Tour Run" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Stadium / Arena Venue Placement:</label>
                            <input type="text" name="ev_venue" required placeholder="E.g., Wembley Arena, Madison Square Garden" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Event Timestamp Configuration:</label>
                            <input type="datetime-local" name="ev_date" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Stadium View Image / Seating Plan Blueprint Map File:</label>
                        <input type="file" name="ev_map" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl transition-all shadow">Publish Live Show Instance</button>
                </form>
            </div>
        </div>

        <div id="inventory-panel" class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 mb-12">
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-lg font-bold text-slate-900">Setup Stadium Sector Allocation Scheme & Matrix Prices</h3>
                <p class="text-xs text-gray-400">Generate pricing matrices and seating grids for published show dates.</p>
            </div>
            <form method="POST" class="space-y-6">
                <input type="hidden" name="crud_action_token" value="new_allocation">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Target Event Execution Context Scope:</label>
                        <select name="alloc_event" required class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600 bg-white font-medium">
                            <?php foreach($events_ledger as $ev): ?>
                                <option value="<?= $ev['id']; ?>"><?= htmlspecialchars($ev['artist_name']); ?> - <?= htmlspecialchars($ev['title']); ?> (<?= htmlspecialchars($ev['venue']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Section Identifier Tag Label:</label>
                        <input type="text" name="alloc_name" required placeholder="E.g., Section A, West GA Stand Block" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Allocation Setup Scheme Mode:</label>
                        <select name="alloc_type" id="schema_view_selector" onchange="toggleFormSchemeView(this.value)" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none focus:border-blue-600 bg-white font-medium">
                            <option value="0">Assigned Numbered Seats Row Grid</option>
                            <option value="1">General Admission (Open Floor / Hover Price)</option>
                        </select>
                    </div>
                </div>

                <div id="ga_alloc_view_fields" class="hidden border-l-4 border-blue-500 bg-blue-50/50 p-5 rounded-r-xl grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-blue-800 mb-1">General Admission Ticket Target Price ($):</label>
                        <input type="number" name="ga_price_field" step="0.01" placeholder="0.00" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-blue-800 mb-1">Total Available Capacity Unit Pool:</label>
                        <input type="number" name="ga_qty_field" placeholder="E.g., 500" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                </div>

                <div id="row_alloc_view_fields" class="border-l-4 border-green-500 bg-green-50/50 p-5 rounded-r-xl grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-green-800 mb-1">Row Block Label:</label>
                        <input type="text" name="row_tag_field" placeholder="E.g., Row A" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-green-800 mb-1">Seat Loop Initial Integer #:</label>
                        <input type="number" name="seat_start_num" placeholder="1" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-green-800 mb-1">Seat Loop Terminal Integer #:</label>
                        <input type="number" name="seat_end_num" placeholder="20" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-green-800 mb-1">Individual Grid Value ($):</label>
                        <input type="number" name="seat_price_field" step="0.01" placeholder="0.00" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-white">
                    </div>
                </div>

                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl transition-all shadow">Run Dynamic Matrix Allocation Generator</button>
            </form>
        </div>

        <div id="users-panel" class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 mb-12">
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-lg font-bold text-slate-900">Registered System Profile Accounts Ledger</h3>
                <p class="text-xs text-gray-400">Review and modify registered user records and administrative credentials.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-slate-400 uppercase bg-slate-50 font-bold">
                        <tr>
                            <th class="p-4">User ID</th>
                            <th class="p-4">Username</th>
                            <th class="p-4">Email Address Reference</th>
                            <th class="p-4">Authorization Role</th>
                            <th class="p-4 text-center">Record Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-slate-700">
                        <?php foreach($users_ledger as $u): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-4 font-mono text-xs">#<?= $u['id']; ?></td>
                                <td class="p-4 font-bold text-slate-900"><?= htmlspecialchars($u['username']); ?></td>
                                <td class="p-4 text-xs"><?= htmlspecialchars($u['email']); ?></td>
                                <td class="p-4">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded <?= ($u['role'] === 'admin') ? 'bg-purple-100 text-purple-800':'bg-blue-100 text-blue-800'; ?>">
                                        <?= $u['role']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick="openUserEditModal(<?= htmlspecialchars(json_encode($u)); ?>)" class="text-xs text-blue-600 hover:text-blue-900 font-bold bg-blue-50 px-3 py-1 rounded-md transition-colors">Modify Profile</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="transfers-panel" class="bg-white rounded-2xl border border-gray-100 shadow-md p-6">
            <div class="border-b border-gray-100 pb-3 mb-6">
                <h3 class="text-lg font-bold text-slate-900">Secondary Market Transfer Approvals Desk</h3>
                <p class="text-xs text-gray-400">Inspect uploaded document assets and description metadata metrics to issue systematic confirmations.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-slate-400 uppercase bg-slate-50 font-bold">
                        <tr>
                            <th class="p-4">User Profile</th>
                            <th class="p-4">Reference File Proof Asset</th>
                            <th class="p-4">Description Ledger Entry Notes</th>
                            <th class="p-4 text-center">Current State</th>
                            <th class="p-4 text-right">Verification Action Triggers</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-slate-700 font-medium">
                        <?php if(empty($transfers_ledger)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-xs text-gray-400">No external secondary market listings recorded inside active buffer files.</td></tr>
                        <?php else: ?>
                            <?php foreach($transfers_ledger as $t_log): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-4 font-bold text-slate-900 text-xs"><?= htmlspecialchars($t_log['username']); ?></td>
                                    <td class="p-4 text-xs text-blue-600 hover:underline">
                                        <a href="uploads/transfers/<?= htmlspecialchars($t_log['ticket_file']); ?>" target="_blank"><i class="fas fa-external-link-alt"></i> View Reference Attachment</a>
                                    </td>
                                    <td class="p-4 text-xs max-w-xs truncate" title="<?= htmlspecialchars($t_log['description']); ?>"><?= htmlspecialchars($t_log['description']); ?></td>
                                    <td class="p-4 text-center">
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded <?= ($t_log['status'] === 'approved') ? 'bg-green-100 text-green-800' : (($t_log['status'] === 'rejected') ? 'bg-red-100 text-red-800':'bg-yellow-100 text-yellow-800'); ?>">
                                            <?= $t_log['status']; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <form method="POST" class="inline-flex items-center gap-1">
                                            <input type="hidden" name="crud_action_token" value="resolve_transfer">
                                            <input type="hidden" name="transfer_scope_id" value="<?= $t_log['id']; ?>">
                                            <select name="status_resolution" class="border border-gray-200 text-xs rounded p-1 outline-none font-semibold bg-white">
                                                <option value="approved">Approve</option>
                                                <option value="rejected">Reject</option>
                                            </select>
                                            <button type="submit" class="bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider py-1.5 px-2.5 rounded hover:bg-slate-800 transition-colors">Confirm</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="user-credential-modal-frame" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden p-6 border border-gray-100 animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-lg font-black text-slate-900">Update User System Ledger Account</h3>
                <button onclick="closeUserEditModal()" class="text-gray-400 hover:text-slate-900"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="crud_action_token" value="update_user_credentials">
                <input type="hidden" name="user_target_id" id="modal_user_id">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Username Reference:</label>
                    <input type="text" name="updated_username" id="modal_username" required class="w-full border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-600 font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Email Address Token:</label>
                    <input type="email" name="updated_email" id="modal_email" required class="w-full border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-600 font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Assign Credentials Password (Leave blank to keep unchanged):</label>
                    <input type="password" name="updated_password" placeholder="••••••••" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-600 font-medium bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Platform Security Clearance Level Role:</label>
                    <select name="updated_role" id="modal_role" class="w-full border border-gray-200 rounded-xl p-2.5 text-sm outline-none focus:border-blue-600 bg-white font-semibold">
                        <option value="user">USER (Standard Client Mapping)</option>
                        <option value="admin">ADMIN (Console Supervisor Clearances)</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider py-3 px-4 rounded-xl shadow transition-colors">Apply Ledger Updates</button>
            </form>
        </div>
    </div>

    <script>
    function toggleFormSchemeView(val) {
        if(val === '1') {
            document.getElementById('ga_alloc_view_fields').classList.remove('hidden');
            document.getElementById('row_alloc_view_fields').classList.add('hidden');
        } else {
            document.getElementById('ga_alloc_view_fields').classList.add('hidden');
            document.getElementById('row_alloc_view_fields').classList.remove('hidden');
        }
    }
    
    function openUserEditModal(userObj) {
        document.getElementById('modal_user_id').value = userObj.id;
        document.getElementById('modal_username').value = userObj.username;
        document.getElementById('modal_email').value = userObj.email;
        document.getElementById('modal_role').value = userObj.role;
        
        const modal = document.getElementById('user-credential-modal-frame');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeUserEditModal() {
        const modal = document.getElementById('user-credential-modal-frame');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    </script>
</body>
</html>
<?php
// Handle simple administrative logout sessions cleanly
if (isset($_GET['terminate_session'])) {
    unset($_SESSION['admin_session_active']);
    unset($_SESSION['admin_user_string']);
    header("Location: admin.php");
    exit;
}
?>
