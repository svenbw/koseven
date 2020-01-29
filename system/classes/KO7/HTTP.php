<?php
/**
 * This class only contains static functions which can do the following:
 *
 *  - HTTP Redirection
 *  - E-Tag Cache comparison
 *  - HTTP Header Parsing
 *  - URL-Formatting
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.dev/LICENSE
 * @since          3.1.0
 *
 * @package        KO7\HTTP
 */
abstract class KO7_HTTP {

	/**
	 * The default protocol to use if it cannot be detected
	 *
	 * @var string
	 */
	public static $protocol = 'HTTP/1.1';

	/**
	 * Issues a HTTP redirect.
	 *
	 * @param string $uri  URI to redirect to
	 * @param int    $code HTTP Status code to use for the redirect (RFC 7231)
	 *
	 * @throws HTTP_Exception
	 */
	public static function redirect($uri = '', $code = 302)
	{
		// Check if Redirection code is valid according to RFC 7231
		if ($code < 300 || $code > 308)
		{
			throw new HTTP_Exception('Invalid redirect code \':code\'', [':code' => $code]);
		}

		$e = HTTP_Exception::factory($code);

		throw $e->location($uri);
	}

	/**
	 * Checks the browser cache to see the response needs to be returned,
	 * execution will halt and a 304 Not Modified will be sent if the
	 * browser cache is up to date.
	 *
	 * @param Request  $request  Request
	 * @param Response $response Response
	 * @param string   $etag     Resource ETag
	 *
	 * @throws Request_Exception
	 * @throws HTTP_Exception_304
	 *
	 * @return Response
	 */
	public static function check_cache(Request $request, Response $response, $etag = NULL)
	{
		// Generate an etag if necessary
		if ($etag === NULL)
		{
			$etag = $response->generate_etag();
		}

		// Set the ETag header
		$response->headers('etag', $etag);

		// Add the Cache-Control header if it is not already set
		// This allows etags to be used with max-age, etc
		if ($response->headers('cache-control'))
		{
			$response->headers('cache-control', $response->headers('cache-control') . ', must-revalidate');
		}
		else
		{
			$response->headers('cache-control', 'must-revalidate');
		}

		// Check if we have a matching etag
		if ($request->headers('if-none-match') && (string)$request->headers('if-none-match') === $etag)
		{
			// No need to send data again
			throw HTTP_Exception::factory(304)->headers('etag', $etag);
		}

		return $response;
	}

	/**
	 * Parses a HTTP header string into an associative array
	 *
	 * @param string $header_string Header string to parse
	 *
	 * @return  HTTP_Header
	 */
	public static function parse_header_string($header_string)
	{
		// If the PECL HTTP extension is loaded
		if (extension_loaded('http'))
		{
			// Use the fast method to parse header string
			// Ignore Code Coverage in case pecl_http is not loaded
			// @codeCoverageIgnoreStart
			$headers = (new http\Header)->parse($header_string);
			return new HTTP_Header($headers);
			// @codeCoverageIgnoreEnd
		}

		// Otherwise we use the slower PHP parsing
		$headers = [];

		// Match all HTTP headers
		if (preg_match_all('/(\w[^\s:]*):[ ]*([^\r\n]*(?:\r\n[ \t][^\r\n]*)*)/', $header_string, $matches))
		{
			// Parse each matched header
			foreach ($matches[0] as $key => $value)
			{
				// If the header has not already been set
				if (!isset($headers[$matches[1][$key]]))
				{
					// Apply the header directly
					$headers[$matches[1][$key]] = $matches[2][$key];
				} // Otherwise there is an existing entry
				else
				{
					// If the entry is an array
					if (is_array($headers[$matches[1][$key]]))
					{
						// Apply the new entry to the array
						$headers[$matches[1][$key]][] = $matches[2][$key];
					} // Otherwise create a new array with the entries
					else
					{
						$headers[$matches[1][$key]] = [$headers[$matches[1][$key]], $matches[2][$key],];
					}
				}
			}
		}

		// Return the headers
		return new HTTP_Header($headers);
	}

	/**
	 * Parses the the HTTP request headers and returns an array containing
	 * key value pairs. This method is slow, but provides an accurate
	 * representation of the HTTP request.
	 *
	 * @return  HTTP_Header
	 */
	public static function request_headers()
	{
		// If running on apache server
		if (function_exists('apache_request_headers'))
		{
			// Return the much faster method
			// Ignore Code Coverage....apache_request_headers will *never* be present with PHPUnit
			return new HTTP_Header(apache_request_headers()); // @codeCoverageIgnore
		}

		// If `pecl_http` extension is installed and loaded
		if (extension_loaded('http'))
		{
			// Return the faster method
			// Ignore Code Coverage in case pecl_http is not loaded
			// @codeCoverageIgnoreStart
			$headers = (new http\Env)->getRequestHeader();
			return new HTTP_Header($headers);
			// @codeCoverageIgnoreEnd
		}

		// Setup the output
		$headers = [];

		// Parse the content type
		if (!empty($_SERVER['CONTENT_TYPE']))
		{
			$headers['content-type'] = $_SERVER['CONTENT_TYPE'];
		}

		// Parse the content length
		if (!empty($_SERVER['CONTENT_LENGTH']))
		{
			$headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
		}

		foreach ($_SERVER as $key => $value)
		{
			// If there is no HTTP header here, skip
			if (strpos($key, 'HTTP_') !== 0)
			{
				continue;
			}

			// This is a dirty hack to ensure HTTP_X_FOO_BAR becomes X-FOO-BAR
			$headers[str_replace('_', '-', substr($key, 5))] = $value;
		}

		return new HTTP_Header($headers);
	}

	/**
	 * Processes an array of key value pairs and encodes
	 * the values to meet RFC 3986
	 *
	 * @param array $params Params
	 *
	 * @return  string
	 */
	public static function www_form_urlencode(array $params)
	{
		$encoded = [];

		foreach ($params as $key => $value)
		{
			$encoded[] = $key . '=' . rawurlencode($value);
		}

		return implode('&', $encoded);
	}

}
