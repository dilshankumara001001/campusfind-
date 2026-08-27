<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

logoutUser();
header('Location: index.php?logged_out=1');
exit;
?>