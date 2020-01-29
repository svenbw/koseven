<?php
/**
 * [Request_Client_External] provides a wrapper for all external request
 * processing. This class should be extended by all drivers handling external
 * requests.
 *
 * Supported out of the box:
 *  - Streams (default)
 *  - Curl (default if loaded)
 *  - PECL HTTP (default if curl not loaded and pecl_http loaded)
 *
 * @package        KO7\Base
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 *
 */
abstract class KO7_Request_Client_External extends Request_Client {

	/**
	 * Use:
	 *  - Request_Client_Stream (default)
	 *  - Request_Client_HTTP
	 *  - Request_Client_Curl
	 *
	 * @var    string    Defines the external client to use by default
	 */
	public static $client;

	/**
	 * Request options
	 *
	 * @var     array
	 * @link    http://www.php.net/manual/function.curl-setopt
	 * @link    http://www.php.net/manual/http.request.options
	 */
	protected $_options = [];

	/**
	 * Sends the HTTP message [Request] to a remote server and processes
	 * the response. This one needs to be implemented by all Request Drivers.
	 *
	 * @param Request  $request  Request to send
	 * @param Response $response Response to send
	 *
	 * @return  Response
	 */
	abstract protected function _send_message(Request $request, Response $response) :\Response;

	/**
	 * Factory method to create a new Request_Client_External object based on
	 * the client name passed, or defaulting to Request_Client_External::$client
	 * by default.
	 *
	 * Request_Client_External::$client can be set in the application bootstrap.
	 *
	 * @param array  $options Request options to pass to the client
	 * @param string $client  External client to use (to override default one)
	 *
	 * @throws  Request_Exception
	 *
	 * @return  Request_Client_External
	 */
	public static function factory(array $options = [], $client = NULL)
	{
		// If no client given determine which one to use (prefer the faster and mature ones)
		//@codeCoverageIgnoreStart
		if ($client === NULL)
		{
			if (static::$client === NULL)
			{
				if (extension_loaded('curl'))
				{
					static::$client = 'Request_Client_Curl';
				}
				elseif (extension_loaded('http'))
				{
					static::$client = 'Request_Client_HTTP';
				}
				else
				{
					static::$client = 'Request_Client_Stream';
				}
			}

			$client = static::$client;
		}
		//@codeCoverageIgnoreEnd

		$client = new $client($options);

		// Check if client extends Request_Client_External
		if ( ! $client instanceof Request_Client_External)
		{
			throw new Request_Exception(':client is not a valid external Request Client.', [
				'client' => get_class($client)
			]);
		}

		// Set Request Options
		$client->options($options);

		return $client;
	}

	/**
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 * 1. Before the controller action is called, the [Controller::before] method
	 * will be called.
	 * 2. Next the controller action will be called.
	 * 3. After the controller action is called, the [Controller::after] method
	 * will be called.
	 *
	 * By default, the output from the controller is captured and returned, and
	 * no headers are sent.
	 *
	 *     $request->execute();
	 *
	 * @param Request  $request  A request object
	 * @param Response $response A response object
	 *
	 * @throws Exception
	 * @return Response
	 */
	public function execute_request(Request $request, Response $response)
	{
		//@codeCoverageIgnoreStart
		if (KO7::$profiling)
		{
			// Set the benchmark name
			$benchmark = '"' . $request->uri() . '"';

			if ($request !== Request::$initial AND Request::$current)
			{
				// Add the parent request uri
				$benchmark .= ' « "' . Request::$current->uri() . '"';
			}

			// Start benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}
		//@codeCoverageIgnoreEnd

		// Store the current active request and replace current with new request
		$previous = Request::$current;
		Request::$current = $request;

		// Resolve the POST fields
		if ($post = $request->post())
		{
			$request
				->body(http_build_query($post, NULL, '&'))
				->headers('content-type', 'application/x-www-form-urlencoded; charset=' . KO7::$charset);
		}

		$request->headers('content-length', (string)$request->content_length());

		// If KO7 expose, set the user-agent
		if (KO7::$expose)
		{
			$request->headers('user-agent', KO7::version());
		}

		try
		{
			$response = $this->_send_message($request, $response);
		}
		catch (Exception $e)
		{
			// Restore the previous request
			Request::$current = $previous;

			//@codeCoverageIgnoreStart
			if (isset($benchmark))
			{
				// Delete the benchmark, it is invalid
				Profiler::delete($benchmark);
			}
			//@codeCoverageIgnoreEnd

			// Re-throw the exception
			throw $e;
		}

		// Restore the previous request
		Request::$current = $previous;

		//@codeCoverageIgnoreStart
		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}
		//@codeCoverageIgnoreEnd

		// Return the response
		return $response;
	}

	/**
	 * Set and get options for this request.
	 *
	 * @param mixed $key    Option name, or array of options
	 * @param mixed $value  Option value
	 *
	 * @return  mixed
	 */
	public function options($key = NULL, $value = NULL)
	{
		if ($key === NULL)
		{
			return $this->_options;
		}

		if (is_array($key))
		{
			$this->_options = $key;
		}
		elseif ($value === NULL)
		{
			return Arr::get($this->_options, $key);
		}
		else
		{
			$this->_options[$key] = $value;
		}

		return $this;
	}

}