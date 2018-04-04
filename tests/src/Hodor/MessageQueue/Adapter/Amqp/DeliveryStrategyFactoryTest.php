<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

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
        $queues = [
            'q_one' => ['queue_name' => uniqid('q_one_')] + ConfigProvider::getQueueConfig(),
            'q_two' => ['queue_name' => uniqid('q_two_')] + ConfigProvider::getQueueConfig(),
        ];
        $strategy_factory = $this->getTestStrategyFactory($queues);

        $this->assertSame(
            $queues['q_one']['queue_name'],
            $strategy_factory->getProducerStrategy('q_one')->getQueueName()
        );
        $this->assertSame(
            $queues['q_two']['queue_name'],
            $strategy_factory->getConsumerStrategy('q_two')->getQueueName()
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
            $strategy_factory->getProducerStrategy('q_one'),
            $strategy_factory->getProducerStrategy('q_one')
        );
        //$this->assertSame(
        //    $strategy_factory->getConsumerStrategy('q_two'),
        //    $strategy_factory->getConsumerStrategy('q_two')
        //);
    }

    /**
     * @covers ::__construct
     * @covers ::getProducerStrategy
     * @covers ::getConsumerStrategy
     * @covers ::<private>
     */
    public function testStrategyFactoryUsesSameChannelFactoryPassedToConstructor()
    {
        $config = ConfigProvider::getConfigAdapter(['q_one', 'q_two']);

        $channel_factory = new ChannelFactory($config);
        $strategy_factory = new DeliveryStrategyFactory($channel_factory);

        $this->assertSame(
            $channel_factory->getProducerChannel('q_one'),
            $strategy_factory->getProducerStrategy('q_one')->getChannel()
        );
        $this->assertSame(
            $channel_factory->getConsumerChannel('q_two'),
            $strategy_factory->getConsumerStrategy('q_two')->getDeliveryStrategy()->getChannel()
        );
    }

    /**
     * @param array $queues
     * @return DeliveryStrategyFactory
     */
    private function getTestStrategyFactory(array $queues = null)
    {
        if (!$queues) {
            $queues = ['q_one', 'q_two'];
        }

        $config = ConfigProvider::getConfigAdapter($queues);
        $channel_factory = new ChannelFactory($config);
        $strategy_factory = new DeliveryStrategyFactory($channel_factory);

        return $strategy_factory;
    }
}
