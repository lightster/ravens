<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\Testing\Config;

class ConfigProvider
{
    /**
     * @param array $queues
     * @param array $config_overrides
     * @return Config
     */
    public static function getConfigAdapter(array $queues, array $config_overrides = [])
    {
        $config = new Config([]);
        foreach ($queues as $queue_key => $queue_config) {
            if (is_string($queue_config)) {
                $queue_key = $queue_config;
                $queue_config = self::getQueueConfig();
            }

            $queue_config = array_merge($queue_config, $config_overrides);
            $config->addQueueConfig($queue_key, $queue_config);
        }

        return $config;
    }

    /**
     * @return array
     */
    public static function getQueueConfig()
    {
        $rabbit_credentials = self::getRabbitCredentials();

        return [
            'host'       => $rabbit_credentials['host'],
            'port'       => $rabbit_credentials['port'],
            'username'   => $rabbit_credentials['username'],
            'password'   => $rabbit_credentials['password'],
            'queue_name' => $rabbit_credentials['queue_prefix'] . uniqid(),
        ];
    }

    /**
     * @return array
     */
    private static function getRabbitCredentials()
    {
        $config = require __DIR__ . '/../../../../../../config/config.test.php';

        return $config['test']['rabbitmq'];
    }
}
