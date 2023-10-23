# PHP Taurus Queue Publisher

[![Latest Version](https://img.shields.io/github/v/release/not-empty/taurus-publisher-php-lib.svg?style=flat-square)](https://github.com/not-empty/taurus-publisher-php-lib/releases)
[![codecov](https://codecov.io/gh/not-empty/taurus-publisher-php-lib/graph/badge.svg?token=AEMV163UW6)](https://codecov.io/gh/not-empty/taurus-publisher-php-lib)
[![CI Build](https://img.shields.io/github/actions/workflow/status/not-empty/taurus-publisher-php-lib/php.yml)](https://github.com/not-empty/taurus-publisher-php-lib/actions/workflows/php.yml)
[![Downloads Old](https://img.shields.io/packagist/dt/kiwfy/taurus-publisher-php?logo=old&label=downloads%20legacy)](https://packagist.org/packages/kiwfy/taurus-publisher-php)
[![Downloads](https://img.shields.io/packagist/dt/not-empty/taurus-publisher-php-lib?logo=old&label=downloads)](https://packagist.org/packages/not-empty/taurus-publisher-php-lib)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com)
[![Packagist License (custom server)](https://img.shields.io/packagist/l/not-empty/taurus-publisher-php-lib)](https://github.com/not-empty/taurus-publisher-php-lib/blob/master/LICENSE)

PHP library using LUA script to send for redis a job for Taurus queue

### Installation

[Release 5.0.0](https://github.com/not-empty/taurus-publisher-php-lib/releases/tag/5.0.0) Requires [PHP](https://php.net) 8.2

[Release 4.0.0](https://github.com/not-empty/taurus-publisher-php-lib/releases/tag/4.0.0) Requires [PHP](https://php.net) 8.1

[Release 3.0.0](https://github.com/not-empty/taurus-publisher-php-lib/releases/tag/3.0.0) Requires [PHP](https://php.net) 7.4

[Release 2.0.0](https://github.com/not-empty/taurus-publisher-php-lib/releases/tag/2.0.0) Requires [PHP](https://php.net) 7.3

[Release 1.0.0](https://github.com/not-empty/taurus-publisher-php-lib/releases/tag/1.0.0) Requires [PHP](https://php.net) 7.1

The recommended way to install is through [Composer](https://getcomposer.org/).

```sh
composer require not-empty/taurus-publisher-php-lib
```

### Usage

Publishing

```php
use TaurusPublisher\TaurusPublisher;
$queue = 'test';
$data = [
	'publisher' => 'example',
];
$taurus = new TaurusPublisher();
$result = $taurus->add(
    $queue,
    $data
);
var_dump($result);
```

Publishing with redis config

```php
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
$result = $taurus->add(
    $queue,
    $data
);
var_dump($result);
```

Publishing with queue config

```php
use TaurusPublisher\TaurusPublisher;
$queue = 'test';
$data = [
	'publisher' => 'example',
];
$taurus = new TaurusPublisher();
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
var_dump($result);
```

Publishing in loop without exceed redis connections (reuse redis connections)

you may pass a redis connection to persist connection and allow you to loop add method without overload redis with connections

```php
use TaurusPublisher\TaurusPublisher;
use Predis\Client as Redis;
$redisConfig = [
    'scheme' => 'tcp',
    'host'   => 'redis',
    'port'   => 6379,
];
$client = new Redis($redisConfig);
$queue = 'test';
$data = [
	'publisher' => 'example',
];
$taurus = new TaurusPublisher(
    $redisConfig,
    [],
    $client
);
for ($i=0; $i < 1000000; $i++) { 
    $result = $taurus->add(
        $queue,
        $data
    );
    var_dump($result);
}
```

if you want an environment to run or test it, you can build and install dependences like this

```sh
docker build --build-arg PHP_VERSION=8.2-cli -t not-empty/taurus-publisher-php-lib:php82 -f contrib/Dockerfile .
```

Access the container
```sh
docker run -v ${PWD}/:/var/www/html -it not-empty/taurus-publisher-php-lib:php82 bash
```

Verify if all dependencies is installed
```sh
composer install --no-dev --prefer-dist
```

and run (you will need redis)
```sh
php sample/publisher-sample.php
```

### Development

Want to contribute? Great!

The project using a simple code.
Make a change in your file and be careful with your updates!
**Any new code will only be accepted with all validations.**

To ensure that the entire project is fine:

First you need to building a correct environment to install all dependences

```sh
docker build --build-arg PHP_VERSION=8.2-cli -t not-empty/taurus-publisher-php-lib:php82 -f contrib/Dockerfile .
```

Access the container
```sh
docker run -v ${PWD}/:/var/www/html -it not-empty/taurus-publisher-php-lib:php82 bash
```

Install all dependences
```sh
composer install --dev --prefer-dist
```

Run all validations
```sh
composer check
```

**Not Empty Foundation - Free codes, full minds**
