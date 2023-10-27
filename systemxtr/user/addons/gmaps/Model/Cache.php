<?php

/**
 * @author		Rein de Vries <support@reinos.nl>
 * @link		http://ee.reinos.nl
 * @copyright 	Copyright (c) 2017 Reinos.nl Internet Media
 * @license     http://ee.reinos.nl/commercial-license
 *
 * Copyright (c) 2017. Reinos.nl Internet Media
 * All rights reserved.
 *
 * This source is commercial software. Use of this software requires a
 * site license for each domain it is used on. Use of this software or any
 * of its source code without express written permission in the form of
 * a purchased commercial or other license is prohibited.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * As part of the license agreement for this software, all modifications
 * to this source must be submitted to the original author for review and
 * possible inclusion in future releases. No compensation will be provided
 * for patches, although where possible we will attribute each contribution
 * in file revision notes. Submitting such modifications constitutes
 * assignment of copyright to the original author (Rein de Vries and
 * Reinos.nl Internet Media) for such modifications. If you do not wish to assign
 * copyright to the original author, your license to  use and modify this
 * source is null and void. Use of this software constitutes your agreement
 * to this clause.
 */

namespace Reinos\Gmaps\Model;

use Gmaps_helper;
use EllisLab\ExpressionEngine\Service\Model\Model;

class Cache extends Model {

    protected static $_primary_key = 'cache_id';
    protected static $_table_name = 'gmaps_cache';

    protected $cache_id;
    protected $address;
    protected $date;
    protected $search_key;
    protected $geocoder;
    protected $type;
    protected $result_object;

    protected static $_typed_columns = array(
        'result_object' => 'serialized'
    );

    /**
     * Need to refresh?
     *
     * @param $cache_time
     * @return bool
     */
    public function needToRefresh($cache_time)
    {
        if(time() - $this->getProperty('date') > $cache_time)
        {
            //delete the record
            $this->delete();

            //log
            Gmaps_helper::log('refresh '.$this->getRawProperty('type').' cache for key '.$this->getRawProperty('search_key'), 3);

            return true;
        }

        return false;
    }
}
