<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\Amqp\ConfigProvider;
use Hodor\MessageQueue\Adapter\FactoryTest as BaseFactoryTest;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\Factory
 */
class FactoryTest extends BaseFactoryTest
{
    /**
     * @return Factory
     */
    protected function getTestFactory()
    {
        return new Factory(ConfigProvider::getConfigAdapter(['only_q']));
    }
}
