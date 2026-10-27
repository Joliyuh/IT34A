```php
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../functions/session.php';
require_once __DIR__ . '/../functions/activity.php';

if (isset($_SESSION['user_id'])) {
    logActivity(
        $pdo,
        $_SESSION['user_id'],
        $_SESSION['user_email'],
        'logout',
        'success'
    );

    endSession($pdo);
}

$_SESSION = [];
session_destroy();

header('Location: ' . BASE_URL . '/index.php');
exit;
?>
