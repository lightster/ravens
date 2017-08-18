#!/bin/bash

case "$1" in
    test)
        php /ravens/vendor/bin/phpunit
        ;;

    install)
        cd /ravens
        composer install
        php -r '
            $config = require_once "/ravens/config/dist/config.test.php";
            $config['test']['rabbitmq']['host'] = 'rabbitmq';
            $config_text = "<?php\nreturn " . var_export($config, true) . ";";
            file_put_contents("/ravens/config/config.test.php", $config_text);
        '
        ;;

    bash)
        /bin/bash
        ;;

    *)
        echo $"Usage: $0 {test|bash}"
        exit 1
esac
