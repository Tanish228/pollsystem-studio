<!-- Testing branch feature -->
 
<?php
require_once 'includes/auth.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.html');
}
exit;
