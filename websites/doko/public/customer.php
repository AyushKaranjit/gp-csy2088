<?php
// Legacy customer dashboard: redirect to the new profile page.
declare(strict_types=1);
session_start();

$qs = $_SERVER['QUERY_STRING'] ?? '';
$target = 'profile.php' . ($qs ? ('?' . $qs) : '');

if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        // Use a temporary redirect to avoid clients caching an outdated target.
        header('Location: ' . $target, true, 302);
        exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">
    <title>Redirecting…</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <p>Redirecting to <a href="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">your profile</a>…</p>
</body>
</html>
