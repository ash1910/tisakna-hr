<?php
namespace Zenbu\controllers;

use Zenbu\librairies;
use Zenbu\librairies\ArrayHelper;
use Zenbu\librairies\Fields;
use Zenbu\librairies\Filters;
use Zenbu\librairies\platform\ee\Authors;
use Zenbu\librairies\platform\ee\Base;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Categories;
use Zenbu\librairies\platform\ee\Cp;
use Zenbu\librairies\platform\ee\Db;
use Zenbu\librairies\platform\ee\Entries;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Pagination;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Session;
use Zenbu\librairies\platform\ee\Statuses;
use Zenbu\librairies\platform\ee\Url;
use Zenbu\librairies\platform\ee\View;
use Zenbu\librairies\SavedSearches;
use Zenbu\librairies\Sections;
use Zenbu\librairies\Settings;
use Zenbu\librairies\Users;

class ZenbuBaseController
{
	public $user;
	public $nav_label;
	public $sections;
	public $fields;
	public $all_fields;
	public $filters;
	public $settings;
	public $general_settings;
	public $display_settings;
	public $saved_searches;
	public $debug_mode;
	public $vars;

	public function init()
	{
		// Setup section object
		$this->sectionsObj         = new Sections();
		$sections = $this->sectionsObj->setSections(null);

		// Setup fields object
		$this->fieldsObj         = new Fields();
		$this->fieldsObj->setSections($this->sectionsObj->getSections());

		// Setup fields vars
		$this->default_fields = $this->fieldsObj->getDefaultFields();
		$this->all_fields     = $this->fieldsObj->getFields();
		$this->fieldtypes     = ArrayHelper::flatten_to_key_val('field_id', 'field_type', $this->all_fields);
		$this->field_settings = ArrayHelper::flatten_to_key_val('field_id', 'field_settings', $this->all_fields);
		$this->field_ids      = ArrayHelper::make_array_of('field_id', $this->all_fields);

		// Setup settings object
		$this->settingsObj             = new Settings();
		$this->settingsObj->setFieldsObj($this->fieldsObj);
		$this->settingsObj->setAllFields($this->all_fields);

		// Create settings vars
		$this->display_settings     = $this->settingsObj->getDisplaySettings();
		$this->all_display_settings = $this->settingsObj->getDisplaySettings('all');
		$this->general_settings     = $this->settingsObj->getGeneralSettings();
		$this->permissions          = $this->settingsObj->getPermissions();

		// Set some vars to the fields object, since it will need them
		$this->fieldsObj->setDisplaySettings($this->display_settings);
		$this->fieldsObj->setAllFields($this->all_fields);

		$this->session          = new Session();
		$this->user             = Session::user();
		$this->cache            = new Cache();
		$this->users            = new Users();
		$this->request          = new Request;
		$this->db               = new Db();

		// Setup filters object
		$this->filtersObj          = new Filters();
		$this->filtersObj->setSectionsObj($this->sectionsObj);
		$this->filtersObj->setSections($this->sectionsObj->getSections());
		$this->filtersObj->setFieldsObj($this->fieldsObj);
		$this->filtersObj->setAllFields($this->all_fields);
		$this->filtersObj->setGeneralSettings($this->general_settings);
		$this->filtersObj->setSections($this->sectionsObj->getSections());
		$this->filtersObj->setDefaultFields($this->default_fields);

		// Setup entries object
		$this->entries          = new Entries();
		$this->entries->setSections($sections);
		$this->entries->setFieldsObj($this->fieldsObj);
		$this->entries->setDisplaySettings($this->display_settings);
		$this->entries->setPermissions($this->permissions);
		$this->entries->setAllFields($this->all_fields);
		$this->entries->setFieldtypes($this->fieldtypes);

		// Setup CP object
		$this->cp               = new Cp();
		$this->cp->setPermissions($this->permissions);

		$this->authors          = new Authors();
		$this->statuses         = new Statuses();
		$this->categories       = new Categories();
		$this->pagination       = new Pagination();
		$this->url              = new Url();
		$this->view             = new View();
		$this->saved_searches   = new SavedSearches();
		$this->debug_mode       = $this->settingsObj->isDebugEnabled();

		Lang::load(array('zenbu'));

		$this->vars['fields'] 			= $this->all_fields;
		$this->vars['message']          = ee('CP/Alert')->getAllInlines();
		$this->vars['permissions']      = $this->permissions;
		$this->vars['display_settings'] = $this->display_settings;
		$this->vars['general_settings'] = $this->general_settings;
		$this->vars['debug_mode']       = $this->debug_mode;

		View::includeJs(array('resources/js/zenbu_common.js'));
	}
}