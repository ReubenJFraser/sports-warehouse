<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$excelPath = 'C:/Users/rjfra/OneDrive - TAFE NSW/Cert_IV-Website_Design/Hornsby/Assignments/Sport_Warehouse/Diploma/Database/SportWarehouse_FULL_2026-01-04.xlsx';

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

/**
 * Fail fast if path is wrong
 */
if (!file_exists($excelPath)) {
    die("ERROR: Excel file not found at:\n{$excelPath}\n");
}

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
 * LOAD EXCEL — FORCE XLSX READER (NO AUTO-DETECT)
 */
$reader = new Xlsx();
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($excelPath);

echo "=== Workbook diagnostics ===\n";
echo "Active sheet: " . $spreadsheet->getActiveSheet()->getTitle() . "\n";
echo "All sheets: " . implode(', ', $spreadsheet->getSheetNames()) . "\n\n";

$sheetName = 'SportWarehouse_ProductDB';
$sheet = $spreadsheet->getSheetByName($sheetName);
if (!$sheet) {
    die("ERROR: Sheet '{$sheetName}' not found.\n");
}
echo "Using sheet: {$sheetName}\n\n";

/**
 * HEADER ROW (ROW 1)
 */
$highestColumn = $sheet->getHighestColumn();
$headerRow = $sheet->rangeToArray("A1:{$highestColumn}1", null, true, true, true)[1];

/**
 * SAFE HEADER MAP
 */
$headers = [];
foreach ($headerRow as $colLetter => $val) {
    $key = trim((string)$val);
    if ($key !== '') {
        $headers[$key] = $colLetter;
    }
}

foreach (['db_itemId', 'external_item_id'] as $required) {
    if (!isset($headers[$required])) {
        echo "Header row values:\n";
        foreach ($headerRow as $c => $v) {
            echo "  {$c} => " . var_export($v, true) . "\n";
        }
        die("\nERROR: Required column '{$required}' not found in header row.\n");
    }
}

$dbItemIdCol = $headers['db_itemId'];
$externalCol = $headers['external_item_id'];

echo "Resolved columns:\n";
echo "  db_itemId => {$dbItemIdCol}\n";
echo "  external_item_id => {$externalCol}\n\n";

/**
 * COUNT NON-EMPTY external_item_id
 */
$highestRow = $sheet->getHighestRow();
$nonEmptyExternal = 0;
for ($r = 2; $r <= $highestRow; $r++) {
    $v = trim((string)($sheet->getCell("{$externalCol}{$r}")->getValue() ?? ''));
    if ($v !== '') {
        $nonEmptyExternal++;
    }
}
echo "Non-empty external_item_id cells: {$nonEmptyExternal} (rows 2..{$highestRow})\n\n";

/**
 * ROW DIAGNOSTICS
 */
$checkStmt = $pdo->prepare(
    "SELECT itemId FROM item WHERE itemId = :itemId"
);

echo "=== Row diagnostics ===\n";
for ($r = 2; $r <= $highestRow; $r++) {
    $dbItemId = trim((string)($sheet->getCell("{$dbItemIdCol}{$r}")->getValue() ?? ''));
    $external  = trim((string)($sheet->getCell("{$externalCol}{$r}")->getValue() ?? ''));

    if ($dbItemId === '') {
        echo "Row {$r}: SKIPPED — empty db_itemId\n";
        continue;
    }

    $checkStmt->execute([':itemId' => $dbItemId]);
    $exists = $checkStmt->fetchColumn();

    echo "Row {$r} | db_itemId='{$dbItemId}' | external_item_id='{$external}' | "
       . ($exists ? "MATCHED" : "NOT FOUND") . "\n";
}

echo "=== End ===\n";



