<?php
// scripts/smoke.php
// Lightweight smoke test: iterate public pages & report errors without executing actions needing POST/auth.

$root = dirname(__DIR__);
$publicDir = $root . '/public';
$ignore = [
    'login.php','logout.php','register.php','full-system-check.php',
    'api-test.php','test-auth.php','test-functionality.php','test-product-api.html',
    'admin.php','customer-dashboard.php','customer-dashboard-new.php' // may depend on session/admin
];

$php = PHP_BINARY;
$files = glob($publicDir . '/*.php');
$total = 0; $fail = 0; $warn = 0; $results=[];
foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name,$ignore)) continue;
    $cmd = escapeshellcmd($php) . ' ' . escapeshellarg($file) . ' 2>&1';
    $raw = shell_exec($cmd);
    $output = (string)($raw ?? '');
    $hasFatal = $output !== '' && stripos($output,'Fatal error') !== false;
    $hasWarn  = $output !== '' && (stripos($output,'Warning:') !== false || stripos($output,'Notice:') !== false);
    $total++;
    if ($hasFatal) $fail++; if ($hasWarn && !$hasFatal) $warn++;
    $results[] = [
        'file'=>$name,
        'fatal'=>$hasFatal,
        'warn'=>$hasWarn,
    ];
}

echo "Smoke Report (public/*.php)\n";
foreach ($results as $r) {
    echo sprintf("%-25s : %s%s\n", $r['file'], $r['fatal']? 'FATAL':'OK', $r['warn']?' (warnings)':'');
}

echo "Summary: total=$total ok=" . ($total-$fail) . " fatal=$fail warn=$warn\n";
exit($fail>0?1:0);
