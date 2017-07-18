<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Exception\TimeoutException;
use Hodor\MessageQueue\IncomingMessage as MqMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class Consumer implements ConsumerInterface
{
    /**
     * @var string
     */
    private $queue_key;

    /**
     * @var DeliveryStrategyFactory
     */
    private $strategy_factory;

    /**
     * @var DeliveryStrategy
     */
    private $delivery_strategy;

    /**
     * @param string $queue_key
     * @param DeliveryStrategyFactory $strategy_factory
     */
    public function __construct($queue_key, DeliveryStrategyFactory $strategy_factory)
    {
        $this->queue_key = $queue_key;
        $this->strategy_factory = $strategy_factory;
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
            $this->getDeliveryStrategy()->getQueueName(),
            '',
            false,
            ($auto_ack = false),
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
     * @return DeliveryStrategy
     */
    private function getDeliveryStrategy()
    {
        if ($this->delivery_strategy) {
            return $this->delivery_strategy;
        }

        $this->delivery_strategy = $this->strategy_factory->getProducerStrategy(
            $this->queue_key
        );

        return $this->delivery_strategy;
    }

    /**
     * @return Channel
     */
    private function getChannel()
    {
        return $this->getDeliveryStrategy()->getChannel();
    }
}
