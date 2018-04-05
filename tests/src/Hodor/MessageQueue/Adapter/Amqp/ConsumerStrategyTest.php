<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\ConsumerStrategy
 */
class ConsumerStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getDeliveryStrategy
     */
    public function testDeliveryStrategyPassedToConstructorIsTheSameRetrieved()
    {
        $channel = $this->getMockChannel();
        $delivery_strategy = new DeliveryStrategy($channel, ['queue_name' => 'irrelevant']);
        $consumer_strategy = new ConsumerStrategy($delivery_strategy, 'only_q');
        $this->assertEquals($delivery_strategy, $consumer_strategy->getDeliveryStrategy());
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueName
     * @covers ::<private>
     */
    public function testQueueNameRetrieved()
    {
        $queue_name = uniqid();
        $channel = $this->getMockChannel();
        $delivery_strategy = new DeliveryStrategy($channel, ['queue_name' => $queue_name]);
        $consumer_strategy = new ConsumerStrategy($delivery_strategy, 'only_q');
        $this->assertEquals($queue_name, $consumer_strategy->getQueueName());
    }

    /**
     * @return Channel
     */
    private function getMockChannel()
    {
        return $this
            ->getMockBuilder('Hodor\MessageQueue\Adapter\Amqp\Channel')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
