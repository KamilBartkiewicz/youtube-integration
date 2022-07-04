# Youtube Channel Subscriptions App

Simple application to receive data about our subscriptions on Youtube platform.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose up` (the logs will be displayed in the current shell)
3. Run `docker exec -it symfony bash`, next `cd app/` and run `composer install`
4. pen `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker-compose down` to stop the Docker containers.

## Features

* Google oAuth2 authorization (only in specified emails, app in development mode).
* MySQL database (not used for now).
* Cache based on FileSystem (not enough time to implement redis)

## Test Account
* username: symfony.test.application@gmail.com
* password: Qwerty123#
