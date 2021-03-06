# Linkener PHP Backend Test

Welcome to the test for the position of PHP Backend Developer at Linkener.

In this test we will ask you to modify an existing RESTful microservice that registers readings coming from a measuring device.

The application already contains a working implementation of some parts of the API.

> Before you start coding please make sure the software runs correctly.

## deliver

Please read the [API](#api) section of this document carefully, it contains all requirements of the test. If you have any doubts, please contact us.

You can create a fork of this repository and send us the link, or you can zip your finished work and send the zip-archive (please don't include the /vendor folder in the zip). It doesn't really matter how you send it to us :)

We will evaluate your code and may invite you to our office to talk about it and do a final interview.

## install

1. Install [docker](https://docs.docker.com/install/) (>= 18.02.0)
2. Install [docker-compose](https://docs.docker.com/compose/install/) (>= 1.20.0)
3. Run `docker-compose up` and wait until the output stops (then leave this running and continue in a new terminal)
4. Run `docker-compose exec php composer install` to install the composer dependencies
5. Run `docker-compose exec php bin/console doctrine:schema:create` to create the database tables

> Note: this software uses port 8080 and 8081. The command `docker-compose up` will fail if those ports are not available

Check if the application is up and running by navigating to <http://localhost:8080/system/health>. This URL returns a JSON file containing the system time.

To stop the software Ctrl+C (terminate) the running `docker-compose up` command and run `docker-compose rm` to remove the containers.

### docker

If you are not familiar with docker please read this:

- if `docker-compose up` fails with errors: please contact us! We are not looking for a DevOp, so this will not have any negative impact on your application at Linkener.
- `docker-compose up` does not terminate when it is running correctly. If you terminate that process, then all services (see below) are terminated as well
- the MySQL is not exposed to your host system, you can only access it through docker
- changes in any file in the `docker/*` folder only apply when you restart the docker container

`docker-compose up` command starts 4 services:

- The [nginx](https://hub.docker.com/_/nginx) that proxies the requests to the PHP-FPM
- The [application](./Dockerfile) that is a custom made docker images based on [PHP-FPM v7.3](https://hub.docker.com/_/php) that runs the applications PHP code
- The [mysql v5.7](https://hub.docker.com/_/mysql) for development/testing purposes
- The [swagger-ui app](https://hub.docker.com/r/swaggerapi/swagger-ui) to explore and test the API

The service exposes its `php_info()` for you to better understand how the development environment works. You can access it at <http://localhost:8080/system/phpInfo> 

### Xdebug

The application runs with an enabled Xdebug v2.7 in case you want to debug during development. This README does not provide a guide on how to set up Xdebug in your IDE with docker. There are lots of resources available for different IDEs on the internet.

> If you run this software with docker on an operating system other then Linux, you highly likely need to change some values in the `docker/php.ini` file for Xdebug to work with your IDE.

## API

This software was created using the [symfony best practices](https://symfony.com/doc/current/best_practices/creating-the-project.html) in version 4.3 with PHP 7.3 and should be run in docker.

The API definition can be found in the [open api 3 format](./doc/api.yml). If you started this software with docker compose, then you can access the swagger-ui version of this definition at <http://localhost:8081/>. The "Try it out" feature for the API is fully working and will request the running application.

### Meters

The `meters` part of the API is already implemented and serves as a reference for you.

> Note: you do NOT need to implement anything regarding the /meter API. You work exclusively on the /reading API!

A Meter is a device that measures energy consumption. For the proposes of this test a meter just has one important property: `serial`. The serial is a [UUID v4](https://en.wikipedia.org/wiki/Universally_unique_identifier) which identifies the meter. The serial also serves as primary key for the `meter` database table.

### Readings

> Note: this is the part of the API you are suppose to implement!

A meter regularly reads the energy consumption and sends it to this application. The meter sends a POST request to this application containing the measurement.

We expect you to:

- create a new database table `reading` that stores the measurements of the meters. 
    - Internal rules dictate, that all dates must be stored as type "datetime" in MySQL, even though the meters send timestamps.
    - There can only ever be one Reading per meter and time

- implement and test the `POST /api/v1/reading` API as specified in <http://localhost:8081/#/readings/createReading>. The code must: 
    - identify the meter using the `X-Serial` header and return a 404 if the meter is not found
    - if a Reading for the given meter and time already exists: the existing value in the database must be overwritten
    - return a 400 if the request body contains invalid JSON or an invalid reading object
    - return a 200 containing the serialized reading 
