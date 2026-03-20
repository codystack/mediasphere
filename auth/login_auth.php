<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/mailer.php'; // sendMail($to, $subject, $html)

// Get and sanitize inputs
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Basic validation
if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format."]);
    exit;
}

// Find admin by email
$stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode(["success" => false, "message" => "No admin account found with that email."]);
    exit;
}

// Check for suspension first
if ($admin['status'] === 'suspended') {
    echo json_encode(["success" => false, "message" => "Your account has been suspended."]);
    exit;
}

// Then check for inactive
if ($admin['status'] !== 'active') {
    echo json_encode(["success" => false, "message" => "Your admin account is inactive."]);
    exit;
}

// Verify password
if (!password_verify($password, $admin['password'])) {
    echo json_encode(["success" => false, "message" => "Incorrect password."]);
    exit;
}

// Set session data
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_email'] = $admin['email'];
$_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
$_SESSION['designation'] = $admin['designation'];
$_SESSION['admin_phone'] = $admin['phone'];
$_SESSION['admin_picture'] = $admin['picture'];
$_SESSION['first_name'] = $admin['first_name'];
$_SESSION['last_name'] = $admin['last_name'];

// Update last login
$pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

// Gather login meta info
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
$device = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
$time = date('l, jS F Y \a\t g:i A');

// Determine location
$location = 'Unknown';
try {
    $json = @file_get_contents("https://ipapi.co/{$ip}/json/");
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['city']) && !empty($data['country_name'])) {
            $location = "{$data['city']}, {$data['country_name']}";
        }
    }
} catch (Exception $e) {
    $location = 'Unavailable';
}

// Log admin login attempt
try {
    $logStmt = $pdo->prepare("INSERT INTO admin_login_logs (email, admin_id, ip_address, device_info, location, status) VALUES (?, ?, ?, ?, ?, 'success')");
    $logStmt->execute([$email, $admin['id'], $ip, $device, $location]);
} catch (Exception $e) {
    // fail silently
}

// Send Login Email
$subject = "Login Notification";

$message = "
<table style='width:100%;background:#f5f6fa;font-family:Arial,sans-serif;padding:20px;'>
    <tbody>
        <tr>
            <td>
                <table style='margin:0 auto;max-width:600px;background:#fff;border-radius:8px;overflow:hidden;'>
                    <tbody>
                        <tr>
                            <td style='text-align:center;padding-top:30px;'>
                                <a href='https://mediasphere.store/'>
                                    <img src='https://res.cloudinary.com/dzow7ui7e/image/upload/v1773901499/ms-dark_yvbuvl.png' alt='Media Sphere' width='200'>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding:30px 30px 10px 30px;text-align:left;color:#000;'>
                                <h2 style='font-size:20px;margin:0 0 15px;'>Admin Login Alert</h2>
                                <p style='font-size:14px;margin:0 0 15px;'>Hi {$admin['first_name']},</p>
                                <p style='font-size:14px;margin:0 0 20px;'>A login was just detected on your Media Sphere admin account.</p>
                                <table style='width:100%;background:#f5f6fa;border-radius:6px;padding:15px;'>
                                    <tr><td><b>Time:</b> {$time}</td></tr>
                                    <tr><td><b>IP:</b> {$ip}</td></tr>
                                    <tr><td><b>Location:</b> {$location}</td></tr>
                                    <tr><td><b>Device:</b> {$device}</td></tr>
                                </table>
                                <p style='font-size:13px;color:#000;margin-top:15px;'>If this was you, no action is required.<br>If not, please reset your password immediately.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='text-align:center;padding:20px 30px 40px 30px;'>
                                <p style='font-size:12px;color:#aaa;margin-top:20px;'>&copy; " . date('Y') . " Media Sphere. All rights reserved.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
";

// Send email alert
sendMail($admin['email'], $subject, $message);

// Return success JSON
echo json_encode([
    "success" => true,
    "message" => "Welcome back, {$admin['first_name']}!",
    "redirect" => "dashboard"
]);
exit;