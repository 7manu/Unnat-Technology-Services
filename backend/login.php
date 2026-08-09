<?php
/**
 * Administrator sign-in.
 *
 * Accounts live in the `cms_admins` table so passwords can be changed from the
 * admin panel. The environment/legacy credentials remain valid as a fallback for
 * first sign-in and for recovery if the table is unavailable.
 */
declare(strict_types=1);

require __DIR__ . '/_admin_auth.php';
require_once __DIR__ . '/cms.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php', true, 303);
    exit;
}

$mobile = trim((string) ($_POST['mobile'] ?? ''));
$passcode = (string) ($_POST['passcode'] ?? '');

/** Starts the authenticated session for the supplied identity. */
$signIn = static function (int $id, string $name, string $mobile): void {
    session_regenerate_id(true);
    $_SESSION['uts_admin_authenticated'] = true;
    $_SESSION['uts_admin_id'] = $id;
    $_SESSION['uts_admin_name'] = $name;
    $_SESSION['uts_admin_mobile'] = $mobile;
    $_SESSION['uts_admin_csrf'] = bin2hex(random_bytes(32));
    header('Location: ../admin.php', true, 303);
    exit;
};

$conn = cms_db();
if ($conn instanceof mysqli) {
    try {
        $statement = $conn->prepare('SELECT `id`, `name`, `mobile`, `password_hash` FROM `cms_admins` WHERE `mobile` = ? AND `is_active` = 1 LIMIT 1');
        $statement->bind_param('s', $mobile);
        $statement->execute();
        $account = $statement->get_result()->fetch_assoc();

        if ($account && password_verify($passcode, (string) $account['password_hash'])) {
            $touch = $conn->prepare('UPDATE `cms_admins` SET `last_login_at` = NOW() WHERE `id` = ?');
            $touch->bind_param('i', $account['id']);
            $touch->execute();
            $signIn((int) $account['id'], (string) $account['name'], (string) $account['mobile']);
        }
    } catch (Throwable $exception) {
        // The table may not exist yet on a brand-new deployment; fall through.
        error_log('UTS admin table sign-in skipped: ' . $exception->getMessage());
    }
}

/* Fallback: environment variables, then the shipped default credentials. */
$fallbackMobile = getenv('UTS_ADMIN_MOBILE') ?: '9818059661';
$fallbackHash = getenv('UTS_ADMIN_PASSWORD_HASH')
    ?: '$2y$12$gO86NL7JQ65mNYiPBjuTnO.9SG65MO1tXir2h6.T9kdSl/WWeHcAu';

if (hash_equals($fallbackMobile, $mobile) && password_verify($passcode, $fallbackHash)) {
    $signIn(0, 'Administrator', $fallbackMobile);
}

header('Location: ../login.php?error=invalid', true, 303);
exit;
