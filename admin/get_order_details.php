<?php
// get_order_details.php
header('Content-Type: application/json');

// Disable internal HTML rendering of header files if they contain visual layouts
// Ensure this script only manages database connectivity definitions
require_once __DIR__ . '/inc/header.php'; 

$deposit_id = isset($_GET['deposit_id']) ? (int)$_GET['deposit_id'] : 0;
$order_id   = isset($_GET['order_id'])   ? trim($_GET['order_id']) : '';

if ($deposit_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Deposit Reference ID.']);
    exit;
}

try {
    /* Step 1: Fetch the precise deposit row to secure the true user_id 
       and verified order context links saved in the ledger.
    */
    $depStmt = $pdo->prepare("SELECT user_id, order_ids FROM deposits WHERE deposit_id = ?");
    $depStmt->execute([$deposit_id]);
    $depositRecord = $depStmt->fetch(PDO::FETCH_ASSOC);

    if (!$depositRecord) {
        echo json_encode(['success' => false, 'message' => 'Deposit record tracking log not found.']);
        exit;
    }

    $userId = $depositRecord['user_id'];
    // Parse order fallback targets if query string argument did not transfer correctly
    $targetTicketId = !empty($order_id) ? $order_id : $depositRecord['order_ids'];

    /* Step 2: Pull corresponding user profile data details
    */
    $userStmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name' => 'Unknown User', 'email' => 'N/A'];

    /* Step 3: Pull matching ticket/order specifications directly via ticket_id primary index
    */
    $ticketStmt = $pdo->prepare("SELECT ticket_id, event_title, ticket_type, quantity, total_price, status, created_at FROM tickets WHERE ticket_id = ?");
    $ticketStmt->execute([$targetTicketId]);
    $ticketData = $ticketStmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Package compound metrics safely into one clean object mapping
    echo json_encode([
        'success' => true,
        'data' => [
            'full_name'     => $userData['full_name'],
            'email'         => $userData['email'],
            'ticket_id'     => $ticketData ? $ticketData['ticket_id'] : $targetTicketId,
            'event_title'   => $ticketData ? $ticketData['event_title'] : 'Custom Purchase Entry',
            'ticket_type'   => $ticketData ? $ticketData['ticket_type'] : 'N/A',
            'quantity'      => $ticketData ? $ticketData['quantity'] : '1',
            'total_price'   => $ticketData ? $ticketData['total_price'] : '0.00',
            'ticket_status' => $ticketData ? $ticketData['status'] : 'pending',
            'purchase_date' => $ticketData ? $ticketData['created_at'] : 'N/A'
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database exception layout error: ' . $e->getMessage()]);
}
exit;
