While Ravens will probably not directly have a config file (since it is a library, not an
application), this is what I am thinking the configuration concept / objects will be based off: 

```php
$config['exchanges'] = [
    'hodor-buffer' => [
        'host'       => 'rabbit-hodor-buffer',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-buffer-exchange'
        'publishers' => [
            'hodor-buffer' => ['subscribers' => ['hodor-buffer']],
        ],
    ],
    'hodor-worker' => [
        'host'       => 'rabbit-hodor-worker',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-worker-exchange',
        'publishers' => [
            'hodor-worker-default' => ['subscribers' => ['hodor-worker-default', 'hodor-logger-worker']],
            'hodor-worker-long-running' => ['subscribers' => ['hodor-worker-long-running', 'hodor-logger-worker']],
        ],
    ],

    'hodor-manage' => [
        'host'       => 'rabbit-hodor-manage',
        'port'       => '',
        'username'   => '',
        'password'   => '',
        'exchange'   => 'hodor-manage-exchange'
        'publishers' => [
            'hodor-manage' => ['subscribers' => gethostname()],
        ],
        'subscribers' => [
            gethostname() => ['expiration' => 300],
        ],
    ],
];
```
