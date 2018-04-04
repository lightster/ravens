<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\FactoryInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;

class Factory implements FactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ChannelFactory
     */
    private $channel_factory;

    /**
     * @var DeliveryStrategyFactory
     */
    private $delivery_strategy_factory;

    /**
     * @var Consumer[]
     */
    private $consumers = [];

    /**
     * @var Producer[]
     */
    private $producers = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $queue_key
     * @return ConsumerInterface
     */
    public function getConsumer($queue_key)
    {
        if (array_key_exists($queue_key, $this->consumers)) {
            return $this->consumers[$queue_key];
        }

        $consumer_strategy = $this->getDeliveryStrategyFactory()->getConsumerStrategy($queue_key);
        $this->consumers[$queue_key] = new Consumer($consumer_strategy);

        return $this->consumers[$queue_key];
    }

    /**
     * @param string $queue_key
     * @return ProducerInterface
     */
    public function getProducer($queue_key)
    {
        if (array_key_exists($queue_key, $this->producers)) {
            return $this->producers[$queue_key];
        }

        $delivery_strategy = $this->getDeliveryStrategyFactory()->getProducerStrategy($queue_key);
        $this->producers[$queue_key] = new Producer($delivery_strategy);

        return $this->producers[$queue_key];
    }

    public function disconnectAll()
    {
        if (!$this->channel_factory) {
            return;
        }

        $this->channel_factory->disconnectAll();
    }

    /**
     * @return DeliveryStrategyFactory
     */
    private function getDeliveryStrategyFactory()
    {
        if ($this->delivery_strategy_factory) {
            return $this->delivery_strategy_factory;
        }

        $this->channel_factory = new ChannelFactory($this->config);
        $this->delivery_strategy_factory = new DeliveryStrategyFactory($this->channel_factory);

        return $this->delivery_strategy_factory;
    }
}
