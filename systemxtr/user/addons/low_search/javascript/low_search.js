/**
 * Low Search JS file
 *
 * @package        low_search
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2017, Low
 */

// Make sure LOW namespace is valid
if (typeof LOW == 'undefined') var LOW = new Object;

(function($){

// --------------------------------------
// Language lines
// --------------------------------------

var lang = function(str) {
	return (typeof EE.LOW_lang[str] == 'undefined') ? str : EE.LOW_lang[str];
}

// --------------------------------------
// Create Low Index object
// --------------------------------------

LOW.Index = function(cell) {

	// Reference to this object instance
	var self = this;

	// Get jQuery objects for this object
	var $cell = $(cell),
		$link = $cell.find('a'),
		$bar  = $cell.find('.toolbar'),
		$pb   = $('<div class="progress-bar"/>'),
		$prog = $('<div class="progress" style="width:0"/>').appendTo($pb);

	// Private vars, based on <td>'s data attrs
	var _colId    = $cell.data('collection'),
		_total    = $cell.data('total'),
		_lexicon  = $cell.data('lexicon');

	// For ajax calls, define url and vars to send
	var	url = location.href.replace('low_search/collections', 'low_search/build');

	// Variables to send along with Ajax call
	var vars = {
		CSRF_TOKEN: EE.CSRF_TOKEN,
		collection_id: _colId
	};

	// Initiate oncomplete event
	this.oncomplete = function(){};

	// Reset variables
	var reset = function() {
		vars.start = 0;
		setProg(0, 0);
	};

	// Update the progress bar value and text, ie. 50 / 1000
	var setProg = function(value, text) {
		var w = value + '%';
		text = text + ' / ' + _total;
		$prog.attr('value', value).css('width', w); //.text(text);
	};

	// Function to fire when a link is clicked
	var click = function(event) {
		// Prevent the default
		event.preventDefault();
		// What are we building?
		self.setType($(this).data('build'));
		// And build it
		self.build(event.altKey);
	};

	// Set the build type
	this.setType = function(type) {
		vars.build = type;
	};

	// Callable build function to trigger the build
	this.build = function(rebuild) {
		// Don't build if we're building a lexicon, but don't have one
		if (vars.build != 'index' && !_lexicon) return self.oncomplete();

		// Reset counter
		reset();

		// Remove toolbar and add progress bar
		$bar.hide();
		$cell.append($pb);

		// Call function
		buildBatch(rebuild);
	};

	// Perform Ajax Call for this batch
	var buildBatch = function(rebuild) {
		// Set rebuild var accordingly
		vars.rebuild = rebuild ? 'yes' : false;
		// Data to post
		$.post(url, vars, respond, 'text').fail(respond);
	};

	// Handle Ajax response from the server
	var respond = function(data, status, xhr) {

		// String responses
		if (typeof data == 'string') {

			// Only 'true' or '[digit]' are valid
			return (data.match(/^(true|\d+)$/))
				? update($.parseJSON(data), xhr)
				: showError(data, xhr);

		}

		// If data is an object, it's actually an xhr
		if (typeof data == 'object') {

			var response = data.responseText;

			if (response.match(/^\{/)) {
				response = $.parseJSON(response);
				response = response.error || response.toString();
			}

			return showError(response, data);

		}

		alert('Well, this is awkward.');

	};

	// Function to execute after each Ajax call
	var update = function(start, xhr) {
		var val, text, done;

		if (start === true) {
			done = true;
			val  = 100;
			text = _total;
		} else {
			done = false;
			val  = (start / _total * 100);
			text = start;
		}

		// Update progress bar with new info
		setProg(val, text);

		if (done) {

			// Get a glimpse of the finished progress bar
			setTimeout(function(){
				$pb.remove();
				$bar.show();
			}, 500);

			// And trigger oncomplete
			self.oncomplete();

		} else {

			// Set new csrf_token
			var token = xhr.getResponseHeader('X-CSRF-TOKEN') || null;
			if (token) vars.CSRF_TOKEN = token;

			// Set new start value
			vars.start = start;

			// And build the next batch
			buildBatch(false);
		}

	};

	// Show error message when building borks
	var showError = function(response, xhr) {

		// Basic text
		$cell.text('An error occurred building the index.');

		//console.log(response);

		// Add response-link?
		if (response) {
			// Div for dialog
			var $response = $('<div/>').html(response);

			// Add span to view response in an alert
			var $view = $('<span/>').text('View response').on('click', function(){
				$response.dialog({
					modal: true,
					title: xhr.status+': '+xhr.statusText,
					width: '50%'
				});
			}).css('cursor','pointer');

			$cell.append($view);
		}
	};

	$cell.find('a').on('click', click);

	return this;
};

// --------------------------------------
// Controller for collections/indexes
// --------------------------------------

LOW.Collections = function() {

	var index = [];

	$('td.low-index').each(function(){
		index.push(new LOW.Index(this));
	});

	$('.low-build-all a').click(function(event){
		event.preventDefault();
		var $cell = $(this).parent(),
			build = $(this).data('build');
		$cell.text(lang('working'));
		$(index).each(function(i){
			var next = index[i + 1];
			index[i].oncomplete = function(){
				if (next) {
					next.setType(build);
					next.build();
				} else {
					$cell.text(lang('done'));
				}
			};
		});
		index[0].setType(build);
		index[0].build();
	});
};

$(LOW.Collections);

// --------------------------------------
// Collection Settings
// --------------------------------------

LOW.CollectionSettings = function() {

	// pre-fill channel Title and Name
	var id = $('input[name="collection_id"]').get(0),
		$select = $('select[name="channel_id"]');

	$select.on('change', function(){
		var val = $(this).val();
		if (val && id && id.value == 'new') {
			var channel = EE.low_search_channels[val];
			$('input[name="collection_label"]').val(channel.channel_title);
			$('input[name="collection_name"]').val(channel.channel_name);
		}
	});

};

$(LOW.CollectionSettings);

// ------------------------------------------
// Sortable shortcuts
// ------------------------------------------

LOW.Sortcuts = function(){
	var $table = $('.low-shortcuts');

	if ( ! $table.length) return;

	var url = $table.data('order-url'),
		groupId = $table.data('group-id');

	var getOrder = function() {
		var order = [];
		$table.find('td:nth-child(2)').each(function(){
			order.push(this.innerText);
		});
		return order;
	};

	var updateOrder = function() {
		$.post(url, {
			CSRF_TOKEN: EE.CSRF_TOKEN,
			order: getOrder(),
			group_id: groupId
		});
	};

	$table.eeTableReorder({
		afterSort: updateOrder
	});
};

$(LOW.Sortcuts);

// ------------------------------------------
// Shortcut Parameters
// ------------------------------------------

LOW.Params = function(){
	var $el   = $('#parameters'),
		$tmpl = $el.find('div'),
		$add  = $el.find('.add'),
		params = $el.data('params');

	var addFilter = function(event, key, val) {
		// Clone the filter template and remove the id
		var $newFilter = $tmpl.clone().hide();

		// If a key is given, set it
		if (key) $newFilter.find('.param-key').val(key);

		// If a val is given, set it
		if (val) $newFilter.find('.param-val').val(val);

		// Add it just above the add-button
		$add.before($newFilter);

		// If it's a click event, slide down the new filter,
		// Otherwise just show it
		if (event) {
			event.preventDefault();
			$newFilter.slideDown(100);
		} else {
			$newFilter.show();
		}

		$newFilter.find('.param-key').focus();
	};

	// If we have reorder fields pre-defined, add them to the list
	if (typeof params == 'object') {

		// Remove template from DOM
		$tmpl.remove();

		for (var i in params) {
			addFilter(null, i, params[i]);
		}

	}

	// Enable the add-button
	$add.click(addFilter);

	// Enable all future remove-buttons
	$el.delegate('button.remove', 'click', function(event){
		event.preventDefault();
		$(this).parent().remove();
	});
};

$(LOW.Params);

// ------------------------------------------
// Search Log
// ------------------------------------------

LOW.SearchLog = function() {
	var $cells = $('td.params');
	$cells.each(function(){
		var $td   = $(this),
			$more = $('<span>&hellip;</span>'),
			$lis  = $td.find('li'),
			$tr   = $td.parent();

		if ($lis.length > 1) {
			$lis.first().append($more);
			$td.on('click', function(){
				$tr.toggleClass('more');
			}).addClass('has-more');
		}
	});

	// $th.on('click', function(){
	// 	var method = open ? 'removeClass' : 'addClass';
	// 	$cells.filter('.has-more')[method]('open');
	// 	open = ! open;
	// });
};

$(LOW.SearchLog);

// ------------------------------------------
// Tabs object
// ------------------------------------------

LOW.Tabs = function(el) {

	var self   = this,
		$el    = $(el),
		$pages = $el.find('.low-tab'),
		$tabs  = $(),
		names  = $el.data('names'),
		_class = 'active';

	var toggle = function(event) {

		event.preventDefault();

		// Which tab is this?
		var i = $(this).data('index'),
			prev = 'low-tab-active-' + self.active,
			next = 'low-tab-active-' + i;

		// Deactivate all
		$tabs.removeClass(_class);
		$pages.removeClass(_class);
		$el.removeClass(prev);

		// Activate one
		$tabs.eq(i).addClass(_class);
		$pages.eq(i).addClass(_class);
		$el.addClass(next);

		// Remember which is active
		// and fire onchange event
		self.active = i;
		self.onchange();

	};

	// Build tab for each page
	$pages.each(function(i){
		var $page = $(this),
			$name = $page.find(names),
			title = $name.first().text(),
			$link = $('<a href="#"/>').attr('data-index', i).text(title),
			$tab  = $('<li/>').append($link);

		// If page is active, make tab active too
		if ($page.hasClass(_class)) {
			$tab.addClass(_class);
			self.active = i;
		}

		// This is the change event
		$link.click(toggle);

		$name.remove();

		$tabs = $tabs.add($tab);
	});

	// Create the tabs themselves
	$('<ul/>').addClass('low-tab-links').append($tabs).prependTo($el);

	$el.addClass('low-tab-active-' + self.active);

	// Onchange event handler
	this.onchange = function(){};

	this.change = function(i){
		$tabs.eq(i).find('a').click();
	};

	return this;
};


// ------------------------------------------
// Lexicon object
// ------------------------------------------

LOW.Lexicon = function() {
	var $el     = $('#low-lexicon'),
		$tabs   = $el.find('.low-tabs'),
		$form   = $el.find('form'),
		$input  = $form.find('input[type="text"]'),
		$status = $el.find('.low-status'),
		$target = $el.find('.low-dynamic-content'),
		names   = ['find', 'add'],
		tabs;

	// Initiate tabs and alter input name onchange
	if ($tabs.length) {
		tabs = new LOW.Tabs($tabs.get(0));
		tabs.onchange = function() {
			$input.attr('name', names[this.active]);
			$input.focus();
		};
		$input.focus();
	}

	// Update status numbers
	var updateStatus = function(txt) {
		$status.text(txt);
	};

	// Do something after form was submitted
	var updateTarget = function(data) {
		$target.html('');
		if (data.status) updateStatus(data.status);
		if (data.found) createLinks(data.found);
	};

	var addLink = function(word)  {
		var $a = $('<a href="#"/>').text('Add '+word+'?').appendTo($target);
		$a.click(function(event){
			event.preventDefault();
			tabs.change(1);
			$form.submit();
		});
	};

	var createLinks = function(words) {
		// Containing element
		var $p = $('<p/>').addClass('low-found-words').appendTo($target);

		// Loop through words
		for (var i in words) {

			// Get single word
			var word = words[i];

			// Create link and append
			$('<a href="#"/>').attr('data-lang', word.language).text(word.word).appendTo($p);

			// Add space
			$p.append(' ');
		}
	};

	// Submit form via ajax
	$form.submit(function(event){

		// Cancel submit!
		event.preventDefault();

		// Message
		$target.html(lang('working'));

		// Submit form via Ajax, show result in target
		$.post(this.action, $(this).serialize(), updateTarget, 'json');

	});

	// Delete words from lexicon via ajax
	$target.delegate('a', 'click', function(event){
		event.preventDefault();
		var $el = $(this),
			word = {
				language: $el.data('lang'),
				remove: $el.text()
			};

		$.post(location.href, word, function(data){
			if (data.status) updateStatus(data.status);
			$el.remove();
		}, 'json');
	});

};

$(LOW.Lexicon);

// ------------------------------------------
// Find & Replace functions
// ------------------------------------------

LOW.FindReplace = function(){

	var $far = $('#low-find-replace'),
		$tabs = $far.find('.low-tabs'),
		$form = $far.find('form'),
		$target = $far.find('.low-dynamic-content'),
		$keywords = $far.find('#low-keywords'),
		items;

	// Initiate tabs
	if ($tabs.length) {
		new LOW.Tabs($tabs.get(0));
	}

	// Define BoxSection object: to (de)select all checkboxes that belong to the section
	var BoxSection = function(el) {
		var $el     = $(el),
			$boxes  = $el.find(':checkbox'),
			$toggle = $el.find('h4 span');

		// Add toggle function to channel header
		$toggle.click(function(event){
			event.preventDefault();
			var $unchecked = $el.find('input:not(:checked)');

			if ($unchecked.length) {
				$unchecked.prop('checked', true);
			} else {
				$boxes.prop('checked', false);
			}
		});
	};

	// Channel / field selection options
	$form.find('fieldset').each(function(){

		// Define local variables
		var $self      = $(this),
			$sections  = $self.find('div.low-boxes'),
			$allBoxes  = $self.find('input[name]'),
			$selectAll = $self.find('input.low-select-all');

		// Init channel object per one channel found in main element
		$sections.each(function(){
			new BoxSection(this);
		});

		// Enable the (de)select all checkbox
		$selectAll.on('click', function(){
			$allBoxes.prop('checked', this.checked);
		});
	});

	// Show preview of find & replace action
	$form.submit(function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Validate keywords
		if ( ! $keywords.val()) {
			alert(lang('no_keywords_given'));
			return;
		}

		// Validate field selection
		if ( ! $form.find('input[name^="fields"]:checked').length) {
			alert(lang('no_fields_selected'));
			return;
		}

		// Turn on throbber, empty out target
		$target.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(this.action, $(this).serialize(), function(data){
			items = [];
			$target.html(data);
			$target.find('.item').each(function(){
				items.push(new Item(this));
			});
		});
	});

	var Item = function(el) {
		var $self = this,
			$el   = $(el),
			$box  = $el.find(':checkbox'),
			box   = $box.get(0),
			cn    = 'selected';

		this.on = function() {
			box.checked = true;
			$el.addClass(cn);
		};

		this.off = function() {
			box.checked = false;
			$el.removeClass(cn);
		};

		this.toggle = function(event) {
			if (event && event.target.tagName == 'A') return;
			box.checked ? $self.off() : $self.on();
		};

		this.isOn = function() {
			return box.checked;
		};

		$box.on('click', $self.toggle);
		$el.on('click', $self.toggle);

		return this;
	};

	// (de)select all checkboxes in preview table
	$target.delegate('.low-select-all', 'change', function(){
		for (var i in items) {
			this.checked ? items[i].on() : items[i].off();
		};
	});

	// Form submission after previewing
	$target.delegate('form', 'submit', function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Set local vars
		var $this = $(this);

		// Validate checked entries, destroy notice if okay
		var on = 0;

		for (var i in items) {
			if (items[i].isOn()) on++;
		}

		if ( ! on) {
			alert(lang('no_entries_selected'));
			return;
		}

		// Show message in preview
		$target.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(this.action, $this.serialize(), function(data){ $target.html(data); });
	});
};

$(LOW.FindReplace);

// ------------------------------------------
// Replace Log
// ------------------------------------------

LOW.ReplaceLog = function(){

	// EE's modal
	var $modal = $('.modal-replace-details .box');

	// Set max-height
	$modal.css({
		maxHeight: ($(window).height() - 80 - 55) + 'px',
		overflow: 'auto',
		padding: '10px 10px 0',
	});

	// Links that trigger the modal
	$('.replace-log a').on('click', function(event){
		$modal.html('<p style="text-align:center">Loading</p>');
		$modal.load(this.href);
	});

};

$(LOW.ReplaceLog);

// ------------------------------------------
// Settings
// ------------------------------------------

LOW.Settings = function(){

	// Get trigger
	var $select = $('select[name="excerpt_hilite"]');

	if ( ! $select.length) return;

	var $title = $select.parents('fieldset.col-group').next();

	var toggle = function(event) {
		var method = $select.val() ? 'show' : 'hide';
		$title[method]();
	};

	toggle();
	$select.on('change', toggle);
};

$(LOW.Settings);


})(jQuery);

// --------------------------------------
