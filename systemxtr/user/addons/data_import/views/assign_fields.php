<?php
if($channel_fields)
{	
$this->table->clear();
$this->table->set_template($cp_table_template);
$this->table->set_heading(
		array('data' => lang('params'), 'width' => "30%"),
		array('data' => ''));

	    $this->table->add_row(
	        $this->lang->line('condition'), 
	        form_dropdown("import_if", $remote_table_keys, $import_if).' &nbsp;=&nbsp; '.form_input('condition_equal_value', $condition_equal_value, 'style="width:40%"')
	    );

	    $this->table->add_row(
	        $this->lang->line('create_if_not_exists'), 
	        lang('yes').NBS. form_radio('create_if_not_exists', 'Y', $create_if_not_exists == 'Y').NBS. lang('no').NBS.
	        form_radio('create_if_not_exists', 'N', $create_if_not_exists == 'N') 
	    );

	    $this->table->add_row(
	        $this->lang->line('delete_if_not_exists'), 
	        lang('yes').NBS. form_radio('delete_if_not_exists', 'Y', @$delete_if_not_exists == 'Y').NBS. lang('no').NBS.
	        form_radio('delete_if_not_exists', 'N', @$delete_if_not_exists == 'N') 
	    );
		echo $this->table->generate();
?>
<?php

$this->table->clear();
$this->table->set_template($cp_table_template);
$this->table->set_heading(
		array('data' => lang('entry_info'), 'width' => "30%"),
		array('data' => ''));
		
		$this->table->add_row(
	        $this->lang->line('new_entry_title'), 
		    form_dropdown("assigned_fields[title]", $remote_table_keys, @$assigned_fields['title']).' &nbsp;OR&nbsp; '.form_input('new_entry_title', $new_entry_title,'style="width:40%"')
		);
		
		$this->table->add_row(
		$this->lang->line('entry_date_start'), 
		form_dropdown("assigned_fields[entry_date]", $remote_table_keys, @$assigned_fields['entry_date'])
		);
		
		$this->table->add_row(
		$this->lang->line('entry_date_finish'), 
		form_dropdown("assigned_fields[expiration_date]", $remote_table_keys, @$assigned_fields['expiration_date'])
		);

		$statuses["same"] = "Same";
		
		$this->table->add_row(
		$this->lang->line('entry_status'), 
		form_dropdown("assigned_fields[status]", $statuses, @$assigned_fields['status'])
		);

		/*$statuses["same"] = "Same";

		$this->table->add_row("<label for='condition'>Status for Updated Entries</label><br>Select either open or closed or same as current", 
		form_dropdown("assigned_fields[status_update]", $statuses, @$assigned_fields['status_update'])
		);*/

		$this->table->add_row(
		$this->lang->line('author'), 
		form_dropdown("author_id", $this->db_lib->format_members('username', array('order_by'=>'username')), @$author_id)
		);

		$this->table->add_row(
		$this->lang->line('site'), 
		form_dropdown("site_id", $this->db_lib->format_sites('site_label', array('order_by'=>'site_label')), @$site_id)
		);

		$this->table->add_row(
		$this->lang->line('member_group'), 
		form_dropdown("member_group_id", $member_groups, @$member_group_id)
		);
		echo $this->table->generate();
?>

<?php
$remote_table_keys_short = array();
foreach ($remote_table_keys as $k => $v) 
{
	if( ! strchr($k, '.'))
	{
		$remote_table_keys_short[$k] = $v;
		continue;
	}
	$p = explode('.', $k);
	$remote_table_keys_short[$p[1]] = $v;
}
echo "<h3>".lang('categories')."</h3>";
$this->table->clear();
$cat_template = $cp_table_template;
$cat_template['table_open']	= '<table class="mainTable data_import_table di_category" border="0" cellspacing="0" cellpadding="0">';
$this->table->set_template($cat_template);
$this->table->set_heading(
		array('data' => lang('groups')),
		array('data' => lang('remote_table_field'))
		);
		foreach ($channel_category_groups as $group_id => $group_name) 
		{
			$this->table->add_row(
			    $group_name,
			    form_dropdown("remote_field[$group_id]", $remote_table_keys_short, @$remote_field[$group_id]).$this->lang->line('categories_help')
			);
		}

		echo $this->table->generate();
?>
<?php
	$this->table->clear();
	$this->table->set_template($cp_table_template);
	$this->table->set_heading(
		array('data' => lang('field_label')),
		array('data' => lang('match_key'), 'width'=>'5%'),
		array('data' => lang('required'), 'width'=>'5%'),
		array('data' => lang('remote_table_field')));

	foreach ($channel_fields as $key => $field) 
	{
		
		echo form_hidden("required[$key]", 'N');
		if(is_array($field))
		{
			$field_id = key($field);
			$fields = current($field);
			$table = form_hidden("assigned_fields[{$key}][{$field_id}]", $field_id);

			$table .= "<table border=0 class='mainTable' style='width:40%'>
			<thead>
				<tr>
					<th>".lang('field_label')."</th>
					<th>".lang('match_key')."</th>
					<th>".lang('required')."</th>
					<th>".lang('remote_table_field')."</th>
				</tr>
			</thead>";
			foreach ($fields as $key1 => $field1)
			{
				if( $key1 == 'sale_price' || $key1 == 'sale_price_enabled' ) continue;

					$table .= "<tr><td>{$field1}</td><td>".form_radio("match_key[{$field_id}]", $key1,  (@$match_key[$field_id] == $key1))."</td><td>". form_checkbox("required[$key][$key1]", 'Y',  (@$required[$key][$key1] == 'Y')) ."</td><td>".form_dropdown("assigned_fields[{$key}][{$field_id}][{$key1}]", $remote_table_keys, @$assigned_fields[$key][$field_id][$key1])."</td></tr>";
			}
			$table .= "</table>";
			$this->table->add_row(
		        $all_fields[$field_id]['field_label'],
		        '', 
		        '', 
		        $table
		    );
		    continue;			
		}
		
		$this->table->add_row(
	        $field, 
	        form_radio("match_key", $key,  ($match_key == $key)), 
	        form_checkbox("required[$key]", 'Y',  (@$required[$key] == 'Y')), 
	        form_dropdown("assigned_fields[{$key}]", $remote_table_keys, @$assigned_fields[$key])
	    );		
	}
   
	echo $this->table->generate();
}
?>