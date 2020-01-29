<?php
/**
 * Class for formatting REST-Response bodies as JSON
 *
 * @package        KO7\REST
 *
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.ga/LICENSE
 */
class KO7_REST_Format_JSON extends REST_Format {

    /**
     * Format function
     *
     * @throws REST_Exception
     *
     * @return string
     */
    public function format() : string
    {
        try
        {
            $body = json_encode($this->_body,
                JSON_THROW_ON_ERROR
                |JSON_FORCE_OBJECT
                |JSON_NUMERIC_CHECK
                |JSON_PRESERVE_ZERO_FRACTION
                |JSON_UNESCAPED_UNICODE
            );
        }
        catch(Exception $e)
        {
            throw new REST_Exception($e->getMessage(), NULL, $e->getCode(), $e);
        }

        return $body;
    }

}
