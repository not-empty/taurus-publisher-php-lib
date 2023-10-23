<?php

use TaurusPublisher\TaurusPublisher;
use PHPUnit\Framework\TestCase;
use Predis\Client as Redis;
use Ulid\Ulid;

class TaurusPublisherTest extends TestCase
{
    /**
     * @covers \TaurusPublisher\TaurusPublisher::__construct
     */
    public function testTaurusPublisherCanBeInstanciated()
    {
        $TaurusPublisher = new TaurusPublisher();

        $this->assertInstanceOf(TaurusPublisher::class, $TaurusPublisher);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::configQueue
     */
    public function testConfigQueue()
    {
        $ulid = '01EDRZWN9D9TBN0YCTBR3RQAJ6';
        $timestamp = intval(str_replace('.', '', microtime(true)));

        $result = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => $ulid,
            'timestamp' => $timestamp,
        ];

        $redisSpy = Mockery::spy(Redis::class);

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($ulid)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('getTimestamp')
            ->withNoArgs()
            ->once()
            ->andReturn($timestamp);

        $configQueue = $TaurusPublisher->configQueue([]);

        $this->assertEquals($result, $configQueue);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::configQueue
     */
    public function testConfigQueueWithCustomConfig()
    {
        $ulid = '01EDRZWN9D9TBN0YCTBR3RQAJ6';
        $timestamp = intval(str_replace('.', '', microtime(true)));

        $params = [
            'attempts' => 5,
            'removeOnComplete' => 50,
        ];

        $result = [
            'attempts' => 5,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 50,
            'jobId' => $ulid,
            'timestamp' => $timestamp,
        ];

        $redisSpy = Mockery::spy(Redis::class);

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($ulid)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('getTimestamp')
            ->withNoArgs()
            ->once()
            ->andReturn($timestamp);

        $configQueue = $TaurusPublisher->configQueue($params);

        $this->assertEquals($result, $configQueue);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::redisConfig
     */
    public function testRedisConfig()
    {
        $redisConfig = [];

        $result = [
            'scheme' => 'tcp',
            'host'   => 'localhost',
            'port'   => 6379,
        ];

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();

        $redisConfig = $TaurusPublisher->redisConfig($redisConfig);

        $this->assertEquals($result, $redisConfig);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::redisConfig
     */
    public function testRedisConfigWithCustomConfig()
    {
        $redisConfig = [
            'host' => 'redis',
        ];

        $result = [
            'scheme' => 'tcp',
            'host'   => 'redis',
            'port'   => 6379,
        ];

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();

        $redisConfig = $TaurusPublisher->redisConfig($redisConfig);

        $this->assertEquals($result, $redisConfig);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::add
     */
    public function testAdd()
    {
        $queue = 'test';
        $data = [];
        $opts = [];
        $name = 'process';

        $timestamp = intval(str_replace('.', '', microtime(true)));

        $options = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => '01EDS21K2PZQJ4T4XCZA2B3DEN',
            'timestamp' => $timestamp,
        ];

        $token = '01EDRZWN9D9TBN0YCTBR3RQAJ6';

        $keyPrefix = sprintf('%s:%s:', 'bull', $queue);

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

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($token)
            ->getMock();

        $redisMock = Mockery::mock(Redis::class)
            ->shouldReceive('getProfile')
            ->withNoArgs()
            ->once()
            ->andReturnSelf()
            ->shouldReceive('defineCommand')
            ->with('addjob', 'TaurusPublisher\RedisAddCommand')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addjob')
            ->with(
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
            )
            ->once()
            ->andReturn('')
            ->shouldReceive('close')
            ->withNoArgs()
            ->andReturn(true)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher->shouldReceive('newRedis')
            ->withNoArgs()
            ->once()
            ->andReturn($redisMock)
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('configQueue')
            ->with($opts)
            ->once()
            ->andReturn($options);

        $add = $TaurusPublisher->add($queue, $data, $opts, $name);

        $this->assertEquals('', $add);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::add
     */
    public function testAddAndSendDelayOption()
    {
        $queue = 'test';
        $data = [];
        $name = 'process';

        $opts = [
            'delay' => 1,
        ];

        $timestamp = intval(str_replace('.', '', microtime(true)));

        $options = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => '01EDS21K2PZQJ4T4XCZA2B3DEN',
            'timestamp' => $timestamp,
        ];

        $token = '01EDRZWN9D9TBN0YCTBR3RQAJ6';

        $keyPrefix = sprintf('%s:%s:', 'bull', $queue);

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

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($token)
            ->getMock();

        $redisMock = Mockery::mock(Redis::class)
            ->shouldReceive('getProfile')
            ->withNoArgs()
            ->once()
            ->andReturnSelf()
            ->shouldReceive('defineCommand')
            ->with('addjob', 'TaurusPublisher\RedisAddCommand')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addjob')
            ->with(
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
            )
            ->once()
            ->andReturn('')
            ->shouldReceive('close')
            ->withNoArgs()
            ->andReturn(true)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher->shouldReceive('newRedis')
            ->withNoArgs()
            ->once()
            ->andReturn($redisMock)
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('configQueue')
            ->with($opts)
            ->once()
            ->andReturn($options);

        $add = $TaurusPublisher->add($queue, $data, $opts, $name);

        $this->assertEquals('', $add);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::add
     */
    public function testAddAndSendPriorityOption()
    {
        $queue = 'test';
        $data = [];
        $name = 'process';

        $opts = [
            'priority' => 1,
        ];

        $timestamp = intval(str_replace('.', '', microtime(true)));

        $options = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => '01EDS21K2PZQJ4T4XCZA2B3DEN',
            'timestamp' => $timestamp,
        ];

        $token = '01EDRZWN9D9TBN0YCTBR3RQAJ6';

        $keyPrefix = sprintf('%s:%s:', 'bull', $queue);

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

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($token)
            ->getMock();

        $redisMock = Mockery::mock(Redis::class)
            ->shouldReceive('getProfile')
            ->withNoArgs()
            ->once()
            ->andReturnSelf()
            ->shouldReceive('defineCommand')
            ->with('addjob', 'TaurusPublisher\RedisAddCommand')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addjob')
            ->with(
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
            )
            ->once()
            ->andReturn('')
            ->shouldReceive('close')
            ->withNoArgs()
            ->andReturn(true)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher->shouldReceive('newRedis')
            ->withNoArgs()
            ->once()
            ->andReturn($redisMock)
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('configQueue')
            ->with($opts)
            ->once()
            ->andReturn($options);

        $add = $TaurusPublisher->add($queue, $data, $opts, $name);

        $this->assertEquals('', $add);
    }

    /**
     * @covers \TaurusPublisher\TaurusPublisher::add
     */
    public function testAddAndSendLifoOption()
    {
        $queue = 'test';
        $data = [];
        $name = 'process';

        $opts = [
            'lifo' => 1,
        ];

        $timestamp = intval(str_replace('.', '', microtime(true)));

        $options = [
            'attempts' => 3,
            'backoff' => 30000,
            'delay' => 0,
            'removeOnComplete' => 100,
            'jobId' => '01EDS21K2PZQJ4T4XCZA2B3DEN',
            'timestamp' => $timestamp,
        ];

        $token = '01EDRZWN9D9TBN0YCTBR3RQAJ6';

        $keyPrefix = sprintf('%s:%s:', 'bull', $queue);

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

        $ulidMock = Mockery::mock(Ulid::class)
            ->shouldReceive('generate')
            ->withNoArgs()
            ->once()
            ->andReturn($token)
            ->getMock();

        $redisMock = Mockery::mock(Redis::class)
            ->shouldReceive('getProfile')
            ->withNoArgs()
            ->once()
            ->andReturnSelf()
            ->shouldReceive('defineCommand')
            ->with('addjob', 'TaurusPublisher\RedisAddCommand')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addjob')
            ->with(
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
            )
            ->once()
            ->andReturn('')
            ->shouldReceive('close')
            ->withNoArgs()
            ->andReturn(true)
            ->getMock();

        $TaurusPublisher = Mockery::mock(TaurusPublisher::class)->makePartial();
        $TaurusPublisher->shouldReceive('newRedis')
            ->withNoArgs()
            ->once()
            ->andReturn($redisMock)
            ->shouldReceive('newUlid')
            ->withNoArgs()
            ->once()
            ->andReturn($ulidMock)
            ->shouldReceive('configQueue')
            ->with($opts)
            ->once()
            ->andReturn($options);

        $add = $TaurusPublisher->add($queue, $data, $opts, $name);

        $this->assertEquals('', $add);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
