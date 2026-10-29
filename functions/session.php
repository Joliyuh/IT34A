
<?php

// Start User Session
function startSession($pdo)
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (
            user_id,
            session_start
        ) VALUES (
            :user_id,
            NOW()
        )
    ");

    $stmt->execute([
        'user_id' => $user_id,
    ]);

    return $pdo->lastInsertId();
}

// End User Session
function endSession($pdo)
{
    if (
        !isset($_SESSION['user_id']) ||
        !isset($_SESSION['session_id'])
    ) {
        return false;
    }

    $session_id = $_SESSION['session_id'];

    $stmt = $pdo->prepare("
        UPDATE user_sessions
        SET
            session_end = NOW(),
            session_duration = TIMESTAMPDIFF(
                SECOND,
                session_start,
                NOW()
            )
        WHERE session_id = :session_id
          AND session_end IS NULL
    ");

    return $stmt->execute([
        'session_id' => $session_id
    ]);
}

// Check User for Active Session
function hasActiveSession($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT session_id
        FROM user_sessions
        WHERE user_id = :user_id
          AND session_end IS NULL
        LIMIT 1
    ");

    $stmt->execute([
        'user_id' => $user_id
    ]);

    return (bool) $stmt->fetchColumn();
}

// Session Timeout
function checkSessionTimeout()
{
    global $pdo;

    // 10 seconds of inactivity
    $timeout = 10;

    // Check Last Activity
    if (isset($_SESSION['last_activity'])) {

        $inactive = time() - $_SESSION['last_activity'];

        if ($inactive >= $timeout) {

            // End Database Session
            if (isset($_SESSION['session_id'])) {
                endSession($pdo);
            }

            // Destroy PHP Session
            $_SESSION = [];

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }

            // Redirect to Login Page
            header('Location: ' . BASE_URL . '/index.php?timeout=1');
            exit();
        }
    }

    // Update Last Activity
    $_SESSION['last_activity'] = time();
}

?>
