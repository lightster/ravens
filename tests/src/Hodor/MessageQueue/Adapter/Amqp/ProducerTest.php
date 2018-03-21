<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConfigInterface;
use Hodor\MessageQueue\Adapter\ProducerInterface;
use Hodor\MessageQueue\Adapter\ProducerTest as BaseProducerTest;
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
     * @var ConfigInterface
     */
    private $config;

    public function setUp()
    {
        $this->config = ConfigProvider::getConfigAdapter(['only_q']);
    }

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->channel_factories as $channel_factory) {
            $channel_factory->disconnectAll();
        }
    }

    /**
     * @return ProducerInterface
     */
    protected function getTestProducer()
    {
        $strategy_factory = $this->generateStrategyFactory();
        $test_producer = new Producer($strategy_factory->getProducerStrategy('only_q'));

        return $test_producer;
    }

    /**
     * @return string
     * @throws \Hodor\MessageQueue\Exception\TimeoutException
     */
    protected function consumeMessage()
    {
        $strategy_factory = $this->generateStrategyFactory();
        $consumer = new Consumer($strategy_factory->getConsumerStrategy('only_q'));

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
     * @return DeliveryStrategyFactory
     */
    private function generateStrategyFactory()
    {
        $channel_factory = new ChannelFactory($this->config);

        $this->channel_factories[] = $channel_factory;

        return new DeliveryStrategyFactory($channel_factory);
    }
}
