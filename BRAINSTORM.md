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
