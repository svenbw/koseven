<?php
/**
 * Class for formatting REST-Response bodies as XML
 *
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.ga/LICENSE
 */
class KO7_REST_Format_XML extends REST_Format {

    /**
     * Format function
     *
     * @throws REST_Exception
     *
     * @return string
     */
    public function format() : string
    {
        // Check if php-xml is loaded
        if ( ! extension_loaded('xml'))
        {
            throw new REST_Exception('PHP XML Module not loaded.');
        }

        // Use internal error handling and backup original state
        $backup_error = libxml_use_internal_errors();
        libxml_use_internal_errors(true);

        // Clear error stack BEFORE dealing with xml (in case user forgot to clear it)
        libxml_clear_errors();

        // Create new XML Element
        $xml = $this->iterable_to_xml($this->_body);

        // Restore original handling
        libxml_use_internal_errors($backup_error);

        // Check if xml is valid
        if ( ! $xml)
        {
            throw new REST_Exception($this->evaluate_error());
        }

        return $xml;
    }

    /**
     * Convert iterable to an xml element
     *
     * @param iterable $obj          Iterable element to convert
     * @param mixed    $rootElement  Root element of entry
     * @param null     $xml          Current XML stack
     *
     * @return mixed
     */
    protected function iterable_to_xml(iterable $obj, $rootElement = NULL, $xml = NULL)
    {
        // If there is no Root Element then insert root
        if ($xml === NULL)
        {
            $xml = new SimpleXMLElement($rootElement ?? '<?xml version="1.0" encoding="'.KO7::$charset.'"?><root/>', LIBXML_COMPACT|LIBXML_BIGLINES);
        }

        // Visit all key value pair
        foreach ($obj as $k => $v)
        {
            // If there is nested array then
            if (is_iterable($v))
            {
                // Call function for nested array
                $this->iterable_to_xml($v, $k, $xml->addChild($k));
            }
            else
            {
                // Simply add child element.
                $xml->addChild($k, $v);
            }
        }

        return $xml->asXML();
    }

    /**
     * Evaluate the XML Error and create a error message
     *
     * @return string
     */
    protected function evaluate_error() : string
    {
        $error_message = 'Unknown XML Error';
        foreach (libxml_get_errors() as $error)
        {
            if ($error instanceof LibXMLError)
            {
                switch ($error->level)
                {
                    case LIBXML_ERR_WARNING :
                        $error_message .= 'Warning '.$error->code.': ';
                        break;
                    case LIBXML_ERR_ERROR :
                        $error_message .= 'Error '.$error->code.': ';
                        break;
                    case LIBXML_ERR_FATAL :
                        $error_message .= 'Fatal '.$error->code.': ';
                        break;
                }

                $error_message .= trim($error->message)."\n  Line: $error->line"."\n  Column: $error->column";

                if ($error->file)
                {
                    $error_message .= "\n  File: $error->file";
                }
            }
        }

        // Clear error stack
        libxml_clear_errors();

        return $error_message;
    }

}

