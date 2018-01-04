<?php

namespace Hodor\MessageQueue\Adapter;

interface ConfigInterface
{
    /**
     * @return array
     */
    public function getAdapterFactoryConfig();

    /**
     * @param string $queue_key
     * @return array
     */
    public function getQueueConfig($queue_key);
}
