<?php
require __DIR__ . '/../../vendor/autoload.php';

/**
 * Regenerate Derived_System_Fields
 *
 * PURPOSE:
 * - One-shot regeneration of system-owned fields in Excel
 * - DB → Excel inspection surface
 *
 * SCOPE:
 * - Writes ONLY to Derived_System_Fields
 * - Regenerates rows fully
 * - No authoring, no transforms, no fixes
 */

// --------------------
// CONFIG (explicit)
// --------------------

$excelPath = 'C:/Users/rjfra/OneDrive - TAFE NSW/Cert_IV-Website_Design/Hornsby/Assignments/Sport_Warehouse/Diploma/Database/SportWarehouse_FULL_2026-01-04.xlsx';
$worksheetName = 'Derived_System_Fields';

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

// --------------------
// BOOTSTRAP & GUARDS
// --------------------

if (!file_exists($excelPath)) {
    exit("ERROR: Excel file not found at path:\n$excelPath\n");
}

if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
    exit("ERROR: PhpSpreadsheet is not available. Install via Composer.\n");
}

// --------------------
// DB CONNECT
// --------------------

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
    exit("ERROR: DB connection failed: {$e->getMessage()}\n");
}

// --------------------
// DB READ (identity only)
// --------------------

$sql = "
    SELECT
        itemId,
        thumbnails_json,
        is_active
    FROM item
";

$rows = $pdo->query($sql)->fetchAll();

if ($rows === false) {
    exit("ERROR: DB query returned no results.\n");
}

// --------------------
// EXCEL OPEN
// --------------------

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load($excelPath);
$sheet = $spreadsheet->getSheetByName($worksheetName);

if ($sheet === null) {
    exit("ERROR: Worksheet '{$worksheetName}' does not exist.\n");
}

$headerRow = 2;
$dataStartRow = 3;

// --------------------
// HEADER VALIDATION
// --------------------

$expectedHeaders = [
    'external_product_code',
    'db_itemId',
    'db_is_active',
    'thumbnails_json'
];

$actualHeaders = [];
$col = 1;

foreach ($expectedHeaders as $_) {
    $actualHeaders[] = trim((string)$sheet->getCell([$col, $headerRow])->getValue());
    $col++;
}

if ($actualHeaders !== $expectedHeaders) {
    exit(
        "ERROR: Header mismatch in '{$worksheetName}'.\n" .
        "Expected: " . implode(', ', $expectedHeaders) . "\n" .
        "Found:    " . implode(', ', $actualHeaders) . "\n"
    );
}

// --------------------
// CLEAR DATA ROWS
// --------------------

$highestRow = $sheet->getHighestRow();

if ($highestRow >= $dataStartRow) {
    $sheet->removeRow($dataStartRow, $highestRow - $dataStartRow + 1);
}

// --------------------
// WRITE FRESH ROWS
// --------------------

$currentRow = $dataStartRow;

foreach ($rows as $row) {
    $sheet->setCellValue([1, $currentRow], null);                       // external_product_code
    $sheet->setCellValue([2, $currentRow], $row['itemId']);             // db_itemId
    $sheet->setCellValue([3, $currentRow], $row['is_active']);          // db_is_active
    $sheet->setCellValue([4, $currentRow], $row['thumbnails_json']);    // thumbnails_json
    $currentRow++;
}

// --------------------
// SAVE & EXIT
// --------------------

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($excelPath);

$count = count($rows);

echo "SUCCESS: Regenerated '{$worksheetName}'.\n";
echo "Rows written: {$count}\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";


