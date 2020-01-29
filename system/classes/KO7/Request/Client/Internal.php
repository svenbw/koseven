<?php
/**
 * Request Client for Internal Execution
 *
 * @package        KO7\Base
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 *
 * @since          3.1.0
 */
class KO7_Request_Client_Internal extends Request_Client {

	/**
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 *     $request->execute();
	 *
	 * @param Request  $request		Request Object
	 * @param Response $response	Response Object
	 *
	 * @return  Response
	 */
	public function execute_request(Request $request, Response $response)
	{
		// Create the class prefix
		$prefix = 'Controller_';

		// Directory
		$directory = $request->directory();

		// Controller
		$controller = $request->controller();

		if ($directory)
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(['\\', '/'], '_', trim($directory, '/')) . '_';
		}

		if (KO7::$profiling)
		{
			// Set the benchmark name
			$benchmark = '"' . $request->uri() . '"';

			if ($request !== Request::$initial AND Request::$current)
			{
				// Add the parent request uri
				$benchmark .= ' « "' . Request::$current->uri() . '"'; // @codeCoverageIgnore
			}

			// Start benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}

		// Store the currently active request
		$previous = Request::$current;

		// Change the current request to this request
		Request::$current = $request;

		try
		{
			if ( ! class_exists($prefix . $controller))
			{
				throw HTTP_Exception::factory(404, 'The requested URL :uri was not found on this server.', [
					':uri' => $request->uri()
				])->request($request);
			}

			// Load the controller using reflection
			$class = new ReflectionClass($prefix . $controller);

			if ($class->isAbstract())
			{
				throw new KO7_Exception('Cannot create instances of abstract :controller', [
					':controller' => $prefix . $controller
				]);
			}

			// Create a new instance of the controller
			$controller = $class->newInstance($request, $response);

			// Run the controller's execute() method
			$response = $class->getMethod('execute')->invoke($controller);

			if ( ! $response instanceof Response)
			{
				// Controller failed to return a Response.
				throw new KO7_Exception('Controller failed to return a Response');
			}
		}
		catch (HTTP_Exception $e)
		{
			// Store the request context in the Exception
			if ($e->request() === NULL)
			{
				$e->request($request); // @codeCoverageIgnore
			}

			// Get the response via the Exception
			$response = $e->get_response();
		}
		catch (Exception $e)
		{
			// Generate an appropriate Response object
			$response = KO7_Exception::_handler($e);
		}

		// Restore the previous request
		Request::$current = $previous;

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		// Return the response
		return $response;
	}
}
