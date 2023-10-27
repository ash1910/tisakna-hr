<?=form_open($form_action, array('id' => 'unique_channel_fields_form'));?>

<?php

echo '<h3>'.lang('unique_channel_fields_description').'</h3>';

$cp_pad_table_template['cell_start'] = '<td valign="top">';
$cp_pad_table_template['cell_alt_start'] = '<td valign="top">';

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('channels'), 'style' => 'width:25%;'),
    array('data' => lang('fields')),
	array('data' => lang('remove'), 'style' => 'width:5%;')
);


$this->table->add_row(
	lang('select_channels'),
	lang('select_fields'),
	''
);

if (isset($current['fields']))
{
	foreach ($current['fields'] as $channel_id => $channel_data)
	{
		$field_selects = '';
		
		if (is_array($channel_data))
		{
			foreach ($channel_data as $key => $field_array)
			{
				$field_selects .= '<p>';
				foreach ($field_array as $val => $field_id)
				{
					$field_selects .= '<label>'.form_dropdown('fields['.$channel_id.']['.$key.'][]', array('' => ' -- Select -- ')+$fields[$channel_id], $field_id).'</label> <span class="and">AND</span> ';
				}
				$field_selects .= '<label class="add_select">'.form_dropdown('fields['.$channel_id.']['.$key.'][]', array('' => ' -- Select -- ')+$fields[$channel_id]).'</label>';
				$field_selects .= '</p> <div class="or">OR</div> ';
			}
			$field_selects .= '<p><label class="add_select">'.form_dropdown('fields['.$channel_id.']['.($key+1).'][]', array('' => ' -- Select -- ')+$fields[$channel_id]).'</label></p>';
		}
		
		$this->table->add_row(
			//'<p><label>'.form_dropdown('channels['.$channel_id.']', $channels, $channel_id).'</label></p>',
			'<h3>'.$channels[$channel_id].'</h3>',
			'<div class="field_selects">'.$field_selects.'</div>',
			'<a class="remove_row"> - </a>'
		);

	}

}
elseif (isset($current['channels']))
{
	
	$channel_id = $current['channels'];
	
	$field_selects .= '<label>'.form_dropdown('fields['.$channel_id.'][][]', array('' => ' -- Select -- ')+$fields[$channel_id], $field_id).'</label>';
		
	$this->table->add_row(
		'<p><label>'.form_dropdown('channels['.$channel_id.']', $channels, $channel_id).'</label></p>',
		'<div class="field_selects">'.$field_selects.'</div>',
		''
	);
}

$this->table->add_row(
	array(
		'data' => '<div class="field_add"><p><label>'.lang('add_channel').'<br>'.form_dropdown('add[channel]', $channels, '').'</label> <span class="arrow">&raquo;</span> </p></div>',
		'class' => 'add_row'
	),
	array(
		'data' => '<div class="field_add"><p><label>'.lang('channel_fields').'<br>'.form_dropdown('add[fields][]', $field_titles, '').'</label></p></div>',
		'class' => 'add_row'
	),
	array(
		'data' => '',
		'class' => 'add_row'
	)
);

echo $this->table->generate();

echo '<p>' . form_submit('submit', lang('submit'), 'class="btn submit"') . '</p>';

?>

<br>

<?php
if ( ! $ee3) 
{

	echo '<h3>'.lang('preferences').'</h3>';

	$this->table->set_heading(array('data' => lang('settings'), 'style' => 'width:25%;'), lang('preference'));

	$this->table->add_row(
		'<h4>'.lang('show_confirm').'</h4>'.lang('show_confirm_description'),
		'<label>'.form_radio('settings[show_confirm]', 'y', ($show_confirm == 'y' ? TRUE : FALSE)).' Yes</label> &nbsp; '.
		'<label>'.form_radio('settings[show_confirm]', 'n', ($show_confirm != 'y' ? TRUE : FALSE)).' No</label>'
	);

	echo $this->table->generate();

	echo '<p>' . form_submit('submit', lang('submit'), 'class="btn submit"') . '</p>';

}
?>

<?php $this->table->clear()?>
<?=form_close()?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/unique_channel_fields/views/index.php */