<?php /* SVN FILE: $Id$ */
extract($data);
$this->set('title_for_layout', $Enum['display']);
?>
<table>
<?php
	extract($data);
	echo $html->tableCells(array('id', $Enum['id']));
	echo $html->tableCells(array('type', $Enum['type']));
	echo $html->tableCells(array('order', $Enum['order']));
	echo $html->tableCells(array('display', $Enum['display']));
	echo $html->tableCells(array('value', $Enum['value']));
	echo $html->tableCells(array('description', $Enum['description']));
	echo $html->tableCells(array('default', $Enum['default']));
	echo $html->tableCells(array('created', $Enum['created']));
	echo $html->tableCells(array('modified', $Enum['modified']));

$menu->settings(__('Options', true), array('overwrite' => true));
$menu->add(array(
	array('title' => __('New Enum', true), 'url' => array('action' => 'add', $Enum['type'])),
	array('title' => __('List Enums', true), 'url' => array('action' => 'index', $Enum['type']))
));
?>
</table>