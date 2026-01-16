<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * CONFIG
 */
$excelPath = 'C:/Users/rjfra/OneDrive - TAFE NSW/Cert_IV-Website_Design/Hornsby/Assignments/Sport_Warehouse/Diploma/Database/SportWarehouse_FULL_2026-01-04.xlsx';
$sheetName = 'SportWarehouse_ProductDB';

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

/**
 * DB CONNECT
 */
$pdo = new PDO(
    "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4",
    $db['user'],
    $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/**
 * LOAD EXCEL
 */
$spreadsheet = IOFactory::load($excelPath);
$sheet = $spreadsheet->getSheetByName($sheetName);

if (!$sheet) {
    die("ERROR: Sheet '{$sheetName}' not found.\n");
}

/**
 * READ HEADERS (ROW 1)
 */
$highestColumn = $sheet->getHighestColumn();
$headerRow = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1];

$headers = [];
foreach ($headerRow as $col => $value) {
    $key = trim((string)$value);
    if ($key !== '') {
        $headers[$key] = $col;
    }
}

foreach (['db_itemId', 'external_item_id'] as $required) {
    if (!isset($headers[$required])) {
        die("ERROR: Required column '{$required}' not found in Excel.\n");
    }
}

$dbItemIdCol = $headers['db_itemId'];
$externalCol = $headers['external_item_id'];

/**
 * PREPARE STATEMENTS
 */
$checkStmt = $pdo->prepare(
    "SELECT itemId FROM item WHERE itemId = :itemId"
);

$updateStmt = $pdo->prepare(
    "UPDATE item
     SET external_item_id = :external_item_id
     WHERE itemId = :itemId"
);

/**
 * PROCESS ROWS
 */
$highestRow = $sheet->getHighestRow();

$updated = 0;
$skipped = 0;
$notFound = 0;

echo "=== Import external_item_id ===\n";

for ($r = 2; $r <= $highestRow; $r++) {
    $itemId = trim((string)$sheet->getCell("{$dbItemIdCol}{$r}")->getValue());
    $external = trim((string)$sheet->getCell("{$externalCol}{$r}")->getValue());

    if ($itemId === '' || $external === '') {
        $skipped++;
        continue;
    }

    $checkStmt->execute([':itemId' => $itemId]);
    if (!$checkStmt->fetchColumn()) {
        echo "Row {$r}: itemId {$itemId} NOT FOUND\n";
        $notFound++;
        continue;
    }

    $updateStmt->execute([
        ':external_item_id' => $external,
        ':itemId' => $itemId
    ]);

    $updated++;
}

echo "Updated rows: {$updated}\n";
echo "Skipped rows: {$skipped}\n";
echo "Not found: {$notFound}\n";
echo "=== Done ===\n";



