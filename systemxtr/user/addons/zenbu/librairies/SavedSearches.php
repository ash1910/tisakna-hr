<?php namespace Zenbu\librairies;

use Craft;
use Zenbu\librairies\platform\ee\Cache;
use Zenbu\librairies\platform\ee\Lang;
use Zenbu\librairies\platform\ee\Request;
use Zenbu\librairies\platform\ee\Session;
use Zenbu\librairies\platform\ee\Convert;
use Zenbu\librairies\platform\ee\Db;
use Zenbu\librairies\platform\ee\Url;

class SavedSearches
{
    var $fields;

    public function __construct()
    {
    }

    /**
     * Retrieve saved searches
     * @return array
     */
    public function getSavedSearches()
    {
        if(Cache::get('saved_searches'))
        {
            return Cache::get('saved_searches');
        }

        $sql = 'SELECT * FROM zenbu_saved_searches
                WHERE userId = ' . Session::user()->id . '
                ORDER BY `order` ASC';

        $results = Db::rawQuery($sql);

        $output['base_url'] = Url::zenbuUrl('', TRUE);
        $output['items'] = array();

        if(count($results > 0))
        {
            foreach($results as $row)
            {
                $output['items'][] = $row;
            }
        }

        Cache::set('saved_searches', $output, 3600);

        return $output;

    } // END getSavedSearches()

    // --------------------------------------------------------------------


    /**
     * Retrieve user's group saved searches
     * @return array
     */
    public function getGroupSavedSearches()
    {
        if(Cache::get('saved_searches_Group_' . Session::user()->group_id))
        {
            return Cache::get('saved_searches_Group_' . Session::user()->group_id);
        }

        $sql = 'SELECT * FROM zenbu_saved_searches
                WHERE userGroupId = ' . Session::user()->group_id . '
                ORDER BY `order` ASC';

        $results = Db::rawQuery($sql);

        $output['base_url'] = Url::zenbuUrl();
        $output['items'] = array();

        if(count($results > 0))
        {
            foreach($results as $row)
            {
                $output['items'][] = $row;
            }
        }

        Cache::set('saved_searches_Group_' . Session::user()->group_id, $output, 3600);

        return $output;

    } // END getGroupSavedSearches()

    // --------------------------------------------------------------------


    public function getSavedSearchFilters()
    {
        $searchId = Request::param('searchId');

        if( ctype_digit($searchId) )
        {
            if(Cache::get('saved_search_filters_SearchId_' . $searchId))
            {
                return Cache::get('saved_search_filters_SearchId_' . $searchId);
            }

            $sql = 'SELECT ssf.*
                    FROM zenbu_saved_search_filters ssf
                    JOIN zenbu_saved_searches ss ON ss.id = ssf.searchId
                    WHERE ssf.searchId = ' . $searchId . '
                    AND ss.userId = ' . Session::user()->id . '
                    ORDER BY `order` ASC';

            $results = Db::rawQuery($sql);

            $output = array();

            if(count($results > 0))
            {
                foreach($results as $row)
                {
                    $output[] = $row;
                }
            }

            Cache::set('saved_search_filters_SearchId_' . $searchId, $output, 3600);

            return $output;
        }
        else
        {
            return FALSE;
        }
    } // END getSavedSearchFilters()

    // --------------------------------------------------------------------

    /**
     * Permute filter array to work with Zenbu's getEntries conditional
     * @param  array $filters Filter array with old keys
     * @return array          The permuted array
     */
    public function permuteSavedSearchFilters($filters)
    {
        foreach($filters as $key => $filter)
        {
            foreach($filter as $criteria => $val)
            {
                if($criteria == 'filterAttribute1')
                {
                    $criteria = '1st';
                }

                if($criteria == 'filterAttribute2')
                {
                    $criteria = '2nd';
                }

                if($criteria == 'filterAttribute3')
                {
                    $criteria = '3rd';
                }

                $output[$key][$criteria] = $val;
            }
        }

        return isset($output) ? $output : FALSE;

    } // END permuteSavedSearchFilters()

    // --------------------------------------------------------------------
}