<?php
// dashboard.php
session_start();
require_once 'db.php';

// Safe sandbox fallback to preserve developer integration context
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'BuyerProfileSandbox';
}

$user_id = $_SESSION['user_id'];
$status_banner = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['transfer_document'])) {
    $notes = trim($_POST['transfer_notes']);
    $directory_target = "uploads/transfers/";
    
    if (!file_exists($directory_target)) {
        mkdir($directory_target, 0777, true);
    }
    
    $clean_filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["transfer_document"]["name"]));
    $full_path_destination = $directory_target . $clean_filename;
    $file_extension = strtolower(pathinfo($full_path_destination, PATHINFO_EXTENSION));
    
    if (in_array($file_extension, ['pdf', 'png', 'jpg', 'jpeg'])) {
        if (move_uploaded_file($_FILES["transfer_document"]["tmp_name"], $full_path_destination)) {
            $stmt = $pdo->prepare("INSERT INTO ticket_transfers (user_id, ticket_file, description) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $clean_filename, $notes]);
            $status_banner = "<div class='bg-green-100 border border-green-400 text-green-700 p-4 rounded-xl mb-6 font-semibold text-sm'>Vault asset uploaded successfully. Administrator inspection sequence scheduled.</div>";
        } else {
            $status_banner = "<div class='bg-red-100 border border-red-400 text-red-700 p-4 rounded-xl mb-6 font-semibold text-sm'>Execution block exception error: Failed moving asset storage pointer location.</div>";
        }
    } else {
        $status_banner = "<div class='bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded-xl mb-6 font-semibold text-sm'>Prohibited file format exception. Upload verified PDF blueprints or static PNG/JPG snapshots only.</div>";
    }
}

$historyStmt = $pdo->prepare("SELECT * FROM ticket_transfers WHERE user_id = ? ORDER BY created_at DESC");
$historyStmt->execute([$user_id]);
$records = $historyStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<?php include "inc/head.php"; ?>
<body class="bg-gray-50 text-gray-900">
<?php include "inc/navbar1.php"; ?>
<?php include "inc/navbar2.php"; ?>

<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4">
        <div>
            <h2 class="text-3xl font-black text-gray-900">User Dashboard</h2>
            <p class="text-xs text-gray-400 mt-1 font-mono">Profile Context Mapping: Base ID <?= $user_id; ?> (<?= htmlspecialchars($_SESSION['username']); ?>)</p>
        </div>
        <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Account Active</span>
    </div>

    <?= $status_banner; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 h-fit">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Secondary Market Transfers</h3>
            <p class="text-xs text-gray-500 mb-6">List your owned physical assets inside our internal global platform network for verification checking loops.</p>
            
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Upload Reference File:</label>
                    <input type="file" name="transfer_document" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-[10px] text-gray-400 mt-1">Accepted: Secure PDF, PNG, or JPEG digital exports.</p>
                </div>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Listing Descriptions & Parameters:</label>
                    <textarea name="transfer_notes" rows="4" required placeholder="Specify sector tags cleanly. E.g., General Admission Stand, Row C, Seat 12, Gate 4 structural access path configuration context." class="w-full text-sm p-3 border border-gray-200 rounded-xl outline-none focus:border-blue-600 resize-none"></textarea>
                </div>
                
                <button type="submit" class="w-full bg-[#024DDF] hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider transition-colors shadow-md">
                    Initialize Verification Protocol
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Ownership Verification Registry Ledger</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-50 font-bold">
                        <tr>
                            <th class="p-4 rounded-l-lg">Document Asset</th>
                            <th class="p-4">Description Context Notes</th>
                            <th class="p-4 rounded-r-lg text-center">Status Flag</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($records)): ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-xs text-gray-400 font-medium">No transfer ledger interactions registered under profile identity mapping yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($records as $rec): 
                                $badgeColor = "bg-yellow-100 text-yellow-800";
                                if($rec['status'] === 'approved') $badgeColor = "bg-green-100 text-green-800";
                                if($rec['status'] === 'rejected') $badgeColor = "bg-red-100 text-red-800";
                            ?>
                                <td class="p-4 font-medium text-blue-600 hover:underline">
                                    <a href="uploads/transfers/<?= htmlspecialchars($rec['ticket_file']); ?>" target="_blank"><i class="fas fa-file-invoice"></i> Analyze Document File</a>
                                </td>
                                <td class="p-4 text-xs text-gray-700 max-w-xs truncate" title="<?= htmlspecialchars($rec['description']); ?>">
                                    <?= htmlspecialchars($rec['description']); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md <?= $badgeColor; ?>">
                                        <?= $rec['status']; ?>
                                    </span>
                                </td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "inc/footer.php"; ?>
