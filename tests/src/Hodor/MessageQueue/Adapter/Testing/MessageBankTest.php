<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Hodor\MessageQueue\Adapter\Testing\MessageBank
 */
class MessageBankTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::consumeMessage
     * @expectedException \Hodor\MessageQueue\Exception\TimeoutException
     */
    public function testConsumingWhileNoMessagesAreQueuedThrowsAnException()
    {
        $message_bank = new MessageBank();

        $message_bank->consumeMessage();
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     */
    public function testProducedMessageCanBeConsumed()
    {
        $message_bank = new MessageBank();

        $message = uniqid();

        $message_bank->produceMessage($message);
        $this->assertSame($message, $message_bank->consumeMessage()->getContent());
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     */
    public function testMultipleProducedMessagesCanBeConsumed()
    {
        $message_bank = new MessageBank();

        $messages = [
            'a-' . uniqid(),
            'b-' . uniqid(),
        ];
        foreach ($messages as $message) {
            $message_bank->produceMessage($message);
        }

        foreach ($messages as $message) {
            $this->assertSame($message, $message_bank->consumeMessage()->getContent());
        }
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     * @expectedException \Hodor\MessageQueue\Exception\TimeoutException
     */
    public function testConsumingWhileNoUnreceivedMessagesAreQueuedThrowsAnException()
    {
        $message_bank = new MessageBank();

        $message_bank->produceMessage('does-not-matter');
        $message_bank->consumeMessage();
        $message_bank->consumeMessage();
    }

    /**
     * @covers ::acknowledgeMessage
     * @expectedException Exception
     */
    public function testAnUnknownMessageCannotBeAcknowledged()
    {
        $message_bank = new MessageBank();

        $message_bank->acknowledgeMessage('unknown');
    }

    /**
     * @covers ::consumeMessage
     * @covers ::produceMessage
     * @covers ::acknowledgeMessage
     * @covers ::emulateReconnect
     */
    public function testOnlyAckedMessagesComeBackOnReconnect()
    {
        $message_bank = new MessageBank();

        $acked_message = 'a-' . uniqid();
        $unacked_message = 'b-' . uniqid();

        $message_bank->produceMessage($acked_message);
        $message_bank->produceMessage($unacked_message);

        $consumed_ack_message = $message_bank->consumeMessage();
        $this->assertSame($acked_message, $consumed_ack_message->getContent());
        $consumed_ack_message->acknowledge();

        $this->assertSame($unacked_message, $message_bank->consumeMessage()->getContent());

        $message_bank->emulateReconnect();

        $this->assertSame($unacked_message, $message_bank->consumeMessage()->getContent());
    }
}
