# JSON API

[![Join the chat at https://gitter.im/neomerx/json-api](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/neomerx/json-api?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Version](https://img.shields.io/packagist/v/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neomerx/json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/neomerx/json-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Build Status](https://travis-ci.org/neomerx/json-api.svg?branch=master)](https://travis-ci.org/neomerx/json-api)
[![HHVM](https://img.shields.io/hhvm/neomerx/json-api.svg)](https://travis-ci.org/neomerx/json-api)
[![License](https://img.shields.io/packagist/l/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)

## JSON API Version

This package implements the latest [JSON API](http://jsonapi.org/) version RC3.

Support representation for

* Pagination
* Meta data for document, encoded resources and links
* Resource references
* Resource inclusion
* Errors

This package is under active development and should be completed by the early May 2015.

Features under development

* Encoding empty links (nulls and empty arrays)
* Inclusion of nested resources
* Sparsing field sets
* Support for web requests workflow
* JSON API extensions

## Description

Framework agnostic [JSON API](http://jsonapi.org/) implementation.

This package covers encoding PHP objects to JavaScript Object Notation (JSON) as described in [JSON API Format](http://jsonapi.org/format/).

## Questions?

Do not hesitate to contact us on [@twitter](https://twitter.com/NeomerxCom) or post an [issue](https://github.com/neomerx/json-api/issues).

## Testing

``` bash
$ phpunit
```

## Versioning

The project is using [Semantic Versioning](http://semver.org/).

## Credits

- [Neomerx](https://github.com/neomerx)
- [All Contributors](../../contributors)

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.
