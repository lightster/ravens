<?php

namespace Hodor\MessageQueue\Adapter;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getAdapterFactoryConfig();

    /**
     * @param string $queue_key
     * @return array
     */
    public function getQueueConfig($queue_key);
}
