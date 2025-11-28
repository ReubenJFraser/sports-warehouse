<?php
/**
 * scripts/color-tag-images.php
 *
 * Auto-tag product colors (multi-color aware).
 *  - Scans images on disk
 *  - Maps pixels to a named palette
 *  - Stores top1/top2 with percentages per image
 *  - Aggregates to item-level (color_primary, color_secondary, is_multicolor)
 *
 * Usage (from project root):
 *   php scripts/color-tag-images.php --write
 * Options:
 *   --write                 actually write to DB (otherwise dry-run)
 *   --only-missing          skip images already present in item_image_color
 *   --limit=100             process first N items
 *   --itemId=27             process only certain itemId (repeatable)
 *   --p1=65                 primary threshold (top1% >= p1 => single-color)
 *   --p2=25                 secondary threshold (top2% >= p2 => multi-color)
 *   --alpha=96              alpha cutoff (0..127) — higher ignores more transparent pixels
 *   --downscale=64          sampling dimension (NxN); 8..256 sensible
 *   --ignore-skin=1|0       exclude likely skin pixels (default from config, usually 1)
 */

declare(strict_types=1);

$projectRoot = realpath(__DIR__ . '/..');
$imagesBase  = $projectRoot . DIRECTORY_SEPARATOR . 'images';

require $projectRoot . '/db.php'; // provides $pdo

// NEW: load canonical palette config and color normalization helpers
$cfg = @require $projectRoot . '/config/color.php';
if (!is_array($cfg)) $cfg = [];

$CANONICAL_PALETTE = $cfg['palette'] ?? [
  'black','white','grey','navy','blue','teal','green','olive',
  'yellow','orange','red','pink','purple','brown','beige',
];

require_once $projectRoot . '/inc/color-normalize.php';

// ---------- Args ----------
$args  = array_slice($argv, 1);
$flags = [
  'write'        => false,
  'only_missing' => false,
  'limit'        => null,
  'itemIds'      => [],
  // defaults sourced from config/color.php (with safe fallbacks)
  'p1'           => (int)($cfg['p1_min']       ?? 65),   // primary threshold %
  'p2'           => (int)($cfg['p2_min']       ?? 25),   // secondary threshold %
  'alpha'        => (int)($cfg['alpha_cutoff'] ?? 96),   // 0..127 (GD alpha)
  'downscale'    => (int)($cfg['downscale']    ?? 64),   // 8..256 sensible
  'ignore_skin'  => (bool)($cfg['ignore_skin'] ?? true),
];

foreach ($args as $a) {
  if ($a === '--write') $flags['write'] = true;
  elseif ($a === '--only-missing') $flags['only_missing'] = true;
  elseif (preg_match('/^--limit=(\d+)/', $a, $m)) $flags['limit'] = (int)$m[1];
  elseif (preg_match('/^--itemId=(\d+)/', $a, $m)) $flags['itemIds'][] = (int)$m[1];
  elseif (preg_match('/^--p1=(\d+)/', $a, $m)) $flags['p1'] = max(1, min(99, (int)$m[1]));
  elseif (preg_match('/^--p2=(\d+)/', $a, $m)) $flags['p2'] = max(1, min(99, (int)$m[1]));
  elseif (preg_match('/^--alpha=(\d{1,3})$/', $a, $m)) $flags['alpha'] = max(0, min(127, (int)$m[1]));
  elseif (preg_match('/^--downscale=(\d{1,3})$/', $a, $m)) $flags['downscale'] = max(8, min(256, (int)$m[1]));
  elseif (preg_match('/^--ignore-skin=(0|1)$/', $a, $m)) $flags['ignore_skin'] = ($m[1] === '1');
  else { fwrite(STDERR, "Unknown option: $a\n"); exit(2); }
}

// ---------- Palette (editable) ----------
$PALETTE = [
  'black'  => [0,0,0],
  'white'  => [255,255,255],
  'grey'   => [128,128,128],
  'navy'   => [10,20,60],
  'blue'   => [50,100,200],
  'teal'   => [20,120,120],
  'green'  => [40,140,60],
  'olive'  => [110,110,30],
  'yellow' => [235,210,60],
  'orange' => [240,140,50],
  'red'    => [200,40,40],
  'pink'   => [220,120,180],
  'purple' => [120,70,160],
  'brown'  => [110,70,50],
  'beige'  => [220,200,170],
];

// ---------- Color space helpers ----------
function rgb2xyz($r, $g, $b): array {
  $r/=255; $g/=255; $b/=255;
  $r = ($r>0.04045)? pow(($r+0.055)/1.055,2.4):($r/12.92);
  $g = ($g>0.04045)? pow(($g+0.055)/1.055,2.4):($g/12.92);
  $b = ($b>0.04045)? pow(($b+0.055)/1.055,2.4):($b/12.92);
  return [
    $r*0.4124+$g*0.3576+$b*0.1805,
    $r*0.2126+$g*0.7152+$b*0.0722,
    $r*0.0193+$g*0.1192+$b*0.9505
  ];
}
function xyz2lab($x,$y,$z): array {
  $xr=$x/0.95047; $yr=$y/1.00000; $zr=$z/1.08883;
  $fx = $xr>0.008856? pow($xr,1/3):(7.787*$xr+16/116);
  $fy = $yr>0.008856? pow($yr,1/3):(7.787*$yr+16/116);
  $fz = $zr>0.008856? pow($zr,1/3):(7.787*$zr+16/116);
  return [116*$fy-16, 500*($fx-$fy), 200*($fy-$fz)];
}
function rgb2lab($r,$g,$b): array { [$x,$y,$z]=rgb2xyz($r,$g,$b); return xyz2lab($x,$y,$z); }
function labD(array $a,array $b): float { return sqrt(($a[0]-$b[0])**2+($a[1]-$b[1])**2+($a[2]-$b[2])**2); }

$PALETTE_LAB=[];
foreach ($PALETTE as $name=>$rgb) $PALETTE_LAB[$name]=rgb2lab($rgb[0],$rgb[1],$rgb[2]);

// ---------- Image I/O ----------
function is_supported_img(string $path): bool {
  return preg_match('~\.(png|jpe?g|webp)$~i', $path) === 1;
}
function publicToDisk(string $rel, string $projectRoot): string {
  $rel = ltrim($rel, '/\\');
  return $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
}
function loadImage(string $abs) {
  $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
  if (!is_file($abs)) return null;
  switch ($ext) {
    case 'png':  return @imagecreatefrompng($abs);
    case 'jpg':
    case 'jpeg': return @imagecreatefromjpeg($abs);
    case 'webp': return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($abs) : null;
    default:     return null;
  }
}

// ---------- NEW: pixel/alpha + skin helpers ----------
function rgbaAt($im, int $x, int $y): array {
  $idx = imagecolorat($im, $x, $y);
  $a = ($idx & 0x7F000000) >> 24; // 0..127 (0 = opaque, 127 = fully transparent)
  $r = ($idx >> 16) & 0xFF;
  $g = ($idx >>  8) & 0xFF;
  $b =  $idx        & 0xFF;
  return [$r,$g,$b,$a];
}
function rgb2hsv(int $r, int $g, int $b): array {
  $r/=255; $g/=255; $b/=255;
  $max = max($r,$g,$b); $min = min($r,$g,$b);
  $d = $max - $min;
  $h = 0.0;
  if ($d != 0.0) {
    if ($max === $r)      { $h = fmod((($g - $b) / $d), 6); }
    elseif ($max === $g)  { $h = (($b - $r) / $d) + 2; }
    else                  { $h = (($r - $g) / $d) + 4; }
    $h *= 60.0; if ($h < 0) $h += 360.0;
  }
  $s = ($max == 0.0) ? 0.0 : ($d / $max);
  $v = $max;
  return [$h,$s,$v];
}
function rgb2ycbcr(int $r, int $g, int $b): array {
  $y  = (  0.257*$r + 0.504*$g + 0.098*$b) + 16;
  $cb = ( -0.148*$r - 0.291*$g + 0.439*$b) + 128;
  $cr = (  0.439*$r - 0.368*$g - 0.071*$b) + 128;
  return [$y,$cb,$cr];
}
// Conservative skin detector (YCbCr ∩ HSV)
function is_skin_pixel(int $r,int $g,int $b): bool {
  [$y,$cb,$cr] = rgb2ycbcr($r,$g,$b);
  $yc_ok = ($cb >= 70 && $cb <= 135) && ($cr >= 130 && $cr <= 180);
  [$h,$s,$v] = rgb2hsv($r,$g,$b);
  $h_ok = ($h >= 0 && $h <= 50) || ($h >= 340 && $h <= 360);
  $s_ok = ($s >= 0.15 && $s <= 0.75);
  $v_ok = ($v >= 0.35 && $v <= 0.98);
  return $yc_ok && $h_ok && $s_ok && $v_ok;
}

// ---------- Per-image color distribution (alpha-aware + optional skin filter) ----------
function imagePaletteDistribution($im, array $PALETTE_LAB, array $opts = []): ?array {
  if (!$im) return null;
  $w = imagesx($im); $h = imagesy($im); if ($w===0||$h===0) return null;

  // options
  $alphaCutoff = $opts['alpha_cutoff'] ?? 96;   // 0..127 (<= means opaque enough)
  $ignoreSkin  = $opts['ignore_skin']  ?? true;
  $ds          = max(8, (int)($opts['downscale'] ?? 64));

  // Downscale with alpha preserved
  $t = imagecreatetruecolor($ds, $ds);
  imagealphablending($t, false);
  imagesavealpha($t, true);
  $transparent = imagecolorallocatealpha($t, 0,0,0,127);
  imagefilledrectangle($t, 0,0, $ds,$ds, $transparent);
  imagecopyresampled($t, $im, 0,0, 0,0, $ds,$ds, $w,$h);

  $counts=[]; $total=0;

  for ($y=0; $y<$ds; $y++) {
    for ($x=0; $x<$ds; $x++) {
      [$r,$g,$b,$a] = rgbaAt($t,$x,$y);

      // 1) ignore transparent pixels (transparent backgrounds)
      if ($a > $alphaCutoff) continue;

      // 2) optionally ignore likely skin pixels
      if ($ignoreSkin && is_skin_pixel($r,$g,$b)) continue;

      // 3) map to nearest palette color in LAB
      $lab = rgb2lab($r,$g,$b);
      $bestName = null; $bestD = 1e9;
      foreach ($PALETTE_LAB as $name=>$pLab) {
        $d = labD($lab, $pLab);
        if ($d < $bestD) { $bestD = $d; $bestName = $name; }
      }
      if ($bestName === null) continue;

      $counts[$bestName] = ($counts[$bestName] ?? 0) + 1;
      $total++;
    }
  }

  if ($total === 0) return null;

  arsort($counts);
  $out = [];
  foreach ($counts as $name=>$c) {
    $pct = (int) round(100 * $c / $total);
    if ($pct > 0) $out[] = ['name'=>$name,'pct'=>$pct];
  }
  // cosmetic normalization
  $sum = array_sum(array_column($out,'pct'));
  if ($sum > 100) $out[0]['pct'] -= ($sum - 100);

  return $out;
}

// ---------- Candidate image list for a row ----------
function candidateImages(array $row): array {
  $out=[];
  $chosen=trim((string)($row['chosen_image']??''));
  if ($chosen!=='' && is_supported_img($chosen)) $out[]=$chosen;

  $thumbs=trim((string)($row['thumbnails_json']??''));
  if ($thumbs!==''){
    if ($thumbs[0]==='['){
      $arr=json_decode($thumbs,true);
      if (is_array($arr)) foreach($arr as $p) if(is_supported_img($p)) $out[]=$p;
    }else{
      foreach(array_map('trim', explode(';',$thumbs)) as $p){
        if ($p!=='' && is_supported_img($p)) $out[]=$p;
      }
    }
  }
  // unique-preserve order
  $seen=[]; $uniq=[];
  foreach($out as $p){ $k=strtolower($p); if(!isset($seen[$k])){ $seen[$k]=1; $uniq[]=$p; } }
  return $uniq;
}

// ---------- Query items ----------
$where=[]; $params=[];
if (!empty($flags['itemIds'])) {
  $in=implode(',', array_fill(0,count($flags['itemIds']), '?'));
  $where[]="i.itemId IN ($in)";
  $params=array_merge($params,$flags['itemIds']);
}
$sql="SELECT i.itemId, i.itemName, i.chosen_image, i.thumbnails_json
      FROM item i ".($where?"WHERE ".implode(' AND ',$where):"")."
      ORDER BY i.itemId";
if ($flags['limit']) $sql.=" LIMIT ".(int)$flags['limit'];

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
if(!$rows){ echo "No items found.\n"; exit; }

// ---------- Prepared statements ----------
$selExists = $pdo->prepare("SELECT 1 FROM item_image_color WHERE itemId=? AND src=?");
$upsertImg = $pdo->prepare(
  "INSERT INTO item_image_color (itemId, src, primary_color, secondary_color, primary_pct, secondary_pct, colors_json)
   VALUES (:id, :src, :p1, :p2, :pct1, :pct2, :json)
   ON DUPLICATE KEY UPDATE
     primary_color = VALUES(primary_color),
     secondary_color = VALUES(secondary_color),
     primary_pct   = VALUES(primary_pct),
     secondary_pct = VALUES(secondary_pct),
     colors_json   = VALUES(colors_json)"
);
$updItem = $pdo->prepare(
  "UPDATE item
     SET color_primary = :p1,
         color_secondary = :p2,
         is_multicolor = :mc
   WHERE itemId = :id"
);

// ---------- Process ----------
$totalItems=0; $taggedImages=0; $updatedItems=0;

foreach($rows as $row){
  $totalItems++;
  $imgs = candidateImages($row);
  if (!$imgs){ echo "Item {$row['itemId']} has no local images.\n"; continue; }

  // accumulate across images for item-level decision
  $aggCounts=[];

  foreach($imgs as $rel){
    $abs = publicToDisk($rel, $projectRoot);
    if (!is_file($abs)){ echo "  missing: $rel\n"; continue; }

    if ($flags['only_missing']){
      $selExists->execute([$row['itemId'],$rel]);
      if ($selExists->fetchColumn()) continue;
    }

    $im = loadImage($abs);
    if (!$im){ echo "  unreadable: $rel\n"; continue; }

    // options come from config (overridable via CLI)
    $dist = imagePaletteDistribution($im, $PALETTE_LAB, [
      'alpha_cutoff' => $flags['alpha'],
      'ignore_skin'  => $flags['ignore_skin'],
      'downscale'    => $flags['downscale'],
    ]);
    imagedestroy($im);

    if (!$dist){ echo "  no-signal: $rel\n"; continue; }

    // per-image top1/top2
    $p1   = $dist[0]['name'];
    $pct1 = (int)$dist[0]['pct'];
    $p2   = $dist[1]['name'] ?? null;
    $pct2 = (int)($dist[1]['pct'] ?? 0);

    // store per-image distribution JSON
    $json = json_encode($dist, JSON_UNESCAPED_SLASHES);

    $taggedImages++;
    echo "  $rel => $p1 {$pct1}%"
       . ($p2 ? (", $p2 {$pct2}%") : "")
       . "\n";

    if ($flags['write']){
      $upsertImg->execute([
        ':id'   => $row['itemId'],
        ':src'  => $rel,
        ':p1'   => $p1,
        ':p2'   => $p2,
        ':pct1' => $pct1,
        ':pct2' => $pct2,
        ':json' => $json,
      ]);
    }

    // aggregate counts (by percentage weight)
    foreach ($dist as $entry){
      $name=$entry['name']; $pct=$entry['pct'];
      $aggCounts[$name] = ($aggCounts[$name] ?? 0) + $pct;
    }
  }

  if ($aggCounts){
    arsort($aggCounts);
    $names = array_keys($aggCounts);
    $vals  = array_values($aggCounts);
    $sum   = array_sum($vals);
    // normalize to 0..100
    $pctPrimary    = (int)round(100*$vals[0]/max(1,$sum));
    $primaryName   = $names[0];
    $secondaryName = $names[1] ?? null;
    $pctSecondary  = $secondaryName ? (int)round(100*$vals[1]/max(1,$sum)) : 0;

    // Multi-color rule (tweakable via --p1/--p2 or config)
    // - If top1 is strong enough (>= p1) AND top2 is small (< p2) => single color
    // - Else => multicolor
    $isMulti = !($pctPrimary >= $flags['p1'] && $pctSecondary < $flags['p2']);

    // normalize & clamp to canonical palette before persisting
    $normP1 = sw_color_normalize($primaryName);
    $normP1 = sw_color_clamp($normP1, $CANONICAL_PALETTE);

    $normP2 = null;
    if ($secondaryName !== null) {
      $normP2 = sw_color_normalize($secondaryName);
      $normP2 = sw_color_clamp($normP2, $CANONICAL_PALETTE);
    }

    echo "Item {$row['itemId']} \"{$row['itemName']}\" "
       . "=> {$normP1} {$pctPrimary}%"
       . ($normP2 ? ", {$normP2} {$pctSecondary}%" : "")
       . ($isMulti ? "  [MULTI]\n" : "\n");

    if ($flags['write']){
      $updItem->execute([
        ':p1' => $normP1,
        ':p2' => $normP2,
        ':mc' => $isMulti ? 1 : 0,
        ':id' => $row['itemId'],
      ]);
      $updatedItems++;
    }
  }
}

echo "\nScanned items:  {$totalItems}\n";
echo "Tagged images:  {$taggedImages}\n";
echo ($flags['write'] ? "Items updated: {$updatedItems}\n" : "Dry run (no DB writes)\n");




