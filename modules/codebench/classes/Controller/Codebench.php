<?php
/**
 * Codebench — A benchmarking module.
 *
 * @package    koseven/Codebench
 * @category   Controllers
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @license    https://koseven.dev/LICENSE
 */
class Controller_Codebench extends KO7_Controller_Template {

	// The codebench view
	public $template = 'codebench';

	public function action_index()
	{
		$class = $this->request->param('class');

		// Convert submitted class name to URI segment
		if (isset($_POST['class']))
		{
			throw HTTP_Exception::factory(302)->location('codebench/'.trim($_POST['class']));
		}

		// Pass the class name on to the view
		$this->template->class = (string) $class;

		// Try to load the class, then run it
		if (KO7::auto_load($class) === TRUE)
		{
			$codebench = new $class;
			$this->template->codebench = $codebench->run();
		}
	}
}
