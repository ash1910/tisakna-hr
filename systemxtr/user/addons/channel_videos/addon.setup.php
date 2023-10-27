<?php

if ( ! defined('CHANNEL_VIDEOS_NAME')) {
    define('CHANNEL_VIDEOS_NAME',         'Channel Videos');
    define('CHANNEL_VIDEOS_CLASS_NAME',   'channel_videos');
    define('CHANNEL_VIDEOS_VERSION',      '3.2.1');
}

return array(
	'author'      => 'DevDemon',
	'author_url'  => 'https://devdemon.com/',
	'name'        => CHANNEL_VIDEOS_NAME,
	'description' => 'Add videos from Youtube/Vimeo to Channel Entries',
	'version'     => CHANNEL_VIDEOS_VERSION,
	'namespace'   => 'DevDemon\ChannelVideos',
	'settings_exist' => true,

    'settings_module' => array(
        'youtube' => array(
            'width' => 560,
            'height' => 315,
            'autohide' => 1,
            'autoplay' => 0,
            'cc_load_policy' => 0,
            'color' => 'red',
            'controls' => 1,
            'disablekb' => 0,
            'enablejsapi' => 0,
            'end' => '',
            'fs' => 1,
            'iv_load_policy' => 1,
            'list' => '',
            'listType' => '',
            'loop' => 0,
            'modestbranding' => 0,
            'origin' => '',
            'playerapiid' => '',
            'playlist' => '',
            'rel' => 1,
            'showinfo' => 1,
            'start' => 0,
            'theme' => 'dark',
        ),
        'vimeo' => array(
            'width' => 500,
            'height' => 281,
            'title' => 1,
            'byline' => 1,
            'portrait' => 1,
            'color' => '00adef',
            'autoplay' => 0,
            'loop' => 0,
            'api' => 0,
            'player_id' =>'',
        )
    )
);

