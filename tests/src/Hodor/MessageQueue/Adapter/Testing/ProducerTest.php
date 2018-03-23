<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Adapter\ProducerTest as BaseProducerTest;
use Hodor\MessageQueue\IncomingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Producer
 */
class ProducerTest extends BaseProducerTest
{
    /**
     * @var MessageBank[]
     */
    private $message_banks = [];

    /**
     * @return ProducerInterface
     */
    protected function getTestProducer()
    {
        $message_bank = $this->getMessageBank('only_q');
        $test_producer = new Producer($message_bank);

        return $test_producer;
    }

    /**
     * @return string
     */
    protected function consumeMessage()
    {
        $message_bank = $this->getMessageBank('only_q');
        $consumer = new Consumer($message_bank);

        $consumer->consumeMessage(function (IncomingMessage $message) use (&$return) {
            $return = $message->getContent();
            $message->acknowledge();
        });

        return $return;
    }

    /**
     * @param string $queue_key
     * @return MessageBank
     */
    private function getMessageBank($queue_key)
    {
        if (!empty($this->message_banks[$queue_key])) {
            return $this->message_banks[$queue_key];
        }

        $this->message_banks[$queue_key] = new MessageBank(ConfigProvider::getQueueConfig());

        return $this->message_banks[$queue_key];
    }
}
