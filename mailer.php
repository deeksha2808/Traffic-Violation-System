<?php
// ===== EMAIL NOTIFICATION via Gmail SMTP =====
// Uses PHPMailer to send real emails

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// ---- UPDATE THESE WITH YOUR GMAIL CREDENTIALS ----
define('MAIL_FROM',     'your_gmail@gmail.com');   // your Gmail address
define('MAIL_PASSWORD', 'your_app_password');       // Gmail App Password (not your login password)
define('MAIL_NAME',     'Smart Traffic System');
// --------------------------------------------------

function sendChallanEmail($to_email, $to_name, $vehicle_number, $violation_type, $location, $fine_amount) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = '127.0.0.1';  // Mailpit local SMTP
        $mail->SMTPAuth   = false;
        $mail->Port       = 1025;         // Mailpit SMTP port

        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = '🚦 Traffic Challan Notice - Vehicle ' . $vehicle_number;
        $mail->Body    = "
        <div style='font-family:Segoe UI,sans-serif;max-width:500px;margin:auto;border:1px solid #f5d9c8;border-radius:16px;overflow:hidden;'>
          <div style='background:linear-gradient(135deg,#fde8d8,#e8d5f5);padding:28px 32px;text-align:center;'>
            <h2 style='color:#3d2b1f;margin:0;'>🚦 Traffic Challan Notice</h2>
          </div>
          <div style='padding:28px 32px;background:#fff;'>
            <p style='color:#7a5c4f;'>Dear <strong>$to_name</strong>,</p>
            <p style='color:#7a5c4f;'>A traffic violation has been recorded for your vehicle. Please review the details below and pay the fine at your earliest.</p>
            <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
              <tr style='background:#fdf6f0;'><td style='padding:10px 14px;font-weight:600;color:#5a3e35;'>Vehicle Number</td><td style='padding:10px 14px;color:#3d3d3d;'>$vehicle_number</td></tr>
              <tr><td style='padding:10px 14px;font-weight:600;color:#5a3e35;'>Violation Type</td><td style='padding:10px 14px;color:#3d3d3d;'>$violation_type</td></tr>
              <tr style='background:#fdf6f0;'><td style='padding:10px 14px;font-weight:600;color:#5a3e35;'>Location</td><td style='padding:10px 14px;color:#3d3d3d;'>$location</td></tr>
              <tr><td style='padding:10px 14px;font-weight:600;color:#5a3e35;'>Fine Amount</td><td style='padding:10px 14px;color:#c0392b;font-weight:700;'>₹$fine_amount</td></tr>
              <tr style='background:#fdf6f0;'><td style='padding:10px 14px;font-weight:600;color:#5a3e35;'>Status</td><td style='padding:10px 14px;color:#c0392b;font-weight:700;'>UNPAID</td></tr>
            </table>
            <p style='color:#9e7a6a;font-size:0.88rem;'>Please log in to the Smart Traffic System to pay your fine online.</p>
          </div>
          <div style='background:#fdf6f0;padding:16px 32px;text-align:center;'>
            <p style='color:#b05e3a;font-size:0.82rem;margin:0;'>Smart Traffic Violation Detection &amp; Management System</p>
          </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error silently — don't break the app if email fails
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>
