<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Gmaps
 *
 * @package             Gmaps Fieldtype for EE3
 * @author              Rein de Vries (info@reinos.nl)
 * @copyright           Copyright (c) 2015 Rein de Vries
 * @license  			http://reinos.nl/add-ons/commercial-license
 * @link                http://reinos.nl/add-ons/gmaps
 */

require_once(PATH_THIRD.'gmaps_fieldtype/config.php');

return array(
    'author'      => GMAPS_FT_AUTHOR,
    'author_url'  => GMAPS_FT_AUTHOR_URL,
    'name'        => GMAPS_FT_NAME,
    'description' => GMAPS_FT_DESCRIPTION,
    'version'     => GMAPS_FT_VERSION,
    'docs_url'  => GMAPS_FT_DOCS,
    'settings_exist' => true,
    'namespace'   => 'GmapsFieldtype',
    'models' => array(
        'Cache' => 'Model\History'
    )
);