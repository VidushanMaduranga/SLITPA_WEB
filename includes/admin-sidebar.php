<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'pending-members.php' ? 'active' : ''; ?>" href="pending-members.php">
                    <i class="bi bi-person-plus"></i>
                    Pending Members
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'approve_partners.php' ? 'active' : ''; ?>" href="approve_partners.php">
                    <i class="bi bi-building"></i>
                    Pending Partners
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'manage_events.php' ? 'active' : ''; ?>" href="manage_events.php">
                    <i class="bi bi-calendar-event"></i>
                    Manage Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav> 