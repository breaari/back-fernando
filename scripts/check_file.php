<?php
$path = __DIR__ . '/../.env';
echo "Checking: $path\n";
if (file_exists($path)) {
    echo "exists\n";
    $c = file_get_contents($path);
    echo "len=" . strlen($c) . "\n";
    echo "content:\n" . $c . "\n";
} else {
    echo "not exists\n";
}
