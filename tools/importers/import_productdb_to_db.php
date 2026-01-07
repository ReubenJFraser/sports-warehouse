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
while ($subcategory = trim((string)$mappingsSheet->getCell("C{$row}")->getValue())) {
    $defaultSeason = trim((string)$mappingsSheet->getCell("E{$row}")->getValue());

    if ($defaultSeason === '') {
        $subcategoryToSeason[$subcategory] = null;
    } elseif (in_array($defaultSeason, $ALLOWED_SEASONAL, true)) {
        $subcategoryToSeason[$subcategory] = $defaultSeason;
    } else {
        exit("ERROR: Invalid seasonal_context '{$defaultSeason}' in Mappings row {$row}\n");
    }
    $row++;
}

// --------------------
// READ PRODUCTDB
// --------------------

$productSheet = $spreadsheet->getSheetByName('ProductDB');
if (!$productSheet) {
    exit("ERROR: ProductDB worksheet missing\n");
}

$row = 2;
$updated = 0;

while ($externalCode = trim((string)$productSheet->getCell("A{$row}")->getValue())) {

    $subcategory = trim((string)$productSheet->getCell("D{$row}")->getValue());

    $seasonalContext = $subcategoryToSeason[$subcategory] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO item (
            external_product_code,
            brand,
            category,
            subcategory,
            price,
            sale_price,
            is_active,
            seasonal_context
        ) VALUES (
            :external_product_code,
            :brand,
            :category,
            :subcategory,
            :price,
            :sale_price,
            :is_active,
            :seasonal_context
        )
        ON DUPLICATE KEY UPDATE
            brand = VALUES(brand),
            category = VALUES(category),
            subcategory = VALUES(subcategory),
            price = VALUES(price),
            sale_price = VALUES(sale_price),
            is_active = VALUES(is_active),
            seasonal_context = VALUES(seasonal_context)
    ");

    $stmt->execute([
        ':external_product_code' => $externalCode,
        ':brand'        => trim((string)$productSheet->getCell("B{$row}")->getValue()),
        ':category'     => trim((string)$productSheet->getCell("C{$row}")->getValue()),
        ':subcategory'  => $subcategory,
        ':price'        => $productSheet->getCell("E{$row}")->getValue(),
        ':sale_price'   => $productSheet->getCell("F{$row}")->getValue(),
        ':is_active'    => (int)$productSheet->getCell("G{$row}")->getValue(),
        ':seasonal_context' => $seasonalContext
    ]);

    $updated++;
    $row++;
}

echo "SUCCESS: {$updated} products imported\n";



