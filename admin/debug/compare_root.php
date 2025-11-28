<?php
header('Content-Type: text/plain');

$root = realpath(__DIR__ . '/../../');

$problem_folders = [
    '_backup_2025-09-26',
    'sw_git_bak',
    '.git',
];

echo "Scanning for duplicate/colliding site roots:\n\n";

foreach ($problem_folders as $d) {
    $path = "$root/$d";
    echo "$d => " . (is_dir($path) ? "FOUND" : "missing") . "\n";
}

echo "\nNested copies of css/, js/, inc/:\n";
$scan = ['css', 'js', 'inc', 'images', 'db'];

foreach ($scan as $folder) {
    echo "\n$folder/:\n";
    exec("find $root -type d -name $folder 2>/dev/null", $out);
    foreach ($out as $path) echo "• $path\n";
    $out = [];
}
