# Core - Helper

Class `ArkHelper` provides several static methods to help your coding more easy and robust.

## Class Autoloader

Following PSR-4, if a class could be described with a namespace and a name, and be placed in certain directory,
it would be able to auto loaded.

`ArkHelper` provides static method `registerAutoload` to implement class autoload function,
as classes should be placed in one base path (or its sub directories), and share one base namespace,
and nesting namespace is as nesting directories.

```php
$base_namespace = "sinri\project";
$base_path = "~/project";
$extension = ".php"; // this is optional and '.php' is the default value.
ArkHelper::registerAutoload($base_namespace, $base_path, $extension);
```

Then, if you refer to `sinri\project\library\MyLibrary`, file `~/project/library/MyLibrary.php` would be required.

## Safe Array/Object Reader

PHP grammar allow referring to an array or an object with any index, 
but runtime NOTICE would be raised if the reference is not available.
For example:

```php
$array=['k1'=>1,'k2'=>['K3'=>2]];
echo $array['k1']; // safe, output 1
echo $array['k2']['k3']; // safe, output 2
// however
echo $array['k3'];// NOTICE
echo $array['k2']['k1'];// NOTICE

// same if as object
$object=json_decode(json_encode($array));
```

`ArkHelper` provides you a static method `readTarget` to do safe reading on any array or object, or even not such.
It has five parameters to fill, while the three at tail are optional:

1. the target variable, might be an array, an object, or anything else;
1. the keychain, might be a key or an array of keys in order to nest, i.e. `'key'` or `['key']` would lead to `$target['key']` or `$target->key`, and `['key1','key2']` means `$target['key1']['key2']` or `$target->key1->key2`;
1. the default value, use `NULL` if not given; this would be returned when no value set for keychain or the value read could not pass the regular expression validation; 
1. the regular expression to validate the read out value, by default is `NULL`, which means to neglect validation;
1. a variable to receive the exception might be thrown out, optional and be `NULL` by default, if no exception (read the target by keychain successfully), `NULL` would be return.

```php
$result = ArkHelper::readTarget($target, $keychain, $default = null, $regex = null, &$exception = null);
``` 

## Safe Array Writer

As the same rule of Safe Reader, `ArkHelper` provides another static method `writeIntoArray` to do safe writing.
It accepts three parameters,

1. the array variable;
1. the keychain, rule is same as reader;
1. the value to write into the array variable.

```php
ArkHelper::writeIntoArray(&$array, $keychain, $value);
```

> You might think about why no Safe Object Writer. It is easy to get: `$object=json_decode(json_encode($array));`.

## Array Association

You might sometimes want to transform an pure list to a mapped(associated) array.
With `ArkHelper`, just give list and the list item key as mapping key, the task would be done. 

```php
$result = ArkHelper::turnListToMapping(
    [
        ["id" => "A", "value" => "aaa"], 
        ["id" => "B", "value" => "bbb"],
    ],
    "id"
);
```

## Simple Assert

`ArkHelper` provides you an easy way to do assert by static method `assertItem`, which throw exception if not pass the assertion.
It has three parameters while the final two are optional.

1. the object to challenge the assertion;
1. the exception message used when the assertion failed;
1. the type mask which might be grouped by OR operation.

The task mask would be one or mix of these:

* `ArkHelper::ASSERT_TYPE_NOT_EMPTY` // object is not: NULL, FALSE, or 0, '0', '', [].
* `ArkHelper::ASSERT_TYPE_NOT_VAIN` // object is not: 0, '0', '', [].
* `ArkHelper::ASSERT_TYPE_NOT_NULL` // object is not NULL.
* `ArkHelper::ASSERT_TYPE_NOT_FALSE` // object is not FALSE.

```php
ArkHelper::assertItem($object, $exception_message = null, $type = self::ASSERT_TYPE_NOT_EMPTY)
```

## Is running in CLI mode

A simple but convenient method, to check if the code is running in CLI mode.

```php
if(
    ArkHelper::isCLI()
) {
    echo "HERE IS CLI!";
}
``` 