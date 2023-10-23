# PHP Taurus Queue Publisher

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com)

PHP library using LUA script to send for redis a job for Taurus queue

### Installation

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

if you want an environment to run or test it, you can build and install dependences like this

```sh
docker build --build-arg PHP_VERSION=7.1.33-cli -t not-empty/taurus-publisher-php-lib:php71 -f contrib/Dockerfile .
```

Access the container
```sh
docker run -v ${PWD}/:/var/www/html -it not-empty/taurus-publisher-php-lib:php71 bash
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
docker build --build-arg PHP_VERSION=7.1.33-cli -t not-empty/taurus-publisher-php-lib:php71 -f contrib/Dockerfile .
```

Access the container
```sh
docker run -v ${PWD}/:/var/www/html -it not-empty/taurus-publisher-php-lib:php71 bash
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
