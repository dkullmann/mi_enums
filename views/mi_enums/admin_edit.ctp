<?php
if ($data) {
	extract($data);
}
$action = in_array($this->action, array('add', 'admin_add'))?'Add':'Edit';
$action = Inflector::humanize($action);
if ($action == 'Add' && !$type) {
	$typeType = null;
} else {
	$typeType = 'hidden';
}
if ($data) {
	$type = $data['Enum']['type'];
}
$name = isset($this->data[$modelClass]['display'])?$this->data[$modelClass]['display']:'Add New';
$name = low(str_replace('.', ' ', Inflector::humanize(Inflector::underscore($type)))) . ':' . $name;
$this->set('title_for_layout', $name);
?>
<div class="form-container">
<?php
echo $form->create();
echo $form->inputs(array(
	'legend' => false,
	'id',
	'type' => array('type' => $typeType, 'value' => $type),
	//'order',
	'display',
	'value',
	'default',
));
echo $form->inputs(array(
	'legend' => false,
	'description',
));
echo $form->end('Submit');
$menu->settings(__d('mi_enums', 'Options', true), array('overwrite' => true));
$menu->add(array('title' => __d('mi_enums', 'New Enum', true), 'url' => array('action' => 'add', $type)));
?>
</div>
