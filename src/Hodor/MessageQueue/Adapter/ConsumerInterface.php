<?php

namespace Hodor\MessageQueue\Adapter;

interface ConsumerInterface
{
    /**
     * @param callable $callback
     * @param array|null $options
     */
    public function consumeMessage(callable $callback, array $options = null);
}
