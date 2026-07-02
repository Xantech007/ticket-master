<?php
session_start();
header('Content-Type: application/json');

require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Login required.'
    ]);
    exit;
}

$db = new Database();
$pdo = $db->connect();

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['seats'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No seats selected.'
    ]);
    exit;
}

try {

    $pdo->beginTransaction();

    $insert = $pdo->prepare("
        INSERT INTO orders
        (
            user_id,
            ticket_id,
            status,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            'pending',
            NOW()
        )
    ");

    $orders = [];

    foreach ($data['seats'] as $seat) {

        // Use whatever uniquely identifies a seat
        $ticket_id = $seat['id'];

        $insert->execute([
            $user_id,
            $ticket_id
        ]);

        $orders[] = [
            'order_id' => $pdo->lastInsertId(),
            'user_id'  => $user_id,
            'ticket_id'=> $ticket_id
        ];
    }

    $pdo->commit();

    echo json_encode([
        'success'=>true,
        'orders'=>$orders
    ]);

} catch(Exception $e){

    $pdo->rollBack();

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ]);
}
