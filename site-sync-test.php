<?php

// --------------------------------------------
// REAL-TIME DEBUG OUTPUT (NO BUFFERING)
// --------------------------------------------
header("Content-Type: text/plain");
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
while (ob_get_level()) ob_end_flush();
ob_implicit_flush(true);

// --------------------------------------------
// SETTINGS
// --------------------------------------------
$host = '109.106.254.43';
$port = 65002;
$user = 'u642727376';
$pubkey = 'C:/Users/rjfra/.ssh/hostinger_gha.pub';
$privkey = 'C:/Users/rjfra/.ssh/hostinger_gha';

echo "=== SPORTS WAREHOUSE SYNC TEST ===\n";
echo "This script will test SSH, AUTH, and SFTP setup.\n\n";

// --------------------------------------------
// STEP 1 — SSH CONNECT
// --------------------------------------------
echo "[1] Connecting to SSH…\n";

$start = microtime(true);
$ssh = @ssh2_connect($host, $port, [], ['disconnect' => true]);
$elapsed = round(microtime(true) - $start, 3);

if (!$ssh) {
    echo "❌ SSH connection FAILED (after {$elapsed}s)\n";
    exit;
}

echo "✔ SSH connection succeeded ({$elapsed}s)\n\n";


// --------------------------------------------
// STEP 2 — AUTHENTICATE WITH PUBLIC KEY
// --------------------------------------------
echo "[2] Authenticating with public key…\n";

$start = microtime(true);
$authOK = @ssh2_auth_pubkey_file(
    $ssh,
    $user,
    $pubkey,
    $privkey,
    '' // blank passphrase
);
$elapsed = round(microtime(true) - $start, 3);

if (!$authOK) {
    echo "❌ Authentication FAILED ({$elapsed}s)\n";
    exit;
}

echo "✔ Authentication succeeded ({$elapsed}s)\n\n";


// --------------------------------------------
// STEP 3 — INITIALIZE SFTP SUBSYSTEM
// --------------------------------------------
echo "[3] Initializing SFTP subsystem…\n";

$start = microtime(true);
$sftp = @ssh2_sftp($ssh);
$elapsed = round(microtime(true) - $start, 3);

if (!$sftp) {
    echo "❌ SFTP initialization FAILED ({$elapsed}s)\n";
    exit;
}

echo "✔ SFTP subsystem ready ({$elapsed}s)\n\n";


// --------------------------------------------
// FINAL RESULT
// --------------------------------------------
echo "=== TEST COMPLETE ===\n";
echo "If all 3 steps are green ✔, your sync script's freeze is elsewhere.\n";
echo "If ANY step above freezes or fails, THAT is the cause.\n";

?>



