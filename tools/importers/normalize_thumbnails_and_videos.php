<?php
/**
 * Robust normalization of thumbnails_json
 * - Converts ANY legacy format to real JSON
 * - Separates images vs videos
 * - Relocates videos to item.videos (merge-safe)
 */

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

$imageExts = ['png', 'jpg', 'jpeg', 'webp'];
$videoExts = ['mp4', 'webm', 'mov'];

function isValidJsonArray(string $value): bool {
    $decoded = json_decode($value, true);
    return is_array($decoded);
}

function splitLegacyAssets(string $raw): array {
    // Normalize separators to semicolon
    $raw = str_replace(["\r\n", "\n", "\r"], ';', $raw);
    return array_values(array_filter(array_map('trim', explode(';', $raw))));
}

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4",
        $db['user'],
        $db['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    exit("DB connection failed: {$e->getMessage()}\n");
}

$rows = $pdo->query("
    SELECT itemId, thumbnails_json, videos
    FROM item
")->fetchAll();

$update = $pdo->prepare("
    UPDATE item
    SET thumbnails_json = :thumbs,
        videos = :videos
    WHERE itemId = :itemId
");

$updated = 0;

foreach ($rows as $row) {
    $itemId = $row['itemId'];
    $raw = trim((string)$row['thumbnails_json']);

    if ($raw === '') {
        continue;
    }

    // Skip already-correct JSON
    if (isValidJsonArray($raw)) {
        continue;
    }

    $assets = splitLegacyAssets($raw);

    $images = [];
    $videos = [];

    foreach ($assets as $path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, $imageExts, true)) {
            $images[] = $path;
        } elseif (in_array($ext, $videoExts, true)) {
            $videos[] = $path;
        }
    }

    // Merge with existing videos
    $existingVideos = [];
    if (!empty($row['videos'])) {
        $decoded = json_decode($row['videos'], true);
        if (is_array($decoded)) {
            $existingVideos = $decoded;
        }
    }

    $mergedVideos = array_values(array_unique(array_merge($existingVideos, $videos)));

    // DEBUG: explicit visibility for Zenvy
    if ($itemId == 27) {
        echo "\n--- DEBUG itemId 27 ---\n";
        echo "BEFORE thumbnails_json:\n{$raw}\n";
        echo "AFTER thumbnails_json:\n" . json_encode($images, JSON_UNESCAPED_SLASHES) . "\n";
        echo "VIDEOS:\n" . json_encode($mergedVideos, JSON_UNESCAPED_SLASHES) . "\n";
        echo "----------------------\n\n";
    }

    $update->execute([
        ':thumbs' => json_encode($images, JSON_UNESCAPED_SLASHES),
        ':videos' => $mergedVideos ? json_encode($mergedVideos, JSON_UNESCAPED_SLASHES) : null,
        ':itemId' => $itemId
    ]);

    $updated++;
}

echo "Normalization complete.\n";
echo "Rows updated: {$updated}\n";


