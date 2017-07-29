Ravens
======

[![Build Status](https://travis-ci.org/hold-the-door/ravens.svg?branch=master)](https://travis-ci.org/hold-the-door/ravens)
[![Test Coverage](https://codeclimate.com/github/hold-the-door/ravens/badges/coverage.svg)](https://codeclimate.com/github/hold-the-door/ravens/coverage)
[![Code Climate](https://codeclimate.com/github/hold-the-door/ravens/badges/gpa.svg)](https://codeclimate.com/github/hold-the-door/ravens)

PHP library for connecting to RabbitMQ

## Requirements

 - PHP >= 5.5.18
 - Composer
 - RabbitMQ

## Development Env Tips

 - Set test RabbitMQ queues to automatically delete after 10 minutes
    ```
    rabbitmqctl set_policy expire-test-hodor "test-hodor-.*" '{"expires":600000}' --apply-to queues
    ```
