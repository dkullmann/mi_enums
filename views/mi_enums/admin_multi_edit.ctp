<?php /* SVN FILE: $Id: admin_multi_edit.ctp 1723 2009-10-16 10:36:34Z AD7six $ */
$this->set('title_for_layout', __('Enums', true));
echo $form->create(); ?>
<table>
<?php
if (!empty($type)) {
	$th = array(
		'Id',
		'Display',
		'Value',
		'Default',
		'actions'
	);
} else {
	$th = array(
		'Id',
		'Type',
		'Display',
		'Value',
		'Default',
	);
}
echo $html->tableHeaders($th);
foreach ($data as $i => $row) {
	if (!is_array($row) || !isset($row['Enum'])) {
		continue;
	}
	extract($row);
	if (!empty($type)) {
		$tr = array(
			array(
				$Enum['id'] . $form->hidden($i . '.Enum.id'),
				$form->input($i . '.Enum.display', array('div' => false, 'label' => false)),
				$form->input($i . '.Enum.value', array('div' => false, 'label' => false)),
				$form->input($i . '.Enum.default', array('div' => false, 'label' => false)),
			),
		);
	} else {
		$tr = array(
			array(
				$Enum['id'] . $form->hidden($i . '.Enum.id'),
				$form->input($i . '.Enum.type', array('div' => false, 'label' => false)),
				$form->input($i . '.Enum.display', array('div' => false, 'label' => false)),
				$form->input($i . '.Enum.value', array('div' => false, 'label' => false)),
				$form->input($i . '.Enum.default', array('div' => false, 'label' => false)),
			),
		);
	}
	$class = $i%2?'even':'odd';
	echo $html->tableCells($tr, compact('class'), compact('class'));
}
?>
</table>
<?php echo $form->end('Submit');