<?php
session_start();
ob_start();
header('Content-Type: application/json');
error_reporting(0);

require_once __DIR__ . '/../config/db.php';      // defines $pdo
require_once __DIR__ . '/../utils/mailer.php';   // sendMail()

// Collect & sanitize input
$first_name  = trim($_POST['first_name'] ?? '');
$last_name   = trim($_POST['last_name'] ?? '');
$email       = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone       = trim($_POST['phone'] ?? '');
$gender      = trim($_POST['gender'] ?? '');
$designation = trim($_POST['designation'] ?? '');

// Basic validation
if (!$first_name || !$last_name || !$email || !$phone || !$gender || !$designation) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address."]);
    exit;
}

if (!preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
    echo json_encode(["success" => false, "message" => "Invalid phone number."]);
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "An admin account with this email already exists."]);
    exit;
}

// Generate a secure random password (10 characters)
$plainPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%&*'), 0, 10);
$hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

// Assign default avatar
$picture = (strtolower($gender) === 'female')
    ? 'assets/img/female-avatar.png'
    : 'assets/img/male-avatar.png';

try {
    // Insert admin record
    $stmt = $pdo->prepare("
        INSERT INTO admin (first_name, last_name, email, gender, phone, password, picture, designation, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$first_name, $last_name, $email, $gender, $phone, $hashedPassword, $picture, $designation]);

    // Send Email Notification
    $subject = "Account Creation";

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
                                <td style='text-align:center;padding:30px 30px 10px 30px;'>
                                    <h2 style='font-size:22px;color:#000000;margin:0;'>Admin Account Created</h2>
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align:center;padding:0 30px 20px 30px;'>
                                    <p style='font-size:14px;color:#666;margin:0 0 15px 0;'>Hello <b>{$first_name}</b>,</p>
                                    <p style='font-size:14px;color:#666;margin:0 0 25px 0;'>Your admin account has been successfully created. <br />Below are your login credentials:</p>
                                    <table style='margin:0 auto;background:#f9f9f9;border-radius:8px;padding:15px 25px;text-align:left;'>
                                        <tr><td style='padding:5px 0;'><b>Email:</b> {$email}</td></tr>
                                        <tr><td style='padding:5px 0;'><b>Password:</b> {$plainPassword}</td></tr>
                                    </table>
                                    <p style='font-size:13px;color:#888;margin:20px 0;'>You can log in to your dashboard and change your password immediately for security reasons.</p>
                                    <a href='https://backoffice.mediasphere.store/' style='display:inline-block;margin-top:10px;padding:10px 25px;background:#000000;color:#fff;text-decoration:none;border-radius:5px;'>Go to Admin Dashboard</a>
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align:center;padding:20px 30px 40px 30px;'>
                                    <p style='font-size:12px;color:#aaa;margin:0;'>&copy; " . date('Y') . " Media Sphere. All rights reserved.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    ";

    sendMail($email, $subject, $message);

    echo json_encode([
        "success" => true,
        "message" => "Admin account created successfully. Login credentials have been sent to {$email}."
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
    exit;
}