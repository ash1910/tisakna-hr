<?php
namespace Zenbu\controllers;

use Zenbu\controllers\ZenbuBaseController as ZenbuBaseController;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Convert;
use Zenbu\librairies\platform\ee\Cp;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Url;
use Zenbu\librairies\platform\ee\View;

class ZenbuController extends ZenbuBaseController
{
	public function __construct()
	{
		parent::init();
	}

	public function actionClearZenbuCache()
	{
		ee()->cache->delete('/zenbu/');
		return 'Zenbu caches deleted';
	}

	/**
	 * Main page
	 * @return string Rendered template
	 */
	public function actionIndex()
	{
		if(Request::isAjax() === FALSE)
		{
			$this->cp->title(Lang::t('entry_manager'));
			$this->cp->rightNav();
			$this->vars['sections']                 = $this->sectionsObj->getSections();
			$selectOptions                          = $this->sectionsObj->getSectionSelectOptions();
			$this->vars['section_dropdown_options'] = $selectOptions['sections'];
			$this->vars['limit_options']            = $this->filtersObj->getLimitSelectOptions();
			$this->vars['orderby_options']          = $this->filtersObj->getOrderBySelectOptions();
			$this->vars['sort_options']             = $this->filtersObj->getSortSelectOptions();
			$this->vars['firstFilterOptions']       = $this->filtersObj->getFirstFilterOptions();
			$this->vars['secondFilterOptions']      = $this->filtersObj->getSecondFilterOptions();
			$this->vars['fields']                   = $this->all_fields;
			$this->vars['fields_2nd_filter_type']   = $this->fieldsObj->getFieldsSecondFilterType();
			$this->vars['sections']                 = $this->sectionsObj->getSections();
			$this->vars['savedSearches']            = $this->saved_searches->getSavedSearches();
			$this->vars['savedSearch']              = $this->saved_searches->getSavedSearchFilters();
			$this->vars['statusFilterOptions']      = $this->statuses->getStatusFilterOptions();
			$this->vars['nested_categories']        = $this->categories->getNestedCategories();
			$this->vars['category_group_names']     = $this->categories->getCategoryGroups();
			$this->vars['category_dropdowns']       = $this->categories->makeCategoryDropdown($this->vars['nested_categories'], $this->vars['category_group_names']);
			$this->vars['storedFilterData']         = array();
			$this->vars['sidebar_hidden']           = Cache::get('zenbu_sidebar_hidden');

			/**
			*	======================================
			*	Extension Hook zenbu_add_content_before_create_button
			*	======================================
			*
			*	Enables the addition of extra code before the "Create New" link
			*	@return string 	$this->vars_other['extra_content_before_create_button']	The output HTML
			*
			*/
			if (ee()->extensions->active_hook('zenbu_add_content_before_create_button') === TRUE)
			{
				$this->vars['zenbu_add_content_before_create_button'] = ee()->extensions->call('zenbu_add_content_before_create_button');
				if (ee()->extensions->end_script === TRUE) return;
			}

			/**
			*	======================================
			*	Extension Hook zenbu_after_main_content
			*	======================================
			*
			*	Adds extra code after the main Zenbu section
			*	@return string 	The output HTML
			*
			*/
			if (ee()->extensions->active_hook('zenbu_after_main_content') === TRUE)
			{
				$this->vars['zenbu_after_main_content'] = ee()->extensions->call('zenbu_after_main_content');
				if (ee()->extensions->end_script === TRUE) return;
			}

			//	----------------------------------------
			//	If this is not a saved search request, or
			//	there is no saved search to retrieve,
			//	try getting filters from cache, if
			//	there's anything available
			//	----------------------------------------
			if( ! $this->vars['savedSearch'] )
			{
				// Comment out the following line if constant cache retrieval is being bothersome
				if(Cache::get('zenbu_filter_cache'))
				{
					$this->vars['storedFilterData'] = Cache::get('zenbu_filter_cache');
				}
			}
		}

		//	----------------------------------------
		//	Order "show" fields first
		//	----------------------------------------
		$orderedFields = $this->fieldsObj->getOrderedFields();

		$this->vars['columns'] = empty($orderedFields) ? $this->vars['fields'] : $orderedFields;

		$this->vars['status_colors']          = $this->statuses->getStatusColors();
		$this->vars['authors']                = $this->authors->getAuthorSelectOptions();
		$this->vars['result_array']           = $results = $this->entries->getEntries();
		$this->vars['entries']                = $results['results'];
		$this->vars['entries_override']       = $this->entries->getOverrides($this->vars['entries']);
		$this->vars['entry_categories']       = $this->categories->getEntryCategories($this->vars['entries']);
		$this->vars['total_results']          = $results['total_results'];
		$this->vars['action_url']             = Url::zenbuUrl();
		$this->vars['multi_edit_action_url']  = Url::zenbuUrl("multi_edit");
		$this->vars['save_search_action_url'] = Url::zenbuUrl("save_searches");

		//	----------------------------------------
		//	Pagination
		//	----------------------------------------
		$this->vars['pagination']   = $this->pagination->getPagination($this->vars['total_results'], Request::param('limit', 25));
		$this->vars['results_from'] = Request::param('perpage') && Request::param('perpage') != 1 ? (Request::param('perpage') - 1) * Request::param('limit', 25) + 1 : 1;
		$this->vars['results_to']   = $this->vars['results_from'] != 1 ? $this->vars['results_from'] + Request::param('limit', 25) - 1 : Request::param('limit', 25);
		$this->vars['results_to']   = $this->vars['results_to'] > $this->vars['total_results'] ? $this->vars['total_results'] : $this->vars['results_to'];

		//	----------------------------------------
		//	Response
		//	----------------------------------------
		if(Request::isAjax() !== FALSE)
		{
			//	----------------------------------------
			// 	Return JSON string if in CP and
			// 	request is not from Zenbu
			//	----------------------------------------
			if(REQ == 'CP' && ee()->uri->segment(4) != 'zenbu')
			{
				return json_encode($this->vars);
			}
			echo View::render('main/results.twig', $this->vars);
			exit();
		}
		else
		{
			//	----------------------------------------
			// 	Return JSON string if request is not
			// 	from within the CP
			//	----------------------------------------
			if(REQ != 'CP')
			{
				return json_encode($this->vars);
			}

			if(REQ == 'CP' && ee()->uri->segment(4) != 'zenbu')
			{
				return $this->vars;
			}

			//	----------------------------------------
			//	If were loading Zenbu with a channel filter,
			//	remove temporarily cached search filters since
			//	these will interfere with the channel filter.
			//	----------------------------------------
			if(Request::get('channel_id'))
			{
				Cache::delete('TempFilters');
			}

			ee()->cp->add_js_script(array('ui' => 'datepicker'));
			View::includeCss(array(
				'resources/select2/css/select2.min.css',
				'resources/css/select2-ee3.css',
				'resources/css/zenbu_main.css',
				'resources/css/font-awesome.min.css',
				));
			View::includeJs(array(
				'resources/select2/js/select2.min.js',
				'resources/js/typewatch.js',
				'resources/js/zenbu_common.js',
				'resources/js/zenbu_main.js'
				));
			return array(
			  'body'       => View::render('main/index.twig', $this->vars),
			  'breadcrumb' => array(),
			  'heading'  => Lang::t('entry_manager'),
			);
		}

	} // END actionIndex()

	// --------------------------------------------------------------------

	/**
     * Save the sidebar state in cache, open or closed
     * @return void Echoed JSON string
     */
    public function actionSaveSidebarState()
    {
		$state = Request::param('state', 'open');

		$sidebar_hidden = $state == 'open' ? FALSE : TRUE;

		Cache::set('zenbu_sidebar_hidden', $sidebar_hidden, 0);

		echo json_encode(array('state' => $state, 'sidebar_hidden' => $sidebar_hidden));

		exit();
    } // END actionSaveSidebarState()

    // --------------------------------------------------------------------
}
