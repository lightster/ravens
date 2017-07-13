<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use LogicException;

class DeliveryStrategy
{
    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var array
     */
    private $queue_config;

    /**
     * @var bool
     */
    private $is_initialized = false;

    /**
     * @param Channel $channel
     * @param array $queue_config
     */
    public function __construct(Channel $channel, array $queue_config)
    {
        $this->channel = $channel;
        $this->queue_config = $queue_config;

        $this->validateConfig();
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        if ($this->is_initialized) {
            return $this->channel;
        }

        $this->initialize();

        return $this->channel;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queue_config['queue_name'];
    }

    private function initialize()
    {
        $this->channel->getAmqpChannel()->queue_declare(
            $this->queue_config['queue_name'],
            false,
            ($is_durable = true),
            false,
            false
        );

        $this->is_initialized = true;
    }

    /**
     * @throws LogicException
     */
    private function validateConfig()
    {
        foreach (['queue_name'] as $key) {
            if (empty($this->queue_config[$key])) {
                throw new LogicException("The channel config must contain a '{$key}' config.");
            }
        }
    }
}
