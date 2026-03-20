<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
session_start();

$current_admin_id = $_SESSION['admin_id'] ?? null;

if (!$current_admin_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

try {
    // Make sure your DB variable name here matches config/db.php
    $stmt = $pdo->prepare("
        SELECT id, CONCAT(first_name, ' ', last_name) AS name, 
               email, picture AS avatar, 
               CASE WHEN status = 'active' THEN 'Online' ELSE 'Offline' END AS status
        FROM admin
        WHERE id != :id
        ORDER BY first_name ASC
    ");
    $stmt->execute(['id' => $current_admin_id]);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fallback avatar
    foreach ($staff as &$user) {
        $user['avatar'] = $user['avatar'] ?: '../../img/people/default-avatar.png';
    }

    echo json_encode($staff);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
