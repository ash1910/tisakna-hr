<?php
namespace Zenbu\controllers;

use Zenbu\controllers\ZenbuBaseController as ZenbuBaseController;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Convert;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\View;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Url;
use Zenbu\librairies\platform\ee\Cp;
use Zenbu\models as Model;

class Zenbu_DisplaySettingsController extends ZenbuBaseController
{
	var $permissions;
	var $general_settings;

	public function __construct()
	{
		parent::init();
	}


	/**
	 * Display Settings
	 * @return string Rendered template
	 */
    public function actionIndex()
    {
    	$this->cp->title(Lang::t('display_settings'));
    	$this->cp->initSidebar();
		$fields                               = $this->vars['fields'];
		$this->vars['settings']               = $this->all_display_settings;
		$this->vars['extra_display_settings'] = $this->settingsObj->getExtraDisplaySettingsFields('all');
		$this->vars['general_settings']       = $this->general_settings;

		// We don't want some general setting keys to show up
		// in the Display Settings, so removing them here.
		unset($this->vars['general_settings']['default_2nd_filter_type']);

		$this->vars['permissions']            = $this->permissions;
		$this->vars['message']                = ee('CP/Alert')->getAllInlines();

		//	----------------------------------------
		//	Get rid of Zenbu filter cache
		//	This avoids sectionId/entryTypeId mismatch if
		//	returning to main Zenbu page
		//	----------------------------------------
		Cache::delete('zenbu_filter_cache');

		//	----------------------------------------
		//	Order "show" fields first
		//	----------------------------------------
		$this->vars['rows']                     = $this->fieldsObj->getOrderedFields(TRUE);
		$selectOptions                          = $this->sectionsObj->getSectionSelectOptions();
		$this->vars['section_dropdown_options'] = $selectOptions['sections'];
		$this->vars['user_groups']              = $this->users->getUserGroups();
		$this->vars['limit_options']            = $this->filtersObj->getLimitSelectOptions();
		$this->vars['orderby_options']          = $this->filtersObj->getOrderBySelectOptions();
		$this->vars['sort_options']             = $this->filtersObj->getSortSelectOptions();

		//	----------------------------------------
		//	Action URLs
		//	----------------------------------------
		$this->vars['section_select_action_url'] = Url::zenbuUrl("display_settings");
		$this->vars['action_url']                = Url::zenbuUrl("save_settings");

		if(Request::isAjax() !== FALSE)
		{
			echo View::render('display_settings/settings.twig', $this->vars);
			exit();
		}
		else
		{
			// View::includeCss(array('zenbu/css/font-awesome.min.css', 'zenbu/css/zenbu_main.css'));
			// View::includeJs(array('zenbu/js/zenbu_main.js', 'zenbu/js/jquery-ui.min.js', 'zenbu/js/typewatch.js'));

			View::includeCss(array(
				'resources/select2/css/select2.min.css',
				'resources/css/select2-ee3.css',
				'resources/css/zenbu_main.css',
				'resources/css/font-awesome.min.css'
				));
			View::includeJs(array(
				'resources/js/typewatch.js',
				'resources/select2/js/select2.min.js',
				'resources/js/zenbu_display_settings.js',
				'resources/js/zenbu_common.js',
				'resources/js/zenbu_main.js',
				));
			return array(
              'body'       => View::render('display_settings/index.twig', $this->vars),
              'breadcrumb' => array(Url::zenbuUrl()->compile() => Lang::t('entry_manager')),
              'heading'  => Lang::t('display_settings'),
            );
		}
    } // END actionIndex()

    // --------------------------------------------------------------------


    public function actionSave()
    {
		$fields        = Request::post('field');
		$fieldsettings = Request::post('settings'); // Extra display settings
		$applyTo       = Request::post('applyTo');

		$sectionId     = Request::param(Convert::string('sectionId'), 0);
		//$entryTypeId   = Request::param(Convert::string('subSectionId'), 0);
		$sectionId     = empty($sectionId) ? 0 : $sectionId;
		//$entryTypeId   = empty($entryTypeId) ? 0 : $entryTypeId;
		$c             = 1;

    	$this->db->delete('exp_zenbu_display_settings', '(sectionId IS NULL OR sectionId = ?) AND userId = ?', array(
    			$sectionId,
    			$this->user->id
			)
		);

    	if($applyTo)
    	{
	    	foreach($applyTo as $group_id)
	    	{
	    		$this->db->delete('exp_zenbu_display_settings', '(sectionId IS NULL OR sectionId = ?) AND (userId IS NULL OR userId = 0) AND userGroupId = ?', array(
    			$sectionId,
    			$group_id
					)
				);
	    	}
	    }

    	foreach($fields as $key => $setting)
    	{
    		foreach($setting as $handle => $show)
    		{
				$settingsHandle                  = is_integer($handle) ? 'field_id_'.$handle : $handle;
				$display_settings                = new Model\ZenbuDisplaySettingsModel();
				$display_settings->userId        = $this->user->id;
				$display_settings->userGroupId   = 0;
				$display_settings->sectionId     = $sectionId;
				//$display_settings->entryTypeId = $entryTypeId;
				$display_settings->fieldType     = is_integer($handle) ? 'field' : $handle;
				$display_settings->fieldId       = is_integer($handle) ? $handle : 0;
				$display_settings->order         = $c;
				$display_settings->show          = empty($show) ? '0' : '1';
				$display_settings->settings      = isset($fieldsettings[$sectionId][$settingsHandle]) ? json_encode($fieldsettings[$sectionId][$settingsHandle]) : NULL;
		    	$display_settings->save();

		    	if($applyTo)
		    	{
			    	foreach($applyTo as $group_id)
			    	{
			    		$display_settings->userId = 0;
			    		$display_settings->userGroupId = $group_id;
			    		$display_settings->save();
			    	}
			    }

		    	unset($display_settings);

	    		$c++;
    		}
    	}


    	//	----------------------------------------
    	//	General Settings
    	//	----------------------------------------

    	$g_settings        = Request::post('general_settings');

    	if($g_settings)
    	{
			foreach($g_settings as $g_setting => $value)
			{
				$general_settings          = new Model\ZenbuGeneralSettingsModel();
				$general_settings->userId  = $this->user->id;
				$general_settings->setting = $g_setting;
				$general_settings->value   = $value;
				$general_settings->save();

				if($applyTo)
		    	{
			    	foreach($applyTo as $group_id)
			    	{
			    		$general_settings->userId = 0;
			    		$general_settings->userGroupId = $group_id;
			    		$general_settings->save();
			    	}
			    }

		    	unset($general_settings);
			}
    	}

    	$caches_to_keep[] = Cache::get('TempFilters');
		$caches_to_keep[] = Cache::get('zenbu_sidebar_hidden');
		$caches_to_keep[] = Cache::get('zenbu_filter_cache');
		list($a, $b, $c) = $caches_to_keep;
		$this->cache->delete();
		Cache::set('TempFilters', $a, 60);
		Cache::set('zenbu_sidebar_hidden', $b, 0);
		Cache::set('zenbu_filter_cache', $c, 600);

		$this->cp->message('success', Lang::t('display_settings_save_success'));

    	Request::redirect(Url::zenbuUrl('display_settings&'.Convert::string('sectionId').'='.$sectionId));
    } // END actionSave()

    // --------------------------------------------------------------------

}
