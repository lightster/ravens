<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\OutgoingMessage;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

class Producer implements ProducerInterface
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
     * @param OutgoingMessage $message
     */
    public function produceMessage(OutgoingMessage $message)
    {
        $this->getChannel()->getAmqpChannel()->basic_publish(
            $this->generateAmqpMessage($message),
            '',
            $this->getDeliveryStrategy()->getQueueName()
        );
    }

    /**
     * @param OutgoingMessage[] $messages
     */
    public function produceMessageBatch(array $messages)
    {
        $amqp_channel = $this->getChannel()->getAmqpChannel();

        foreach ($messages as $message) {
            $amqp_channel->batch_basic_publish(
                $this->generateAmqpMessage($message),
                '',
                $this->getDeliveryStrategy()->getQueueName()
            );
        }
        $amqp_channel->publish_batch();
    }

    /**
     * @param OutgoingMessage $message
     * @return AMQPMessage
     * @throws RuntimeException
     */
    private function generateAmqpMessage(OutgoingMessage $message)
    {
        return new AMQPMessage(
            $message->getEncodedContent(),
            [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]
        );
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
