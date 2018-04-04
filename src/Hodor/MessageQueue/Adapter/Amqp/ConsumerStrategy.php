<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

class ConsumerStrategy
{
    /**
     * @var DeliveryStrategy
     */
    private $delivery_strategy;

    /**
     * @var string
     */
    private $queue_key;

    /**
     * @param DeliveryStrategy $delivery_strategy
     * @param string $queue_key
     */
    public function __construct(DeliveryStrategy $delivery_strategy, $queue_key)
    {
        $this->delivery_strategy = $delivery_strategy;
        $this->queue_key = $queue_key;
    }

    /**
     * @return DeliveryStrategy
     */
    public function getDeliveryStrategy()
    {
        return $this->delivery_strategy;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->delivery_strategy->getQueueName();
    }
}
