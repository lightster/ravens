<?php

namespace Hodor\MessageQueue;

use Hodor\MessageQueue\Adapter\Testing\Config;
use Hodor\MessageQueue\Adapter\Testing\Factory;
use Hodor\MessageQueue\Adapter\Testing\MessageBankFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Consumer
 */
class ConsumerTest extends TestCase
{
    /**
     * @var MessageBankFactory
     */
    private $message_bank_factory;

    /**
     * @var Factory
     */
    private $adapter_factory;

    /**
     * @var Consumer
     */
    private $consumer;

    public function setUp()
    {
        parent::setUp();

        $config = new Config([]);
        $config->addQueueConfig('some-queue-name', []);
        $config->addQueueConfig('multi-message-queue-name', []);
        $config->addQueueConfig('limited-time-queue', []);
        $this->message_bank_factory = new MessageBankFactory();
        $this->adapter_factory = new Factory($config, $this->message_bank_factory);
        $this->consumer = new Consumer($this->adapter_factory);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::consume
     * @covers ::<private>
     */
    public function testMessageCanBeConsumed()
    {
        $expected = ['name' => __METHOD__, 'number' => 1];

        $message_bank = $this->message_bank_factory->getMessageBank('some-queue-name');

        $message_bank->produceMessage(json_encode($expected));
        $this->consumer->getQueue('some-queue-name')->consume(function (IncomingMessage $message) use ($expected) {
            $this->assertSame($expected, $message->getContent());
            $message->acknowledge();
        });
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::consume
     * @covers ::<private>
     */
    public function testMaxMessagesPerConsumeIsRespected()
    {
        $message_bank = $this->message_bank_factory->getMessageBank('multi-message-queue-name');

        for ($i = 1; $i <= 6; $i++) {
            $message_bank->produceMessage(json_encode($i));
        }

        $sum = 0;
        $this->consumer->getQueue('multi-message-queue-name')->consume(
            function (IncomingMessage $message) use (&$sum) {
                $sum += $message->getContent();
            },
            ['max_message_count' => 5]
        );

        $this->assertSame(1 + 2 + 3 + 4 + 5, $sum);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::consume
     * @covers ::<private>
     */
    public function testTimePerConsumeIsRespected()
    {
        $message_bank = $this->message_bank_factory->getMessageBank('limited-time-queue');

        for ($i = 1; $i <= 3; $i++) {
            $message_bank->produceMessage(json_encode($i));
        }

        $sum = 0;
        $this->consumer->getQueue('limited-time-queue')->consume(
            function (IncomingMessage $message) use (&$sum) {
                sleep(2);
                $sum += $message->getContent();
            },
            ['soft_time_limit' => 1, 'max_message_count' => 5]
        );

        $this->assertSame(1, $sum);
    }

    /**
     * @covers ::__construct
     * @covers ::getQueue
     * @covers ::consume
     * @covers ::<private>
     */
    public function testConsumerQueueIsReused()
    {
        $this->assertSame(
            $this->consumer->getQueue('limited-time-queue'),
            $this->consumer->getQueue('limited-time-queue')
        );
        $this->assertNotSame(
            $this->consumer->getQueue('some-queue-name'),
            $this->consumer->getQueue('limited-time-queue')
        );
    }
}
