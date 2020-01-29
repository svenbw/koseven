<?php
/**
 * Class for formatting REST-Response bodies as HTML
 *
 * @package        KO7\REST
 *
 * @copyright  (c) since 2016 Koseven Team
 * @license        https://koseven.ga/LICENSE
 */
class KO7_REST_Format_HTML extends REST_Format {

    /**
     * Format function
     *
     * @throws REST_Exception
     *
     * @return string
     */
    public function format() : string
    {
        // Filter the path parts, remove empty ones
        $path = array_filter([
            $this->_request->directory(),
            $this->_request->controller(),
            $this->_request->action(),
        ]);

        $path = strtolower(implode(DIRECTORY_SEPARATOR, $path));

        // Try to find view, change view name if not found
        // e.g Controller: Welcome, check views/Welcome.php else views/welcome/{index,update,create,delete}.php
        if (KO7::find_file('views', dirname($path)))
        {
            $path = dirname($path);
        }

        // Try to initialize View
        try
        {
            return View::factory($path,  $this->_body)->render();
        }
        catch (View_Exception $e)
        {
            throw new REST_Exception($e->getMessage());
        }
    }

}
