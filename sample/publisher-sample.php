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

$result = $bull->add(
    $queue,
    $data
);

print_r($result);
echo PHP_EOL;
