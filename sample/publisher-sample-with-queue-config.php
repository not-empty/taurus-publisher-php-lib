<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BullPublisher\BullPublisher;

$queue = 'test';
$data = [
	'publisher' => 'example',
];

$bull = new BullPublisher();

print_r('Publish');
echo PHP_EOL;

$queueConfig = [
    'attempts' => 4,
    'backoff' => 20000,
    'delay' => 1,
    'removeOnComplete' => 20,
];

$result = $bull->add(
    $queue,
    $data,
    $queueConfig
);

print_r($result);
echo PHP_EOL;
