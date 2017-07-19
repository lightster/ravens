<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use LogicException;

class ChannelFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Connection[]
     */
    private $connections = [];

    /**
     * @var Channel[]
     */
    private $channels = [];

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function disconnectAll()
    {
        foreach ($this->connections as $connection) {
            $connection->disconnect();
        }
    }

    /**
     * @param string $queue_key
     * @return Channel
     */
    public function getConsumerChannel($queue_key)
    {
        return $this->getChannel('consumer', $queue_key);
    }

    /**
     * @param string $queue_key
     * @return Channel
     */
    public function getProducerChannel($queue_key)
    {
        return $this->getChannel('producer', $queue_key);
    }

    /**
     * @param  string $queue_key
     * @param  string $use
     * @return Channel
     */
    private function getChannel($use, $queue_key)
    {
        $queue_config = $this->getQueueConfig($queue_key);
        $channel_key = $this->getChannelKey($use, $queue_config);

        if (isset($this->channels[$channel_key])) {
            return $this->channels[$channel_key];
        }

        $connection = $this->getConnection($queue_config);

        $this->channels[$channel_key] = new Channel($connection, $queue_config);

        return $this->channels[$channel_key];
    }

    /**
     * @param $queue_key
     * @return array
     */
    private function getQueueConfig($queue_key)
    {
        $queue_config = array_merge(
            [
                'fetch_count'     => 1,
                'connection_type' => 'stream',
            ],
            $this->config->getQueueConfig($queue_key)
        );

        foreach (['host', 'port', 'username', 'password', 'queue_name'] as $key) {
            if (empty($queue_config[$key])) {
                throw new LogicException("The queue config must contain a '{$key}' config.");
            }
        }

        return $queue_config;
    }

    /**
     * @param  array  $queue_config
     * @return Connection
     */
    private function getConnection(array $queue_config)
    {
        $connection_key = $this->getConnectionKey($queue_config);

        if (isset($this->connections[$connection_key])) {
            return $this->connections[$connection_key];
        }

        $this->connections[$connection_key] = new Connection($queue_config);

        return $this->connections[$connection_key];
    }

    /**
     * @param  array  $queue_config
     * @return string
     */
    private function getConnectionKey(array $queue_config)
    {
        return implode(
            '::',
            [
                $queue_config['host'],
                $queue_config['port'],
                $queue_config['username'],
            ]
        );
    }

    /**
     * @param string $use
     * @param array $queue_config
     * @return string
     */
    private function getChannelKey($use, array $queue_config)
    {
        return implode(
            '::',
            [
                $this->getConnectionKey($queue_config),
                $use,
                $queue_config['fetch_count'],
            ]
        );
    }
}
