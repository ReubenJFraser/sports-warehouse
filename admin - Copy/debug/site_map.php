<?php
header('Content-Type: text/plain');

function printTree($dir, $prefix = '') {
    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = "$dir/$item";

        echo $prefix . $item . (is_dir($path) ? "/" : "") . "\n";

        if (is_dir($path)) {
            printTree($path, $prefix . "  ");
        }
    }
}

printTree(realpath(__DIR__ . '/../../'));
