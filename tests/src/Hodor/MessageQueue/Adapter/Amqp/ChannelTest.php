<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Channel
 */
class ChannelTest extends TestCase
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
     */
    public function testAmqpChannelsCanBeRetrieved()
    {
        foreach ($this->getQueueConfigs() as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertInstanceOf('PhpAmqpLib\Channel\AMQPChannel', $channel->getAmqpChannel());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getAmqpChannel
     */
    public function testAmqpChannelsCanBeReused()
    {
        foreach ($this->getQueueConfigs() as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $this->assertSame($channel->getAmqpChannel(), $channel->getAmqpChannel());
        }
    }

    /**
     * @return array
     */
    public function getQueueConfigs()
    {
        return [
            'q_one' => ConfigProvider::getQueueConfig(),
            'q_two' => ConfigProvider::getQueueConfig(),
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
