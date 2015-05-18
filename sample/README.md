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

For more details see [this sample file](sample.php)

## Neomerx JSON API performance test

This application includes performance test as well. It can be run with default parameters

```
$ php sample.php -t
```

or with execution time measurement and specified number of iterations

```
$ php time sample.php -t=10000
```

If your system has debug assertions enabled it is recommended to turn them off. Just to give you an idea that debug assert are not free here is the execution time comparison

|Debug asserts mode   |Command                                           |Execution time|
|---------------------|--------------------------------------------------|--------------|
|Enabled              |```$ php -d assert.active=1 sample.php -t=10000```|7.589s        |
|Disabled             |```$ php -d assert.active=0 sample.php -t=10000```|2.884s        |

The following command could be used for performance profiling with [blackfire.io](https://blackfire.io/)

```
$ blackfire --slot <slot number here> --samples 1 run php -d assert.active=0 sample.php -t=100
```

Are you in a mood to optimize performance? You can start from this [performance baseline profile](https://blackfire.io/profiles/a25af07b-b0c2-4a80-ae0b-8e64222b1e10/graph).
