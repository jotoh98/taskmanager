<?php
$start = microtime(true);

require_once (__DIR__.'/tm-includes/tm-setup.php');

//$re = tm_create_user('asdadasasdass', 'lasdasdasasasd@asdk.com');


$stop = microtime(true);

echo '<br>'. round(($stop-$start)*1000, 2) .' ms';