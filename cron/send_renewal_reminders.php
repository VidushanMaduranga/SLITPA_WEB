<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send email
function send_renewal_reminder($member) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($member['email'], $member['full_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'SLITPA Membership Renewal Reminder';
        
        // Calculate days until expiry
        $days_until_expiry = ceil((strtotime($member['membership_end_date']) - time()) / (60 * 60 * 24));
        
        $mail->Body = "
            <p>Dear {$member['full_name']},</p>
            
            <p>This is a reminder that your SLITPA membership will expire in {$days_until_expiry} days on {$member['membership_end_date']}.</p>
            
            <p>To continue enjoying your membership benefits, please renew your membership before the expiry date.</p>
            
            <p>You can log in to your account at " . SITE_URL . " to process the renewal.</p>
            
            <p>If you have any questions, please don't hesitate to contact us.</p>
            
            <p>Best regards,<br>SLITPA Team</p>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error sending renewal reminder to {$member['email']}: {$mail->ErrorInfo}");
        return false;
    }
}

// Connect to database
$conn = get_db_connection();
if (!$conn) {
    die("Database connection failed");
}

// Get members whose membership is expiring soon
$query = "
    SELECT m.*, u.email 
    FROM members m
    JOIN users u ON m.user_id = u.id
    WHERE 
        u.status = 'active'
        AND m.membership_end_date IS NOT NULL
        AND m.membership_end_date > CURDATE()
        AND DATEDIFF(m.membership_end_date, CURDATE()) <= ?
        AND (
            m.last_reminder_sent IS NULL
            OR DATEDIFF(CURDATE(), m.last_reminder_sent) >= 7
        )
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", RENEWAL_REMINDER_DAYS);
$stmt->execute();
$result = $stmt->get_result();
$members = $result->fetch_all(MYSQLI_ASSOC);

// Send reminders
foreach ($members as $member) {
    if (send_renewal_reminder($member)) {
        // Update last reminder sent date
        $stmt = $conn->prepare("UPDATE members SET last_reminder_sent = CURDATE() WHERE id = ?");
        $stmt->bind_param("i", $member['id']);
        $stmt->execute();
        
        echo "Sent reminder to {$member['email']}\n";
    } else {
        echo "Failed to send reminder to {$member['email']}\n";
    }
}

$conn->close();

// Log completion
echo "Renewal reminder process completed at " . date('Y-m-d H:i:s') . "\n"; 