<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Try multiple possible vendor locations
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',        // typical when utils/ is inside project root
    __DIR__ . '/../../vendor/autoload.php',     // if utils/ is nested deeper
    dirname(__DIR__) . '/vendor/autoload.php',  // safer relative path
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    // Return JSON-safe error if called via AJAX
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => "Server misconfiguration: autoload.php not found."
        ]);
        exit;
    } else {
        throw new Exception("Composer autoload.php not found. Run composer install.");
    }
}

function sendMail($to, $subject, $bodyHtml, $bodyAlt = '') {
    $mail = new PHPMailer(true);

    try {
        // Detect environment
        $env = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) ? 'development' : 'production';

        $mail->isSMTP();

        if ($env === 'development') {
            // Gmail SMTP for testing
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ebuzzadvert@gmail.com'; // your Gmail
            $mail->Password   = 'buzrjirnhbxlcuio';      // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
        } else {
            // Production (custom domain SMTP)
            $mail->Host       = 'server362.web-hosting.com'; // your mail server
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@mediasphere.store';    // your domain email
            $mail->Password   = 'fYnVhy^}Nvo)';              // your domain email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
        }

        // From / To
        $mail->setFrom('noreply@mediasphere.store', 'Media Sphere');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = $bodyAlt ?: strip_tags($bodyHtml);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}