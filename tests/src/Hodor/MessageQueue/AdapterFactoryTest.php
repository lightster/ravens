<?php

namespace Hodor\MessageQueue;

use Exception;
use Hodor\MessageQueue\Adapter\Testing\Config;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\AdapterFactory
 */
class AdapterFactoryTest extends TestCase
{
    /**
     * @covers ::getAdapter
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testExceptionIsThrownIfAdapterTypeIsNotProvided()
    {
        $factory = new AdapterFactory();
        $config = new Config([]);

        $factory->getAdapter($config);
    }

    /**
     * @covers ::getAdapter
     * @covers ::<private>
     * @expectedException Exception
     */
    public function testExceptionIsThrownIfUnknownAdapterTypeIsProvided()
    {
        $factory = new AdapterFactory();
        $config = new Config(['type' => 'unknown']);

        $factory->getAdapter($config);
    }

    /**
     * @covers ::getAdapter
     * @covers ::<private>
     */
    public function testTestingAdapterCanBeGenerated()
    {
        $factory = new AdapterFactory();
        $config = new Config(['type' => 'testing']);

        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Testing\Factory',
            $factory->getAdapter($config)
        );
    }

    /**
     * @covers ::getAdapter
     * @covers ::<private>
     */
    public function testAmqpAdapterCanBeGenerated()
    {
        $factory = new AdapterFactory();
        $config = new Config(['type' => 'amqp']);

        $this->assertInstanceOf(
            'Hodor\MessageQueue\Adapter\Amqp\Factory',
            $factory->getAdapter($config)
        );
    }
}
