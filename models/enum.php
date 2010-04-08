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
 * @package       mi_enums
 * @subpackage    mi_enums.models
 * @since         v 1.0
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Vendor', 'Mi.MiCache');

/**
 * Enum class
 *
 * This class is used to administer the enum values for any field, in any model(table) in the system (database)
 * It assumes that models are fully Cake-conventional and will also update the default value on the database
 * table if/when defaults are changed
 *
 * @uses          MiEnumsAppModel
 * @package       mi_enums
 * @subpackage    mi_enums.models
 */
class Enum extends MiEnumsAppModel {

/**
 * name property
 *
 * @var string 'Enum'
 * @access public
 */
	var $name = 'Enum';

/**
 * displayField property
 *
 * @var string 'display'
 * @access public
 */
	var $displayField = 'display';

/**
 * validates property
 *
 * @var array
 * @access public
 */
	var $validate = array(
		'display' => array(
			'missing' => array('rule' => 'notEmpty', 'last' => true),
			'duplicate' => array('rule' => 'checkDuplicate')
		)
	);

/**
 * actsAs property
 *
 * @var array
 * @access public
 */
	var $actsAs = array(
		'Mi.List' => array('scope' => 'type')
	);

/**
 * order property
 *
 * @var array
 * @access public
 */
	var $order = array('Enum.type', 'Enum.order');

/**
 * mode property
 *
 * @var bool false
 * @access private
 */
	var $__mode = null;

/**
 * error property
 *
 * @var bool false
 * @access private
 */
	var $__error = false;

/**
 * afterSave method
 *
 * If a default has just been saved check the db and update the table if the db default is different.
 *
 * @return void
 * @access public
 */
	function afterSave() {
		if (isset($this->data['Enum']['default']) && $this->data['Enum']['default']) {
			$conditions['type'] = isset($this->data['Enum']['type'])?$this->data['Enum']['type']:$this->field('type');
			$conditions['NOT']['Enum.id'] = $this->id;
			$this->updateAll(array('default' => 'false'), $conditions);
			$default = isset($this->data['Enum']['value'])?$this->data['Enum']['value']:$this->field('value');
			if ($default) {
				$db =& ConnectionManager::getDataSource($this->useDbConfig);
				App::import('Model', 'CakeSchema');
				$Schema = new CakeSchema(array('connection' => $this->useDbConfig));
				$update = $current = $Schema->read(array('models' => false));
				list($model, $field) = explode('.', $conditions['type']);
				$table = Inflector::tableize($model);
				if (isset($update['tables'][$table][$field])) {
					$update['tables'][$table][$field]['default'] = $default;
					$update['tables'][$table][$field]['null'] = true;
					$compare = $Schema->compare($current, $update);
					foreach ($compare as $table => $changes) {
						$db->execute($db->alterSchema(array($table => $changes), $table));
					}
					Cache::delete($this->useDbConfig . '_' . $table, '_cake_model_');
				}
			}
		}
	}

/**
 * autoPopulate method
 *
 * Read the current modes, and populate the enums table with all enum values.
 *
 * Check for a configs/enums.php file to see if there are any enums defined there
 * If there is no config file - auto detect/populate boolean and status fields
 * Merge the enum values with any existing values in the db.
 * Save the values to the db, move on
 *
 * @param mixed $mode null
 * @param string $dbConfig 'default'
 * @return void
 * @access public
 */
	function autoPopulate($mode = null, $dbConfig = 'default') {
		if (!$mode) {
			$modes = $this->modes();
			if (file_exists(CONFIGS . 'enums.php')) {
				include(CONFIGS . 'enums.php');
				$modes = array_unique(Set::merge($modes, array_keys($config)));
			}
			foreach ($modes as $mode) {
				$this->autoPopulate($mode);
			}
			return true;
		}
		list($model, $field) = explode('.', $mode);
		App::import('Vendor', 'MiCache');
		$models = MiCache::mi('models');

		if (in_array($model, $models)) {
			$Inst =& ClassRegistry::init($model);
		} else {
			$db =& ConnectionManager::getDataSource($dbConfig);
			$Inst =& ClassRegistry::init(array(
				'class' => $model,
				'connection' => $dbConfig,
			));
		}

		$data = array();
		if (file_exists(CONFIGS . 'enums.php')) {
			include(CONFIGS . 'enums.php');
			if (isset($config[$mode])) {
				$data = $config[$mode];
			}
		}
		if (!$data) {
			$schema = $Inst->schema();
			if (isset($schema[$field])) {
				if (in_array($schema[$field]['type'], array('integer', 'boolean'))) {
					if ($schema[$field]['length'] === 1) {
						$data = array(
							0 =>  __('no', true),
							1 =>  __('yes', true),
						);
					} elseif ($schema[$field]['length'] === 2) {
						$data = array(
							0 =>  __('inactive', true),
							1 =>  __('active', true),
							2 =>  __('unconfirmed', true),
							3 =>  __('needs action', true),
						);
					}
				}
			}
		}
		if ($Inst->hasField($mode)) {
			$rowData = $Inst->find('list', array(
				'order' => $mode,
				'fields' => array($mode, $mode)
			));
			$diff = array_diff_key($rowData, $data);
			if ($diff) {
				$data = Set::merge($data, $diff);
			}
		}

		if (!$data) {
			return true;
		}
		foreach ($data as $value => $display) {
			$default = false;
			if (is_array($display)) {
				list($display, $default) = $display;
			}
			if (!$this->find('count', array('conditions' => array('type' => $mode, 'value' => $value)))) {
				$this->create();
				$this->save(array(
					'type' => $mode,
					'display' => $display,
					'default' => $default,
					'value' => $value
				));
			}
		}
		if (Configure::read()) {
			$this->export();
		}
		return true;
	}

/**
 * checkDuplicate method
 *
 * Prevent duplicate display names from being saved. Duplicate 'value's are permitted to cater
 * for the circumstance of wishing to have 2 or more enumerated values that have the same meaning or
 * significance to the system, but want to be displayed differently to the admin user
 *
 * @param mixed $in
 * @return void
 * @access public
 */
	function checkDuplicate() {
		$recursive = -1;
		$conditions['Enum.type'] = $this->data['Enum']['type'];
		if (isset($this->data['Enum']['id'])) {
			$conditions['id !='] = $this->data['Enum']['id'];
		}
		$conditions['display'] = $this->data['Enum']['display'];
		$count = $this->find('all', compact('conditions', 'recursive'));
		return !$count;
	}

/**
 * modes method
 *
 * List all modes
 *
 * @param bool $reset
 * @return void
 * @access public
 */
	function modes($reset = false) {
		if (!$reset) {
			$data = cache('enum_modes');
			if ($data) {
				return unserialize($data);
			}
		}
		$data = $this->find('all', array('fields' => array('DISTINCT Enum.type'), 'order' => 'type'));
		if (!$data) {
			$components = MiCache::mi('components');
			$tables = MiCache::mi('tables');
			foreach ($tables as $table) {
				$class = Inflector::classify($table);
				if (in_array($class, $components)) {
					continue;
				}
				if ($Inst = ClassRegistry::init($class)) {
					if ($Inst->Behaviors->attached('Enum')) {
						foreach ($Inst->actsAs['Enum'] as $field) {
							$data[] = $Inst->name . '.' . $field;
						}
					}
				}
			}
		} else {
			$data = Set::extract($data, '/Enum/type');
		}
		if ($data) {
			cache('enum_modes', serialize($data), '+1 day');
		}
		return $data;
	}

/**
 * checkDbDefaults method
 *
 * Loop on everything in the enums table that is set as a default and save it to trigger the db
 * checks for each enumerated field in the system
 *
 * @return void
 * @access public
 */
	function checkDbDefaults() {
		foreach ($this->find('list', array('conditions' => array('default' => true))) as $id => $_) {
			$this->id = $id;
			$this->saveField('default', true);
		}
	}

/**
 * defaultValue method
 *
 * @param string $identifier ''
 * @return void
 * @access public
 */
	function defaultValue($identifier = '') {
		if (!$identifier) {
			return false;
		}
		return $this->field('value', array('type' => $identifier, 'default' => true));
	}

/**
 * Save current enum definitions, after merging with existing content, to the
 * configs/enums.php file
 *
 * @param mixed $configFile null
 * @return void
 * @access public
 */
	function export($configFile = null) {
		$config = array();
		if (!$configFile) {
			$configFile = CONFIGS . 'enums.php';
		}
		if (file_exists($configFile)) {
			include($configFile);
		}
		$modes = $this->modes();
		foreach($modes as $mode) {
			$values = $this->values($mode);
			$default = $this->defaultValue($mode);
			if ($default !== false && isset($values[$default]) && !is_array($values[$default])) {
				$values[$default] = array($values[$default], true);
			}
			if (isset($config[$mode])) {
				if (isset($config[$mode][0])) {
					$config[$mode] = Set::merge($config[$mode], $values);
				} else {
					$config[$mode] = array_merge($config[$mode], $values);
				}
			} else {
				$config[$mode] = $values;
			}
		}
		ksort($config);
		$config = preg_replace("@=> (?:array\()?'(.*)'@", '=> __(\'\1\', true)', '$config = ' . var_export($config, true));
		file_put_contents($configFile, "<?php\n//@noverify\n" . $config . ';');
	}

/**
 * values method
 *
 * @param string $identifier ''
 * @return void
 * @access public
 */
	function values($identifier = '', $dbConfig = 'default') {
		if (!$identifier) {
			return false;
		}
		$conditions = array('type' => $identifier);
		$order = $this->alias . '.order';
		$fields = array('value', 'display');
		$this->Behaviors->disable('List');
		$return = $this->find('list', compact('conditions', 'order', 'fields'));
		$this->Behaviors->enable('List');
		if (!$return) {
			$this->autoPopulate($identifier, $dbConfig);
			$this->Behaviors->disable('List');
			$return = $this->find('list', compact('conditions', 'order', 'fields'));
			$this->Behaviors->enable('List');
		}
		return $return;
	}
}