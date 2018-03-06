# Make Response for Requests

Here describe the ways to make and send response for requests.

## ArkWebOutput

Class `ArkWebOutput` is an integrated web input handler. 
It should be used as a singleton, commonly used as `Ark()->webOutput()`.

### HTTP STATUS CODE

Use method `responseHTTPCode` to set HTTP status code.

### Set Content-Type Header

Use method `setContentTypeHeader` to set the `Content-Type` header,
you can optionally set the charset.

### Output as JSON
 
Use method `json` to output anything encoded in JSON.
Besides, method `jsonForAjax` make a rule for AJAX.

```json
{
  "code":"CODE","data":"ANYTHING"
}
``` 

The code might be `OK` or `FAIL`, and the data might be anything. 
Usually the data is a string as error message when the code is `FAIL`.

### Output with Page Template

Method `displayPage` allows you to output a page with template and parameters.
Just give an template file path and an array of parameters.

Here is a simple sample:

File /xxx/template.php :

```php
<html>
<header>
</header>
<body>
    <p>Parameter a: <?php echo $a; ?></p>
    <p>Parameter b: <?php foreach ($b as $bb)echo $bb." "; ?></p>
</body>
</html> 

```

The Ark Web Service: 

```php
$templateFile='/xxx/template.php';
$parameters=[
    "a"=>"TEST",
    "b"=>["one","two","three"],
];
Ark()->webOutput()->displayPage($templateFile,$parameters);
```

### Download File

It is common to provide function to download files but it is sometimes important that let no user access local files.
With PHP environment, you can use `fread` and `echo` to proxy.
Method `downloadFileIndirectly` is designed for this.
It accepts three parameters:

1. target file: path of the target local file.
1. content type: optionally, value of the `content-type` header. By default `application/octet-stream`.
1. download name: optionally, initial name for download file dialogue. By default the local file name.

  