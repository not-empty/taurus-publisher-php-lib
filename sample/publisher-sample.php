<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TaurusPublisher\TaurusPublisher;

$queue = 'test';
$data = [
	'publisher' => 'example',
];

$taurus = new TaurusPublisher();

print_r('Publish');
echo PHP_EOL;

$result = $taurus->add(
    $queue,
    $data
);

print_r($result);
echo PHP_EOL;
