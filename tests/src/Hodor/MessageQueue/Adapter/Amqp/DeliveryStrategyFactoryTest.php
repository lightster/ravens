<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\Testing\Config;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\DeliveryStrategyFactory
 */
class DeliveryStrategyFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProducerStrategy
     * @covers ::getConsumerStrategy
     * @covers ::<private>
     */
    public function testStrategyFactoryReturnsStrategiesWithConfiguredQueueNames()
    {
        $queues = $this->getTestQueues();
        $strategy_factory = $this->getTestStrategyFactory($queues);

        $this->assertSame(
            $queues['fast_jobs']['queue_name'],
            $strategy_factory->getProducerStrategy('fast_jobs')->getQueueName()
        );
        $this->assertSame(
            $queues['slow_jobs']['queue_name'],
            $strategy_factory->getConsumerStrategy('slow_jobs')->getQueueName()
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getProducerStrategy
     * @covers ::getConsumerStrategy
     * @covers ::<private>
     */
    public function testStrategiesAreReused()
    {
        $strategy_factory = $this->getTestStrategyFactory();

        $this->assertSame(
            $strategy_factory->getProducerStrategy('fast_jobs'),
            $strategy_factory->getProducerStrategy('fast_jobs')
        );
        $this->assertSame(
            $strategy_factory->getConsumerStrategy('slow_jobs'),
            $strategy_factory->getConsumerStrategy('slow_jobs')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getProducerStrategy
     * @covers ::getConsumerStrategy
     * @covers ::<private>
     */
    public function testStrategyFactoryUsesSameChannelFactoryPassedToConstructor()
    {
        $queues = $this->getTestQueues();
        $config = $this->getTestConfig($queues);

        $channel_factory = new ChannelFactory($config);
        $strategy_factory = new DeliveryStrategyFactory($channel_factory);

        $this->assertSame(
            $channel_factory->getProducerChannel('fast_jobs'),
            $strategy_factory->getProducerStrategy('fast_jobs')->getChannel()
        );
        $this->assertSame(
            $channel_factory->getConsumerChannel('slow_jobs'),
            $strategy_factory->getConsumerStrategy('slow_jobs')->getChannel()
        );
    }

    /**
     * @param array|null $queues
     * @return DeliveryStrategyFactory
     */
    private function getTestStrategyFactory(array $queues = null)
    {
        if (!$queues) {
            $queues = $this->getTestQueues();
        }
        $config = $this->getTestConfig($queues);

        $channel_factory = new ChannelFactory($config);
        return new DeliveryStrategyFactory($channel_factory);
    }

    /**
     * @param array $queues
     * @return Config
     */
    private function getTestConfig(array $queues)
    {
        $config_provider = new ConfigProvider($this);

        return $config_provider->getConfigAdapter($queues);
    }

    /**
     * @return array
     */
    private function getTestQueues()
    {
        $config_provider = new ConfigProvider($this);

        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
            'slow_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
