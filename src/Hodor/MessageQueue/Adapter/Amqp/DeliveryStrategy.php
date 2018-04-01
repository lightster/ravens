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

    /**
     * @return string
     */
    public function getExchangeName()
    {
        return $this->queue_config['exchange_name'];
    }

    /**
     * @return string
     */
    public function getRoutingKey()
    {
        return $this->queue_config['queue_name'];
    }

    private function initialize()
    {
        $amqp_channel = $this->channel->getAmqpChannel();

        $amqp_channel->queue_declare(
            $this->queue_config['queue_name'],
            $this->queue_config['passive'],
            $this->queue_config['durable'],
            $this->queue_config['exclusive'],
            $this->queue_config['auto_delete']
        );

        if ($this->getExchangeName()) {
            $amqp_channel->exchange_declare(
                $this->getExchangeName(),
                'direct'
            );
            $amqp_channel->queue_bind(
                $this->queue_config['queue_name'],
                $this->getExchangeName(),
                $this->getRoutingKey()
            );
        }

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

        $this->queue_config = array_merge(
            [
                'exchange_name' => '',
                'passive'       => false,
                'durable'       => true,
                'exclusive'     => false,
                'auto_delete'   => false,
            ],
            $this->queue_config
        );
    }
}
