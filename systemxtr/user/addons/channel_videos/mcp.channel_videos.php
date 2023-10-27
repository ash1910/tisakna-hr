<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Videos Module Control Panel Class
 *
 * @package         DevDemon_ChannelVideos
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/channel_videos/
 * @see             http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_videos_mcp
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        ee()->load->library('channel_videos_helper');

        // Some Globals
        $this->initGlobals();
    }

    // ********************************************************************************* //

    /**
     * MCP PAGE: Index
     *
     * @access public
     * @return string
     */
    public function index($players='')
    {
        if ($players != 'vimeo') $players = 'youtube';

        // Sidebar
        $this->sidebar = ee('CP/Sidebar')->make();
        $this->navVimeo = $this->sidebar->addHeader(lang('cv:player:vimeo'), ee('CP/URL', 'addons/settings/channel_videos/index/vimeo'));
        $this->navYoutube = $this->sidebar->addHeader(lang('cv:player:youtube'), ee('CP/URL', 'addons/settings/channel_videos/index/youtube'));
        $this->navDocs = $this->sidebar->addHeader(lang('cv:docs'), ee()->cp->masked_url('http://www.devdemon.com/channel_videos/docs/'))->urlIsExternal(true);


        if ($players == 'vimeo' ) {
            $this->navVimeo->isActive();
            $this->vData['players'] = $players;
            $this->vData['cp_page_title'] = lang('cv:player:vimeo');
            $this->vData['sections'] = array(
                array(
                    array(
                        'title' => lang('cv:vi:width'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][width]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['width']) == TRUE) ? $this->vData['vimeo']['width'] : 500),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:height'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][height]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['height']) == TRUE) ? $this->vData['vimeo']['height']: 281),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:title'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][title]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['title']) == TRUE) ? $this->vData['vimeo']['title'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:byline'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][byline]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['byline']) == TRUE) ? $this->vData['vimeo']['byline'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:portrait'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][portrait]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['portrait']) == TRUE) ? $this->vData['vimeo']['portrait'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:color'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][color]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['color']) == TRUE) ? $this->vData['vimeo']['color'] : '00adef'),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:autoplay'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][autoplay]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['autoplay']) == TRUE) ? $this->vData['vimeo']['autoplay']: 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:loop'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][loop]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['loop']) == TRUE) ? $this->vData['vimeo']['loop']: 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:api'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][api]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['api']) == TRUE) ? $this->vData['vimeo']['api'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:vi:player_id'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[vimeo][player_id]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['vimeo']['player_id']) == TRUE) ? $this->vData['vimeo']['player_id'] : ''),
                            ),
                        ),
                    ),
                ),
            );
        } elseif ($players=='youtube') {

            $this->vData['cp_page_title'] = lang('cv:player:youtube');
            $this->vData['players'] = $players;
            $this->vData['sections'] = array(
                array(
                    array(
                        'title' => lang('cv:yt:width'),
                        'desc' => '<small>('.lang('cv:flash').','.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][width]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['width']) == TRUE) ? $this->vData['youtube']['width'] : 560),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:height'),
                        'desc' => '<small>('.lang('cv:flash').' &amp; '.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][height]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['height']) == TRUE) ? $this->vData['youtube']['height']: 315),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:autohide'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][autohide]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['autohide']) == TRUE) ? $this->vData['youtube']['autohide'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:autoplay'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][autoplay]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['autoplay']) == TRUE) ? $this->vData['youtube']['autoplay'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:cc_load_policy'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][cc_load_policy]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['cc_load_policy']) == TRUE) ? $this->vData['youtube']['cc_load_policy'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:color'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][color]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['color']) == TRUE) ? $this->vData['youtube']['color'] : 'red'),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:controls'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][controls]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['controls']) == TRUE) ? $this->vData['youtube']['controls']: 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:disablekb'),
                        'desc' => '<small>('.lang('cv:flash').')</small>',
                        'fields' => array(
                            'players[youtube][disablekb]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['disablekb']) == TRUE) ? $this->vData['youtube']['disablekb']: 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:enablejsapi'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][enablejsapi]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['enablejsapi']) == TRUE) ? $this->vData['youtube']['enablejsapi'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:end'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][end]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['end']) == TRUE) ? $this->vData['youtube']['end'] : ''),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:iv_load_policy') ,
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][iv_load_policy]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['iv_load_policy']) == TRUE) ? $this->vData['youtube']['iv_load_policy'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:list'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][list]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['list']) == TRUE) ? $this->vData['youtube']['list'] : ''),
                            ),
                        ),
                    ),
                    array(
                        'title' => lang('cv:yt:listType'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][listType]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['listType']) == TRUE) ? $this->vData['youtube']['listType'] : 500),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:loop'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][loop]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['loop']) == TRUE) ? $this->vData['youtube']['loop'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:modestbranding'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][modestbranding]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['modestbranding']) == TRUE) ? $this->vData['youtube']['modestbranding'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:origin'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][origin]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['origin']) == TRUE) ? $this->vData['youtube']['origin'] : ''),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:playerapiid'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][playerapiid]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['playerapiid']) == TRUE) ? $this->vData['youtube']['playerapiid'] : ''),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:playlist'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][playlist]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['playlist']) == TRUE) ? $this->vData['youtube']['playlist'] : ''),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:rel'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][rel]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['rel']) == TRUE) ? $this->vData['youtube']['rel'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:showinfo'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][showinfo]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['showinfo']) == TRUE) ? $this->vData['youtube']['showinfo'] : 1),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:start'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][start]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['start']) == TRUE) ? $this->vData['youtube']['start'] : 0),
                            ),
                        ),
                    ),
                    array(
                        'title' =>lang('cv:yt:theme'),
                        'desc' => '<small>('.lang('cv:html5').')</small>',
                        'fields' => array(
                            'players[youtube][theme]'=> array(
                                'type' => 'text',
                                'value' => ((isset($this->vData['youtube']['theme']) == TRUE) ? $this->vData['youtube']['theme'] : 'dark'),
                            ),
                        ),
                    ),
                ),
            );
        }

        $this->vData['base_url'] =ee('CP/URL', 'addons/settings/channel_videos/update_players&players=' . $players) ;
      	$this->vData['save_btn_text'] = 'Save Player';
        $this->vData['save_btn_text_working'] = 'btn_saving';

        return array(
			'heading' => lang('cv:players'),
			'body' => ee('View')->make('channel_videos:mcp')->render($this->vData),
			'sidebar' => $this->sidebar,
			'breadcrumb' 	=> array(
				$this->baseUrl->compile() => lang('channel_videos')
			)
		);
    }

    // ********************************************************************************* //

    public function update_players()
    {
        $defaultSettings = ee('App')->get('channel_videos')->get('settings_module');

        // Grab Settings
        $query = ee()->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Channel_videos'");

        if ($query->row('settings') != false) {
            $settings = @unserialize($query->row('settings'));

            if (isset($settings['site:'.$this->site_id]) == false) {
                $settings['site:'.$this->site_id] = array(
                    'players' => array(
                        'vimeo' => $defaultSettings['vimeo'],
                        'youtube' => $defaultSettings['youtube'],
                    )
                );
            }
        }

        if (isset($_POST['players']['vimeo']) == true) {
            $settings['site:'.$this->site_id]['players']['vimeo'] = array_merge($defaultSettings['vimeo'], $_POST['players']['vimeo']);
        }

        if (isset($_POST['players']['youtube']) == true) {
            $settings['site:'.$this->site_id]['players']['youtube'] = array_merge($defaultSettings['youtube'], $_POST['players']['youtube']);
        }

        // Put it Back
        ee()->db->set('settings', serialize($settings));
        ee()->db->where('module_name', 'Channel_videos');
        $upd= ee()->db->update('exp_modules');

        ee('CP/Alert')->makeInline('shared-form')
            ->asSuccess()
            ->withTitle(lang('channel_videos_updated'))
            ->addToBody(sprintf(lang('channel_videos_updated_desc'), 'Player '.$upd))
            ->defer();

        ee()->functions->redirect(ee('CP/URL', 'addons/settings/channel_videos/index/'.ee()->input->get('players')));
    }

    // ********************************************************************************* //

    private function initGlobals()
    {
        // Some Globals
        $this->baseUrl = ee('CP/URL', 'addons/settings/channel_videos');
        $this->site_id = ee()->config->item('site_id');
        $this->vData['baseUrl'] = $this->baseUrl->compile();
        $this->base = $this->baseUrl;
        $this->base_short = $this->baseUrl;


        ee()->view->cp_page_title = ee()->lang->line('channel_videos_module_name');

        ee()->channel_videos_helper->addMcpAssets('gjs');
        ee()->channel_videos_helper->addMcpAssets('css', 'css/mcp.css?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'mcp');

        if (ee()->config->item('channel_videos_debug') == 'yes') {
             ee()->channel_videos_helper->addMcpAssets('js', 'js/mcp.js?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'mcp');
        } else {
             ee()->channel_videos_helper->addMcpAssets('js', 'js/mcp.min.js?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'mcp');
        }
        /* Cargar Configuracion */
        $query = ee()->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Channel_videos'");
        if ($query->row('settings') != FALSE)
        {
            $settings = @unserialize($query->row('settings'));

            if (isset($settings['site:'.$this->site_id]) == FALSE)
            {
                $settings['site:'.$this->site_id] = array();
            }
        }

        if (isset($settings['site:'.$this->site_id]['players']) == FALSE )
             $settings['site:'.$this->site_id]['players'] = array();


        $this->vData = array_merge($this->vData, $settings['site:'.$this->site_id]['players']);

        ee()->view->header = array(
			'title' => lang('channel_videos'),
		);
    }

    // ********************************************************************************* //

    public function ajax_router()
    {

        // -----------------------------------------
        // Ajax Request?
        // -----------------------------------------
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            // Load Library
            if (class_exists('Channel_Videos_AJAX') != TRUE) include 'ajax.channel_videos.php';

            $AJAX = new Channel_Videos_AJAX();

            // Shoot the requested method
            $method = ee()->input->get_post('ajax_method');
            echo $AJAX->$method();
            exit();
        }
    }

    // ********************************************************************************* //

} // END CLASS

/* End of file mcp.shop.php */
/* Location: ./system/expressionengine/third_party/points/mcp.shop.php */
