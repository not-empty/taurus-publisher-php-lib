<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BullPublisher\BullPublisher;

$queue = 'test';
$data = [
	'publisher' => 'example',
];

$redisConfig = [
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => 6379,
];

$bull = new BullPublisher($redisConfig);

print_r('Publish');
echo PHP_EOL;

$result = $bull->add(
    $queue,
    $data
);

print_r($result);
echo PHP_EOL;
