<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use LogicException;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\ChannelFactory
 */
class ChannelFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::<private>
     */
    public function testConfigPassedInIsSameConfigRetrieved()
    {
        $config = ConfigProvider::getConfigAdapter([]);

        $channel_factory = new ChannelFactory($config);

        $this->assertSame($config, $channel_factory->getConfig());
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::<private>
     */
    public function testChannelsCanBeRetrieved()
    {
        $queues = ['q_one', 'q_two'];
        $config = ConfigProvider::getConfigAdapter($queues);

        $channel_factory = new ChannelFactory($config);
        foreach ($queues as $queue_key) {
            $channel = $channel_factory->getConsumerChannel($queue_key);
            $this->assertInstanceOf('Hodor\MessageQueue\Adapter\Amqp\Channel', $channel);
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::<private>
     */
    public function testChannelsAreReusedIfSameQueueKeyIsRequested()
    {
        $config = ConfigProvider::getConfigAdapter(['q_one']);

        $channel_factory = new ChannelFactory($config);
        $this->assertSame(
            $channel_factory->getConsumerChannel('q_one'),
            $channel_factory->getConsumerChannel('q_one')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::getProducerChannel
     * @covers ::<private>
     */
    public function testChannelsAreNotReusedIfUseIsDifferent()
    {
        $config = ConfigProvider::getConfigAdapter(['q_one']);

        $channel_factory = new ChannelFactory($config);
        $this->assertNotSame(
            $channel_factory->getConsumerChannel('q_one'),
            $channel_factory->getProducerChannel('q_one')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::<private>
     */
    public function testChannelsAreReusedIfSameChannelSettingsAreUsed()
    {
        $config = ConfigProvider::getConfigAdapter(['q_one', 'q_two']);

        $channel_factory = new ChannelFactory($config);
        $this->assertSame(
            $channel_factory->getConsumerChannel('q_one'),
            $channel_factory->getConsumerChannel('q_two')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::getProducerChannel
     * @covers ::<private>
     */
    public function testConnectionsAreReusedEvenIfUseIsDifferent()
    {
        $queue_config = ConfigProvider::getQueueConfig();
        $queues = [
            'original'  => $queue_config,
            'duplicate' => $queue_config,
        ];
        $config = ConfigProvider::getConfigAdapter($queues);

        $channel_factory = new ChannelFactory($config);
        $this->assertSame(
            $channel_factory->getConsumerChannel('original')->getAmqpChannel()->getConnection(),
            $channel_factory->getProducerChannel('duplicate')->getAmqpChannel()->getConnection()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumerChannel
     * @covers ::getProducerChannel
     * @covers ::disconnectAll
     * @covers ::<private>
     */
    public function testAllConnectionsAreClosedWhenDisconnectAllIsCalled()
    {
        $config = ConfigProvider::getConfigAdapter([
            'q_one' => ConfigProvider::getQueueConfig(),
            'q_two' => ConfigProvider::getQueueConfig(),
        ]);

        $channel_factory = new ChannelFactory($config);

        $one = $channel_factory->getProducerChannel('q_one')->getAmqpChannel()->getConnection();
        $two = $channel_factory->getConsumerChannel('q_two')->getAmqpChannel()->getConnection();

        $this->assertTrue($one->isConnected());
        $this->assertTrue($two->isConnected());

        $channel_factory->disconnectAll();

        $this->assertFalse($one->isConnected());
        $this->assertFalse($two->isConnected());
    }

    /**
     * @covers ::__construct
     * @covers ::getProducerChannel
     * @covers ::<private>
     * @dataProvider provideRequiredQueueConfigOptions
     * @param string $config_key
     * @expectedException LogicException
     */
    public function testAnExceptionIsThrownIfAnyRequiredConfigElementsAreMissing($config_key)
    {
        $queue = ConfigProvider::getQueueConfig();
        unset($queue[$config_key]);
        $config = ConfigProvider::getConfigAdapter(['broken_config' => $queue]);

        $channel_factory = new ChannelFactory($config);
        $channel_factory->getProducerChannel('broken_config');
    }

    /**
     * @return array
     */
    public function provideRequiredQueueConfigOptions()
    {
        return [['host'], ['port'], ['username'], ['password'], ['queue_name'],];
    }
}
