<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TaurusPublisher\TaurusPublisher;

$queue = 'test';
$data = [
	'publisher' => 'example',
];

$redisConfig = [
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => 6379,
];

$taurus = new TaurusPublisher($redisConfig);

print_r('Publish');
echo PHP_EOL;

$result = $taurus->add(
    $queue,
    $data
);

print_r($result);
echo PHP_EOL;
