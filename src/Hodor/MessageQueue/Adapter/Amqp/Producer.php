<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\OutgoingMessage;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;

class Producer implements ProducerInterface
{
    /**
     * @var DeliveryStrategy
     */
    private $delivery_strategy;

    /**
     * @param DeliveryStrategy $delivery_strategy
     */
    public function __construct(DeliveryStrategy $delivery_strategy)
    {
        $this->delivery_strategy = $delivery_strategy;
    }

    /**
     * @param OutgoingMessage $message
     */
    public function produceMessage(OutgoingMessage $message)
    {
        $this->getChannel()->getAmqpChannel()->basic_publish(
            $this->generateAmqpMessage($message),
            $this->getDeliveryStrategy()->getExchangeName(),
            $this->getDeliveryStrategy()->getRoutingKey()
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
                $this->getDeliveryStrategy()->getExchangeName(),
                $this->getDeliveryStrategy()->getRoutingKey()
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
