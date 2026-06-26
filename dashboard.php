<?php
include "inc/header.php";
include "inc/navbar.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* CONNECT DB */
if (!isset($conn)) {
    require_once "config/database.php";
    $db = new Database();
    $conn = $db->connect();
}

// FETCH USER
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// TOTAL EARNINGS
$earn = $conn->prepare("SELECT SUM(amount) as total FROM earnings WHERE user_id=?");
$earn->execute([$user_id]);
$totalEarned = $earn->fetch()['total'] ?? 0;

// TOTAL WITHDRAWN (approved only)
$with = $conn->prepare("SELECT SUM(amount) as total FROM withdrawals WHERE user_id=? AND status=1");
$with->execute([$user_id]);
$totalWithdrawn = $with->fetch()['total'] ?? 0;

$balance = $user['balance'] ?? 0;

// Fetch minimum withdrawal from region_settings
$minStmt = $conn->prepare("SELECT min_wdr FROM region_settings LIMIT 1");
$minStmt->execute();
$minWithdrawal = $minStmt->fetchColumn() ?? 10.00;

// EARNINGS HISTORY
$historyStmt = $conn->prepare("SELECT * FROM earnings WHERE user_id=? ORDER BY id DESC LIMIT 10");
$historyStmt->execute([$user_id]);
$earningsHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

// WITHDRAWALS HISTORY
$wdStmt = $conn->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY id DESC LIMIT 10");
$wdStmt->execute([$user_id]);
$withdrawals = $wdStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Mobile-First Design */
* { box-sizing: border-box; }

.container {
    max-width: 1200px;
    margin: auto;
    padding: 15px;
}

h1 {
    font-size: 32px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* SUMMARY CARDS */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}
.card {
    background: #fff;
    padding: 24px 20px;
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.07);
    text-align: center;
}
.card h3 {
    color: #666;
    font-size: 15px;
    margin-bottom: 8px;
    font-weight: 500;
}
.card p {
    font-size: 26px;
    font-weight: bold;
    margin: 0;
    color: #1e40af;
}

/* WITHDRAWAL BOX */
.withdrawal-box {
    background: #fff;
    padding: 30px 25px;
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.07);
    margin-bottom: 45px;
}
.withdrawal-box h2 {
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.withdrawal-box p {
    color: #555;
    margin-bottom: 25px;
    font-size: 15.5px;
}

.form-group {
    margin-bottom: 22px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #374151;
}
.form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 16px;
}
.form-group input:focus {
    outline: none;
    border-color: #00aaff;
    box-shadow: 0 0 0 4px rgba(0,170,255,0.12);
}

.btn {
    width: 100%;
    background: #00aaff;
    color: #fff;
    padding: 16px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 17px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
}
.btn:hover {
    background: #0088cc;
}

/* TABLES */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 25px rgba(0,0,0,0.07);
    margin-bottom: 45px;
}
table th, table td {
    padding: 16px 18px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
table th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 15px;
}

/* STATUS BADGES */
.status {
    padding: 7px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
}
.status-0 { background: #fef3c7; color: #d97706; }   /* Yellow - Pending */
.status-1 { background: #d1fae5; color: #10b981; }   /* Green  - Approved */
.status-2 { background: #fee2e2; color: #ef4444; }   /* Red    - Rejected */

.icon-blue  { color: #00aaff; }
.icon-green { color: #22c55e; }

/* Mobile Optimizations */
@media (max-width: 768px) {
    h1 {
        font-size: 28px;
    }
    .cards {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .card p {
        font-size: 24px;
    }
    table th, table td {
        padding: 14px 12px;
        font-size: 14.5px;
    }
    .withdrawal-box {
        padding: 25px 20px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 12px;
    }
    .btn {
        padding: 17px;
        font-size: 16.5px;
    }
    .card {
        padding: 22px 18px;
    }
}
</style>

<div class="container">

    <h1><i class="fa-solid fa-chart-line icon-blue"></i> My Dashboard</h1>

    <!-- SUMMARY CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Available Balance</h3>
            <p>$<?php echo number_format($balance, 2); ?></p>
        </div>
        <div class="card">
            <h3>Total Earned</h3>
            <p>$<?php echo number_format($totalEarned, 2); ?></p>
        </div>
        <div class="card">
            <h3>Total Withdrawn</h3>
            <p>$<?php echo number_format($totalWithdrawn, 2); ?></p>
        </div>
    </div>

    <!-- WITHDRAWAL SECTION -->
    <div class="withdrawal-box">
        <h2><i class="fa-solid fa-wallet icon-green"></i> Request Withdrawal</h2>
        <p>
            Minimum withdrawal: <strong>$<?php echo number_format($minWithdrawal, 2); ?></strong><br>
            Processed within <strong>15 minutes</strong>
        </p>

        <form method="POST" action="withdrawals.php">
            <div class="form-group">
                <label for="amount">Withdrawal Amount (USD)</label>
                <input type="number" id="amount" name="amount"
                       step="0.01"
                       min="<?php echo $minWithdrawal; ?>"
                       placeholder="Enter amount (min $<?php echo number_format($minWithdrawal, 2); ?>)"
                       required>
            </div>

            <button type="submit" class="btn">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Request Withdrawal
            </button>
        </form>
    </div>

    <!-- EARNINGS HISTORY -->
    <h2><i class="fa-solid fa-clock icon-blue"></i> Recent Earnings</h2>
    <table>
        <tr><th>Game</th><th>Amount</th><th>Date</th></tr>
        <?php if (count($earningsHistory) > 0): ?>
            <?php foreach ($earningsHistory as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['game'] ?? 'Game Play'); ?></td>
                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center;padding:40px 20px;">No earnings yet. Start playing to earn!</td></tr>
        <?php endif; ?>
    </table>

    <!-- WITHDRAWALS HISTORY -->
    <h2><i class="fa-solid fa-money-bill-transfer icon-blue"></i> Withdrawal History</h2>
    <table>
        <tr><th>Amount</th><th>Status</th><th>Date</th></tr>
        <?php if (count($withdrawals) > 0): ?>
            <?php foreach ($withdrawals as $w):
                $statusClass = 'status-' . $w['status'];
                $statusText = match((int)$w['status']) {
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected',
                    default => 'Unknown'
                };
            ?>
            <tr>
                <td>$<?php echo number_format($w['amount'], 2); ?></td>
                <td><span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                <td><?php echo date('M d, Y H:i', strtotime($w['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3" style="text-align:center;padding:40px 20px;">No withdrawals yet.</td></tr>
        <?php endif; ?>
    </table>

</div>

<?php include "inc/footer.php"; ?>
