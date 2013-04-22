<?php
//include_once '../../../../xhprof/external/header.php';
require('../../src/view.php');

$startTime = microtime(true);
$view = new View\view();

$view->setPath('../phpUnit/templates/');
$view->load('index');

for ($i=0;$i<10000;$i++) {
        $view->render();
}

echo "Time:  " . number_format(( microtime(true) - $startTime), 4) . " Seconds\n";
//include_once '../../../../xhprof/external/footer.php';