<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//if (isset(ee()) == FALSE) // ee() =& get_instance(); // For EE 2.2.0+

// Default Field Columns
$config['cv_columns']['image']		= ee()->lang->line('cv:video');
$config['cv_columns']['title']		= ee()->lang->line('cv:title');
$config['cv_columns']['desc']		= ee()->lang->line('cv:desc');
$config['cv_columns']['duration']	= ee()->lang->line('cv:duration');
$config['cv_columns']['views']		= ee()->lang->line('cv:views');
$config['cv_columns']['date']		= ee()->lang->line('cv:date');
$config['cv_columns']['cvfield_1']	= '';
$config['cv_columns']['cvfield_2']	= '';
$config['cv_columns']['cvfield_3']	= '';
$config['cv_columns']['cvfield_4']	= '';
$config['cv_columns']['cvfield_5']	= '';

// Defaults!
$config['cv_defaults']['video_limit'] = '';
