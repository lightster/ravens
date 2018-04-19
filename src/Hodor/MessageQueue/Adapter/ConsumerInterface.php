<?php

namespace Hodor\MessageQueue\Adapter;

use Hodor\MessageQueue\Exception\TimeoutException;

interface ConsumerInterface
{
    /**
     * @param callable $callback
     * @param array|null $options
     * @throws TimeoutException
     */
    public function consumeMessage(callable $callback, array $options = null);
}
