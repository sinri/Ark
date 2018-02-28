# Handle Input From Web

PHP was designed originally for Web Service. 
It is a very usual situation to handle input from web,
to parse the incoming requests as GET, POST, etc.
Among them might File Uploading be, and some lower level meta mixed.
Ark provides a series of classes to support you.

## ArkWebInput

Class `ArkWebInput` is an integrated web input handler. 
It should be used as a singleton, commonly used as `Ark()->webInput()`.

### Definition of Methods 

It defined const of methods as HTTP protocol defined, the values are listed here.

* HEAD
* GET
* POST
* PUT
* DELETE
* OPTION
* PATCH
* cli
* ANY

The constants are used inside the whole Ark Library.

### Fetch Request Data

Method `readRequest` allows you to fetch data from incoming web request with four parameters.
The fetch operation is done by `ArkHelper::readTarget`, so same as the parameters.

1. name : the keychain for the target field in the incoming request
1. default : the fallback value (optionally and `null` by default), used when the keychain does not associate to value or the value fetched does not match regex validation
1. regex : a regular expression to validate the fetched value, optionally and `null` by default which means execute no validation
1. error : an optional variable reference to receive any `\Exception` thrown inside 

Especially, the value is first from `$_REQUEST`,
if the request holds a header `Content-Type` case-insensitively as `application/json` or alike,
no matter what method the request is,
it would try to parse the request body as json and try fetching again.

It would return what value fetched, which might be the fallback value when the expected value unavailable.

If you want to read `$_GET` and `$_POST` instead, use `readGet` and `readPost` methods with same parameters.
Those two methods are based on PHP core function.

### Fetch Raw Data of Request

There are two methods to handle the raw request body data, `getRawPostBody` and `getRawPostBodyParsedAsJson`.
The first fetch the data posted, and the second would do some more things to try parsing the data as json. 

### Fetch Request Header

You can fetch header from incoming request case-insensitively with `readHeader`.
It parsed the headers to lower case names and use `ArkHelper::readTarget` to fetch.
So the parameters are same as `ArkHelper::readTarget` defined. 

### Fetch Session Data

Use method `readSession` to fetch session value, from `$_SESSION`.

### Get IP of Client

Use method `visitorIP` to get the client IP from which the request was sent.
If your server is deployed behind the proxies, you might pass an array of proxy IP(s) as parameter.

### Get Method of Request

Use method `requestMethod`.
It would try to parse the `REQUEST_METHOD` field of `$_SERVER` first,
if not available, it would try to check whether in CLI mode,
or FALSE would be returned.

## IP Helper

Class `WebInputIPHelper` is designed for handling IP address.
It could be accessed by the method `getIpHelper` of class `ArkWebInput`.

### Validate IP Address

Method `validateIP` can validate an IP address expression,
and you can optionally pass a second parameter to show which type is used to validate,
which would use two constants, `IP_TYPE_V4` and `IP_TYPE_V6`.

### Determine Type of IP Address

Method `determineVersionOfIP` can check the version of given IP address and return the constant of type.
If it is not a validated IP address, false would be returned.

## Header Helper

Class `WebInputHeaderHelper` is designed for handling headers.
It would be accessed by the method `getHeaderHelper` of class `ArkWebInput`.

### Get All Headers

Method `getHeaders` does this job.

## File Upload Helper

Class `WebInputFileUploadHelper` is designed for handling file uploading.
It would be accessed by the method `getUploadFileHelper` of class `ArkWebInput`.

This class provides methods to handle file uploading,
for single file using `handleUploadFileWithCallback` and for multiple, use `handleUploadFilesWithCallback`.
The two methods require same parameters:

1. name : the post field name
1. callback : an callback as `function($original_file_name, $file_type, $file_size, $file_tmp_name, $error){}`
1. $error : an reference of variable to receive error thrown

The callback would be called for each file uploaded. 