# Controllers

A Controller is a class file that stands in between the models and the views in an application. It passes information 
on to the model when data needs to be changed and it requests information from the model when data needs to be loaded. 
Controllers then pass on the information of the model to the views where the final output can be rendered for the users.
Controllers essentially control the flow of the application.

Controllers are called by the [Request::execute()](../api/Request#execute) function based on the Route that 
the url matched. Be sure to read the [Routing](routing) page to understand how to use routes to map urls to your 
controllers.

Since version 4.0 Koseven also supports REST Controller (look below for more Information)

## Creating Standard Controllers

In order to function, a controller must do the following:

* Reside in `classes/Controller` (or a sub-directory)
* Filename must match the class name exactly, e.g. `Articles.php`
* The class name must map to the filename (with `/` replaced with `_`) and each word is capitalized
* Must have the Controller class as a (grand)parent

Some examples of controller names and file locations:

	// classes/Controller/Foobar.php
	class Controller_Foobar extends Controller {
	
	// classes/Controller/Admin.php
	class Controller_Admin extends Controller {

Controllers can be in sub-folders:

	// classes/Controller/Baz/Bar.php
	class Controller_Baz_Bar extends Controller {
	
	// classes/Controller/Product/Category.php
	class Controller_Product_Category extends Controller {
	
[!!] Note that controllers in sub-folders can not be called by the default route, you will need to define a route that has a [directory](routing#regex) param or sets a default value for directory.

Controllers can extend other controllers.

	// classes/Controller/Users.php
	class Controller_Users extends Controller_Template
	
	// classes/Controller/Api.php
	class Controller_Api extends Controller_REST
	
[!!] [Controller_Template](../api/Controller_Template) is an example controller provided in Koseven.

You can also have a controller extend another controller to share common things, such as requiring you to be logged in 
to use all of those controllers.

	// classes/Controller/Admin.php
	class Controller_Admin extends Controller {
		// This controller would have a before() that checks if the user is logged in
	
	// classes/Controller/Admin/Plugins.php
	class Controller_Admin_Plugins extends Controller_Admin {
		// Because this controller extends Controller_Admin, it would have the same logged in check
		
## Request and Response

Every controller has the [$request](../api/Controller#property-request) property which is the 
[Request](../api/Request) object that called the controller. You can use this to get information about the current 
request.
 
In addition every controller has the [$response](../api/Controller#property-responsez) property which is the 
[Response](../api/Response) object that can be used to set the output body. Example:

    $this->response->body('Hello, World!');

## Actions

You create actions for your controller by defining a public function with an `action_` prefix.  Any method that is not 
declared as `public` and prefixed with `action_` can NOT be called via routing.

An action method will decide what should be done based on the current request, it *controls* the application.
Did the user want to save a blog post? Did they provide the necessary fields? 
Do they have permission to do that?  The controller will call other classes, including models, to accomplish this.
Every action should use `$this->response->body($view)` to set the [view file](mvc/views) which is sent to the browser,
unless it redirects or ended the script earlier.

A very basic action method that simply loads a [view](mvc/views) file.

	public function action_hello()
	{
		$this->response->body(View::factory('hello/world')); // This will load views/hello/world.php
	}

### Parameters

Parameters are accessed by calling `$this->request->param('name')` where `name` is the name defined in the route.

	// Assuming Route::set('example','<controller>(/<action>(/<id>(/<new>)))');
	
	public function action_foobar()
	{
		$id = $this->request->param('id');
		$new = $this->request->param('new');

If that parameter is not set it will be returned as NULL. You can provide a second parameter to set a default value if 
that param is not set.

	public function action_foobar()
	{
		// $id will be false if it was not supplied in the url
		$id = $this->request->param('user',FALSE);
		
Query Parameters can be accessed by calling `$this->request->query('name')` where `name` is the query key.

    // Assuming the user called /index.php?name=John
    
    public function action_index()
    {
        $name = $this->request->query('name') // Will contain "John";

### Examples

A view action for a product page.

	public function action_view()
	{
		$product = new Model_Product($this->request->param('id'));

		if ( ! $product->loaded())
		{
			throw HTTP_Exception::factory(404, 'Product not found!');
		}

		$this->response->body(View::factory('product/view')
			->set('product', $product));
	}

A user login action.

	public function action_login()
	{
		$view = View::factory('user/login');

		if ($this->request->post())
		{
			// Try to login
			if (Auth::instance()->login($this->request->post('username'), $this->request->post('password')))
			{
				$this->redirect('home', 303);
			}

			$view->errors = 'Invalid email or password';
		}

		$this->response->body($view);
	}

## Before and after

You can use the `before()` and `after()` functions to have code executed before or after the action is executed. 
For example, you could check if the user is logged in, set a template view, loading a required file, etc.

You can check what action has been requested (via `$this->request->action`) and do something based on that, such as 
requiring the user to be logged in to use a controller, unless they are using the login action.

	// Checking auth/login in before, and redirecting if necessary:

	Controller_Admin extends Controller {

		public function before()
		{
			// If this user doesn't have the admin role, and is not trying to login, redirect to login
			if ( ! Auth::instance()->logged_in('admin') AND $this->request->action !== 'login')
			{
				$this->redirect('admin/login', 302);
			}
		}
		
		public function action_login() {
			...

### Custom __construct() function

In general, you should not have to change the `__construct()` function, as anything you need for all actions can be done 
in `before()`. If you need to change the controller constructor, you must preserve the parameters or PHP will complain.
This is so the Request object that called the controller is available. 
*Again, in most cases you should probably be using `before()`, and not changing the constructor*, but if you really, 
*really* need to it should look like this:

	// You should almost never need to do this, use before() instead!

	// Be sure Kohana_Request is in the params
	public function __construct(Request $request, Response $response)
	{
		// You must call parent::__construct at some point in your function
		parent::__construct($request, $response);
		
		// Do whatever else you want
	}

# Creating REST-Controllers

Creating REST-Controller is easy and very much the same as creating a standard controller.
The only difference here is that instead of `Controller` your class has to extend `Controller_REST`

Example:

	// classes/Controller/Foobar.php
	class Controller_Foobar extends Controller_REST {
	
	// classes/Controller/Admin.php
	class Controller_Admin extends Controller_REST {

That's it. They work the same way as standard controller (`Controller_REST` extends `Controller`)
There are some 'extras' with a REST Controller, which are explained the following sections.

## Formatter

REST Controller support different formatter. A formatter is a class which handles formatting the response body.

Formatter's are stored like normal classes inside the `classes\REST\Format` folder.
Each formatter needs to extend the `REST_Format` class and needs to implement the `format` method which is automatically called
inside the `after` method of the Controller.

The output content-type header is automatically determined by the Controllers name via `File::mime_by_ext`.
e.g `Format_JSON` = `application/json`

JSON is the default formatter, there are 2 Options to tell the Controller which formatter to use:

**First** of all you could change the default formatter - NOTE: This will change the default output for all `REST` Controller -
to do this add the following line in your `bootstrap.php`:

    REST_Format::$default_format = <formatter>;
    
    // e.g. This will change the default formatter to XML
    REST_Format::$default_format = 'xml';
    
**Second** Option is you can set it via the Route, passing it as a parameter called `format`:

    Route::set('api', 'api/<controller>(.<format>)', [
        'format'  => '(json|xml|html)'
	])
	->defaults([
	    'controller' => 'welcome',
	    'format'     => 'html'
	]);
	
In the example above the format can be set by the client (default is set to `html`), meaning that for example the url:
`https://www.example.com/api/welcome.json` will format the body with the `JSON` formatter.

Koseven comes shipped with 3 integrated Formatter which are described below.

#### JSON (default)

JSON is the default formatter for Koseven.
It simply formats the output body as JSON string.

#### HTML

This formatter searches for a view inside `views` folder with the same name as the controller and renders it.

e.g `Controller_Welcome` will search for `views/Welcome.php`

If you need different views for different methods/actions you can also do this, because
if the above view is not found Koseven will look for it depending on the action you called:

e.g `Controller_Welcome::index()` will render `views/Welcome/index.php`

The body is passed to the view, which means it's array key's are available as variables inside the view.

#### XML

XML formatter simply converts the response body to an XML String.

## Response Body

The response body for REST Controller supports arrays to be passed.
If you pass a string to the body function it will automatically be converted to an array with 'data' as key.
Example:

    // Example No 1
    // Pass array as response
    $this->response->body(['name' => 'Name']);
    
    // Example No 2
    // Pass string as response
    $this->response->body('Name');
    
Example Output (JSON used as Formatter)
    
    // Example No 1
    {"name":"Name"}
    
    // Example No 2`
    {"data":"Name"}
    
## Special parameter

`attachment` - you may sometimes like to allow your users to query your API directly from their browser with a direct link to download the data. For these occasions you may add this parameter with a value representing a file name. This will make the module declare a "content-disposition" header that'll make the user's browser open a download window.

`suppressResponseCodes` - some clients cannot handle HTTP responses different than 200. Passing suppressResponseCodes=true will make the response always return 200 OK, while attaching the real response code as an extra key (`responseCode`) in the response body.