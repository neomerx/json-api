# Overview

This is performance test suite for the library that uses [Blackfire](https://blackfire.io) _Performance Management Solution_.

## Prerequisites
- Install [docker](https://docs.docker.com/install/#supported-platforms);
- Install  [docker-compose](https://docs.docker.com/compose/install/).

## Installation

- Copy `blackfire.io.env.sample` to `blackfire.io.env`;
- Put your Client ID, Client Token, Server ID and Server Token to `blackfire.io.env` from [Blackfire.io credentials page](https://blackfire.io/my/settings/credentials) (registration needed).

## Profile Performance

```bash
$ docker-compose run --rm cli_php blackfire run php -d zend.assertions=-1 /app/sample/sample.php -t=100
```

The output will contain basic performance info and a URL with detailed profiling info [such as this one](https://blackfire.io/profiles/207fb294-d851-48ad-a31c-db29478172e3/graph).

> Note: The **first** run will download necessary docker images which takes some time. The subsequent runs will not require such downloads and be faster. 

The created container can be removed from the local machine with

```bash
$ docker rmi perf_cli_php
```
