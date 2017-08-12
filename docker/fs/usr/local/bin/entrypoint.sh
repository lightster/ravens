#!/bin/bash

case "$1" in
    test)
        php /ravens/vendor/bin/phpunit
        ;;

    bash)
        /bin/bash
        ;;

    *)
        echo $"Usage: $0 {test|bash}"
        exit 1
esac
