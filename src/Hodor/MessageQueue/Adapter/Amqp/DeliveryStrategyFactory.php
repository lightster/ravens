<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

class DeliveryStrategyFactory
{
    /**
     * @var ChannelFactory
     */
    private $channel_factory;

    /**
     * @var DeliveryStrategy[]
     */
    private $delivery_strategy = [];

    /**
     * @param ChannelFactory $channel_factory
     */
    public function __construct(ChannelFactory $channel_factory)
    {
        $this->channel_factory = $channel_factory;
    }

    /**
     * @param string $queue_key
     * @return DeliveryStrategy
     */
    public function getConsumerStrategy($queue_key)
    {
        return $this->getStrategy('consumer', $queue_key);
    }

    /**
     * @param string $queue_key
     * @return DeliveryStrategy
     */
    public function getProducerStrategy($queue_key)
    {
        return $this->getStrategy('producer', $queue_key);
    }

    /**
     * @param string $use
     * @param string $queue_key
     * @return DeliveryStrategy
     */
    private function getStrategy($use, $queue_key)
    {
        $cache_key = "{$use}:{$queue_key}";

        if (isset($this->delivery_strategy[$cache_key])) {
            return $this->delivery_strategy[$cache_key];
        }

        $this->delivery_strategy[$cache_key] = new DeliveryStrategy(
            $this->getChannel($use, $queue_key),
            $this->channel_factory->getConfig()->getQueueConfig($queue_key)
        );

        return $this->delivery_strategy[$cache_key];
    }

    /**
     * @param string $use
     * @param string $queue_key
     * @return Channel
     */
    private function getChannel($use, $queue_key)
    {
        if ('producer' === $use) {
            return $this->channel_factory->getProducerChannel($queue_key);
        }

        return $this->channel_factory->getConsumerChannel($queue_key);
    }
}
