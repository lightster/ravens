<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Hodor\MessageQueue\Adapter\ConsumerInterface;
use Hodor\MessageQueue\Exception\TimeoutException;
use Hodor\MessageQueue\IncomingMessage;

class Consumer implements ConsumerInterface
{
    /**
     * @var MessageBank
     */
    private $message_bank;

    /**
     * @param MessageBank $message_bank
     */
    public function __construct(MessageBank $message_bank)
    {
        $this->message_bank = $message_bank;
    }

    /**
     * @param callable $callback
     * @param array|null $options
     * @throws TimeoutException
     */
    public function consumeMessage(callable $callback, array $options = null)
    {
        $message_adapter = $this->message_bank->consumeMessage();
        $incoming_message = new IncomingMessage($message_adapter);

        $callback($incoming_message, $options);
    }
}
