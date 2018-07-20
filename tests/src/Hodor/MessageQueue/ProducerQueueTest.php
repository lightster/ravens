<?php

namespace Hodor\MessageQueue;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\ProducerQueue
 */
class ProducerQueueTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::push
     */
    public function testMessageCanBeProduced()
    {
        $expected_value = "hi there, " . uniqid();
        $pusher = function ($message) use ($expected_value) {
            $this->assertSame($expected_value, $message);
        };

        $producer_queue = new ProducerQueue($pusher);
        $producer_queue->push($expected_value);
    }
}
