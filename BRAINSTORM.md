# Brainstorming

## Handling Exchanges
While Ravens will probably not directly have a config file (since it is a library, not an
application), this is what I am thinking the configuration concept / objects will be based off: 

```php
$config['exchanges'] = [
    'buffer' => [
        'host'       => 'rabbit-hodor-buffer',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-buffer-exchange'
        'publishers' => [
            'buffer' => ['subscribers' => ['buffer']],
        ],
        'subscribers' => [
            'buffer' => ['queue_name' => 'hodor-buffer'],
        ],
    ],
    'worker' => [
        'host'       => 'rabbit-hodor-worker',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-worker-exchange',
        'publishers' => [
            'worker-default' => [
                'subscribers' => ['worker-default', 'logger-worker']
            ],
            'worker-long-running' => [
                'subscribers' => ['worker-long-running', 'logger-worker']
            ],
        ],
        'subscribers' => [
            'worker-default' => ['queue_name' => 'hodor-worker-default'],
            'worker-long-running' => ['queue_name' => 'hodor-worker-long-running'],
            'logger-worker' => ['queue_name' => 'hodor-logger-worker'],
        ],
    ],

    'manage' => [
        'host'       => 'rabbit-hodor-manage',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-manage-exchange'
        'publishers' => [
            'process-killer' => [
                'subscribers' => env('HODOR_WORKER_SERVER') ? ['process-killer'] : []
            ],
        ],
        'subscribers' => [
            'process-killer' => [
                'queue_name' => 'hodor-process-killer-' . gethostname(),
                'expiration' => 300
            ],
        ],
    ],
];
```

So with this idea, DeliveryStrategy would take a list of queues and then the consumer would
need to know which queue it is consuming from (not via the DeliveryStrategy).

```php
$exchange = $producer->getExchange('worker');
$exchange->push('worker-default', ['the' => 'message']);

$queue = $consumer->getQueue('worker-default');
$queue->consume(
    function (IncomingMessage $message) {
        $message->acknowledge();
    },
    ['max_message_count' => 3]
);
```

## Handling Forks

If a process is forked, any channels that were open when the process was forked need to
be re-initialized.  That is, channels cannot be re-used across forks.  However, the
connection can be re-used across the forked processes.

The connection does require special handling, though, when it comes to closing the
connection.  The child processes should not close the connection when exiting because
the connection may be in use by other child processes or the parent process.

A few potential solutions:
 - Record the PID when creating an AMQP channel and check the PID every time the channel
   is used.  If the PID has changed, then re-initialize the AMQP channel.  Calling getmypid()
   in PHP a million times on my 3+ year old Mac runs in about 0.12 seconds.
 - Add a method to Adapter\FactoryInterface that allows for the process to be marked as
   a fork, causing all channels to be re-initialized.  
