<?php

namespace Hodor\MessageQueue\Adapter;

interface FactoryInterface
{
    /**
     * @param string $queue_key
     * @return ConsumerInterface
     */
    public function getConsumer($queue_key);

    /**
     * @param string $queue_key
     * @return ProducerInterface
     */
    public function getProducer($queue_key);
}
