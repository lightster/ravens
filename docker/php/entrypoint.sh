#!/bin/bash

ENV="${1}"
COMMAND="${2}"

wait_for_it ()
{
    local host=$1
    local port=$2

    bash /usr/local/bin/wait-for-it.sh -q "${host}:${port}"
    local exit_code=$?
    if [ $exit_code -eq 0 ]; then
        return
    fi

    echo "Unable to connect to '${host}' port ${port}"
    exit $exit_code
}

wait_on_depends ()
{
    if [ "docker" != "${ENV}" ]; then
        return
    fi

    wait_for_it rabbitmq 5672
}

case "${COMMAND}" in
    test)
        wait_on_depends
        php vendor/bin/phpunit "${@:3}"
        ;;

    install)
        composer install
        php -r '
            $config = require_once "config/dist/config.test.php";
            $config['test']['rabbitmq']['host'] = 'rabbitmq';
            $config_text = "<?php\nreturn " . var_export($config, true) . ";";
            file_put_contents("config/config.test.php", $config_text);
        '
        ;;

    bash)
        /bin/bash
        ;;

    *)
        echo $"Usage: $0 {docker|shell} {test|install|bash}"
        exit 1
esac
