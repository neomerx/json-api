## Neomerx JSON API sample application

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
        "first_name": "John",
        "last_name": "Dow",
        "links": {
            "self": "http:\/\/example.com\/people\/123"
        }
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
        "name": "JSON API Samples",
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
            "first_name": "John",
            "last_name": "Dow"
        },
        {
            "type": "comments",
            "id": "456",
            "body": "Included objects work as easy as basic ones",
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
            "body": "Let's try!",
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
            "title": "Included objects",
            "body": "Yes, it is supported",
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
$options  = new EncodingOptions(
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
        "name": "JSON API Samples",
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
            "first_name": "John"
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

## Neomerx JSON API performance test

This application includes performance test as well. It can be run with default parameters

```
$ php sample.php -t
```

or with measuring execution time and specified number of iterations

```
$ php time sample.php -t=10000
```

If your system has debug assertions enabled it is recommended to turn them off. Just to give you an idea that debug assert are not free here is the execution time comparison

|Debug asserts mode   |Command                                           |Execution time|
|---------------------|--------------------------------------------------|--------------|
|Enabled              |```$ php -d assert.active=1 sample.php -t=10000```|16.208s       |
|Disabled             |```$ php -d assert.active=0 sample.php -t=10000```|3.990s        |

The following command could be used for performance profiling with [blackfire.io](https://blackfire.io/)

```
$ blackfire --slot <slot number here> --samples 1 run php -d assert.active=0 sample.php -t=100
```