<?php

/**
 * NORMALISE thumbnails_json
 *
 * Canonical output:
 * ["images/.../01.png","images/.../02.png"]
 *
 * RULES:
 * - Always JSON array
 * - Comma-separated
 * - No semicolons
 * - No videos (.mp4)
 * - No banner assets
 */

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

$pdo = new PDO(
    "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4",
    $db['user'],
    $db['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// ------------------------------------
// Fetch items
// ------------------------------------

$sql = "
    SELECT itemId, thumbnails_json
    FROM item
    WHERE thumbnails_json IS NOT NULL
      AND thumbnails_json != ''
";

$items = $pdo->query($sql)->fetchAll();

$update = $pdo->prepare("
    UPDATE item
    SET thumbnails_json = :json
    WHERE itemId = :id
");

$changed = 0;

foreach ($items as $item) {

    $raw = trim($item['thumbnails_json']);
    $paths = [];

    // -------------------------------
    // STEP 1: Decode or fallback
    // -------------------------------

    $decoded = json_decode($raw, true);

    if (is_array($decoded)) {
        // JSON exists — flatten all elements
        foreach ($decoded as $element) {
            if (is_string($element)) {
                $paths = array_merge(
                    $paths,
                    preg_split('/\s*;\s*/', $element)
                );
            }
        }
    } else {
        // Legacy semicolon string
        $paths = preg_split('/\s*;\s*/', $raw);
    }

    // -------------------------------
    // STEP 2: Normalise assets
    // -------------------------------

    $clean = [];

    foreach ($paths as $p) {
        $p = trim($p);

        if ($p === '') continue;

        // Remove videos
        if (preg_match('/\.mp4$/i', $p)) continue;

        // Remove banner assets
        if (strpos($p, '/banners/') !== false) continue;

        $clean[] = $p;
    }

    $clean = array_values(array_unique($clean));

    // -------------------------------
    // STEP 3: Encode canonical JSON
    // -------------------------------

    $json = json_encode($clean, JSON_UNESCAPED_SLASHES);

    if ($json !== $raw) {
        $update->execute([
            'json' => $json,
            'id'   => $item['itemId']
        ]);
        $changed++;
    }
}

echo "NORMALISATION COMPLETE\n";
echo "Rows updated: {$changed}\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";

