<?php

namespace Hodor\MessageQueue;

class ConsumerQueue
{
    /**
     * @var callable
     */
    private $consumer;

    /**
     * @param callable $consumer
     */
    public function __construct(callable $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @param callable $callback
     * @param array|null $options
     */
    public function consume(callable $callback, array $options = null)
    {
        call_user_func($this->consumer, $callback, $options);
    }
}
