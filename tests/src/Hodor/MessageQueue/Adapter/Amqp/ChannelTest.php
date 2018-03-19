<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Channel
 */
class ChannelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::<private>
     */
    public function testConnectionCanBeInstantiatedWithoutError()
    {
        $connection = $this->getMockConnection();
        $channel = new Channel($connection, ['queue_name' => uniqid()]);

        $this->assertInstanceOf('Hodor\MessageQueue\Adapter\Amqp\Channel', $channel);
    }

    /**
     * @covers ::__construct
     * @covers ::getAmqpChannel
     * @dataProvider provideQueueList
     * @param array $queues
     */
    public function testAmqpChannelsCanBeRetrieved(array $queues)
    {
        foreach ($queues as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertInstanceOf('PhpAmqpLib\Channel\AMQPChannel', $channel->getAmqpChannel());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getAmqpChannel
     * @dataProvider provideQueueList
     * @param array $queues
     */
    public function testAmqpChannelsCanBeReused(array $queues)
    {
        foreach ($queues as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertSame($channel->getAmqpChannel(), $channel->getAmqpChannel());
        }
    }

    /**
     * @return array
     */
    public function provideQueueList()
    {
        $config_provider = new ConfigProvider();

        return [
            [
                [
                    'fast_jobs' => $config_provider->getQueueConfig(),
                    'slow_jobs' => $config_provider->getQueueConfig(),
                ]
            ]
        ];
    }

    /**
     * @return Connection
     */
    private function getMockConnection()
    {
        /**
         * @var Connection $connection
         */
        return $this
            ->getMockBuilder('Hodor\MessageQueue\Adapter\Amqp\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
