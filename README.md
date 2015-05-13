[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neomerx/json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/neomerx/json-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neomerx/json-api/?branch=master)
[![Build Status](https://travis-ci.org/neomerx/json-api.svg?branch=master)](https://travis-ci.org/neomerx/json-api)
[![HHVM](https://img.shields.io/hhvm/neomerx/json-api.svg)](https://travis-ci.org/neomerx/json-api)
[![License](https://img.shields.io/packagist/l/neomerx/json-api.svg)](https://packagist.org/packages/neomerx/json-api)

## Description

Framework agnostic [JSON API](http://jsonapi.org/) implementation.

This package covers encoding PHP objects to JavaScript Object Notation (JSON) as described in [JSON API Format](http://jsonapi.org/format/).

* Resource attributes and complex attributes
* Meta information for document, resources and link objects
* Link objects (including links as references, links to null and empty arrays)
* Compound documents with included resources
* Limits for input data parsing depth
* Circular references in resources
* Sparse fieldset filter rules
* Pagination links
* Errors

## Versioning

This package implements the latest [JSON API](http://jsonapi.org/) version RC3 and is using [Semantic Versioning](http://semver.org/).
The package version reflects the fact JSON API specification has not been finally released yet but not the package readiness.

## Install

Via Composer

```
$ composer require neomerx/json-api ~0.2
```

## Usage

### Basic usage

```php
$encoder = Encoder::instance([
    Author::class  => AuthorSchema::class,
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($author) . PHP_EOL;
```

will output

```json
{
    "data": {
        "type": "people",
        "id": "123",
        "attributes": {
            "first_name": "John",
            "last_name": "Dow"
        },
        "links": {
            "self": "http:\/\/example.com\/people\/123"
        }
    }
}
```

The ```AuthorSchema``` looks like

```php
class AuthorSchema extends SchemaProvider
{
    protected $resourceType = 'people';
    protected $baseSelfUrl  = 'http://example.com/people/';

    public function getId($author)
    {
        /** @var Author $author */
        return $author->authorId;
    }

    public function getAttributes($author)
    {
        /** @var Author $author */
        return [
            'first_name' => $author->firstName,
            'last_name'  => $author->lastName,
        ];
    }
}
```

### Object hierarchy with included objects

```php
$encoder  = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => CommentSchema::class,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($site) . PHP_EOL;
```

will output

```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "attributes": {
            "name": "JSON API Samples"
        },
        "links": {
            "self": "http:\/\/example.com\/sites\/1",
            "posts": {
                "linkage": {
                    "type": "posts",
                    "id": "321"
                }
            }
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "attributes": {
                "first_name": "John",
                "last_name": "Dow"
            }
        },
        {
            "type": "comments",
            "id": "456",
            "attributes": {
                "body": "Included objects work as easy as basic ones"
            },
            "links": {
                "self": "http:\/\/example.com\/comments\/456",
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                }
            }
        },
        {
            "type": "comments",
            "id": "789",
            "attributes": {
                "body": "Let's try!"
            },
            "links": {
                "self": "http:\/\/example.com\/comments\/789",
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                }
            }
        },
        {
            "type": "posts",
            "id": "321",
            "attributes": {
                "title": "Included objects",
                "body": "Yes, it is supported"
            },
            "links": {
                "author": {
                    "linkage": {
                        "type": "people",
                        "id": "123"
                    }
                },
                "comments": {
                    "linkage": [
                        {
                            "type": "comments",
                            "id": "456"
                        },
                        {
                            "type": "comments",
                            "id": "789"
                        }
                    ]
                }
            }
        }
    ]
}
```

### Sparse and fields sets

Output result could be filtered by included relations and object attributes.

```php
$options  = new EncodingParameters(
    ['posts.author'], // Paths to be included
    [
        // Attributes and links that should be shown
        'sites'  => ['name'],
        'people' => ['first_name'],
    ]
);
$encoder  = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => CommentSchema::class,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($site, null, null, $options) . PHP_EOL;
```

Note that output does not contain objects from neither ```posts``` nor ```posts.comments``` relations. Attributes of ```site``` and ```author``` are filtered as well.

```json
{
    "data": {
        "type": "sites",
        "id": "1",
        "attributes": {
            "name": "JSON API Samples"
        },
        "links": {
            "self": "http:\/\/example.com\/sites\/1"
        }
    },
    "included": [
        {
            "type": "people",
            "id": "123",
            "attributes": {
                "first_name": "John"
            }
        }
    ]
}
```

### View customization

You can fully customize how your output result will look like

* Add links to document.
* Add meta to document, resource objects or link objects.
* Show object links as references.
* Show/hide ```self```, ```related```, ```links```, ```meta``` for each resource type individually for both main and included objects and links.
* Specify what links should place resources to ```included``` section and set default inclusion depth for each resource type independently.

### Sample source code

The full sample source code with performance test could be found [here](sample/)

## Advanced usage

### Link resources

Adding links to a resource is easy. You only need to add ```getLinks``` method to resource schema

```php
class PostSchema extends SchemaProvider
{
    ...
    public function getLinks($post)
    {
        /** @var Post $post */
        return [
            'author'   => [self::DATA => $post->author],
            'comments' => [self::DATA => $post->comments],
        ];
    }
    ...
}
```

Apart from ```self::DATA``` the following keys are supported

* ```self::INCLUDED``` (bool) - if resource(s) from this link should be added to ```included``` section.
* ```self::SHOW_META``` (bool) - if resource meta information should be added to output link description.
* ```self::SHOW_LINKAGE``` (bool) - if linkage information should be added to output link description.
* ```self::SHOW_SELF``` (bool) - if link ```self``` URL should be added to output link description. Setting this option to ```true``` requires ```self::SELF_CONTROLLER``` (```mixed```) to be set as well.
* ```self::SHOW_RELATED``` (bool) - if link ```self``` URL should be added to output link description. Setting this option to ```true``` requires ```self::RELATED_CONTROLLER``` (```mixed```) to be set as well.
* ```self::SHOW_AS_REF``` (bool) - if this key is set to ```true``` the link will be rendered as a reference.

### Provide resource's meta information

A ```getMeta``` method should be added to schema

```php
class PostSchema extends SchemaProvider
{
    ...
    public function getMeta($post)
    {
        /** @var Post $post */
        return [
            ...
        ];
    }
    ...
}
```

### Show/Hide 'self' or 'meta' for resource

You can set it up in resource schema

```php
class YourSchema extends SchemaProvider
{
    ...
    protected $isShowSelf = true;
    protected $isShowMeta = false;
    ...
}
```

### Show/Hide 'self' or 'meta' for included resource


```php
class YourSchema extends SchemaProvider
{
    ...
    protected $isShowSelfInIncluded  = false;
    protected $isShowLinksInIncluded = false;
    protected $isShowMetaInIncluded  = false;
    ...
}
```

### Limit depth of resource inclusion

By default the inclusion depth is unlimited thus all the data you pass to encoder will be put to json. You can limit the parsing depth for resource and its links by setting ```$defaultParseDepth```.

```php
class YourSchema extends SchemaProvider
{
    ...
    protected $defaultParseDepth = 1;
    ...
}
```

### Show top level links and meta information

```php
$author = Author::instance('123', 'John', 'Dow');
$meta   = [
    "copyright" => "Copyright 2015 Example Corp.",
    "authors"   => [
        "Yehuda Katz",
        "Steve Klabnik",
        "Dan Gebhardt"
    ]
];
$links  = new DocumentLinks(
    null,
    'http://example.com/people?first',
    'http://example.com/people?last',
    'http://example.com/people?prev',
    'http://example.com/people?next'
);

$encoder  = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => CommentSchema::class,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
], new JsonEncodeOptions(JSON_PRETTY_PRINT));

echo $encoder->encode($author, $links, $meta) . PHP_EOL;
```

will output

```json
{
    "meta": {
        "copyright": "Copyright 2015 Example Corp.",
        "authors": [
            "Yehuda Katz",
            "Steve Klabnik",
            "Dan Gebhardt"
        ]
    },
    "links": {
        "first": "http:\/\/example.com\/people?first",
        "last": "http:\/\/example.com\/people?last",
        "prev": "http:\/\/example.com\/people?prev",
        "next": "http:\/\/example.com\/people?next"
    },
    "data": {
        "type": "people",
        "id": "123",
        "attributes": {
            "first_name": "John",
            "last_name": "Dow"
        },
        "links": {
            "self": "http:\/\/example.com\/people\/123"
        }
    }
}
```

### Dynamic Schemas

Encoder supports dynamic schemas. Instead of schema class name you can specify ```Closure``` which will be invoked on schema creation. This feature could be used for setting up schemas based on configuration settings, environment variables, user input and etc. 

```php
$schemaClosure = function () {
    $schema = new CommentSchema(..., ..., ...);
    return $schema;
};

$encoder = Encoder::instance([
    Author::class  => AuthorSchema::class,
    Comment::class => $schemaClosure,
    Post::class    => PostSchema::class,
    Site::class    => SiteSchema::class
]);
```

## Questions?

Do not hesitate to contact us on [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/neomerx/json-api) or post an [issue](https://github.com/neomerx/json-api/issues).

## Testing

``` bash
$ phpunit
```

## Credits

- [Neomerx](https://github.com/neomerx)
- [All Contributors](../../contributors)

## License

Apache License (Version 2.0). Please see [License File](LICENSE) for more information.
