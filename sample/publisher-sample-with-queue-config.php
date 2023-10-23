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

$queueConfig = [
    'attempts' => 4,
    'backoff' => 20000,
    'delay' => 1,
    'removeOnComplete' => 20,
];

$result = $taurus->add(
    $queue,
    $data,
    $queueConfig
);

print_r($result);
echo PHP_EOL;
