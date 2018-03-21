<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\IncomingMessage;
use Hodor\MessageQueue\OutgoingMessage;
use PHPUnit_Framework_TestCase;

abstract class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getProducer
     * @covers ::getConsumer
     * @covers ::<private>
     */
    public function testFactoryGeneratesWorkingProducerAndConsumer()
    {
        $unique_message = 'hello ' . uniqid();

        $factory = $this->getTestFactory();

        $factory->getProducer('only_q')->produceMessage(new OutgoingMessage($unique_message));

        $factory->getConsumer('only_q')->consumeMessage(function (IncomingMessage $message) use ($unique_message) {
            $this->assertEquals($unique_message, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getProducer
     * @covers ::<private>
     */
    public function testProducerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame(
            $factory->getProducer('only_q'),
            $factory->getProducer('only_q')
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getConsumer
     * @covers ::<private>
     */
    public function testConsumerIsReused()
    {
        $factory = $this->getTestFactory();

        $this->assertSame(
            $factory->getConsumer('only_q'),
            $factory->getConsumer('only_q')
        );
    }

    /**
     * @return FactoryInterface
     */
    abstract protected function getTestFactory();
}
