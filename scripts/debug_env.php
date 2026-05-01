<?php
$path = __DIR__ . '/../.env';
if (!file_exists($path)) { echo "NOT FOUND: $path\n"; exit(1); }
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $i => $line) {
    echo "LINE $i: [" . $line . "]\n";
    if (strpos(trim($line), '#') === 0) { echo "  comment\n"; continue; }
    if (strpos($line, '=') === false) { echo "  no eq\n"; continue; }
    [$key, $value] = explode('=', $line, 2);
    echo "  KEY=[$key] VAL=[$value]\n";
}
