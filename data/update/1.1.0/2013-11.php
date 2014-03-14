<?php

use Gc\Layout;
use Gc\Script;
use Gc\View;

echo 'Rename configuration file...' . PHP_EOL;
rename(
    GC_APPLICATION_PATH . '/config/autoload/global.php',
    GC_APPLICATION_PATH . '/config/autoload/local.php'
);

echo 'Update layouts...' . PHP_EOL;
$collection = new Layout\Collection();
foreach ($collection->getLayouts() as $layout) {
    $layout->save();
}

echo 'Done';

echo 'Update scripts...' . PHP_EOL;
$collection = new Script\Collection();
foreach ($collection->getScripts() as $script) {
    $script->save();
}

echo 'Done';

echo 'Update views...' . PHP_EOL;
$collection = new View\Collection();
foreach ($collection->getViews() as $view) {
    $view->save();
}

echo 'Done' . PHP_EOL;
