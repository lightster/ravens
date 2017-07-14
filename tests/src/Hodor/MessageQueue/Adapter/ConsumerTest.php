<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\OutgoingMessage;
use PHPUnit_Framework_TestCase;

abstract class ConsumerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::consumeMessage
     * @covers ::<private>
     */
    public function testMessageCanBeConsumed()
    {
        $unique_message = 'hello ' . uniqid();

        $this->produceMessage(new OutgoingMessage($unique_message));

        $this->getTestConsumer()->consumeMessage(function (IncomingMessage $message) use ($unique_message) {
            $this->assertEquals($unique_message, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::consumeMessage
     * @covers ::<private>
     * @expectedException \Hodor\MessageQueue\Exception\TimeoutException
     */
    public function testWaitingForMessageCanBeTimedOut()
    {
        $this->getTestConsumer()->consumeMessage(function () {
            $this->fail('There should have been no message to consume.');
        }, ['wait_timeout' => 1]);
    }

    /**
     * @covers ::getMaxMessagesPerConsume
     */
    public function testMaxMessagesPerConsumeIsReturnedAsExpected()
    {
        $max_messages_per_consume = 3;

        $config_overrides = [
            'max_messages_per_consume' => $max_messages_per_consume
        ];

        $this->assertSame(
            $max_messages_per_consume,
            $this->getTestConsumer($config_overrides)->getMaxMessagesPerConsume()
        );
    }

    /**
     * @covers ::getMaxTimePerConsume
     */
    public function testMaxTimePerConsumeIsReturnedAsExpected()
    {
        $max_time_per_consume = 60;

        $config_overrides = [
            'max_time_per_consume' => $max_time_per_consume
        ];

        $this->assertSame(
            $max_time_per_consume,
            $this->getTestConsumer($config_overrides)->getMaxTimePerConsume()
        );
    }

    /**
     * @param array $config_overrides
     * @return ConsumerInterface
     */
    abstract protected function getTestConsumer(array $config_overrides = []);

    /**
     * @param OutgoingMessage $message
     */
    abstract protected function produceMessage(OutgoingMessage $message);
}
