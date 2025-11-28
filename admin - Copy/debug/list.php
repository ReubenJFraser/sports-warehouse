<?php
header('Content-Type: text/plain');

$root = realpath(__DIR__ . '/../../'); // public_html

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($rii as $file) {
    if ($file->isFile()) {
        echo substr($file->getPathname(), strlen($root) + 1) . "\n";
    }
}
