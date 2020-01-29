# Error/Exception Handling

Koseven provides both an exception handler and an error handler that transforms errors into Error_Exceptions using PHP's [ErrorException](http://php.net/errorexception) class. Many details of the error and the internal state of the application is displayed by the handler:

1. Exception class
2. Error level
3. Error message
4. Source of the error, with the error line highlighted
5. A [debug backtrace](http://php.net/debug_backtrace) of the execution flow
6. Included files, loaded extensions, and global variables

## Example

Click any of the links to toggle the display of additional information:

<div>{{userguide/examples/error}}</div>

## Disabling Error/Exception Handling

If you do not want to use the internal error handling, you can disable it (highly discouraged) when calling [KO7::init]:

    Kohana::init(array('errors' => FALSE));

## Error Reporting

By default, Koseven displays all errors and warnings. This is set using [error_reporting](http://php.net/error_reporting):

    error_reporting(E_ALL);

When you application is live and in production, a more conservative setting is recommended, such as ignoring notices:

    error_reporting(E_ALL & ~E_NOTICE);

If you get a white screen when an error is triggered, your host probably has disabled displaying errors. You can turn it on again by adding this line just after your `error_reporting` call:

    ini_set('display_errors', TRUE);

Errors should **always** be displayed, even in production, because it allows you to use [exception and error handling](debugging.errors) to serve a nice error page rather than a blank white screen when an error happens.

## HTTP Exception Handling

Koseven comes with a robust system for handing http errors. It includes exception classes for each http status code. To trigger a 404 in your application (the most common scenario):

	throw HTTP_Exception::factory(404, 'File not found!');

To register error pages for these, using 404 as an example:

    class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

        public function get_response()
        {
            $response = Response::factory();

            $view = View::factory('errors/404');

            // We're inside an instance of Exception here, all the normal stuff is available.
            $view->message = $this->getMessage();

            $response->body($view->render());

            return $response;
        }

    }

## Deprecation "Error"
Koseven has a built-in function for deprecating code. If one of the 
deprecated function is used, it get's logged into your configured Log File.

It accepts two parameters:
1. (required) version number the function got deprecated in e.g "4.0"
2. (optional) replacement function

Here is a example of deprecating function "test" with function "test2" as replacement 

    public static function test
    {
        // This got deprecated in v. 4.0
        KO7::deprecate('4.0', 'test2');
    }
    
The above will log the following line every time the "test" function get's called:

    YYYY-MM-DD HH:MM:SS --- WARNING: Function "test" inside class <class> is deprecated since version 4.0 and will be removed within the next major release. Please consider replacing it with "test2". in <file>:<line>
    
