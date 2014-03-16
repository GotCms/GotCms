<?php

function moveDirectory($source, $dest) {
    foreach (
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST) as $item
    ) {
        if ($item->isDir()) {
            mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            rename($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }

        echo 'Move: ' . $iterator->getSubPathName() . PHP_EOL;
    }
}

$oldPath = GC_APPLICATION_PATH . '/module/Development/views';
$newPath = GC_APPLICATION_PATH . '/templates';

echo 'Move layouts...' . PHP_EOL;
moveDirectory($oldPath . '/layout', $newPath . '/layout');
echo 'Done' . PHP_EOL;

echo 'Move views...' . PHP_EOL;
moveDirectory($oldPath . '/view', $newPath . '/view');
echo 'Done' . PHP_EOL;

echo 'Move scripts...' . PHP_EOL;
moveDirectory($oldPath . '/script', $newPath . '/script');
echo 'Done' . PHP_EOL;
