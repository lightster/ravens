<?php

namespace Hodor\MessageQueue;

use Hodor\MessageQueue\Adapter\FactoryInterface;

class Consumer
{
    /**
     * @var FactoryInterface
     */
    private $adapter_factory;

    /**
     * @var ConsumerQueue
     */
    private $consumer_queues;

    /**
     * @param FactoryInterface $adapter_factory
     */
    public function __construct(FactoryInterface $adapter_factory)
    {
        $this->adapter_factory = $adapter_factory;
    }

    /**
     * @param string $queue_key
     * @return ConsumerQueue
     */
    public function getQueue($queue_key)
    {
        if (isset($this->consumer_queues[$queue_key])) {
            return $this->consumer_queues[$queue_key];
        }

        $this->checkQueueKey($queue_key);

        $this->consumer_queues[$queue_key] = new ConsumerQueue(
            function (callable $callback, array $options = null) use ($queue_key) {
                $this->consume($queue_key, $callback, $options);
            }
        );

        return $this->consumer_queues[$queue_key];
    }

    /**
     * @param string $queue_key
     * @param callable $callback to use for handling the message
     * @param array|null $options
     */
    private function consume($queue_key, callable $callback, array $options = null)
    {
        $options = array_merge(
            [
                'soft_time_limit'   => 600,
                'max_message_count' => 1,
            ],
            (null !== $options ? $options : [])
        );

        $start_time = time();
        $message_count = 0;

        $consumer = $this->adapter_factory->getConsumer($queue_key);

        $max_message_count = max(1, intval($options['max_message_count']));
        $max_time = intval($options['soft_time_limit']);

        do {
            $consumer->consumeMessage($callback);
            ++$message_count;
        } while ($message_count < $max_message_count && time() - $start_time <= $max_time);
    }

    /**
     * @param string $queue_key
     */
    private function checkQueueKey($queue_key)
    {
        $this->adapter_factory->getConsumer($queue_key);
    }
}
