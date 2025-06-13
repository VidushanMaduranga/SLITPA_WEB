<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get members whose membership expires in the next 30 days
$stmt = $pdo->prepare("
    SELECT m.*, u.email 
    FROM members m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.membership_status = 'active' 
    AND m.membership_expiry BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)
");
$stmt->execute();
$expiring_members = $stmt->fetchAll();

foreach ($expiring_members as $member) {
    $days_remaining = ceil((strtotime($member['membership_expiry']) - time()) / (60 * 60 * 24));
    
    $subject = "SLITPA Membership Renewal Reminder";
    $message = "Dear {$member['full_name']},\n\n";
    $message .= "Your SLITPA membership will expire in {$days_remaining} days (on " . date('F j, Y', strtotime($member['membership_expiry'])) . ").\n\n";
    $message .= "To maintain your membership benefits, please renew your membership before the expiration date.\n\n";
    $message .= "You can renew your membership by logging into your account at: " . SITE_URL . "/member-renewal.php\n\n";
    $message .= "If you have any questions, please don't hesitate to contact us.\n\n";
    $message .= "Best regards,\nSLITPA Team";
    
    // Send email
    mail($member['email'], $subject, $message);
    
    // Log the reminder
    $stmt = $pdo->prepare("
        INSERT INTO membership_reminders (member_id, reminder_date, days_remaining) 
        VALUES (?, CURRENT_DATE, ?)
    ");
    $stmt->execute([$member['id'], $days_remaining]);
}

// Handle expired memberships
$stmt = $pdo->prepare("
    UPDATE members 
    SET membership_status = 'expired' 
    WHERE membership_status = 'active' 
    AND membership_expiry < CURRENT_DATE
");
$stmt->execute();

// Log the script execution
$log_file = __DIR__ . '/cron.log';
$log_message = date('Y-m-d H:i:s') . " - Membership reminder script executed. " . count($expiring_members) . " reminders sent.\n";
file_put_contents($log_file, $log_message, FILE_APPEND); 