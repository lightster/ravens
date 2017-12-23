<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Adapter\ProducerTest as BaseProducerTest;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\IncomingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Producer
 */
class ProducerTest extends BaseProducerTest
{
    /**
     * @var ChannelFactory[]
     */
    private $channel_factories;

    /**
     * @var Config
     */
    private $config;

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->channel_factories as $channel_factory) {
            $channel_factory->disconnectAll();
        }
    }

    /**
     * @param array $config_overrides
     * @return ProducerInterface
     */
    protected function getTestProducer(array $config_overrides = [])
    {
        $strategy_factory = $this->generateStrategyFactory($this->getTestConfig($config_overrides));
        $test_producer = new Producer($strategy_factory->getProducerStrategy('fast_jobs'));

        return $test_producer;
    }

    /**
     * @return string
     * @throws \Hodor\MessageQueue\Exception\TimeoutException
     */
    protected function consumeMessage()
    {
        $strategy_factory = $this->generateStrategyFactory($this->getTestConfig());
        $consumer = new Consumer($strategy_factory->getConsumerStrategy('fast_jobs'));

        $consumer->consumeMessage(function (IncomingMessage $message) use (&$return) {
            $return = $message->getContent();
            $message->acknowledge();
        });

        // disconnect after consuming so the unused channel does not prefetch
        // and hold a message unack'd while another channel is looking for it
        foreach ($this->channel_factories as $channel_factory) {
            $channel_factory->disconnectAll();
        }

        return $return;
    }

    /**
     * @param Config $config
     * @return DeliveryStrategyFactory
     */
    private function generateStrategyFactory(Config $config)
    {
        $channel_factory = new ChannelFactory($config);

        $this->channel_factories[] = $channel_factory;

        return new DeliveryStrategyFactory($channel_factory);
    }

    /**
     * @param array $config_overrides
     * @return Config
     */
    private function getTestConfig(array $config_overrides = [])
    {
        if ($this->config) {
            return $this->config;
        }

        $config_provider = new ConfigProvider($this);
        $test_queues = $this->getTestQueues($config_provider);
        $this->config = $config_provider->getConfigAdapter($test_queues, $config_overrides);

        return $this->config;
    }

    /**
     * @param ConfigProvider $config_provider
     * @return array
     */
    private function getTestQueues(ConfigProvider $config_provider)
    {
        return [
            'fast_jobs' => $config_provider->getQueueConfig(),
        ];
    }
}
