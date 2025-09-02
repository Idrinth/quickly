<?php

require_once 'vendor/autoload.php';

$devContainer = new \Idrinth\Quickly\DependencyInjection\Container(['DI_USE_REFLECTION' => 'true']);
$prodContainer = new \Idrinth\Quickly\DependencyInjection\Container([], constructors: [
    stdClass::class => [],
]);
echo "Starting with reflection\n";
$start = microtime(true);
for($i = 0; $i < 10000; $i++) {
    $devContainer->get(stdClass::class);
}
echo "Duration: ".(microtime(true) - $start)." seconds per 10,000\n";
echo "Starting with configured\n";
$start = microtime(true);
for($i = 0; $i < 10000; $i++) {
    $prodContainer->get(stdClass::class);
}
echo "Duration: ".(microtime(true) - $start)." seconds per 10,000\n";
