<?php
/**
 * UTF8::strrev
 *
 * @package    KO7
 *
 * @copyright  (c) 2007-2016  Kohana Team
 * @copyright  (c) since 2016 Koseven Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrev($str)
{
	if (UTF8::is_ascii($str))
		return strrev($str);

	preg_match_all('/./us', $str, $matches);
	return implode('', array_reverse($matches[0]));
}
