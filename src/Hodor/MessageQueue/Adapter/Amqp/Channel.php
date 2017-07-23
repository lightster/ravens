<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

class Channel
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $channel_config;

    /**
     * @var AMQPChannel
     */
    private $amqp_channel;

    /**
     * @param Connection $connection
     * @param array $channel_config
     */
    public function __construct(Connection $connection, array $channel_config)
    {
        $this->connection = $connection;
        $this->channel_config = array_merge(
            [
                'fetch_count'              => 1,
            ],
            $channel_config
        );
    }

    /**
     * @return AMQPChannel
     */
    public function getAmqpChannel()
    {
        if ($this->amqp_channel) {
            return $this->amqp_channel;
        }

        $this->amqp_channel = $this->connection->getAmqpConnection()->channel();
        $this->amqp_channel->basic_qos(
            null,
            $this->channel_config['fetch_count'],
            false
        );

        return $this->amqp_channel;
    }
}
