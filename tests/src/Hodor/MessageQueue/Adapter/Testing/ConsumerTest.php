<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ConsumerTest as BaseConsumerTest;
use Hodor\MessageQueue\OutgoingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Consumer
 */
class ConsumerTest extends BaseConsumerTest
{
    /**
     * @var MessageBank
     */
    private $message_bank;

    public function setUp()
    {
        parent::setUp();

        $this->message_bank = new MessageBank(ConfigProvider::getQueueConfig());
    }

    /**
     * @return ConsumerInterface
     */
    protected function getTestConsumer()
    {
        $test_consumer = new Consumer($this->message_bank);

        return $test_consumer;
    }

    /**
     * @param OutgoingMessage $message
     */
    protected function produceMessage(OutgoingMessage $message)
    {
        $producer = new Producer($this->message_bank);

        $producer->produceMessage($message);
    }
}
