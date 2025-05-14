<?php
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('register.php', 'Invalid request method.', 'error');
}

$registration_type = $_POST['registration_type'] ?? '';
if (!in_array($registration_type, ['member', 'partner'])) {
    redirect_with_message('register.php', 'Invalid registration type.', 'error');
}

$conn = get_db_connection();
if (!$conn) {
    redirect_with_message('register.php', 'Database connection failed.', 'error');
}

try {
    if ($registration_type === 'member') {
        // Process member registration
        $name = sanitize_input($_POST['member_name']);
        $email = sanitize_input($_POST['member_email']);
        $country = sanitize_input($_POST['member_country']);
        $position = sanitize_input($_POST['member_position']);
        $passport = sanitize_input($_POST['member_passport']);
        $phone = sanitize_input($_POST['member_phone']);
        $password = $_POST['member_password'];
        $confirm_password = $_POST['member_confirm_password'];

        // Validate passwords match
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match.');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already registered.');
        }
        $stmt->close();

        // Start transaction
        $conn->begin_transaction();

        // Insert into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, user_type, status) VALUES (?, ?, 'member', 'pending')");
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();

        // Insert into members table
        $stmt = $conn->prepare("INSERT INTO members (user_id, full_name, country, position, passport_number, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $name, $country, $position, $passport, $phone);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Send notification email to admin
        // TODO: Implement email notification

        $message = 'Registration successful! Please wait for admin approval.';
        $type = 'success';

    } else {
        // Process partner registration
        $company_name = sanitize_input($_POST['company_name']);
        $email = sanitize_input($_POST['partner_email']);
        $contact_person = sanitize_input($_POST['contact_person']);
        $phone = sanitize_input($_POST['partner_phone']);
        $partner_type = sanitize_input($_POST['partner_type']);
        $description = sanitize_input($_POST['partner_description']);
        $password = $_POST['partner_password'];
        $confirm_password = $_POST['partner_confirm_password'];

        // Validate passwords match
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match.');
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already registered.');
        }
        $stmt->close();

        // Start transaction
        $conn->begin_transaction();

        // Insert into users table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, user_type, status) VALUES (?, ?, 'partner', 'pending')");
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $user_id = $conn->insert_id;
        $stmt->close();

        // Insert into partners table
        $stmt = $conn->prepare("INSERT INTO partners (user_id, company_name, partner_type, description, contact_person, contact_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $company_name, $partner_type, $description, $contact_person, $phone);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Send notification email to admin
        // TODO: Implement email notification

        $message = 'Partner registration successful! Please wait for admin approval.';
        $type = 'success';
    }

} catch (Exception $e) {
    // Rollback transaction if there was an error
    if ($conn->connect_error === null) {
        $conn->rollback();
    }
    $message = 'Registration failed: ' . $e->getMessage();
    $type = 'error';
}

$conn->close();
redirect_with_message('register.php', $message, $type); 