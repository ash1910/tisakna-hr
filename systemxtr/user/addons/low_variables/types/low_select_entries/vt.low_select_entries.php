<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Select Entries variable type
 *
 * @package        low_variables
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-variables
 * @copyright      Copyright (c) 2009-2016, Low
 */
class Low_select_entries extends Low_variables_type {

	public $info = array(
		'name' => 'Select Entries'
	);

	public $default_settings = array(
		'show_future'     => 'y',
		'show_expired'    => 'n',
		'channels'        => array(),
		'categories'      => array(),
		'statuses'        => array(),
		'limit'           => '0',
		'orderby'         => 'title',
		'sort'            => 'asc',
		'multiple'        => 'y',
		'separator'       => 'pipe',
		'multi_interface' => 'select'
	);

	// --------------------------------------------------------------------

	/**
	 * Display settings sub-form for this variable type
	 */
	public function display_settings()
	{
		// -------------------------------------
		//  Init return value
		// -------------------------------------

		$r = array();

		// -------------------------------------
		//  Build setting: Future & Expired entries
		// -------------------------------------

		$r[] = array(
			'title' => 'show_future',
			'fields' => array(
				$this->setting_name('show_future') => array(
					'type'  => 'yes_no',
					'value' => $this->settings('show_future') ?: 'n'
				)
			)
		);

		$r[] = array(
			'title' => 'show_expired',
			'fields' => array(
				$this->setting_name('show_expired') => array(
					'type'  => 'yes_no',
					'value' => $this->settings('show_expired') ?: 'n'
				)
			)
		);

		// -------------------------------------
		//  Build setting: channels
		// -------------------------------------

		$channels = ee('Model')
			->get('Channel')
			->filter('site_id', ee()->config->item('site_id'))
			->order('channel_title')
			->all();

		$r[] = array(
			'title' => 'channels',
			//'desc' => 'channel_ids_help',
			'fields' => array(
				$this->setting_name('channels') => array(
					'type' => 'checkbox',
					'wrap' => TRUE,
					'choices' => $channels->getDictionary('channel_id', 'channel_title'),
					'value' => $this->settings('channels')
				)
			)
		);

		// -------------------------------------
		//  Build setting: categories
		// -------------------------------------

		if ($categories = LVUI::get_categories())
		{
			// Init category arrays
			$choices = array('' => lang('select_any'));

			// Loop through groups and create category trees for each of those
			foreach ($categories as $group)
			{
				foreach ($group['categories'] as $cat)
				{
					$choices[$group['name']][$cat['id']] = str_repeat('&nbsp;&nbsp;', $cat['depth']) . $cat['name'];
				}
			}

			$r[] = array(
				'title' => 'categories',
				'fields' => array(array(
					'type' => 'html',
					'content' => LVUI::view_field('select', array(
						'name' => $this->setting_name('categories'),
						'choices' => $choices,
						'value' => $this->settings('categories'),
						'multiple' => TRUE
					))
				))
			);
		}

		// -------------------------------------
		//  Build setting: statuses
		// -------------------------------------

		// Initiate status choices
		$choices = array(
			''       => lang('select_any'),
			'open'   => lang('open'),
			'closed' => lang('closed')
		);

		// Get statuses from DB
		$statuses = ee('Model')
			->get('Status')
			->with('StatusGroup')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('status', 'NOT IN', array_keys($choices))
			->order('StatusGroup.group_name')
			->order('Status.status_order')
			->all();

		// Add statuses to choices
		foreach ($statuses as $status)
		{
			$choices[$status->StatusGroup->group_name][$status->status] = $status->status;
		}

		// Add to form
		$r[] = array(
			'title' => 'statuses',
			'fields' => array(array(
				'type' => 'html',
				'content' => LVUI::view_field('select', array(
					'name' => $this->setting_name('statuses'),
					'choices' => $choices,
					'value' => $this->settings('statuses'),
					'multiple' => TRUE
				))
			))
		);

		// -------------------------------------
		//  Build setting: orderby & sort
		// -------------------------------------

		$r[] = array(
			'title' => 'orderby',
			'fields' => array(
				$this->setting_name('orderby') => array(
					'type' => 'select',
					'value' => $this->settings('orderby'),
					'choices' => array(
						'title'      => lang('title'),
						'entry_date' => lang('entry_date')
					)
				),
				$this->setting_name('sort') => array(
					'type' => 'select',
					'value' => $this->settings('sort'),
					'choices' => array(
						'asc'  => lang('order_asc'),
						'desc' => lang('order_desc')
					)
				)
			)
		);

		// -------------------------------------
		//  Build setting: limit
		// -------------------------------------

		$r[] = array(
			'title' => 'limit',
			'fields' => array(
				$this->setting_name('limit') => array(
					'type' => 'select',
					'value' => $this->settings('limit'),
					'choices' => array(
						'0'    => lang('all'),
						'25'   => '25',
						'50'   => '50',
						'100'  => '100',
						'250'  => '250',
						'500'  => '500',
						'1000' => '1000'
					)
				)
			)
		);

		// -------------------------------------
		//  Build setting: multiple?
		// -------------------------------------

		$r[] = LVUI::setting('multiple', $this->setting_name('multiple'), $this->settings('multiple'));

		// -------------------------------------
		//  Build setting: separator
		// -------------------------------------

		$r[] = LVUI::setting('separator', $this->setting_name('separator'), $this->settings('separator'));

		// -------------------------------------
		//  Build setting: multi interface
		// -------------------------------------

		$r[] = LVUI::setting('interface', $this->setting_name('multi_interface'), $this->settings('multi_interface'));

		// -------------------------------------
		//  Return output
		// -------------------------------------

		return $this->settings_form($r);
	}

	// --------------------------------------------------------------------

	/**
	 * Display input field for regular user
	 */
	public function display_field($var_data)
	{
		// -------------------------------------
		//  Prep options
		// -------------------------------------

		$now = ee()->localize->now;

		// -------------------------------------
		//  Get entries
		// -------------------------------------

		ee()->db
			->select(array('t.entry_id', 't.title'))
			->from('channel_titles as t');

		// Filter out future entries
		if ($this->settings('show_future') != 'y')
		{
			ee()->db->where('t.entry_date <=', $now);
		}

		// Filter out expired entries
		if ($this->settings('show_expired') != 'y')
		{
			ee()->db->where("(t.expiration_date > {$now} OR t.expiration_date = 0)");
		}

		// Filter by channel
		if ($channels = array_filter($this->settings('channels')))
		{
			ee()->db->where_in('t.channel_id', $channels);
		}

		// Filter by category
		if ($categories = array_filter($this->settings('categories')))
		{
			ee()->db->join('category_posts as cp', 't.entry_id = cp.entry_id');
			ee()->db->where_in('cp.cat_id', $categories);
		}

		// Filter by status
		if ($statuses = array_filter($this->settings('statuses')))
		{
			ee()->db->where_in('t.status', $statuses);
		}

		// Order by custom order
		ee()->db->order_by($this->settings('orderby'), $this->settings('sort'));

		// Limit entries
		if ($limit = $this->settings('limit'))
		{
			ee()->db->limit($limit);
		}

		$query = ee()->db->get();
		$choices = low_flatten_results($query->result_array(), 'title', 'entry_id');
		$choices = array_map('htmlspecialchars', $choices);

		// -------------------------------------
		//  Single choice
		// -------------------------------------

		if ($this->settings('multiple') != 'y')
		{
			return array(
				$this->input_name() => array(
					'type' => 'select',
					'choices' => array('' => '--') + $choices,
					'value' => $var_data
				)
			);
		}

		// -------------------------------------
		//  Multiple choice
		// -------------------------------------

		else
		{
			$data = array(
				'name' => $this->input_name(),
				'choices' => $choices,
				'value' => LVUI::explode($this->settings('separator'), $var_data),
				'multiple' => TRUE
			);

			return array(array(
				'type' => 'html',
				'content' => LVUI::view_field($this->settings('multi_interface'), $data)
			));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Prep variable data for saving
	 */
	public function save($var_data)
	{
		return is_array($var_data)
			? LVUI::implode($this->settings('separator'), $var_data)
			: $var_data;
	}

	// --------------------------------------------------------------------

}