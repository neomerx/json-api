## Neomerx JSON API sample application

### Install

Via Composer

```
$ composer install
```

### Run sample

```
$ php sample.php
```

The application will output a few encoding results for

* a resource with no relationships
* a resource with included relationships
* sparse and field set filters usage sample
* top level links and meta information usage sample

For more details see [the app source code](sample.php)

## Performance test

This application includes performance test as well. It can be run with default parameters

```
$ php sample.php -t
```

or with execution time measurement and specified number of iterations

```
$ time php sample.php -t=10000
```

If you have [docker-compose](https://docs.docker.com/compose/) installed you can run performance test in PHP 7.1, 7.2 and 7.3 with commands

```
$ composer perf-test-php-7-1
$ composer perf-test-php-7-2
$ composer perf-test-php-7-3
```
