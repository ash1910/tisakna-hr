<?php

if (!defined('CHANNEL_FILES_NAME')){
    define('CHANNEL_FILES_NAME',         'Channel Files');
    define('CHANNEL_FILES_CLASS_NAME',   'channel_files');
    define('CHANNEL_FILES_VERSION',      '6.0.1');
}

if ( ! function_exists('dd')) {
    function dd()
    {
        array_map(function($x) { var_dump($x); }, func_get_args()); die;
    }
}

return array(
    'author'         => 'DevDemon',
    'author_url'     => 'http://devdemon.com/',
    'docs_url'       => 'http://www.devdemon.com/docs/',
    'name'           => CHANNEL_FILES_NAME,
    'description'    => '',
    'version'        => CHANNEL_FILES_VERSION,
    'namespace'      => 'DevDemon\ChannelFiles',
    'settings_exist' => false,
    'fieldtypes' => array(
        'channel_files' => array(
            'name' => 'Channel Files',
            'compatibility' => null,
        ),
    ),
    'models' => array(
        'File' => 'Model\File',
    ),
    'services'       => array(),
    'services.singletons' => array(
        'Settings' => function($addon) {
            return new DevDemon\ChannelFiles\Service\Settings($addon);
        },
        'Helper' => function($addon) {
            return new DevDemon\ChannelFiles\Service\Helper($addon);
        },
    ),

    //----------------------------------------
    // Default Fieldtype Settings
    //----------------------------------------
    'settings_fieldtype' => array (
        'upload_location'          => 'local',
        'categories'               => array(),
        'show_stored_files'        => 'yes',
        'stored_files_by_author'   => 'no',
        'stored_files_search_type' => 'entry',
        'show_import_files'        => 'no',
        'import_path'              => '',
        'jeditable_event'          => 'click',
        'file_limit'               => '',
        'file_extensions'          => '*.*',
        'entry_id_folder'          => 'yes',
        'prefix_entry_id'          => 'yes',
        'hybrid_upload'            => 'yes',
        'locked_url_fieldtype'     => 'no',
        'show_download_btn'        => 'no',
        'show_file_replace'        => 'no',
        'locations' => array(
            'local' => array(
                'location' => 0,
            ),
            's3' => array(
                'key'               => '',
                'secret_key'        => '',
                'bucket'            => '',
                'region'            => 'us-east-1',
                'acl'               => 'public-read',
                'storage'           => 'standard',
                'force_download'    => 'no',
                'directory'         => '',
                'cloudfront_domain' => '',
            ),
            'cloudfiles' => array(
                'username'  => '',
                'api'       => '',
                'container' => '',
                'region'    => 'us',
                'cdn_uri'   => '',
            ),
            'ftp' => array(
                'hostname' => '',
                'port'     => '21',
                'username' => '',
                'password' => '',
                'passive'  => 'yes',
                'path'     => '/',
                'url'      => '',
                'ssl'      => 'no',
                'debug'    => true,
            ),
            'sftp' => array(
                'hostname' => '',
                'port'     => '22',
                'username' => '',
                'password' => '',
                'passive'  => 'yes',
                'path'     => '/',
                'url'      => '',
            ),
        ),

        'columns' => array(
            'row_num'   => lang('cf:row_num'),
            'id'        => lang('cf:id'),
            'filename'  => lang('cf:filename'),
            'title'     => lang('cf:title'),
            'url_title' => '',
            'desc'      => lang('cf:desc'),
            'category'  => '',
            'cffield_1' => '',
            'cffield_2' => '',
            'cffield_3' => '',
            'cffield_4' => '',
            'cffield_5' => '',
        ),

        'columns_default' => array(
            'title'     => '',
            'url_title' => '',
            'desc'      => '',
            'category'  => '',
            'cffield_1' => '',
            'cffield_2' => '',
            'cffield_3' => '',
            'cffield_4' => '',
            'cffield_5' => '',
        ),
    ),

    //----------------------------------------
    // Location Specific Settings
    //----------------------------------------
    's3_regions' => array(
        'us-east-1'      => 'REGION_US_E1',
        'us-west-1'      => 'REGION_US_W1',
        'us-west-2'      => 'REGION_US_W2',
        'eu'             => 'REGION_EU_W1',
        'eu-central-1'   => 'REGION_EU_C1',
        'ap-southeast-1' => 'REGION_APAC_SE1',
        'ap-southeast-2' => 'REGION_APAC_SE2',
        'ap-northeast-1' => 'REGION_APAC_NE1',
        'sa-east-1'      => 'REGION_SA_E1',
    ),
    's3_endpoints' => array(
        'us-east-1'      => 's3-us-east-1.amazonaws.com',
        'us-west-1'      => 's3-us-west-2.amazonaws.com',
        'us-west-2'      => 's3-us-west-1.amazonaws.com',
        'eu'             => 's3-eu-west-1.amazonaws.com',
        'eu-central-1'   => 's3-eu-central-1.amazonaws.com',
        'ap-southeast-1' => 's3-ap-southeast-1.amazonaws.com',
        'ap-southeast-2' => 's3-ap-southeast-2.amazonaws.com',
        'ap-northeast-1' => 's3-ap-northeast-1.amazonaws.com',
        'sa-east-1'      => 's3-sa-east-1.amazonaws.com',
    ),
    's3_acl' => array(
        'private'            => 'ACL_PRIVATE',
        'public-read'        => 'ACL_PUBLIC',
        'authenticated-read' => 'ACL_AUTH_READ',
    ),
    's3_storage' => array(
        'standard' => 'STORAGE_STANDARD',
        'reduced'  => 'STORAGE_REDUCED',
    ),

    'cloudfiles_regions' => array(
        'us'  => 'US_AUTHURL',
        'uk'  => 'UK_AUTHURL',
    ),
);