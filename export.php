<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$reportType = isset($_GET['type']) ? $_GET['type'] : 'sales';
$allowedReports = ['sales', 'inventory', 'users'];

if (!in_array($reportType, $allowedReports, true)) {
    $reportType = 'sales';
}

header('Location: reports.php?export='.$reportType);
exit;
?>
