<?php

use Gc\Layout;
use Gc\Script;
use Gc\View;
use Exception;

echo 'Update layouts...';
$collection = new Layout\Collection();
foreach ($collection->getLayouts() as $layout) {
    $layout->save();
}

echo 'Done';

echo 'Update scripts...';
$collection = new Script\Collection();
foreach ($collection->getScripts() as $script) {
    $script->save();
}

echo 'Done';

echo 'Update views...';
$collection = new View\Collection();
foreach ($collection->getViews() as $view) {
    $view->save();
}

echo 'Done';
