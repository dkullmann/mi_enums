<?php
/**
 * Short description for enum.php
 *
 * Long description for enum.php
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2008, Andy Dawson
 * @link          www.ad7six.com
 * @package       base
 * @subpackage    base.models.behaviors
 * @since         v 1.0
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * EnumBehavior class
 *
 * @uses          ModelBehavior
 * @package       base
 * @subpackage    base.models.behaviors
 */
class EnumBehavior extends ModelBehavior {

/**
 * defaultSettings property
 *
 * @var array
 * @access protected
 */
	var $_defaultSettings = array(
		'fields' => array(),
		'validate' => false
	);

/**
 * defaults property
 *
 * @var array
 * @access private
 */
	var $__defaults = array();

/**
 * values property
 *
 * @var array
 * @access private
 */
	var $__values = array();

/**
 * setup method
 *
 * Since this behavior is related to table definitions - the $Model->name is used
 * in place of $Model->alias for config settings.
 *
 * @param mixed $Model
 * @param array $config
 * @access public
 * @return void
 */
	function setup(&$Model, $config = array()) {
		if (!isset($config['fields'])) {
			$config = array('fields' => $config);
		}
		$this->settings[$Model->alias] = Set::merge($this->_defaultSettings, $config);
	}

/**
 * beforeValidate method
 *
 * For each enumerated field set the validation to only allow the existing values.
 * If debug is enabled and the value is not in the db already - add it
 *
 * @param mixed $Model
 * @return void
 * @access public
 */
	function beforeValidate(&$Model) {
		extract($this->settings[$Model->name]);
		if (Configure::read() && isset($Model->data[$Model->alias])) {
			$this->_enum();
			foreach ($fields as $field) {
				if (array_key_exists($field, $Model->data[$Model->alias]) && $Model->data[$Model->alias][$field] !== '') {
					$conditions['type'] = $Model->name . '.' . $field;
					$conditions['value'] = $Model->data[$Model->alias][$field];
					if (!$this->Enum->find('count', compact('conditions'))) {
						$this->Enum->create();
						$conditions['display'] = Inflector::humanize(Inflector::underscore($conditions['value']));
						$this->Enum->save($conditions);
					}
				}
			}
		}
		if (!$validate) {
			return;
		}
		foreach ($fields as $field) {
			$values = $this->enumValues($Model, $field);
			$Model->validate[$field][] = array('rule' => array('inList', $values));
		}
		return true;
	}

/**
 * enumDefaults method
 *
 * For the specified field(s) return the default enum values
 *
 * @param mixed $Model
 * @param array $fields
 * @param bool $reset
 * @return void
 * @access public
 */
	function enumDefault(&$Model, $field = '') {
		if (!$field) {
			$fields = $this->enumFields($Model);
			$return = array();
			foreach($fields as $field) {
				$return[$field] = $this->enumDefault($Model, $field);
			}
			return $return;
		}
		App::import('Vendor', 'Mi.MiCache');
		return MiCache::data('MiEnums.Enum', 'defaultValue', $Model->name, $fields);
	}

/**
 * enumFields method
 *
 * For this model what fields are enumerated
 *
 * @param mixed $Model
 * @return void
 * @access public
 */
	function enumFields(&$Model) {
		return $this->settings[$Model->name]['fields'];
	}

/**
 * enumValues method
 *
 * For the specified field, what are the enum values
 *
 * @param mixed $Model
 * @param string $field
 * @param bool $reset
 * @return void
 * @access public
 */
	function enumValues(&$Model, $field = '') {
		if (!$field) {
			$fields = $this->enumFields($Model);
			$return = array();
			foreach($fields as $field) {
				$return[$field] = $this->enumFields($Model, $field);
			}
			return $return;
		}
		App::import('Vendor', 'Mi.MiCache');
		return MiCache::data('MiEnums.Enum', 'values', $Model->name . '.' . $field, $Model->useDbConfig);
	}

/**
 * enum method
 *
 * Load the Enum model on demand
 *
 * @return void
 * @access protected
 */
	function _enum() {
		if (isset ($this->Enum)) {
			return;
		}
		$this->Enum =& ClassRegistry::init('MiEnums.Enum');
	}
}