<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/inc/header.php';

$message = '';
$error   = '';

/* --------------------------------------------------
   FETCH ALL DEPOSITS WITH USER & METHOD DETAILS
-------------------------------------------------- */
try {
    $stmt = $pdo->query("
        SELECT 
            d.*, 
            u.id as user_uid, 
            u.country,
            pm.image_path as method_logo
        FROM deposits d
        LEFT JOIN users u ON d.user_id = u.id
        LEFT JOIN payment_methods pm ON d.payment_id = pm.payment_id
        ORDER BY d.deposit_id DESC
    ");
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $deposits = [];
}

/* --------------------------------------------------
   HANDLE ACTIONS
-------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {

    $action = $_POST['action'] ?? '';

    try {

        /* ---------------- UPDATE STATUS (APPROVE/DECLINE) ---------------- */
        if ($action === 'update_status') {
            $id = (int)($_POST['id'] ?? 0);
            $new_status = $_POST['status'] ?? '';

            if ($id <= 0 || !in_array($new_status, ['approved', 'declined', 'pending'])) {
                throw new Exception("Invalid parameters provided.");
            }

            $stmt = $pdo->prepare("UPDATE deposits SET status = ? WHERE deposit_id = ?");
            $stmt->execute([$new_status, $id]);

            $_SESSION['success'] = "Deposit payment reference status changed to " . ucfirst($new_status) . ".";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        /* ---------------- DELETE DEPOSIT RECORD ---------------- */
        if ($action === 'delete') {

            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid deposit entry ID.");
            }

            $stmt = $pdo->prepare("DELETE FROM deposits WHERE deposit_id = ?");
            $stmt->execute([$id]);

            $_SESSION['success'] = "Deposit reference history log deleted successfully.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<style>
.order-map-link {
    color: #60a5fa; 
    text-decoration: underline; 
    cursor: pointer;
    font-weight: bold;
}
.order-map-link:hover {
    color: #93c5fd;
}

/* Modal UI Window styling overlay configurations */
.details-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s ease;
    padding: 20px;
}
.details-modal-overlay.active {
    opacity: 1;
    pointer-events: auto;
}
.details-modal-card {
    background: #0d1117;
    border: 1px solid #30363d;
    border-radius: 12px;
    width: 100%;
    max-width: 680px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 2rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6);
    color: #c9d1d9;
    position: relative;
    transform: translateY(-15px);
    transition: transform 0.25s ease;
}
.details-modal-overlay.active .details-modal-card {
    transform: translateY(0);
}
.modal-close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    background: transparent;
    border: none;
    color: #8b949e;
    font-size: 26px;
    cursor: pointer;
}
.modal-close-btn:hover {
    color: #ffffff;
}
.modal-title {
    margin-top: 0;
    border-bottom: 1px solid #30363d;
    padding-bottom: 12px;
    font-size: 1.4rem;
    color: #f0f6fc;
}
.modal-section-header {
    margin: 20px 0 10px 0;
    color: #8b949e;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 1.2px;
    font-weight: bold;
    border-left: 3px solid #58a6ff;
    padding-left: 8px;
}
.info-grid {
    background: #161b22;
    padding: 14px;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.6;
    border: 1px solid #21262d;
    margin-bottom: 15px;
}
.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    border-bottom: 1px dashed #21262d;
    padding-bottom: 4px;
}
.info-row:last-child {
    margin-bottom: 0;
    border-bottom: none;
    padding-bottom: 0;
}
.info-label {
    color: #8b949e;
}
.info-value {
    font-weight: 600;
    color: #c9d1d9;
}
.ticket-basket-card {
    background: #161b22;
    border: 1px solid #30363d;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}
.artist-flex {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #0d1117;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 12px;
    border: 1px solid #21262d;
}
.artist-avatar {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    background: #21262d;
    border: 1px solid #30363d;
}
</style>

<main>

<h1 style="text-align:center; margin:2rem 0;">Manage Client Deposits</h1>

<?php if (!empty($_SESSION['success'])): ?>
<div style="background:#238636;color:#fff;padding:1rem;border-radius:8px;text-align:center;max-width:1100px;margin:1rem auto;">
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div style="background:#f85149;color:#fff;padding:1rem;border-radius:8px;text-align:center;max-width:1100px;margin:1rem auto;">
    <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div style="max-width:1100px;margin:0 auto 1.5rem auto;display:flex;gap:15px;justify-content:center;flex-wrap:wrap;">
    <span style="background:#1f2937;padding:8px 16px;border-radius:20px;font-size:13px;border:1px solid #374151;">
        Total Ledger Records: <strong><?= count($deposits) ?></strong>
    </span>
</div>

<div style="max-width:1140px;margin:0 auto;overflow-x:auto;padding:0 10px;">

<table style="width:100%;border-collapse:collapse;background:var(--card);border:1px solid var(--border);border-radius:10px;min-width:1000px;font-size:14px;">

<thead>
<tr style="text-align:left;background:#111827;">
    <th style="padding:12px;">ID</th>
    <th style="padding:12px;">Client ID</th>
    <th style="padding:12px;">Order Maps</th>
    <th style="padding:12px;">Amount Due</th>
    <th style="padding:12px;">Gateway Type</th>
    <th style="padding:12px;">Submitted Payload</th>
    <th style="padding:12px;">Proof File</th>
    <th style="padding:12px;">Status</th>
    <th style="padding:12px;text-align:right;">Actions</th>
</tr>
</thead>

<tbody>

<?php if (empty($deposits)): ?>
<tr>
    <td colspan="9" style="padding:2rem;text-align:center;color:#888;">No deposit transactions found in database logs.</td>
</tr>
<?php endif; ?>

<?php foreach ($deposits as $deposit): ?>
<tr style="border-top:1px solid var(--border); vertical-align: middle;">

    <td style="padding:12px;font-family:monospace;font-weight:bold;">
        #DEP-<?= $deposit['deposit_id'] ?>
    </td>

    <td style="padding:12px;">
        <span style="display:block;font-weight:600;">UID: <?= htmlspecialchars($deposit['user_id'] ?? 'N/A') ?></span>
        <small style="color:#aaa;font-size:11px;text-transform:uppercase;"><?= htmlspecialchars($deposit['country'] ?? 'Unknown Reg') ?></small>
    </td>

    <td style="padding:12px;font-family:monospace;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
        <?php if (!empty($deposit['order_ids'])): ?>
            <span class="order-map-link trigger-map-lookup" data-ids="<?= htmlspecialchars($deposit['order_ids']) ?>">
                <?= htmlspecialchars($deposit['order_ids']) ?>
            </span>
        <?php else: ?>
            <span style="color:#666;">N/A</span>
        <?php endif; ?>
    </td>

    <td style="padding:12px;font-weight:bold;color:#34d399;">
        <?= htmlspecialchars($deposit['currency']) ?> <?= number_format($deposit['amount'], 2) ?>
    </td>

    <td style="padding:12px;">
        <div style="display:flex;align-items:center;gap:8px;">
            <?php if (!empty($deposit['method_logo'])): ?>
                <img src="../uploads/payment-methods/<?= htmlspecialchars($deposit['method_logo']) ?>" 
                     style="width:28px;height:28px;object-fit:contain;background:#fff;padding:2px;border-radius:4px;">
            <?php endif; ?>
            <span style="font-size:12px;text-transform:uppercase;background:#374151;padding:2px 6px;border-radius:4px;font-weight:bold;">
                <?= str_replace('_', ' ', $deposit['payment_type']) ?>
            </span>
        </div>
    </td>

    <td style="padding:12px;font-size:12px;max-width:220px;word-break:break-all;">
        <?php 
            $payload = json_decode($deposit['submitted_details'], true);
            if(is_array($payload)){
                foreach($payload as $key => $val){
                    if(!empty($val)){
                        echo "<strong>".htmlspecialchars(str_replace('_',' ',$key)).":</strong> ".htmlspecialchars($val)."<br>";
                    }
                }
            } else {
                echo '<span style="color:#666;">Raw: '.htmlspecialchars($deposit['submitted_details']).'</span>';
            }
        ?>
    </td>

    <td style="padding:12px;">
        <?php if (!empty($deposit['proof_file'])): ?>
            <a href="../<?= htmlspecialchars($deposit['proof_file']) ?>" target="_blank" 
               style="background:#1f2937;color:#60a5fa;border:1px solid #3b82f6;padding:4px 8px;border-radius:6px;text-decoration:none;font-size:11px;font-weight:bold;display:inline-flex;align-items:center;gap:3px;">
                <i class="fas fa-file-invoice"></i> View Proof
            </a>
        <?php else: ?>
            <span style="color:#f85149;font-size:12px;">Missing Document</span>
        <?php endif; ?>
    </td>

    <td style="padding:12px;">
        <?php 
            $status = $deposit['status'];
            $bg = '#1f2937'; $co = '#9ca3af';
            if($status === 'approved') { $bg = '#14532d'; $co = '#4ade80'; }
            if($status === 'declined') { $bg = '#7f1d1d'; $co = '#f87171'; }
            if($status === 'pending')  { $bg = '#7c2d12'; $co = '#fb923c'; }
        ?>
        <span style="background:<?= $bg ?>;color:<?= $co ?>;padding:4px 8px;border-radius:6px;font-weight:bold;text-transform:uppercase;font-size:11px;letter-spacing:0.5px;">
            <?= $status ?>
        </span>
    </td>

    <td style="padding:12px;white-space:nowrap;text-align:right;">

        <?php if($status === 'pending'): ?>
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="id" value="<?= $deposit['deposit_id'] ?>">
                <button class="btn green" style="padding:5px 8px;font-size:12px;font-weight:bold;cursor:pointer;">
                    Approve
                </button>
            </form>

            <form method="POST" style="display:inline-block;margin-left:3px;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="declined">
                <input type="hidden" name="id" value="<?= $deposit['deposit_id'] ?>">
                <button class="btn red" style="padding:5px 8px;font-size:12px;font-weight:bold;background:#b91c1c;cursor:pointer;">
                    Decline
                </button>
            </form>
        <?php else: ?>
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="pending">
                <input type="hidden" name="id" value="<?= $deposit['deposit_id'] ?>">
                <button class="btn" style="padding:4px 8px;font-size:11px;background:#4b5563;color:#e5e7eb;cursor:pointer;">
                    Reset to Pending
                </button>
            </form>
        <?php endif; ?>

        <form method="POST"
              onsubmit="return confirm('CRITICAL WARN: Are you sure you want to permanently purge this financial tracking ledger record? This step cannot be reversed.');"
              style="display:inline-block;margin-left:6px;">

            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $deposit['deposit_id'] ?>">

            <button class="btn red" style="padding:5px 8px;font-size:12px;background:#310000;border:1px solid #f85149;color:#f85149;cursor:pointer;">
                <i class="fas fa-trash-alt"></i>
            </button>

        </form>

    </td>

</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>

</main>

<div id="mappingDetailsModal" class="details-modal-overlay">
    <div class="details-modal-card">
        <button type="button" class="modal-close-btn" id="closeMappingModalBtn">&times;</button>
        <h3 class="modal-title">Deposit Mappings Ledger</h3>
        
        <div class="modal-section-header">Client Identity Details</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value" id="userFullNameField">Loading data...</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email Address:</span>
                <span class="info-value" id="userEmailField">Loading data...</span>
            </div>
            <div class="info-row">
                <span class="info-label">Country Jurisdiction:</span>
                <span class="info-value" id="userCountryField">Loading data...</span>
            </div>
        </div>

        <div class="modal-section-header">Associated Order Package Basket</div>
        <div id="modalItemsDynamicContainer">
            </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('mappingDetailsModal');
    const closeBtn = document.getElementById('closeMappingModalBtn');
    
    const uName = document.getElementById('userFullNameField');
    const uEmail = document.getElementById('userEmailField');
    const uCountry = document.getElementById('userCountryField');
    const itemsContainer = document.getElementById('modalItemsDynamicContainer');

    document.querySelectorAll('.trigger-map-lookup').forEach(element => {
        element.addEventListener('click', function() {
            const rawOrderIds = this.getAttribute('data-ids');
            
            // Set loading indicators
            uName.textContent = 'Fetching connection nodes...';
            uEmail.textContent = 'Fetching connection nodes...';
            uCountry.textContent = 'Fetching connection nodes...';
            itemsContainer.innerHTML = '<div style="padding:15px;color:#8b949e;text-align:center;">Loading basket contents...</div>';
            
            modal.classList.add('active');
            
            // Execute fetching operations to endpoint passing absolute grouped IDs
            fetch(`get_order_details.php?order_ids=${encodeURIComponent(rawOrderIds)}`)
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        // 1. Render user card metadata details at top
                        uName.textContent = response.user.full_name;
                        uEmail.textContent = response.user.email;
                        uCountry.textContent = response.user.country.toUpperCase();
                        
                        // 2. Loop through and render structural blocks containing Artist, Concert, and Ticket details below
                        itemsContainer.innerHTML = '';
                        response.items.forEach(item => {
                            let imgTag = item.artist.image 
                                ? `<img src="../uploads/artists/${item.artist.image}" class="artist-avatar" alt="">` 
                                : `<div class="artist-avatar" style="display:flex;align-items:center;justify-content:center;font-size:10px;color:#8b949e;background:#21262d;">NO IMG</div>`;

                            let cardHTML = `
                                <div class="ticket-basket-card">
                                    <div class="artist-flex">
                                        ${imgTag}
                                        <div>
                                            <div style="font-size:11px;color:#8b949e;text-transform:uppercase;">Performing Artist</div>
                                            <div style="font-size:16px;font-weight:bold;color:#f0f6fc;">${item.artist.name}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row"><span class="info-label">Concert Title:</span><span class="info-value">${item.concert.title}</span></div>
                                    <div class="info-row"><span class="info-label">Event Schedule:</span><span class="info-value">${item.concert.date} @ ${item.concert.time}</span></div>
                                    <div class="info-row"><span class="info-label">Venue / Arena:</span><span class="info-value">${item.concert.venue} (${item.concert.location})</span></div>
                                    
                                    <div style="margin-top:10px; padding-top:10px; border-top:1px solid #21262d;">
                                        <div class="info-row"><span class="info-label">Ticket Reference:</span><span class="info-value" style="font-family:monospace;color:#58a6ff;">#TKT-${item.ticket_id}</span></div>
                                        <div class="info-row"><span class="info-label">Tier Designation:</span><span class="info-value">${item.ticket_name}</span></div>
                                        <div class="info-row"><span class="info-label">Section Map Location:</span><span class="info-value">Sec ${item.section_name}, Row ${item.row_name}</span></div>
                                        <div class="info-row"><span class="info-label">Cost Value:</span><span class="info-value" style="color:#58a6ff;font-weight:bold;">$${parseFloat(item.price).toFixed(2)}</span></div>
                                    </div>
                                </div>
                            `;
                            itemsContainer.innerHTML += cardHTML;
                        });
                    } else {
                        uName.textContent = 'Error occurred';
                        uEmail.textContent = response.message;
                        uCountry.textContent = 'N/A';
                        itemsContainer.innerHTML = `<div style="padding:15px;color:#f85149;text-align:center;">${response.message}</div>`;
                    }
                })
                .catch(err => {
                    uName.textContent = 'Network communication error';
                    uEmail.textContent = err.message;
                    itemsContainer.innerHTML = `<div style="padding:15px;color:#f85149;text-align:center;">Request failed. Check connections.</div>`;
                });
        });
    });

    closeBtn.addEventListener('click', () => modal.classList.remove('active'));
    window.addEventListener('click', (e) => { if(e.target === modal) modal.classList.remove('active'); });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
