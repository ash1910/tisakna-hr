<?php

namespace DevDemon\ChannelFiles\Service;

class FieldtypeSettingsHelper
{
    protected static $settings;
    protected static $module;

    public static function showSettings($field_id, $settings)
    {
        self::$settings = $settings;
        self::$module = ee('App')->get('channel_files');

        $sections = array();

        $sections['field_options_cf_locations'] = array(
            'label'    => 'cf:loc_settings',
            'group'    => 'channel_files',
            'settings' => self::settingsLocation(),
        );

        $sections['field_options_cf_advsettings'] = array(
            'label'    => 'cf:adv_settings',
            'group'    => 'channel_files',
            'settings' => self::settingsAdvanced(),
        );

        $sections['field_options_cf_columns'] = array(
            'label'    => 'cf:field_columns',
            'group'    => 'channel_files',
            'settings' => self::settingsColumns(),
        );



        return $sections;
    }

    public static function saveSettings($field_id, $data)
    {
        $post = ee('Request')->post('channel_files');

        // -----------------------------------------
        // Parse categories
        // -----------------------------------------
        if (isset($post['categories']) && $post['categories']) {
            $categories = array();

            foreach (explode(',', $post['categories']) as $cat) {
                $cat = trim ($cat);
                if ($cat != false) $categories[] = $cat;
            }

            $post['categories'] = $categories;
        } else {
            $post['categories'] = array();
        }

        // Make sure the import path has a slash at the end
        if (substr($post['locations']['ftp']['path'], -1) != '/') $post['locations']['ftp']['path'] .= '/';
        if (substr($post['locations']['sftp']['path'], -1) != '/') $post['locations']['sftp']['path'] .= '/';
        if (substr($post['import_path'], -1) != '/') $post['import_path'] .= '/';


        $post['field_wide'] = true;
        return $post;
    }

    protected static function settingsLocation()
    {
        $fields = array();

        // -----------------------------------------
        // File Upload Destinations
        // -----------------------------------------
        $locations = array();
        $dbLocs = ee('Model')->get('UploadDestination')->filter('site_id', ee()->config->item('site_id'))->order('name', 'asc')->all();
        $locations = $dbLocs->getDictionary('id', 'name');

        // -----------------------------------------
        // S3, Dropdows
        // -----------------------------------------
        $s3Regions = array();
        foreach (self::$module->get('s3_regions') as $key => $val) {
            $s3Regions[$key] = lang('cf:s3:region:'.$key);
        }

        $s3Acl = array();
        foreach (self::$module->get('s3_acl') as $key => $val) {
            $s3Acl[$key] = lang('cf:s3:acl:'.$key);
        }

        $s3Storage = array();
        foreach (self::$module->get('s3_storage') as $key => $val) {
            $s3Storage[$key] = lang('cf:s3:storage:'.$key);
        }

        $cloudFilesRegions = array();
        foreach (self::$module->get('cloudfiles_regions') as $key => $val) {
            $cloudFilesRegions[$key] = lang('cf:cloudfiles:region:'.$key);
        }

        $actionUrl = ee('channel_files:Helper')->getRouterUrl();

        // ACTION URL
        $fields[] = array(
            'title' => lang('cf:act_url'),
            'desc'  => lang('cf:act_url:exp'),
            'fields' => array(
                'channel_files[efef]' => array(
                    'type' => 'html',
                    'content' => '<a href="'.$actionUrl.'" target="_blank">'.$actionUrl.'</a>'
                ),
            )
        );

        // Upload Location
        $fields[] = array(
            'title' => lang('cf:upload_location'),
            'attrs'  => array('class' => 'cf-upload_toggle'),
            'fields' => array(
                'channel_files[upload_location]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'local'      => lang('cf:local'),
                        's3'         => lang('cf:s3'),
                        'cloudfiles' => lang('cf:cloudfiles'),
                        'ftp'        => lang('cf:ftp'),
                        'sftp'       => lang('cf:sftp'),
                    ),
                    'value' => self::$settings['upload_location'],
                ),
            )
        );

        // -----------------------------------------
        // Local
        // -----------------------------------------
        $fields[] = array(
            'title' => lang('cf:local'),
            'attrs'  => array('class' => 'cf_location-local'),
            'fields' => array(
                'channel_files[locations][local][location]' => array(
                    'type'    => 'select',
                    'choices' => $locations,
                    'value'   => self::$settings['locations']['local']['location'],
                ),
            )
        );

        // -----------------------------------------
        // S3
        // -----------------------------------------
        $fields[] = array(
            'title' => lang('cf:s3:key'),
            'desc' => lang('cf:s3:key_exp'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][key]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['s3']['key'],
                ),
            )
        );

        $fields[] = array(
            'title' => lang('cf:s3:secret_key'),
            'desc' => lang('cf:s3:secret_key_exp'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][secret_key]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['s3']['secret_key'],
                ),
            )
        );

        $fields[] = array(
            'title' => lang('cf:s3:bucket'),
            'desc' => lang('cf:s3:bucket_exp'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][bucket]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['s3']['bucket'],
                ),
            )
        );

        // S3: Region
        $fields[] = array(
            'title' => lang('cf:s3:region'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][region]' => array(
                    'type'    => 'select',
                    'choices' => $s3Regions,
                    'value'   => self::$settings['locations']['s3']['region'],
                ),
            )
        );

        // S3: ACL
        $fields[] = array(
            'title' => lang('cf:s3:acl'),
            'desc' => lang('cf:s3:acl_exp'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][acl]' => array(
                    'type'    => 'select',
                    'choices' => $s3Acl,
                    'value'   => self::$settings['locations']['s3']['acl'],
                ),
            )
        );

        // S3: Storage
        $fields[] = array(
            'title' => lang('cf:s3:storage'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][storage]' => array(
                    'type'    => 'select',
                    'choices' => $s3Storage,
                    'value'   => self::$settings['locations']['s3']['storage'],
                ),
            )
        );

        $fields[] = array(
            'title' => lang('cf:s3:force_download'),
            'desc'  => lang('cf:s3:force_download_exp'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][force_download]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['locations']['s3']['force_download'],
                ),
            ),
        );

        // S3: Directory
        $fields[] = array(
            'title'  => lang('cf:s3:directory'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][directory]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['s3']['directory'],
                ),
            )
        );

        // S3: CloudFront Domain
        $fields[] = array(
            'title'  => lang('cf:s3:cloudfrontd'),
            'attrs'  => array('class' => 'cf_location-s3'),
            'fields' => array(
                'channel_files[locations][s3][cloudfront_domain]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['s3']['cloudfront_domain'],
                ),
            )
        );

        // -----------------------------------------
        // Rackspace
        // -----------------------------------------
        // CloudFiles: Username
        $fields[] = array(
            'title' => lang('cf:cloudfiles:username'),
            'attrs'  => array('class' => 'cf_location-cloudfiles'),
            'fields' => array(
                'channel_files[locations][cloudfiles][username]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['cloudfiles']['username'],
                ),
            ),
        );

        // CloudFiles: API KEY
        $fields[] = array(
            'title' => lang('cf:cloudfiles:api'),
            'attrs'  => array('class' => 'cf_location-cloudfiles'),
            'fields' => array(
                'channel_files[locations][cloudfiles][api]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['cloudfiles']['api'],
                ),
            ),
        );

        // CloudFiles: Container
        $fields[] = array(
            'title' => lang('cf:cloudfiles:container'),
            'attrs'  => array('class' => 'cf_location-cloudfiles'),
            'fields' => array(
                'channel_files[locations][cloudfiles][container]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['cloudfiles']['container'],
                ),
            ),
        );

        // CloudFiles: Region
        $fields[] = array(
            'title'  => lang('cf:cloudfiles:region'),
            'attrs'  => array('class' => 'cf_location-cloudfiles'),
            'fields' => array(
                'channel_files[locations][cloudfiles][region]' => array(
                    'type'    => 'select',
                    'choices' => $cloudFilesRegions,
                    'value'   => self::$settings['locations']['cloudfiles']['region'],
                ),
            ),
        );

        // CloudFiles: Region
        $fields[] = array(
            'title'  => lang('cf:cloudfiles:cdn_uri'),
            'attrs'  => array('class' => 'cf_location-cloudfiles'),
            'fields' => array(
                'channel_files[locations][cloudfiles][cdn_uri]' => array(
                    'type'    => 'text',
                    'value'   => self::$settings['locations']['cloudfiles']['cdn_uri'],
                ),
            ),
        );

        // FTP:  Hostname IP
        $fields[] = array(
            'title' => lang('cf:host_ip'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][hostname]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['hostname'],
                )
            )
        );

        // FTP: Port
        $fields[] = array(
            'title' => lang('cf:port'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][port]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['port'],
                )
            )
        );

        // FTP: Username
        $fields[] = array(
            'title' => lang('cf:username'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][username]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['username'],
                )
            )
        );

        // FTP: Password
        $fields[] = array(
            'title' => lang('cf:password'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][password]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['password'],
                )
            )
        );

        // FTP: Path
        $fields[] = array(
            'title' => lang('cf:path'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][path]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['path'],
                )
            )
        );

        // FTP: URL
        $fields[] = array(
            'title' => lang('cf:url'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][url]' => array(
                    'type' => 'text',
                    'value' => self::$settings['locations']['ftp']['url'],
                )
            )
        );

        // FTP: Passive
        $fields[] = array(
            'title' => lang('cf:ftp:passive'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][passive]' => array(
                    'type'    => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('yes'),
                        'no'  => lang('no'),
                    ),
                    'value'   => self::$settings['locations']['ftp']['passive'],
                )
            )
        );

        // FTP: SSL
        $fields[] = array(
            'title' => lang('cf:ftp:ssl'),
            'attrs'  => array('class' => 'cf_location-ftp'),
            'fields' => array(
                'channel_files[locations][ftp][ssl]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('yes'),
                        'no'  => lang('no'),
                    ),
                    'value' => self::$settings['locations']['ftp']['ssl'],
                )
            )
        );

        // SFTP: HOST IP
        $fields[] = array(
            'title' => lang('cf:host_ip'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][hostname]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['sftp']['hostname'],
                )
            )
        );

        // SFTP: Port
        $fields[] = array(
            'title' => lang('cf:port'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][port]' => array(
                    'type'  => 'text',
                    'value' =>  self::$settings['locations']['sftp']['port'],
                )
            )
        );

        // SFTP: Username
        $fields[] = array(
            'title' => lang('cf:username'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][username]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['sftp']['username'],
                )
            )
        );

        // SFTP: Password
        $fields[] = array(
            'title' => lang('cf:password'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][password]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['sftp']['password'],
                )
            )
        );

        // SFTP: Path
        $fields[] = array(
            'title' => lang('cf:path'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][path]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['sftp']['path'],
                )
            )
        );

        // SFTP: URL
        $fields[] = array(
            'title' => lang('cf:url'),
            'attrs'  => array('class' => 'cf_location-sftp'),
            'fields' => array(
                'channel_files[locations][sftp][url]' => array(
                    'type'  => 'text',
                    'value' => self::$settings['locations']['sftp']['url'],
                )
            )
        );

        // -----------------------------------------
        // Test Location
        // -----------------------------------------
        $fields[] = array(
            'wide'   => true,
            'fields' => array(
                'test' => array(
                    'type'    => 'html',
                    'content' => '
                        <a href="#" class="cf-test_location">' . lang('cf:test_location') . '</a>
                        <div class="modal-wrap modal-cf_test_location hidden">
                            <div class="modal" style="padding-top:10px">
                                <div class="col-group">
                                    <div class="col w-16">
                                        <div class="box">
                                            <h1>' . lang('cf:test_location') . ' <a class="m-close" href="#"></a></h1>
                                            <div class="ajax_results" style="min-height:260px; padding:20px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ',
                ),
            ),
        );

        return $fields;
    }

    public static function settingsAdvanced()
    {
        $fields = array();

        $fields[] = array(
            'title' => lang('cf:categories'),
            'desc' => lang('cf:categories_explain'),
            'fields' => array(
                'channel_files[categories]' => array(
                    'type' => 'text',
                    'value' => implode(',', self::$settings['categories']),
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:show_stored_files'),
            'fields' => array(
                'channel_files[show_stored_files]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['show_stored_files'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:limt_stored_files_author'),
            'desc' => lang('cf:limt_stored_files_author_exp'),
            'fields' => array(
                'channel_files[stored_files_by_author]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['stored_files_by_author'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:stored_files_search_type'),
            'fields' => array(
                'channel_files[stored_files_search_type]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'entry' =>lang('cf:entry_based'),
                        'file' => lang('cf:file_based')
                    ),
                    'value' => self::$settings['stored_files_search_type'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:show_import_files'),
            'desc' => lang('cf:show_import_files_exp'),
            'fields' => array(
                'channel_files[show_import_files]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['show_import_files'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:import_path'),
            'desc' => lang('cf:import_path_exp'),
            'fields' => array(
                'channel_files[import_path]' => array(
                    'type' => 'text',
                    'value' => self::$settings['import_path'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:file_limit'),
            'desc' => lang('cf:file_limit_exp'),
            'fields' => array(
                'channel_files[file_limit]' => array(
                    'type' => 'text',
                    'value' => self::$settings['file_limit'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:file_extensions'),
            'desc' => lang('cf:file_extensions_exp'),
            'fields' => array(
                'channel_files[file_extensions]' => array(
                    'type' => 'text',
                    'value' => self::$settings['file_extensions'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:store_entry_id_folder'),
            'desc' => lang('cf:store_entry_id_folder_exp'),
            'fields' => array(
                'channel_files[entry_id_folder]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['entry_id_folder'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:show_download_btn'),
            //'desc' => 'Camopo 1.11',
            'fields' => array(
                'channel_files[show_download_btn]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['show_download_btn'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:show_file_replace'),
            'fields' => array(
                'channel_files[show_file_replace]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['show_file_replace'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:hybrid_upload'),
            'desc' => lang('cf:hybrid_upload_exp'),
            'fields' => array(
                'channel_files[hybrid_upload]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['hybrid_upload'],
                )
            )
        );

        $fields[] = array(
            'title' => lang('cf:locked_url_fieldtype'),
            'desc' => lang('cf:locked_url_fieldtype_exp'),
            'fields' => array(
                'channel_files[locked_url_fieldtype]' => array(
                    'type' => 'inline_radio',
                    'choices' => array(
                        'yes' => lang('cf:yes'),
                        'no' => lang('cf:no')
                    ),
                    'value' => self::$settings['locked_url_fieldtype'],
                )
            )
        );

        return $fields;
    }

    public static function settingsColumns()
    {
        $fields = array();

        // Make sure all fields have their lang values
        foreach (self::$settings['columns'] as $key => &$val) {
            if (substr($val, 0, 3) == 'cf:') {
                $val = lang($val);
            }
        }

        $fields[] = array(
            'wide' => true,
            'fields' => array(
                'exp' => array(
                    'type' => 'html',
                    'content' => '<small style="display:block; margin:0 0 10px">' . lang('cf:field_columns_exp') . '</small>',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:row_num'),
            'fields' => array(
                'channel_files[columns][row_num]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['row_num'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:id'),
            'fields' => array(
                'channel_files[columns][id]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['id'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:filename'),
            'fields' => array(
                'channel_files[columns][filename]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['filename'],
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:title'),
            'fields' => array(
                'channel_files[columns][title]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['title'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][title]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['title'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:url_title'),
            'fields' => array(
                'channel_files[columns][url_title]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['url_title'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][url_title]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['url_title'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:desc'),
            'fields' => array(
                'channel_files[columns][desc]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['desc'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][desc]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['desc'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:category'),
            'fields' => array(
                'channel_files[columns][category]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['category'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][category]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['category'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:cffield_1'),
            'fields' => array(
                'channel_files[columns][cffield_1]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['cffield_1'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][cffield_1]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['cffield_1'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:cffield_2'),
            'fields' => array(
                'channel_files[columns][cffield_2]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['cffield_2'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][cffield_2]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['cffield_2'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:cffield_3'),
            'fields' => array(
                'channel_files[columns][cffield_3]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['cffield_3'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][cffield_3]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['cffield_3'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:cffield_4'),
            'fields' => array(
                'channel_files[columns][cffield_4]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['cffield_4'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][cffield_4]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['cffield_4'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        $fields[] = array(
            'title' => lang('cf:cffield_5'),
            'fields' => array(
                'channel_files[columns][cffield_5]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns']['cffield_5'],
                    'attrs' => ' style="width:48%" ',
                ),
                'channel_files[columns_default][cffield_5]' => array(
                    'type' => 'text',
                    'value' => self::$settings['columns_default']['cffield_5'],
                    'attrs' => ' style="width:48%; float:right" ',
                ),
            ),
        );

        return $fields;
    }
}

/* End of file FieldtypeSettingsHelper.php */
/* Location: ./system/user/addons/channel_files/Service/FieldtypeSettingsHelper.php */