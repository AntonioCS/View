<?php

//include_once '/home/antoniocs/programming/www/xhprof/external/header.php';
require('../../src/view.php');

$startTime = microtime(true);
$view = new View\view();

$view->setPath('../phpUnit/templates/');
$view->load('index');

//$view->title = 'teste';
//$view->body = 'teste2';
$view->useEval(true);

for ($i=0;$i<10000;$i++) {
    $view->render();
}



echo "Time:  " . number_format(( microtime(true) - $startTime), 4) . " Seconds\n";
//include_once '/home/antoniocs/programming/www/xhprof/external/footer.php';