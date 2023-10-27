<?php
$config['name']='Edit This';
$config['version']='3.0.0';
$config['nsm_addon_updater']['versions_xml']='http://www.hopstudios.com/software/versions/edit_this/';

// Version constant
if (!defined("EDIT_THIS_VERSION")) {
	define('EDIT_THIS_VERSION', $config['version']);
}
