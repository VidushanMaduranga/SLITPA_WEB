<?php
require_once '../config/config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $current_date = date('Y-m-d');
    // Upcoming events: allow if end_date is NULL, empty, '0000-00-00', or in the future
    $stmt = $pdo->prepare("SELECT e.*, em.file_path as featured_image FROM events e LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1 WHERE (e.show_from IS NULL OR e.show_from <= ?) AND (e.show_until IS NULL OR e.show_until >= ?) AND (e.end_date IS NULL OR e.end_date = '' OR e.end_date = '0000-00-00' OR e.end_date >= ?) ORDER BY e.event_date ASC");
    $stmt->execute([$current_date, $current_date, $current_date]);
    $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Past events
    $stmt = $pdo->prepare("SELECT e.*, em.file_path as featured_image FROM events e LEFT JOIN event_media em ON e.id = em.event_id AND em.is_featured = 1 WHERE e.end_date < ? AND e.end_date IS NOT NULL AND e.end_date != '' AND e.end_date != '0000-00-00' ORDER BY e.event_date DESC");
    $stmt->execute([$current_date]);
    $past = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Upcoming CPD sessions
    $stmt = $pdo->prepare("SELECT id, title, session_date, location FROM cpd_sessions WHERE session_date >= ? ORDER BY session_date ASC");
    $stmt->execute([$current_date]);
    $cpd_upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Format dates
    foreach ($upcoming as &$event) {
        $event['date'] = date('F j, Y', strtotime($event['event_date']));
    }
    foreach ($past as &$event) {
        $event['date'] = date('F j, Y', strtotime($event['event_date']));
    }
    foreach ($cpd_upcoming as &$cpd) {
        $cpd['date'] = date('F j, Y', strtotime($cpd['session_date']));
    }
    echo json_encode(['upcoming' => $upcoming, 'cpd_upcoming' => $cpd_upcoming, 'past' => $past]);
    exit;
}

if ($action === 'details' && isset($_GET['id'])) {
    $event_id = $_GET['id'];
    // Try to fetch as event first
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($event) {
    $event['date'] = date('F j, Y', strtotime($event['event_date']));
    // Get media
    $stmt = $pdo->prepare("SELECT * FROM event_media WHERE event_id = ? ORDER BY is_featured DESC");
    $stmt->execute([$event_id]);
    $event['media'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($event);
        exit;
    }
    // If not found, try as CPD session
    $stmt = $pdo->prepare("SELECT * FROM cpd_sessions WHERE id = ?");
    $stmt->execute([$event_id]);
    $cpd = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cpd) {
        $cpd['date'] = date('F j, Y', strtotime($cpd['session_date']));
        // Get media for CPD session
        $stmt = $pdo->prepare("SELECT * FROM cpd_session_media WHERE session_id = ? ORDER BY id ASC");
        $stmt->execute([$event_id]);
        $cpd['media'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cpd['type'] = 'cpd';
        echo json_encode($cpd);
        exit;
    }
    echo json_encode(['error' => 'Event or CPD session not found']);
    exit;
}

echo json_encode(['error' => 'Invalid request']); 