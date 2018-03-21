<?php

namespace Hodor\MessageQueue\Adapter\Amqp;

use Hodor\MessageQueue\Adapter\FactoryTest as BaseFactoryTest;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Amqp\Factory
 */
class FactoryTest extends BaseFactoryTest
{
    /**
     * @var Factory[]
     */
    private $factories;

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->factories as $factory) {
            $factory->disconnectAll();
        }
        $this->factories = [];
    }

    /**
     * @covers ::disconnectAll
     */
    public function testDisconnectAllWorksIfFactoryHasNotBeenUsed()
    {
        $this->getTestFactory()->disconnectAll();

        $this->assertTrue(true);
    }

    /**
     * @covers ::disconnectAll
     */
    public function testDisconnectAllWorksAfterUsingFactory()
    {
        $test_factory = $this->getTestFactory();

        $test_factory->getProducer('only_q');
        $test_factory->disconnectAll();

        $this->assertTrue(true);
    }

    /**
     * @param array $config_overrides
     * @return Factory
     */
    protected function getTestFactory(array $config_overrides = [])
    {
        $config = ConfigProvider::getConfigAdapter(['only_q'], $config_overrides);
        $test_factory = new Factory($config);

        $this->factories[] = $test_factory;

        return $test_factory;
    }
}
