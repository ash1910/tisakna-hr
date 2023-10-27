<?php
/*
==========================================================
	This software package is intended for use with 
	ExpressionEngine.	ExpressionEngine is Copyright © 
	2002-2015 EllisLab, Inc. 
	http://ellislab.com/
==========================================================
	THIS IS COPYRIGHTED SOFTWARE, All RIGHTS RESERVED.
	Written by: Justin Crawford, Travis Smith and Louis Dekeister
	Copyright (c) 2015 Hop Studios
	http://www.hopstudios.com/software/
--------------------------------------------------------
	Please do not distribute this software without written
	consent from the author.
==========================================================
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD."edit_this/config.php";

$plugin_info = array(
  'pi_name'			=> 'Edit This',
  'pi_version'		=> EDIT_THIS_VERSION,
  'pi_author' 		=> 'Travis Smith (Hop Studios)',
  'pi_author_url' 	=> 'http://www.hopstudios.com/software/',
  'pi_description' 	=> 'Adds an "edit this" link to various content',
  'pi_usage' 		=> Edit_this::usage()
);

/**
 * Edit_this Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Justin Crawford, Travis Smith and Louis Dekeister
 * @copyright		Copyright (c) 2015, Hopstudios
 * @link			http://www.hopstudios.com/software
 */
class Edit_this
{

	var $icon_path = '';
	var $session_required = FALSE;
	var $debug = FALSE;
	var $member = NULL;
	
	function __construct()
	{
		$this->icon_path = ee()->config->item('theme_folder_url') . 'user/edit_this/images/';
		$this->debug = (ee()->TMPL->fetch_param('debug') == 'true') ? TRUE : FALSE;
		$member_id = ee()->session->userdata('member_id');
		$this->member = ee('Model')->get('Member', $member_id)->with('MemberGroup')->first();
		
		$this->_debug("initializing");
	}

	/**
	 * create an href and wrap it around an image
	 * @param  string $url  [description]
	 * @param  string $alt  [description]
	 * @param  string $icon [description]
	 * @return [type]       [description]
	 */
	function _get_link($url = '', $alt = '', $icon = 'default') 
	{
		$this->icon_path .= (($icon == '') ? 'default' : $icon) . '_icon.gif';
		$this->_debug("creating a link with '$url'");
		return '<a href="' . $url . '" title="' . $alt . '" target="_blank"><img src="' . $this->icon_path . '" height="14" width="14" class="edit_this" style="padding-left: 4px;" alt="' . $alt . '" align="top" border="0" /></a>';
	}

	function _debug($msg)
	{
		if ($this->debug === TRUE)
		{
			// do something with debug msgs; this is rudimentary, but works.
			echo "edit_this -- " . $msg . "<br />";
		}
	}

	/**
	 * Generate and return a link or a url for an edit_this:entry tag
	 * @return [type] [description]
	 */
	function entry() 
	{
		$this->_debug("handling an entry");
		
		if ($this->member == NULL)
		{
			$this->_debug("unable to load logged in member");
			return;
		}
		
		$can_edit = FALSE;

		// there are three required tag parameters, all numeric
		if ((is_numeric($entry_id = (ee()->TMPL->fetch_param('entry_id')))) && is_numeric($channel_id = (ee()->TMPL->fetch_param('channel_id'))) && is_numeric($author_id = (ee()->TMPL->fetch_param('author_id'))))
		{

			$this->_debug("parameters OK");

			if ($this->member->MemberGroup->getId() == 1)
			{
				// If member is admin, he can do whatever he wants !
				$can_edit = TRUE;
			}
			else 
			{
				// check the cache for authz for this channel
				if (! isset(ee()->session->cache['edit_this']['channel:' . $channel_id]))
				{

					// authz: check that this person's group can edit this channel
					// $assigned_channels = FALSE if the group can't edit any channel
					
					$assigned_channels = $this->member->MemberGroup->AssignedChannels->pluck('channel_id');
					if (is_array($assigned_channels) && in_array($channel_id, $assigned_channels))
					{
						ee()->session->cache['edit_this']['channel:' . $channel_id] = TRUE;
						$can_edit = TRUE;
					}
					else
					{
						ee()->session->cache['edit_this']['channel:' . $channel_id] = FALSE;
					}
					$this->_debug("caching result of channel group authz for channel $channel_id");
					
				}
				
				// if we've cached affirmative authz for this channel...
				if (ee()->session->cache['edit_this']['channel:' . $channel_id] === TRUE)
				{
					$this->_debug("channel group check: TRUE");

					// Check if user can access CP
					if (ee('Permission')->has('can_access_cp'))
					{
						$this->_debug("control panel access ok");
						// Check if user can edit his entry or others entry
						if ($author_id != ee()->session->userdata('member_id'))
						{
							$can_edit = (ee('Permission')->has('can_edit_other_entries'));
							$this->_debug("non-author permission ok");
						}
						else
						{
							$can_edit = (ee('Permission')->has('can_edit_self_entries'));
							$this->_debug("author permission ok");
						}
					}
				}
			}
			
			// if we got through all the above an still $can_edit, then let's do it!
			if ($can_edit)
			{
				// generate the url
				// http://ee30.dev/system/index.php?/cp/publish/edit/entry/2&S=e693e0a104ef021ab16f26bed7531adf
				// This takes care of sessions
				$edit_entry_url = ee('CP/URL', 'publish/edit/entry/'.$entry_id)->compile();
				// Mix generated url + root admin url
				$admin_root_url = ee()->config->item('cp_url');
				$admin_root_url = str_replace('index.php', '', $admin_root_url);
				$url = $admin_root_url.$edit_entry_url;

				// if invoked with two-tag, handle contents
				if (ee()->TMPL->tagdata != '')
				{ 
					return str_replace('{edit_this_url}', $url, ee()->TMPL->tagdata);  
				}
				else 
				{
					$alt = "Edit this item";
					return $this->_get_link($url, $alt, ee()->TMPL->fetch_param('icon'));
				}
			}
		}
		return;
	}

	/**
	 * Generate and return a url or a link for an edit_this:template tag
	 * @return [type] [description]
	 */
	function template()
	{
		$this->_debug("handling a template");
		
		if ($this->member == NULL)
		{
			$this->_debug("unable to load logged in member");
			return;
		}
		
		$can_edit = FALSE;
		
		$template_id = (ee()->TMPL->fetch_param('template_id'));
		$template_group_id = ee()->TMPL->fetch_param('template_group_id');
		if (
			(!ee()->TMPL->fetch_param('template_group', FALSE) && !ee()->TMPL->fetch_param('template_group_id', FALSE))
			|| (!ee()->TMPL->fetch_param('template_group_id', FALSE) && ee()->TMPL->fetch_param('template_group', FALSE) == "")
			|| (!ee()->TMPL->fetch_param('template_group', FALSE) && !is_numeric($template_group_id))
			|| !is_numeric($template_id)
			)
		{
			return;
		}
		
		// Verify the template_group name
		if (!is_numeric($template_group_id))
		{
			$template_group_name = ee()->TMPL->fetch_param('template_group');
			$template_group = ee('Model')->get('TemplateGroup')->filter('group_name', '==', $template_group_name)->first();
			if ($template_group == null)
			{
				return;
			}
			$template_group_id = ($template_group->group_id);
		}

		$this->_debug("parameters OK");

		// verify that they either have an admin session, or don't need one

		if ($this->member->MemberGroup->getId() == 1)
		{
			$can_edit = TRUE;
		}
		else
		{
			// check the cache for a template group authz
			if (! isset(ee()->session->cache['edit_this']['template_group:' . $template_group_id]))
			{
				// Those are required if a user needs access to the template editor
				if (ee('Permission')->hasAll('can_access_cp', 'can_access_design', 'can_edit_templates'))
				{
					$this->_debug('access cp template OK');
					
					$assigned_template_groups = $this->member->MemberGroup->AssignedTemplateGroups->pluck('group_id');
					if (is_array($assigned_template_groups) && in_array($template_group_id, $assigned_template_groups))
					{
						$can_edit = TRUE;
						ee()->session->cache['edit_this']['template_group:' . $template_group_id] = TRUE;
					}
					else
					{
						ee()->session->cache['edit_this']['template_group:' . $template_group_id] = FALSE;
					}
					$this->_debug("caching result of template group authz for template group $template_group_id");
				}
				else
				{
					// User doesn't have cp access or template editor access
					return;
				}
			}
		}
	
		if ($can_edit)
		{
			// http://ee30.dev/system/index.php?/cp/design/template/edit/1&S=e693e0a104ef021ab16f26bed7531adf
			// This takes care of session id
			$edit_template_url = ee('CP/URL', 'design/template/edit/'.$template_id)->compile();
			// Mix generated url + root admin url
			$admin_root_url = ee()->config->item('cp_url');
			$admin_root_url = str_replace('index.php', '', $admin_root_url);
			$url = $admin_root_url.$edit_template_url;

			if (ee()->TMPL->tagdata != '')
			{ 
				return str_replace('{edit_this_url}', $url, ee()->TMPL->tagdata);  
			}
			else 
			{	
				$alt = "Edit this template";
				return $this->_get_link($url, $alt, ee()->TMPL->fetch_param('icon'));
			}
		}

		return;
	}

	/**
	 * Generate and return a url or a link for an edit_this:category tag
	 * @return [type] [description]
	 */
	function category()
	{
		$this->_debug("handling a category");
		
		if ($this->member == NULL)
		{
			$this->_debug("unable to load logged in member");
			return;
		}
		
		$can_edit = FALSE;

		if (is_numeric($category_id = (ee()->TMPL->fetch_param('category_id'))) && is_numeric($category_group_id = (ee()->TMPL->fetch_param('category_group'))))
		{
			$this->_debug("parameters OK");

			// verify that they either have an admin session, or don't need one
			if ($this->member->MemberGroup->getId() == 1)
			{
				$can_edit = TRUE;
			}
			else
			{
				// Check existing cache
				if (! isset(ee()->session->cache['edit_this']['category_group:' . $category_group_id]))
				{
					
					// they must have these perms to edit
					if(ee('Permission')->hasAll('can_access_cp', 'can_admin_channels', 'can_edit_categories'))
					{
						$this->_debug("control panel permission ok");
						// Check in CategoryGroup settings to see if member group can edit
						$category_group = ee('Model')->get('CategoryGroup')->filter('group_id', $category_group_id)->first();
						$authorized_member_groups = explode('|', $category_group->can_edit_categories);
						if (in_array($this->member->MemberGroup->getId(), $authorized_member_groups))
						{
							$can_edit = TRUE;
							ee()->session->cache['edit_this']['category_group:' . $category_group_id] = TRUE;
						}
						else
						{
							ee()->session->cache['edit_this']['category_group:' . $category_group_id] = FALSE;
						}
					}
					else
					{
						return;
					}
				}
				else
				{
					// Look into cache
					$can_edit = ee()->session->cache['edit_this']['category_group:' . $category_group_id];
				}
			}
		
			if ($can_edit)
			{
				// http://ee30.dev/system/index.php?/cp/channels/cat/edit-cat/1/1&S=e693e0a104ef021ab16f26bed7531adf
				// This takes care of session id
				$edit_cat_url = ee('CP/URL', 'channels/cat/edit-cat/'.$category_group_id.'/'.$category_id)->compile();
				// Mix generated url + root admin url
				$admin_root_url = ee()->config->item('cp_url');
				$admin_root_url = str_replace('index.php', '', $admin_root_url);
				$url = $admin_root_url.$edit_cat_url;

				if (ee()->TMPL->tagdata != '')
				{ 
					return str_replace('{edit_this_url}', $url, ee()->TMPL->tagdata);  
				}
				else 
				{	
					$alt = "Edit this category";
					return $this->_get_link($url, $alt, ee()->TMPL->fetch_param('icon'));
				}
			}
			return;
		}
	}

  // ----------------------------------------
  //  Plugin Usage
  // ----------------------------------------

  // This function describes how the plugin is used.
  //  Make sure and use output buffering

  public static function usage()
  {
  ob_start(); 
  ?>

This plugin will put small pencil icons in the live site, but only for users who are logged in, have an administrative session, and have permission to edit that particular resource.

All parameters are required, except "icon".

The simplest usage is simply this tag without any enclosed text. All of these will return an image and link as follows:
<a href="{edit_this_url}" title="Edit this" target="_blank"><img src="/themes/third_party/edit_this/images/default_icon.gif" height="14" width="14" class="edit_this" style="padding-left: 4px;" alt="Edit this" align="top" border="0" /></a>

You can over-ride styles on the image using the .edit_this class.

Entries:
{exp:edit_this:entry entry_id="{entry_id}" channel_id="{channel_id}" author_id="{author_id}"} 

Templates:
{exp:edit_this:template template_id="42" template_group="2"} 
(There's no way to dynamically insert the template_id or template_group, sadly.)

Categories:
{exp:edit_this:category category_id="{category_id}" category_group="{category_group}"}

If you wish to use a different icon image, several different colors are provided (in the /themes/third_party/edit_this/images directory).  You can even put your own image there, as long as the filename ends in “_icon.gif”.  Call alternate icons like so:
{exp:edit_this:template id="42" template_group="2" icon="blue"}

Advanced Use:
=============

You can use the same tag, but enclose some text within it.  The plugin will do the permission check, but leave the presentation of the link to you. This allows you to change the display to whatever you'd like, and you could have entire instructions or alternate content displayed by this plugin.

For example, you can point to a front-end editing template (stand alone editing form) using this option.  Or you could make the link be straight text, not an image.

{exp:edit_this:entry entry_id="{entry_id}" channel_id="{channel_id}" author_id="{author_id}"} 
<a href="{edit_this_url}">I can edit this</a>
{/exp:edit_this:entry}

{exp:edit_this:template template_id="42" template_group="2"} 
<a href="{edit_this_url}">Edit this template</a>
{/exp:edit_this:template}

{exp:edit_this:category category_id="{category_id}" category_group="{category_group}"}
<a href="{edit_this_url}" target="_blank">Click to modify category</a>
{/exp:edit_this:category}

Also, please note, you may need to set a cookie domain, or turn on cookies in the control panel, for edit_this to work.

Permissions Required

* For entries, users must be authorized to edit entries in the specific weblog; they must have control panel access; and they must have access to the edit tab.
* For templates, users must be authorized to edit templates in the specific template group; they must have control panel access; and they must be authorized to administer templates.
* For categories, users must have control panel access; they must be authorized to access the admin tab; and they must be authorized to administer weblogs.
* For all types of content, users must have an authenticated administrative session.

	<?php
  $buffer = ob_get_contents();
	
  ob_end_clean(); 

  return $buffer;
  }
  // END

}

/* End of file edit_this.php */
