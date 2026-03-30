<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/config.php';
header('Location: preview/preview.php');
exit();

?>