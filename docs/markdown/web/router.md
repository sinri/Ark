# Understand Router of Ark

The router of Ark is based on the path of coming request.

## Define One Router Rule

A router rule could be expressed as `ArkRouterRule` instance.

### Determine Support Method

GET, POST, etc.
Or use `ANY` to loose the limit.

### Define Rule for Request Path

Any HTTP request would hold a path, from which a relative script address could be fetched out.

Here are samples, the base path might be `sinri.cc`.

* '/' for 'sinri.cc' and 'sinri.cc/' 
* '/x' for 'sinri.cc/x'
* '/x/{y}' for 'sinri.cc/x/anything'. {y} is called as a path parameter. 

### Set Handle Callable

The handler might be a callable variable, such as an anonymous function with proper parameters for path parameters.
Also, the array of class-method pair is available.

### Use Filters

You can also set the filters in order in an array.
Each filter should be an instance of `ArkRequestFilter`.

## Group of Router Rules

For router rules with shared information, you can use method `group`.
It accepts two parameters, `shared` and `list`.
Parameter `shared` is an array with keys `FILTER`, `PATH` and `NAMESPACE`.
Parameter `list` is an array of array, each item has keys `CALLBACK`,`METHOD` and `PATH`.
Each item in list would be packaged with the shared information to generate a router rule.
The shared path joined with path of item of list would be the final path.

 
## Special Helper for CodeIgniter Style Controllers

A method `loadController` could generate route rules based on a controller class. 
You need to provide:

* base path : "" or "xx/"
* controller class: the class full name (i.e. with namespace)
* filters : array of `ArkRequestFilter` or null

If you put all the controllers in one root directory as CI does,
method `loadAllControllersInDirectoryAsCI` might help.
It use four parameters, the first is the root directory path,
and the following three are as above.

