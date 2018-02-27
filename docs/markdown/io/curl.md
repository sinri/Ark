# Use CURL

`Ark` provides you an easy way to use CURL, with the class `ArkCurl`.
You can simply make up a request by setting the elements,
and get the response after sending.

A request is consisted with:

* method
* url
* query
* body
* header
* cookie

## Raise a CURL call

To raise a CURL call, generally you need two steps,
first to make up a request, then execute it.
After execution, the request information would be cleared.

### Define Method and URL

Use `prepareToRequestURL` method to decide method and URL.
It is always necessary for all requests.
Method might use the method const of class `ArkWebInput`.

Note, you might contain queries inside the URL without any limitation, 
but when you set as that, you should not use `setQueryField` to change the query fields of URL.
So it is recommended to use pure URL here and leave query settings to `setQueryField` later.   

### Append Queries

You can append queries unto URL (which should be a PURE URL).
You could call `setQueryField` each time to append a query with query name and query value.

### Set Body for POST

If you need request for POST, you might need to set it.
The request body might be as form data or JSON string, or any other kinds of string,
with the header you would need to use.

#### Set Body as Simple Post Form Data

If the body is data of a simple form, you can use `setPostFormField` for each field of the form,
with field name and field value.

#### Set Body as Raw Data

If you need to set the post body as json object or array, or just want to set the form data as array in one time,
you might be able to use `setPostContent` with a parameter of anything.
Commonly you might use an array to be parsed into Form Data or JSON,
but sometimes you also might need to send raw strings or XMLs,
so it is possible to set raw string here, but never forget to set the correct headers.

### Set Headers

You can call `setHeader` to set one header with name and value.

### Set Cookie

You can call `setCookie` to set one cookie with name and value.

### Set Raw Options of CURL

Using `setCURLOption`, you can set options directly for CURL.
The options are defined by CURL module, such as 

* CURLOPT_SSL_VERIFYPEER
* CURLOPT_SSL_VERIFYSTATUS

etc.

### Send Request

After you set up all the elements you can call `execute` to send request and receive the result.
For JSON request, a parameter given as bool, i.e. `takePostDataAsJson`. 
It is false by default, if it is set to true, 
The `Content-Type` header would be set to `application/json` and the raw post data set with `setPostContent` would be encoded into JSON.

The method returns the response of the request, and all the request information would be reset.  