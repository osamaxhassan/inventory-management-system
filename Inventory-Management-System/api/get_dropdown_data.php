<?php
// api/get_dropdown_data.php
session_start();
require '../config/db.php';

try {
    // 1. Fetch Active Services
    $stmt = $pdo->query("SELECT service_id, service_name, default_labor_cost FROM services WHERE is_active = 1");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Parts in Stock
    $stmt = $pdo->query("SELECT part_id, part_name, quantity_in_stock, selling_price FROM parts WHERE quantity_in_stock > 0");
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Mechanics
    $stmt = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'Mechanic' AND status = 'Active'");
    $mechanics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Package it up as JSON
    $response = [
        'status'    => 'success',
        'services'  => $services,
        'parts'     => $parts,
        'mechanics' => $mechanics
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>