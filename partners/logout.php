<?php
session_start();
// Unset all partner session variables
unset($_SESSION['partner_id']);
unset($_SESSION['partner_name']);
// Optionally destroy the session if only partners use it
// session_destroy();
header('Location: ../index.php');
exit; 