<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Exception\TimeoutException;
use Hodor\MessageQueue\IncomingMessage as MqMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class Consumer implements ConsumerInterface
{
    /**
     * @var ConsumerStrategy
     */
    private $consumer_strategy;

    /**
     * @param ConsumerStrategy $consumer_strategy
     */
    public function __construct(ConsumerStrategy $consumer_strategy)
    {
        $this->consumer_strategy = $consumer_strategy;
    }

    /**
     * @param callable $callback
     * @param array|null $options
     * @throws TimeoutException
     */
    public function consumeMessage(callable $callback, array $options = null)
    {
        $options = array_merge(
            [
                'wait_timeout' => 0,
            ],
            (null !== $options ? $options : [])
        );

        $amqp_channel = $this->getChannel()->getAmqpChannel();

        $amqp_channel->basic_consume(
            $this->consumer_strategy->getQueueName(),
            '',
            false,
            false,
            false,
            false,
            function ($amqp_message) use ($callback) {
                $message = new MqMessage(new Message($amqp_message));
                $callback($message);
            }
        );

        try {
            $amqp_channel->wait(null, false, intval($options['wait_timeout']));
        } catch (AMQPTimeoutException $exception) {
            throw new TimeoutException();
        }
    }

    /**
     * @return Channel
     */
    private function getChannel()
    {
        return $this->consumer_strategy->getDeliveryStrategy()->getChannel();
    }
}
