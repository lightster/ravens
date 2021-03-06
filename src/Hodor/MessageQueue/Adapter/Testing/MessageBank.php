<?php

namespace Hodor\MessageQueue\Adapter\Testing;

use Exception;
use Hodor\MessageQueue\Exception\TimeoutException;

class MessageBank
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var array
     */
    private $queue_config;

    /**
     * @param array $queue_config
     */
    public function __construct(array $queue_config = [])
    {
        $this->queue_config = $queue_config;
    }

    /**
     * @param string $message_id
     * @throws Exception
     */
    public function acknowledgeMessage($message_id)
    {
        if (!array_key_exists($message_id, $this->messages)) {
            throw new Exception("Message with ID '{$message_id}' not found when acking message.");
        }

        $this->messages[$message_id]['is_acked'] = true;
    }

    /**
     * @param string $content
     */
    public function produceMessage($content)
    {
        $this->messages[uniqid()] = [
            'content'     => $content,
            'is_acked'    => false,
            'is_received' => false,
        ];
    }

    /**
     * @return Message
     * @throws TimeoutException
     */
    public function consumeMessage()
    {
        foreach ($this->messages as $message_id => &$message) {
            if (!$message['is_received']) {
                $message['is_received'] = true;
                return new Message($message['content'], $this, $message_id);
            }
        }

        throw new TimeoutException("There are no messages to consume.");
    }

    public function emulateReconnect()
    {
        $original_messages = $this->messages;
        $this->messages = [];
        foreach ($original_messages as $message) {
            if (!$message['is_acked']) {
                $this->produceMessage($message['content']);
            }
        }
    }
}
