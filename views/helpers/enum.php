<?php
/**
 * A helper used for getting the display value of the an 'enum' value
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2009, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi_enums
 * @subpackage    mi_enums.views.helpers
 * @since         v 1.0 (18-Jun-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * EnumHelper class
 *
 * @uses          AppHelper
 * @package       mi_enums
 * @subpackage    mi_enums.views.helpers
 */
class EnumHelper extends AppHelper {

/**
 * run time cache
 *
 * @var array
 * @access protected
 */
	var $_cache = array();

/**
 * display method
 *
 * @param mixed $identifier null
 * @param mixed $value null
 * @param mixed $default null
 * @return void
 * @access public
 */
	function display($identifier = null, $value = null, $default = null) {
		if (!isset($this->_cache[$identifier])) {
			$this->_cache[$identifier] = MiCache::data('MiEnums.Enum', 'values', $identifier);
		}
		if (isset($this->_cache[$identifier][$value])) {
			return $this->_cache[$identifier][$value];
		}
		if (!$default) {
			return MiCache::data('MiEnums.Enum', 'defaultValue', $identifier);
		}
		return $default;
	}
}