<?php

namespace BullPublisher;

use Predis\Client as Redis;
use Ulid\Ulid;

class BullPublisher
{
    private $prefix = 'bull';
    private $redisConfig;
    private $redisOptions;

    /**
     * construct class with redis config if pass
     * @param array $redisConfig
     * @param array $redisConfig
     * @return void
     */
    public function __construct(
        array $redisConfig = [],
        array $redisOptions = []
    ) {
        $this->redisConfig = $this->redisConfig($redisConfig);
        $this->redisOptions = $redisOptions;
    }

    /**
     * Publish data in Redis using Taurus layout
     *
     * @param string $queue
     * @param array $data
     * @param array $opts
     * @param string $name
     * @return string
     */
    public function add(
        string $queue,
        array $data,
        array $opts = [],
        string $name = 'process'
    ): string {
        $redis = $this->newRedis();

        $redis
            ->getProfile()
            ->defineCommand('addjob', 'BullPublisher\RedisAddCommand');

        $token = $this->newUlid()->generate();
        $keyPrefix = sprintf('%s:%s:', $this->prefix, $queue);

        $options = $this->configQueue($opts);

        $delay = 0;
        if (isset($opts['delay'])) {
            $delay = $options['timestamp'] + $options['delay'];
        }

        $priority = 0;
        if (isset($opts['priority'])) {
            $priority = intval($opts['priority']);
        }

        $lifo = 'LPUSH';
        if (isset($opts['lifo'])) {
            $lifo = 'RPUSH';
        }

        return $redis->addjob(
            $keyPrefix . 'wait',
            $keyPrefix . 'paused',
            $keyPrefix . 'meta-paused',
            $keyPrefix . 'id',
            $keyPrefix . 'delayed',
            $keyPrefix . 'priority',
            $keyPrefix,
            $options['jobId'],
            $name,
            json_encode($data),
            json_encode($options),
            $options['timestamp'],
            $options['delay'],
            $delay,
            $priority,
            $lifo,
            $token
        );
    }

    /**
     * @param array $config
     * return queue config
     * @return array
     */
    public function configQueue(
        array $config
    ): array {
        $defaultConfig = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => $this->newUlid()->generate(),
            'timestamp' => $this->getTimestamp(),
        ];

        return array_merge($defaultConfig, $config);
    }

    /**
     * create predis client config
     * @param array $redisConfig
     * @return array
     */
    public function redisConfig(
        array $redisConfig
    ): array {
        $defaultConfig = [
            'scheme' => 'tcp',
            'host'   => 'localhost',
            'port'   => 6379,
        ];

        return array_merge($defaultConfig, $redisConfig);
    }

    /**
     * @codeCoverageIgnore
     * return timestamp
     * @return int
     */
    public function getTimestamp(): int
    {
        return intval(str_replace('.', '', microtime(true)));
    }

    /**
     * @codeCoverageIgnore
     * create uuid instance
     * @return \Ulid\Ulid
     */
    public function newUlid(): Ulid
    {
        return new Ulid();
    }

    /**
     * @codeCoverageIgnore
     * create predis client instance
     * @return \Predis\Client
     */
    public function newRedis(): Redis
    {
        return new Redis($this->redisConfig, $this->redisOptions);
    }
}
