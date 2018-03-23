<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Adapter\ConsumerTest as BaseConsumerTest;
use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\OutgoingMessage;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Consumer
 */
class ConsumerTest extends BaseConsumerTest
{
    /**
     * @var ChannelFactory[]
     */
    private $channel_factories;

    /**
     * @var Config
     */
    private $config;

    public function setUp()
    {
        parent::setUp();

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
     * @return ConsumerInterface
     */
    protected function getTestConsumer()
    {
        $strategy_factory = $this->generateStrategyFactory();
        $test_consumer = new Consumer($strategy_factory->getConsumerStrategy('only_q'));

        return $test_consumer;
    }

    /**
     * @param OutgoingMessage $message
     */
    protected function produceMessage(OutgoingMessage $message)
    {
        $strategy_factory = $this->generateStrategyFactory();
        $producer = new Producer($strategy_factory->getProducerStrategy('only_q'));

        $producer->produceMessage($message);
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
