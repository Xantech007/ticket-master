<?php
// get_order_details.php
header('Content-Type: application/json');

// Include your database setup file (adjust path if necessary)
// Assuming $pdo is instantiated in the parent setup files
require_once __DIR__ . '/inc/header.php'; 

// Clear any standard header buffer outputs if necessary, or ensure header.php doesn't echo UI elements.
// If your header.php renders HTML layouts, it's safer to extract/instantiate just the PDO connection logic here:
/*
$host = 'sql207.infinityfree.com';
$db   = 'if0_42273705_ticket2';
...
*/

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing Order ID argument.']);
    exit;
}

$order_id = trim($_GET['order_id']);

try {
    // Select the user's full name, email, and the ticket specific details
    // Joining tickets table with users table via user_id relationship field
    $stmt = $pdo->prepare("
        SELECT 
            t.ticket_id,
            t.event_title,
            t.ticket_type,
            t.quantity,
            t.total_price,
            t.status AS ticket_status,
            t.created_at AS purchase_date,
            u.full_name,
            u.email
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.ticket_id = ?
    ");
    $stmt->execute([$order_id]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($details) {
        echo json_encode(['success' => true, 'data' => $details]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No order/ticket logs found for this specific ID.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database exception: ' . $e->getMessage()]);
}
exit;
