<?php
/* SVN FILE: $Id$ */

/**
 * Short description for enums_controller.php
 *
 * Long description for enums_controller.php
 *
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright (c) 2008, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi_enums
 * @subpackage    mi_enums.controllers
 * @since         v 1.0
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * EnumsController class
 *
 * @uses          ListController
 * @package       mi_enums
 * @subpackage    mi_enums.controllers
 */
class MiEnumsController extends MiEnumsAppController {

/**
 * name property
 *
 * @var string 'Enums'
 * @access public
 */
	var $name = 'MiEnums';

	var $uses = array('MiEnums.Enum');

/**
 * paginate property
 *
 * @var array
 * @access public
 */
	var $paginate = array(
		'Enum' => array(
			'order' => array('Enum.type', 'Enum.order'),
			'recursive' => -1
		),
	);

/**
 * postActions property
 *
 * @var array
 * @access public
 */
	var $postActions = array(
		'admin_delete',
		'admin_delete_all',
		'admin_move_down',
		'admin_move_up',
		'admin_recover',
		'admin_verify',
	);

/**
 * admin_add method
 *
 * @param mixed $type
 * @access public
 * @return void
 */
	function admin_add($type = null) {
		if (!empty ($this->data)) {
			$this->Enum->listValue($type);
			$type = $this->data['Enum']['type'];
			if ($this->Enum->save($this->data)) {
				$display = $this->Enum->display();
				$this->Session->setFlash(sprintf(__('Enum "%1$s" added', true), $display));
				return $this->_back();
			} else {
				$this->data = $this->Enum->data;
				if (Configure::read()) {
					$this->Session->setFlash(implode($this->Enum->validationErrors, '<br />'));
				} else {
					$this->Session->setFlash(__('errors in form', true));
				}
			}
		}
		$this->_setSelects();
		$this->set('type', $type);
		$this->render('admin_edit');
	}

/**
 * admin_auto_populate method
 *
 * @return void
 * @access public
 */
	function admin_auto_populate($mode = null) {
		if ($mode === 'index') {
			$mode = null;
		}
		$this->Enum->autoPopulate($mode);
		return $this->_back();
	}

/**
 * admin_db_defaults method
 *
 * @return void
 * @access public
 */
	function admin_db_defaults() {
		$this->Enum->checkDbDefaults();
		return $this->_back();
	}

/**
 * admin_delete method
 *
 * @param mixed $id null
 * @return void
 * @access public
 */
	function admin_delete($id = null) {
		$this->Enum->id = $id;
		if ($id && $this->Enum->exists()) {
			$display = $this->Enum->display($id);
			if ($this->Enum->delete($id)) {
				$this->Session->setFlash(sprintf(__('Enum %1$s "%2$s" deleted', true), $id, $display));
			} else {
				$this->Session->setFlash(sprintf(__('Problem deleting Enum %1$s "%2$s"', true), $id, $display));
			}
		} else {
			$this->Session->setFlash(sprintf(__('Enum with id %1$s doesn\'t exist', true), $id));
		}
		return $this->_back();
	}

/**
 * admin_delete_all method
 *
 * @param mixed $type
 * @return void
 * @access public
 */
	function admin_delete_all($type = null) {
		if (!$type) {
			return;
		}
		$this->Enum->deleteAll(array('type' => $type));
		$this->Session->setFlash(sprintf(__('All %1$s enums deleted', true), $type));
		$this->_back();
	}

/**
 * admin_edit method
 *
 * @param mixed $id null
 * @return void
 * @access public
 */
	function admin_edit($id = null) {
		if ($this->data) {
			if ($this->Enum->saveAll($this->data)) {
				$display = $this->Enum->display();
				$this->Session->setFlash(sprintf(__('Enum "%1$s" updated', true), $display));
				return $this->_back();
			} else {
				$this->data = $this->Enum->data;
				if (Configure::read()) {
					$this->Session->setFlash(implode($this->Enum->validationErrors, '<br />'));
				} else {
					$this->Session->setFlash(__('errors in form', true));
				}
			}
		} elseif ($id) {
			$this->data = $this->Enum->read(null, $id);
		} else {
			return $this->_back();
		}
		$this->_setSelects();
	}

/**
 * admin_index method
 *
 * @param mixed $type null
 * @return void
 * @access public
 */
	function admin_index($type = null) {
		if ($type === 'index') {
			$type = null;
		}
		if ($type) {
			$this->Enum->listValue($type);
			$this->paginate['order'] = 'Enum.order';
			$conditions = array('Enum.type' => $type);
		} else {
			$conditions = array();
			$this->Enum->Behaviors->disable('List');
			$this->paginate['order'] = array('Enum.type', 'Enum.order');
		}
		$this->data = $this->paginate($conditions);
		$types = $this->Enum->modes();
		$this->set(compact('type', 'types'));
	}

/**
 * admin_lookup method
 *
 * @param mixed $type null
 * @param string $input ''
 * @return void
 * @access public
 */
	function admin_lookup($type = null, $input = '') {
		$this->autoRender = false;
		if (!$input) {
			$input = $this->params['url']['q'];
		}
		if (!$input) {
			$this->output = '0';
			return;
		}
		$conditions = array(
			'type' => $type,
			'id LIKE' => $input . '%',
			'display LIKE' => $input . '%',
		);
		if (!$this->data = $this->Enum->find('list', compact('conditions'))) {
			$this->output = '0';
			return;
		}
		return $this->render('/elements/lookup_results');
	}

/**
 * admin_move_down method
 *
 * @param mixed $id
 * @param int $number
 * @return void
 * @access public
 */
	function admin_move_down($id, $number = 1) {
		if (!$this->{$this->modelClass}->moveDown($id, $number)) {
			$this->Session->setFlash('Could not move after.');
		}
		return $this->_back();
	}

/**
 * admin_move_up method
 *
 * @param mixed $id
 * @param int $number
 * @return void
 * @access public
 */
	function admin_move_up($id, $number = 1) {
		if (!$this->{$this->modelClass}->moveUp($id, $number)) {
			$this->Session->setFlash('Could not move previous.');
		}
		return $this->_back();
	}

/**
 * admin_multi_add method
 *
 * @return void
 * @access public
 */
	function admin_multi_add() {
		if ($this->data) {
			$data = array();
			foreach ($this->data as $key => $row) {
				if (!is_numeric($key)) {
					continue;
				}
				$data[$key] = $row;
			}
			if ($this->Enum->saveAll($data, array('validate' => 'first', 'atomic' => false))) {
				$this->Session->setFlash(sprintf(__('Enums added', true)));
				$this->_back();
			} else {
				if (Configure::read()) {
					$this->Session->setFlash(implode($this->Enum->validationErrors, '<br />'));
				} else {
					$this->Session->setFlash(__('Some or all additions did not succeed', true));
				}
			}
		} else {
			$this->data = array(array('Enum' => $this->Enum->create()));
			$this->data[0]['Enum']['id'] = null;
		}
		$this->_setSelects();
		$this->render('admin_multi_edit');
	}

/**
 * admin_multi_edit method
 *
 * @return void
 * @access public
 */
	function admin_multi_edit() {
		if ($this->data) {
			$data = array();
			foreach ($this->data as $key => $row) {
				if (!is_numeric($key)) {
					continue;
				}
				$data[$key] = $row;
			}
			if ($this->Enum->saveAll($data, array('validate' => 'first'))) {
				$this->Session->setFlash(sprintf(__('Enums updated', true)));
			} else {
				if (Configure::read()) {
					$this->Session->setFlash(implode($this->Enum->validationErrors, '<br />'));
				} else {
					$this->Session->setFlash(__('Some or all updates did not succeed', true));
				}
			}
			$this->_setSelects();
		} else {
			$args = func_get_args();
			call_user_func_array(array($this, 'admin_index'), $args);
			array_unshift($this->data, 'dummy');
			unset($this->data[0]);
		}
	}

/**
 * admin_recover method
 *
 * @return void
 * @access public
 */
	function admin_recover($sort = null) {
		if (!$sort) {
			$sort = $this->{$this->modelClass}->displayField;
		}
		$this->{$this->modelClass}->recover(null, $sort);
		$this->Session->setFlash('List reset based on ' . $sort . ' field.');
		return $this->_back();
	}

/**
 * admin_search method
 *
 * @param mixed $term null
 * @return void
 * @access public
 */
	function admin_search($term = null) {
		if ($this->data) {
			$term = trim($this->data['Enum']['query']);
			$url = array(urlencode($term));
			if ($this->data['Enum']['extended']) {
				$url['extended'] = true;
			}
			$this->redirect($url);
		}
		$request = $_SERVER['REQUEST_URI'];
		$term = trim(str_replace(Router::url(array()), '', $request), '/');
		if (!$term) {
			$this->redirect(array('action' => 'index'));
		}
		$conditions = $this->Enum->searchConditions($term, isset($this->passedArgs['extended']));
		$this->Session->setFlash(sprintf(__('All enums matching the term "%1$s"', true), htmlspecialchars($term)));
		$this->data = $this->paginate($conditions);
		$this->_setSelects();
		$this->render('admin_index');
	}

/**
 * admin_verify method
 *
 * @return void
 * @access public
 */
	function admin_verify() {
		$return = $this->{$this->modelClass}->verify();
		if ($return === true) {
			$this->Session->setFlash('Valid!');
		} else {
			$message = 'Found a few problems:<br />';
			foreach ($return as $key => $data) {
				if (is_string($data)) {
					$message .= $data;
				} else {
					$message .= implode ($data, ' ');
				}
				$message .= '<br />';
			}
			$this->Session->setFlash($message);
		}
		return $this->_back();
	}

/**
 * admin_view method
 *
 * @return void
 * @access public
 */
	function admin_view() {
		$this->data = $this->Enum->read(null, $id);
		if(!$this->data) {
			$this->Session->setFlash(__('Invalid enum', true));
			return $this->_back();
		}
	}

/**
 * setSelects method
 *
 * @return void
 * @access protected
 */
	function _setSelects() {
		$types = $this->Enum->modes();
		if ($types && is_array($types)) {
			$this->set('types', array_combine($types, $types));
		} else {
			$this->set('types', array());
		}
	}
}