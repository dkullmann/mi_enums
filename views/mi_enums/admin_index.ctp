<table class="stickyHeader">
<?php
$this->set('title_for_layout', __d('mi_enums', 'Enums', true));
$paginator->options(array('url' => $this->passedArgs));
if (!empty($type)) {
	$th = array(
		//$paginator->sort('Enum.id'),
		$paginator->sort('Order'),
		$paginator->sort('Display'),
		$paginator->sort('Value'),
		$paginator->sort('Default'),
		$paginator->sort('actions')
	);
} else {
	$th = array(
		//$paginator->sort('Id'),
		$paginator->sort('Type'),
		$paginator->sort('Order'),
		$paginator->sort('Display'),
		$paginator->sort('Value'),
		$paginator->sort('Default'),
	);
}
echo $html->tableHeaders($th);
foreach ($data as $i => $row) {
	extract($row);
	if (!empty($type)) {
		$actions = array();
		if ($Enum['order'] > 1) {
			$actions[] = $html->link('↑', array ('action' => 'move_up', $Enum['id']), array ('title' => 'Move Up'));
		}
		if (isset($data[$i+1])) {
			$actions[] = $html->link('↓', array ('action' => 'move_down', $Enum['id']), array ('title' => 'Move Down'));
		}
		$actions[] = $html->link('x', array ('action' => 'delete', $Enum['id']), array ('title' => 'Delete'));
		$actions = implode($actions, ' - ');
		$tr = array(
			//$html->link($Enum['id'], array('action' => 'edit', $Enum['id'])),
			$Enum['order'],
			$Enum['display'],
			$Enum['value'],
			$Enum['default']?'Yes':'',
			$actions
		);
	} else {
		$tr = array(
			//$html->link($Enum['id'], array('action' => 'index', $Enum['type'])),
			$html->link($Enum['type'], array('action' => 'index', $Enum['type'])),
			$Enum['order'],
			$Enum['display'],
			$Enum['value'],
			$Enum['default']?'Yes':'',
		);
	}
	echo $html->tableCells($tr, array('class' => 'odd'), array('class' => 'even'));
}
$menu->settings(__d('mi_enums', 'Options', true), array('overwrite' => true));
if (!empty($type)) {
	$menu->add(array(
		array('title' => __d('mi_enums', 'Check Order', true), 'url' => array('action' => 'verify', $type)),
		array('title' => __d('mi_enums', 'Reset Order', true), 'url' => array('action' => 'recover', $type)),
		array('title' => __d('mi_enums', 'New Enum', true), 'url' => array('action' => 'add', $type)),
		array('title' => __d('mi_enums', 'Verify', true), 'url' => array('action' => 'verify', $type)),
		array('title' => __d('mi_enums', 'Reset Order', true), 'url' => array('action' => 'recover', $type)),
		array('title' => __d('mi_enums', 'Delete', true), 'url' => array('action' => 'delete_all', $type)),
		array('title' => sprintf(__d('mi_enums', 'Auto populate %1$s', true), $type), 'url' => array('action' => 'auto_populate', $type)),
	));
} else {
	$menu->add(array(
		array('title' => __d('mi_enums', 'Check Order', true), 'url' => array('action' => 'verify')),
		array('title' => __d('mi_enums', 'Reset Order', true), 'url' => array('action' => 'recover')),
		array('title' => sprintf(__d('mi_enums', 'Auto populate %1$s', true), __d('mi_enums', 'all Enums', true)), 'url' => array('action' => 'auto_populate')),
	));
}
if (!empty($types) && is_array($types)) {
	$menu->settings(__d('mi_enums', 'Defined Enums', true), array('overwrite' => true));
	foreach ($types as $type) {
		$menu->add(array(
			'url' => array('action' => 'index', $type),
			'title' => sprintf(__d('mi_enums', '%1$s value', true), Inflector::humanize(Inflector::underscore($type)))
		));
	}
}
?>
</table>
<?php echo $this->element('paging');
