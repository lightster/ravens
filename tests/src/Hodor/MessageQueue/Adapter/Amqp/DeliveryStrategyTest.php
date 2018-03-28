<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\DeliveryStrategy
 */
class DeliveryStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::<private>
     * @dataProvider provideQueueConfigMissingARequiredField
     * @expectedException \LogicException
     * @param array $queue_config
     */
    public function testExceptionIsThrownIfARequiredFieldIsMissing(array $queue_config)
    {
        new DeliveryStrategy($this->getMockChannel(), $queue_config);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueueName
     * @covers ::<private>
     */
    public function testQueueNamePassedToConstructorIsTheSameRetrieved()
    {
        $queue_name = uniqid();
        $channel = $this->getMockChannel();
        $strategy = new DeliveryStrategy($channel, ['queue_name' => $queue_name]);
        $this->assertEquals($queue_name, $strategy->getQueueName());
    }

    /**
     * @covers ::__construct
     * @covers ::getExchangeName
     * @covers ::<private>
     */
    public function testExchangeNameIsTheSystemExchange()
    {
        $queue_name = uniqid();
        $channel = $this->getMockChannel();
        $strategy = new DeliveryStrategy($channel, ['queue_name' => $queue_name]);
        $this->assertSame('', $strategy->getExchangeName());
    }

    /**
     * @covers ::__construct
     * @covers ::getRoutingKey
     * @covers ::<private>
     */
    public function testRoutingKeyIsTheQueueName()
    {
        $queue_name = uniqid();
        $channel = $this->getMockChannel();
        $strategy = new DeliveryStrategy($channel, ['queue_name' => $queue_name]);
        $this->assertSame($queue_name, $strategy->getRoutingKey());
    }

    /**
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
     */
    public function testChannelsCanBeRetrieved()
    {
        foreach ($this->getQueueConfigs() as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $strategy = new DeliveryStrategy($channel, $queue_config);
            $this->assertInstanceOf(
                'Hodor\MessageQueue\Adapter\Amqp\Channel',
                $strategy->getChannel()
            );
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getChannel
     * @covers ::<private>
     */
    public function testChannelsCanBeReused()
    {
        foreach ($this->getQueueConfigs() as $queue_config) {
            $connection = new Connection($queue_config);
            $channel = new Channel($connection, $queue_config);
            $strategy = new DeliveryStrategy($channel, $queue_config);
            $this->assertSame($strategy->getChannel(), $strategy->getChannel());
        }
    }

    /**
     * @return array
     */
    public function provideQueueConfigMissingARequiredField()
    {
        $required_fields = [
            'queue_name' => uniqid(),
        ];

        $queue_configs = [];
        foreach (array_keys($required_fields) as $field_to_remove) {
            $queue_config = $required_fields;
            unset($queue_config[$field_to_remove]);

            $queue_configs[] = [$queue_config];
        }

        return $queue_configs;
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
