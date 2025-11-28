<?php
header('Content-Type: text/plain');

echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "PWD: " . getcwd() . "\n\n";

echo "include_path:\n" . get_include_path() . "\n\n";

// Test for .env files
echo "Looking for .env files:\n";
$roots = [
    '.env',
    '_backup_2025-09-26/.env',
    'sw_git_bak/.env'
];

foreach ($roots as $p) {
    echo "$p => " . (file_exists("../../$p") ? "FOUND" : "missing") . "\n";
}

echo "\nTesting common include roots:\n";
$dirs = [
    'inc',
    'sw_git_bak/inc',
    '_backup_2025-09-26/inc'
];

foreach ($dirs as $d) {
    echo "$d => " . (is_dir("../../$d") ? "DIR EXISTS" : "missing") . "\n";
}
