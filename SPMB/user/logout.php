<?php
require_once '../api/config.php';

// Destroy user session
unset($_SESSION['user_id']);
unset($_SESSION['user_nama']);
unset($_SESSION['user_phone']);
unset($_SESSION['user_status']);

header('Location: index.php');
exit;
?>
