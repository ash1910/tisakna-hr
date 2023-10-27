<?php

/**
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 */

namespace GmapsFieldtype\Model;

use EllisLab\ExpressionEngine\Service\Model\Model;

class History extends Model {

    protected static $_primary_key = 'history_id';
    protected static $_table_name = 'gmaps_fieldtype_history';

    protected $history_id;
    protected $entry_id;
    protected $field_id;
    protected $timestamp;
    protected $data;
}