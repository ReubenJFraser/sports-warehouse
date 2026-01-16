<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// --------------------
// CONFIG
// --------------------

$excelPath = 'C:/Users/rjfra/OneDrive - TAFE NSW/Cert_IV-Website_Design/Hornsby/Assignments/Sport_Warehouse/Diploma/Database/SportWarehouse_FULL_2026-01-04.xlsx';

$db = [
    'host'   => 'localhost',
    'dbname' => 'sportswh',
    'user'   => 'root',
    'pass'   => ''
];

$ALLOWED_SEASONAL = ['warm_weather', 'cool_weather'];

// --------------------
// DB CONNECT
// --------------------

$pdo = new PDO(
    "mysql:host={$db['host']};dbname={$db['dbname']};charset=utf8mb4",
    $db['user'],
    $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// --------------------
// LOAD EXCEL
// --------------------

$spreadsheet = IOFactory::load($excelPath);

// --------------------
// BUILD MAPPINGS LOOKUP
// --------------------

$mappingsSheet = $spreadsheet->getSheetByName('Mappings');
if (!$mappingsSheet) {
    exit("ERROR: Mappings worksheet missing\n");
}

$subcategoryToSeason = [];

$row = 2;
while ($subCategory = trim((string)$mappingsSheet->getCell("C{$row}")->getValue())) {

    $defaultSeason = trim((string)$mappingsSheet->getCell("E{$row}")->getValue());

    if ($defaultSeason === '') {
        $subcategoryToSeason[$subCategory] = null;
    } elseif (in_array($defaultSeason, $ALLOWED_SEASONAL, true)) {
        $subcategoryToSeason[$subCategory] = $defaultSeason;
    } else {
        exit("ERROR: Invalid seasonal_context '{$defaultSeason}' in Mappings row {$row}\n");
    }

    $row++;
}

// --------------------
// READ PRODUCTDB
// --------------------

$productSheet = $spreadsheet->getSheetByName('SportWarehouse_ProductDB');
if (!$productSheet) {
    exit("ERROR: SportWarehouse_ProductDB worksheet missing\n");
}

$stmt = $pdo->prepare("
    UPDATE item
    SET seasonal_context = :seasonal_context
    WHERE itemId = :itemId
");

$row = 2;
$updated = 0;

while ($itemId = (int)$productSheet->getCell("Y{$row}")->getValue()) {

    $subCategory = trim((string)$productSheet->getCell("C{$row}")->getValue());
    $seasonalContext = $subcategoryToSeason[$subCategory] ?? null;

    $stmt->execute([
        ':itemId'           => $itemId,
        ':seasonal_context' => $seasonalContext
    ]);

    $updated += $stmt->rowCount();
    $row++;
}

echo "SUCCESS: {$updated} rows updated\n";



