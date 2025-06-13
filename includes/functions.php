<?php
// Common functions

/**
 * Get total number of active members
 * @return int Number of active members
 */
function get_active_member_count() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM members WHERE status = 'active'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['count'];
}

/**
 * Get member count by membership type
 * @param string $type Membership type (regular, premium, corporate)
 * @return int Number of members of specified type
 */
function get_member_count_by_type($type) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM members WHERE membership_type = ? AND status = 'active'");
    $stmt->execute([$type]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['count'];
}
?>