<?php

declare(strict_types=1);

/**
 * Read-only CSV ↔ MySQL image-path reconciliation report generator.
 *
 * Usage:
 *   php tools/reports/image-sync-reconciliation.php
 *   C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe tools\reports\image-sync-reconciliation.php
 */
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "CLI-only.\n"); exit(1);} 
$root = dirname(__DIR__, 2);
$csvPath = $root . '/docs/data/SportWarehouse_ProductDB.csv';
$reportPath = $root . '/docs/operations/generated/image-sync-reconciliation-report.csv';
$summaryPath = $root . '/docs/operations/generated/image-sync-reconciliation-summary.md';
$imagesRoot = $root . '/images';
$reportHeaders = ['match_status','confidence','csv_row_number','csv_db_itemId','mysql_itemId','csv_brand','mysql_brand','csv_itemName','mysql_itemName','csv_gender','mysql_gender_or_demographic','csv_images','csv_thumbnails_json','mysql_thumbnails_json','thumbnails_compare_status','csv_videos','mysql_hero_image','mysql_chosen_image','override_chosen_image','hero_status','override_status','filesystem_status','recommended_action','notes'];
$counts = ['total CSV rows'=>0,'total active MySQL items considered'=>0,'matched rows'=>0,'in_sync'=>0,'mysql_stale_relative_to_csv'=>0,'csv_future_or_staging'=>0,'csv_only_candidate'=>0,'mysql_only_legacy'=>0,'ambiguous_match'=>0,'manual_review_required'=>0,'rows where CSV thumbnails_json is nonblank and MySQL thumbnails_json differs'=>0,'rows where local files are missing for CSV paths'=>0,'rows where local files are missing for MySQL paths'=>0];
$csvKeySamples=[]; $mysqlKeySamples=[]; $blankCsvKeys=0; $blankMysqlKeys=0; $csvKeyCounts=[]; $mysqlKeyCounts=[];
function mustSelect(string $sql): void { if (!preg_match('/^SELECT\b/i', ltrim($sql))) throw new RuntimeException('Refusing non-SELECT SQL.'); }
function qAll(PDO $pdo, string $sql, array $params=[]): array { mustSelect($sql); $s=$pdo->prepare($sql); $s->execute($params); return $s->fetchAll(PDO::FETCH_ASSOC);} 
function norm(?string $v): string { return preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string)$v))) ?? ''; }
function tableColumns(PDO $pdo, string $table): array {
    $rows = qAll($pdo, "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=:t", ['t'=>$table]);
    return array_values(array_map(static fn($r)=>(string)$r['COLUMN_NAME'], $rows));
}
function pickFirstHeader(array $headersMap, array $candidates): array {
    foreach ($candidates as $name) {
        if (isset($headersMap[$name])) return [$name, $headersMap[$name]];
    }
    return ['', -1];
}

function normalizeCsvHeader(string $name): string {
    $withoutBom = preg_replace('/^\xEF\xBB\xBF/u', '', $name) ?? $name;
    $withoutBom = preg_replace('/^\x{FEFF}/u', '', $withoutBom) ?? $withoutBom;
    return trim($withoutBom);
}
function pickFirstMysqlColumn(array $set, array $candidates): string {
    foreach ($candidates as $name) {
        if (isset($set[$name])) return $name;
    }
    return '';
}
function closePairs(array $leftCols, array $rightCols): array {
    $pairs = [];
    foreach ($leftCols as $lc) {
        $nl = norm($lc);
        if ($nl === '') continue;
        $best = '';
        $bestDist = 999;
        foreach ($rightCols as $rc) {
            $nr = norm($rc);
            if ($nr === '') continue;
            similar_text($nl, $nr, $pct);
            $dist = levenshtein($nl, $nr);
            if ($pct >= 72.0 || $dist <= 4) {
                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $best = $rc;
                }
            }
        }
        if ($best !== '') $pairs[] = "{$lc} ~ {$best}";
    }
    return array_values(array_unique($pairs));
}
function parsePathList(?string $v): array { $v=trim((string)$v); if($v==='') return []; return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/',$v) ?: []), fn($x)=>$x!=='')); }
function parseJsonArray(?string $v): array { $v=trim((string)$v); if($v==='') return []; $d=json_decode($v,true); if(!is_array($d)) return []; return array_values(array_filter(array_map(fn($x)=>trim((string)$x),$d),fn($x)=>$x!=='')); }
function pathExistsLocal(string $imagesRoot, string $path): bool { $p=ltrim(str_replace('\\','/',trim($path)),'/'); if($p==='') return true; if(str_starts_with($p,'images/')) return is_file(dirname($imagesRoot).'/'.$p); return is_file($imagesRoot.'/'.$p); }
function thumbsCompare(string $csv, string $mysql): string { $c=parseJsonArray($csv); $m=parseJsonArray($mysql); if(!$c&&!$m) return 'both_blank'; if(!$c&&$m) return 'csv_blank_mysql_nonblank'; if($c&&!$m) return 'mysql_blank_csv_nonblank'; if($c===$m) return 'identical'; $a=$c;$b=$m;sort($a);sort($b); if($a===$b) return 'same_set_different_order'; if(count(array_filter($c,fn($p)=>str_contains($p,'/brands/')))>0&&count(array_filter($m,fn($p)=>!str_contains($p,'/brands/')))>0) return 'csv_has_brands_path_mysql_legacy'; if(count(array_filter($c,fn($p)=>substr_count($p,'/')>=3))>0) return 'csv_has_extra_path_segments'; return 'csv_different_folder_or_filename'; }
require $root . '/db.php';
$itemCols = tableColumns($pdo, 'item');
$itemColsSet = array_fill_keys($itemCols, true);
$preferredDemographicCols = ['gender','age_group','size_type','demographic'];
$chosenDemographicCol = '';
foreach ($preferredDemographicCols as $c) {
    if (isset($itemColsSet[$c])) { $chosenDemographicCol = $c; break; }
}
$optionalItemCols = ['categoryName','subcategory','hero_image','thumbnails_json','chosen_image','is_active'];
$missingOptionalCols = [];
foreach (array_merge($optionalItemCols, $preferredDemographicCols) as $c) {
    if (!isset($itemColsSet[$c])) $missingOptionalCols[] = $c;
}
$selectExpr = [
    'itemId',
    'brand',
    'itemName',
    ($chosenDemographicCol !== '' ? $chosenDemographicCol : 'NULL') . ' AS mysql_gender_or_demographic',
    (isset($itemColsSet['thumbnails_json']) ? 'thumbnails_json' : 'NULL') . ' AS thumbnails_json',
    (isset($itemColsSet['hero_image']) ? 'hero_image' : 'NULL') . ' AS hero_image',
    (isset($itemColsSet['chosen_image']) ? 'chosen_image' : 'NULL') . ' AS chosen_image',
    (isset($itemColsSet['categoryName']) ? 'categoryName' : 'NULL') . ' AS categoryName',
    (isset($itemColsSet['subcategory']) ? 'subcategory' : 'NULL') . ' AS subcategory',
    (isset($itemColsSet['is_active']) ? 'is_active' : 'NULL') . ' AS is_active',
];
$items=qAll($pdo,"SELECT ".implode(', ', $selectExpr)." FROM item");
$counts['total active MySQL items considered']=count($items);
$hasOverride=(int)(qAll($pdo,"SELECT COUNT(*) c FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='hero_override'")[0]['c']??0)>0;
$overrideById=[]; if($hasOverride){ foreach(qAll($pdo,"SELECT itemId, chosen_image FROM hero_override") as $r){$overrideById[(int)$r['itemId']]=(string)($r['chosen_image']??'');}}
$index=[];$itemById=[]; foreach($items as $it){$id=(int)$it['itemId'];$itemById[$id]=$it;$k=norm($it['brand']).'|'.norm($it['itemName']);$index[$k][]=$it;$mysqlKeyCounts[$k]=($mysqlKeyCounts[$k]??0)+1;if(count($mysqlKeySamples)<10)$mysqlKeySamples[]=$k;if($k==='|')$blankMysqlKeys++;}
@mkdir(dirname($reportPath),0777,true);
$in=fopen($csvPath,'rb'); $out=fopen($reportPath,'wb'); fputcsv($out,$reportHeaders); $rawHeaders=fgetcsv($in)?:[]; $headers=[]; foreach($rawHeaders as $i=>$name){$headers[$i]=normalizeCsvHeader((string)$name);} $h=[]; foreach($headers as $i=>$name){$h[$name]=$i;}
$rawFirstHeader = (string)($rawHeaders[0] ?? '');
$rawFirstHeaderHex = strtoupper(bin2hex($rawFirstHeader));
$normalizedFirstHeader = (string)($headers[0] ?? '');
[$csvBrandHeader] = pickFirstHeader($h, ['brand','Brand','brandName','brand_name','vendor','merchant','label']);
[$csvItemHeader] = pickFirstHeader($h, ['itemName','item_name','Item Name','name','product_name','title','productTitle']);
[$csvDbItemIdHeader] = pickFirstHeader($h, ['db_itemId','db_item_id','itemId','item_id','mysql_itemId']);
[$csvGenderHeader] = pickFirstHeader($h, ['gender','Gender','demographic','age_group','size_type']);
[$csvImagesHeader] = pickFirstHeader($h, ['images','image_paths','imagePathList']);
[$csvThumbsHeader] = pickFirstHeader($h, ['thumbnails_json','thumbnail_json','thumbnails']);
[$csvVideosHeader] = pickFirstHeader($h, ['videos','video_urls']);
$mysqlBrandSource = pickFirstMysqlColumn($itemColsSet, ['brand','brandName','brand_name']);
$mysqlItemNameSource = pickFirstMysqlColumn($itemColsSet, ['itemName','item_name','name','product_name','title']);
$rowNum=1; $matched=[];
while(($line=fgetcsv($in))!==false){$rowNum++;$counts['total CSV rows']++; $rowAssoc=[]; foreach($h as $headerName=>$idx){$rowAssoc[$headerName]=isset($line[$idx])?trim((string)$line[$idx]):'';} $get=fn($k)=>$rowAssoc[$k]??'';
$csvBrand=$csvBrandHeader!==''?$get($csvBrandHeader):'';$csvName=$csvItemHeader!==''?$get($csvItemHeader):'';$csvDbId=$csvDbItemIdHeader!==''?$get($csvDbItemIdHeader):'';$csvGender=$csvGenderHeader!==''?$get($csvGenderHeader):'';$csvImages=$csvImagesHeader!==''?$get($csvImagesHeader):'';$csvThumbs=$csvThumbsHeader!==''?$get($csvThumbsHeader):'';$csvVideos=$csvVideosHeader!==''?$get($csvVideosHeader):'';
$csvKey=norm($csvBrand).'|'.norm($csvName);$cands=$index[$csvKey]??[]; $sel=null;$status='manual_review_required';$conf='low';$notes=[];$csvKeyCounts[$csvKey]=($csvKeyCounts[$csvKey]??0)+1;if(count($csvKeySamples)<10)$csvKeySamples[]=$csvKey;if($csvKey==='|')$blankCsvKeys++;
if(count($cands)===1){$sel=$cands[0];$conf='high';} elseif(count($cands)>1){$status='ambiguous_match';$notes[]='Multiple MySQL rows match normalized brand+itemName.';} else {$status=$csvDbId===''?'csv_future_or_staging':'csv_only_candidate';$conf=$csvDbId===''?'medium':'low';$notes[]='No MySQL match by normalized brand+itemName.';}
$mysql=['id'=>'','brand'=>'','name'=>'','gender'=>'','thumbs'=>'','hero'=>'','chosen'=>'','override'=>''];$heroStatus='protected_fields_no_blind_overwrite';$overrideStatus=$hasOverride?'override_table_present':'override_table_missing';$thumbStatus='manual_review_required';$fs='not_checked';$action='manual_review_required';
if($sel){$mysql=['id'=>(string)$sel['itemId'],'brand'=>(string)$sel['brand'],'name'=>(string)$sel['itemName'],'gender'=>(string)($sel['mysql_gender_or_demographic']??''),'thumbs'=>(string)($sel['thumbnails_json']??''),'hero'=>(string)($sel['hero_image']??''),'chosen'=>(string)($sel['chosen_image']??''),'override'=>(string)($overrideById[(int)$sel['itemId']]??'')];$matched[(int)$sel['itemId']]=true;$thumbStatus=thumbsCompare($csvThumbs,$mysql['thumbs']);
if($csvDbId!==''&&(int)$csvDbId===(int)$sel['itemId'])$notes[]='db_itemId agrees with matched MySQL itemId.'; if($csvDbId!==''&&(int)$csvDbId!==(int)$sel['itemId'])$notes[]='db_itemId differs from matched MySQL itemId (clue only).';
$csvMissing=0; foreach(array_merge(parsePathList($csvImages),parseJsonArray($csvThumbs)) as $p){if(!pathExistsLocal($imagesRoot,$p))$csvMissing++;}
$myMissing=0; foreach(array_merge(parseJsonArray($mysql['thumbs']),[$mysql['hero'],$mysql['chosen'],$mysql['override']]) as $p){if(trim($p)!==''&&!pathExistsLocal($imagesRoot,$p))$myMissing++;}
if($csvMissing>0)$counts['rows where local files are missing for CSV paths']++; if($myMissing>0)$counts['rows where local files are missing for MySQL paths']++; $fs="csv_missing={$csvMissing};mysql_missing={$myMissing}";
if(in_array($thumbStatus,['identical','same_set_different_order'],true)){$status='in_sync';$action='no_action';} else {$status='mysql_stale_relative_to_csv';$action='review_update_mysql_thumbnails_from_csv'; if(parseJsonArray($csvThumbs)&&$mysql['thumbs']!==$csvThumbs)$counts['rows where CSV thumbnails_json is nonblank and MySQL thumbnails_json differs']++;}}
if($sel)$counts['matched rows']++;
fputcsv($out,[$status,$conf,$rowNum,$csvDbId,$mysql['id'],$csvBrand,$mysql['brand'],$csvName,$mysql['name'],$csvGender,$mysql['gender'],$csvImages,$csvThumbs,$mysql['thumbs'],$thumbStatus,$csvVideos,$mysql['hero'],$mysql['chosen'],$mysql['override'],$heroStatus,$overrideStatus,$fs,$action,implode(' | ',array_merge($notes,['Never blind-overwrite item.hero_image, item.chosen_image, hero_override.chosen_image.']))]);
}
foreach($itemById as $id=>$it){if(isset($matched[$id]))continue; $counts['mysql_only_legacy']++; fputcsv($out,['mysql_only_legacy','medium','','',(string)$id,'',(string)$it['brand'],'',(string)$it['itemName'],'',(string)($it['mysql_gender_or_demographic']??''),'','',(string)($it['thumbnails_json']??''),'manual_review_required','',(string)($it['hero_image']??''),(string)($it['chosen_image']??''),(string)($overrideById[$id]??''),'protected_fields_no_blind_overwrite',$hasOverride?'override_table_present':'override_table_missing','not_checked','manual_review_required','Present in MySQL local snapshot but not matched from newer CSV catalog rows.']);}
fclose($in); fclose($out);
$reportIn=fopen($reportPath,'rb');$reportHdr=fgetcsv($reportIn)?:[];$statusIdx=array_search('match_status',$reportHdr,true);if($statusIdx!==false){while(($r=fgetcsv($reportIn))!==false){$st=trim((string)($r[$statusIdx]??''));if($st!==''&&isset($counts[$st]))$counts[$st]++;}}fclose($reportIn);
$runtimeNotes = [];
if ($chosenDemographicCol === '') $runtimeNotes[] = "No demographic/report source column available; mysql_gender_or_demographic emitted as blank.";
else $runtimeNotes[] = "Demographic/report source column selected from item table: {$chosenDemographicCol}.";
if ($missingOptionalCols) $runtimeNotes[] = 'Missing optional item columns detected at runtime: ' . implode(', ', $missingOptionalCols) . '.';
$dupCsv=count(array_filter($csvKeyCounts, static fn($c)=>$c>1));
$dupMysql=count(array_filter($mysqlKeyCounts, static fn($c)=>$c>1));
$csvCols = $headers;
$mysqlCols = $itemCols;
$csvNotMysql = array_values(array_diff($csvCols, $mysqlCols));
$mysqlNotCsv = array_values(array_diff($mysqlCols, $csvCols));
$likelyPairs = closePairs($csvNotMysql, $mysqlNotCsv);
$counts['mysql_only_legacy'] = min($counts['mysql_only_legacy'], $counts['total active MySQL items considered']);
$md=["# Image Sync Reconciliation Summary","","Generated: ".gmdate('Y-m-d H:i:s')." UTC","","## Counts"]; foreach($counts as $k=>$v){$md[]="- {$k}: {$v}";} $md[]="";
$md[]="## Source context";
$md[]="- CSV is the newer Excel-derived product source.";
$md[]="- MySQL appears to be an older local database snapshot.";
$md[]="- mismatch may come from broader schema/catalogue restructuring (columns, naming, categorisation, new rows, and db_itemId drift), not only image paths.";
$md[]="";
$md[]="## Detected source columns used for matching";
$md[]="- CSV brand source: " . ($csvBrandHeader !== '' ? $csvBrandHeader : '(not found)');
$md[]="- CSV itemName source: " . ($csvItemHeader !== '' ? $csvItemHeader : '(not found)');
$md[]="- MySQL brand source: " . ($mysqlBrandSource !== '' ? $mysqlBrandSource : 'brand (selected query alias)');
$md[]="- MySQL itemName source: " . ($mysqlItemNameSource !== '' ? $mysqlItemNameSource : 'itemName (selected query alias)');
$md[]="";
$md[]="## Detected headers / columns";
$md[]="- CSV headers (" . count($csvCols) . "): " . ($csvCols ? implode(', ', $csvCols) : '(none)');
$md[]="- MySQL item columns (" . count($mysqlCols) . "): " . ($mysqlCols ? implode(', ', $mysqlCols) : '(none)');
$md[]="- CSV first header raw hex/codepoints before normalization: " . ($rawFirstHeaderHex !== '' ? $rawFirstHeaderHex : '(empty)');
$md[]="- CSV first header after normalization: " . ($normalizedFirstHeader !== '' ? $normalizedFirstHeader : '(empty)');
$md[]="";
$md[]="## Schema/column comparison";
$md[]="- CSV columns not present in MySQL item (" . count($csvNotMysql) . "): " . ($csvNotMysql ? implode(', ', $csvNotMysql) : '(none)');
$md[]="- MySQL item columns not present in CSV (" . count($mysqlNotCsv) . "): " . ($mysqlNotCsv ? implode(', ', $mysqlNotCsv) : '(none)');
$md[]="- likely equivalent columns if names are close: " . ($likelyPairs ? implode('; ', array_slice($likelyPairs, 0, 30)) : '(none)');
$md[]="";
$md[]="## Key diagnostics";
$md[]="- first 10 normalized CSV brand+itemName keys: " . ($csvKeySamples ? implode(', ', $csvKeySamples) : '(none)');
$md[]="- first 10 normalized MySQL brand+itemName keys: " . ($mysqlKeySamples ? implode(', ', $mysqlKeySamples) : '(none)');
$md[]="- blank CSV key count: {$blankCsvKeys}";
$md[]="- blank MySQL key count: {$blankMysqlKeys}";
$md[]="- duplicate CSV key count: {$dupCsv}";
$md[]="- duplicate MySQL key count: {$dupMysql}";
$md[]="";
$md[]="## Image comparison notes";
$md[]='Image-path/thumbnails comparison is one section of this analysis only; it is not treated as the sole root cause explanation.';
$md[]="";
$md[]="## Runtime schema notes"; foreach($runtimeNotes as $n){$md[]="- {$n}";} $md[]=""; $md[]='Interpretation note: zero matches can be legitimate evidence that MySQL is stale/legacy relative to CSV; this report does not assume a matching bug.'; $md[]='This is a read-only analysis. SELECT-only safeguards remain. No SQL writes/migrations/repairs/importers were executed.'; file_put_contents($summaryPath,implode("\n",$md)."\n");
echo "Report: {$reportPath}\nSummary: {$summaryPath}\n"; foreach($counts as $k=>$v) echo "{$k}: {$v}\n";
