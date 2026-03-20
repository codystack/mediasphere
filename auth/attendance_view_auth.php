<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? '';
if (empty($id) || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid attendance ID.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM attendance WHERE id = ? AND admin_id = ?");
$stmt->execute([$id, $_SESSION['admin_id']]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attendance) {
    echo json_encode(['success' => false, 'message' => 'Attendance record not found.']);
    exit;
}

echo json_encode(['success' => true, 'attendance' => $attendance]);