<?php

namespace Hodor\MessageQueue;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\ConsumerQueue
 */
class ConsumerQueueTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::consume
     */
    public function testConsumerReceivesExpectedCallback()
    {
        $expected_callback = function () {};
        $consumer = function (callable $callback) use ($expected_callback) {
            $this->assertSame($expected_callback, $callback);
        };

        $consumer_queue = new ConsumerQueue($consumer);
        $consumer_queue->consume($expected_callback);
    }
}
