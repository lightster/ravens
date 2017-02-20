<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\FactoryInterface;

class Producer
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var bool
     */
    private $is_in_batch = false;

    /**
     * @var array
     */
    private $producer_queues = [];

    /**
     * @var array
     */
    private $batches = [];

    /**
     * @param FactoryInterface $adapter_factory
     */
    public function __construct(FactoryInterface $adapter_factory)
    {
        $this->adapter_factory = $adapter_factory;
    }

    /**
     * @param string $queue_key
     * @return ProducerQueue
     */
    public function getQueue($queue_key)
    {
        if (isset($this->producer_queues[$queue_key])) {
            return $this->producer_queues[$queue_key];
        }

        $this->checkQueueKey($queue_key);

        $this->producer_queues[$queue_key] = new ProducerQueue(function ($message) use ($queue_key) {
            $this->push($queue_key, $message);
        });

        return $this->producer_queues[$queue_key];
    }

    public function beginBatch()
    {
        if ($this->is_in_batch) {
            throw new Exception("The queue is already in transaction.");
        }

        $this->is_in_batch = true;
    }

    public function publishBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        foreach ($this->batches as $queue_key => $batch) {
            $this->adapter_factory->getProducer($queue_key)->produceMessageBatch($batch);
        }

        $this->is_in_batch = false;
        $this->batches = [];
    }

    public function discardBatch()
    {
        if (!$this->is_in_batch) {
            throw new Exception("The queue is not in transaction.");
        }

        $this->is_in_batch = false;
        $this->batches = [];
    }

    /**
     * @param string $queue_key
     * @param mixed $message
     */
    private function push($queue_key, $message)
    {
        if ($this->is_in_batch) {
            $this->batches[$queue_key][] = new OutgoingMessage($message);
            return;
        }

        $this->adapter_factory->getProducer($queue_key)->produceMessage(new OutgoingMessage($message));
    }

    /**
     * @param string $queue_key
     */
    private function checkQueueKey($queue_key)
    {
        $this->adapter_factory->getProducer($queue_key);
    }
}
