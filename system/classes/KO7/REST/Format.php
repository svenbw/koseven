<?php
/**
 * Interface KO7_REST_Format
 * This interface needs to be extended from every REST-Formatter
 *
 * The following formatter come shipped with Koseven:
 *  - JSON
 *  - XML
 *  - HTML
 *
 * @package        KO7\REST
 *
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.ga/LICENSE
 */
abstract class KO7_REST_Format {

    /**
     * Default Output Format
     *
     * @var string
     */
    public static $default_format = 'json';

    /**
     * Holds an instance of the request class
     *
     * @var Request
     */
    protected $_request;

    /**
     * Holds an instance of the response class
     *
     * @var Response
     */
    protected $_response;

    /**
     * Holds the response body
     *
     * @var array|string
     */
    protected $_body;

    /**
     * Factory Method for REST Formatter
     *
     * @param Request  $request  Request Class
     * @param Response $response Response Class
     *
     * @throws REST_Exception
     *
     * @return REST_Format
     */
    public static function factory(Request $request, Response $response) : REST_Format
    {
        // Check if format is set by route, otherwise use default
        if ($request->format() === NULL)
        {
            $request->format(static::$default_format);
        }

        $formatter = 'REST_Format_'.$request->format();

        // Check if formatter Exists
        if ( ! class_exists($formatter))
        {
            throw new REST_Exception('Formatter :formatter does not exist.', [
                ':formatter' => get_class($formatter)
            ]);
        }

        $formatter = new $formatter($request, $response);

        // Check if client extends Request_Client_External
        if ( ! $formatter instanceof REST_Format)
        {
            throw new REST_Exception(':formatter is not a valid REST formatter.', [
                ':formatter' => get_class($formatter)
            ]);
        }

        // Set response content type by format used
        $response->headers('Content-Type', File::mime_by_ext($request->param('format')));

        return $formatter;
    }

    /**
     * KO7_REST_Format constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request, Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;

        // Make sure body is an array
        $body = $response->body();
        if (is_string($body))
        {
            $body = [
              'body' => $body
            ];
        }

        $this->_body = $body;
    }

    /**
     * Function for formatting the body
     *
     * @return string
     */
    abstract public function format() : string;

}