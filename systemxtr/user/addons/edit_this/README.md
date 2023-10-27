# Edit This

Edit This lets you link viewers of your site to specific pages in the control panel.  Using it you can:

- Add a link inside an entry to edit that entry;
- Add a link at the top of a page to edit that page’s template;
- Add a link next to a category name to edit that category;
- Add a link in a photo gallery entry to edit that entry.

The link will only appear if the viewer is logged in and has sufficient privileges to edit the targeted content.

## Usage

This plugin will put small pencil icons in the live site, but only for users who are: logged in; have an administrative session; and have permission to edit that particular resource.

All parameters are required, except “icon”.

### `{exp:edit_this:entry}`

#### Example

```
{exp:edit_this:entry entry_id="{entry_id}" channel_id="{channel_id}" author_id="{author_id}"}  
```

#### Parameters

- `entry_id="{entry_id}"`  THIS IS A REQUIRED ATTRIBUTE
- `channel_id="{channel_id}"`  THIS IS A REQUIRED ATTRIBUTE
- `author_id="{author_id}"` THIS IS A REQUIRED ATTRIBUTE

### `{exp:edit_this:template}`

#### Example

```
{exp:edit_this:template template_id="{template_id}" template_group="{template_group}"}  
```

#### Parameters

- `template_id="{template_id}"` THIS IS A REQUIRED ATTRIBUTE
- `template_group="{template_group}"` One of `template_group` and `template_group_id` is required. `template_group` is expecting a template group name.
- `template_group_id="2"` One of `template_group` and `template_group_id` is required. `template_group_id` is expecting a template group id. We suggest you use `template_group="{template_group}"`, it will avoid you to hard-code the template group id.

### `{exp:edit_this:category}`

#### Example

```
{exp:edit_this:category category_id="{category_id}" category_group="{category_group}"}
```

#### Parameters

- `category_id="{category_id}"` THIS IS A REQUIRED ATTRIBUTE
- `category_group="{category_group}"` THIS IS A REQUIRED ATTRIBUTE

### Tag Pairs

You can use the same tag, but enclose some text within it. The plugin will do the permission check, but leave the presentation of the link to you. This allows you to change the display to whatever you’d like, and you could have entire instructions or alternate content displayed by this plugin.

For example, you can point to a front-end editing template (stand alone editing form) using this option.  Or you could make the link be straight text, not an image.

#### Examples

```
{exp:edit_this:entry entry_id="{entry_id}" channel_id="{channel_id}" author_id="{author_id}"}
<a href="{edit_this_url}">I can edit this</a>
{/exp:edit_this:entry}

{exp:edit_this:template template_id="42" template_group="2"}
<a href="{edit_this_url}">I can edit this</a>
{/exp:edit_this:template}

{exp:edit_this:category category_id="{category_id}" category_group="{category_group}"}
<a href="{edit_this_url}">I can edit this</a>
{/exp:edit_this:category}
```


## Support

Having issues ? Found a bug ? Suggestions ? Contact us at [tech@hopstudios.com](mailto:tech@hopstudios.com)


## Changelog

### 2.4

Fix issue with EE 2.9

### 2.3

Generates new control panel URLs for EE 2.8, and yet is also backwards compatible with EE 2.7 and lower.

### 2.2

Correctly deals with fingerprints instead of session IDs

### 2.1

Added an “edit_this” class to the icon for easier styling

### 2.0.1

Switch to using the third_party themes directory

### 2.0.0

Initial release for EE 2.x

### 1.1.1

Fixed a bug causing newlines and blank pages

### 1.1.0

Substantially modified/improved authz caching; added support for session IDs; modified tags to require more (and different) parameters.

### 1.0.1

Added session caching on security queries, and colored icons!

### 1.0.0

Initial Release



## Licence

Updated: Jan. 6, 2009

####Permitted Use

One license grants the right to perform one installation of the Software. Each additional installation of the Software requires an additional purchased license. For free Software, no purchase is necessary, but this license still applies.

####Restrictions

Unless you have been granted prior, written consent from Hop Studios, you may not:

* Reproduce, distribute, or transfer the Software, or portions thereof, to any third party.
* Sell, rent, lease, assign, or sublet the Software or portions thereof.
* Grant rights to any other person.
* Use the Software in violation of any U.S. or international law or regulation.

####Display of Copyright Notices

All copyright and proprietary notices and logos in the Control Panel and within the Software files must remain intact.
Making Copies

You may make copies of the Software for back-up purposes, provided that you reproduce the Software in its original form and with all proprietary notices on the back-up copy.

####Software Modification

You may alter, modify, or extend the Software for your own use, or commission a third-party to perform modifications for you, but you may not resell, redistribute or transfer the modified or derivative version without prior written consent from Hop Studios. Components from the Software may not be extracted and used in other programs without prior written consent from Hop Studios.

####Technical Support

Technical support is available through e-mail, at sales@hopstudios.com. Hop Studios does not provide direct phone support. No representations or guarantees are made regarding the response time in which support questions are answered.
Refunds

Hop Studios offers refunds on software within 30 days of purchase. Contact sales@hopstudios.com for assistance. This does not apply if the Software is free.
Indemnity

You agree to indemnify and hold harmless Hop Studios for any third-party claims, actions or suits, as well as any related expenses, liabilities, damages, settlements or fees arising from your use or misuse of the Software, or a violation of any terms of this license.

####Disclaimer Of Warranty

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, WARRANTIES OF QUALITY, PERFORMANCE, NON-INFRINGEMENT, MERCHANTABILITY, OR FITNESS FOR A PARTICULAR PURPOSE. FURTHER, HOP STUDIOS DOES NOT WARRANT THAT THE SOFTWARE OR ANY RELATED SERVICE WILL ALWAYS BE AVAILABLE.
Limitations Of Liability

YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS OF THE SOFTWARE BE LIABLE FOR CLAIMS, DAMAGES OR OTHER LIABILITY ARISING FROM, OUT OF, OR IN CONNECTION WITH THE SOFTWARE. LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.
