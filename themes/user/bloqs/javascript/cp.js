;jQuery(function($) {

var summarizers = {
  'text': function($atomContainer) {
    return $atomContainer.find('input').val();
  },
  'date': function($atomContainer) {
    return $atomContainer.find('input').val();
  },
  'email_address': function($atomContainer) {
    return $atomContainer.find('input').val();
  },
  'url': function($atomContainer) {
    return $atomContainer.find('input').val();
  },  
  'wygwam': function($atomContainer) {
    return $($atomContainer.find('textarea').first().val()).text();
  },
  'file': function($atomContainer) {
    return $atomContainer.find('.file-chosen img').attr("alt");
  },
  'relationship': function($atomContainer) {
    var multiselect = $atomContainer.find('.scroll-wrap.pr.ui-sortable:first');
    if (multiselect.length) {
      // If multi...
      var first = true;
      var text = '';
      multiselect.find('label.choice.chosen').each(function() {
        if (first) {
          first = false;
        }
        else {
          text += ', ';
        }
        text += $(this).data('entry-title');
      });
      return text;
    }
    else {
      // If single...
      return $atomContainer.find('select option:selected').text();
    }
  },
  'textarea': function($atomContainer) {
    return $atomContainer.find('textarea').val();
  }, 
  'rte': function($atomContainer) {
    return $($atomContainer.find('textarea').val()).text();
  }
};


// ----------------------------------------------------------------



$('.blocksft').each(function() {

  var blocksft = $(this),
      blocksft_field_id = blocksft.data('field-id'),
      blocks = blocksft.find('.blocksft-blocks'),
      newBlockCount = 1;

  blocksft.on('click', '[js-newblock]', function(e) {
    e.preventDefault();
    var newbutton = $(this);
    var templateid = newbutton.attr('data-template');
    var location = newbutton.attr('data-location');
    var context = newbutton.closest('.blocksft-block');

    createBlock(templateid, location, context, blocksft_field_id);
  });

  fireEvent("display", blocks.find('[data-fieldtype]'));

  //callback for ajax form validation - we'll need to customize this process a bit so it works with bloqs
  if( blocksft_field_id != ''){
    EE.cp && void 0 !== EE.cp.formValidation.bindCallbackForField && EE.cp.formValidation.bindCallbackForField('field_id_'+blocksft_field_id, _bloqsValidationCallback);
  }

  $.each(blocks.find('.blocksft-block'), function(){
    if( $(this).find('.blocksft-atom[data-fieldtype="checkboxes"]').length > 0 )
    {
      EE.cp && void 0 !== EE.cp.formValidation.bindCallbackForField && EE.cp.formValidation.bindCallbackForField('field_id_'+blocksft_field_id+'[]', _bloqsCheckboxValidationCallback);   
    }
  });

  // ----------------------------------------------------------------

    function createBlock(templateid, location, context, block_field_id) {
      //set the block template from the templateid
      var template = blocksft.find('#' + templateid).find('.blocksft-block');
      var clone = template.clone(true, true);
      clone.html(clone.html().replace(RegExp("blocks_new_row_[0-9]{1,}", "g"), "blocks_new_row_" + newBlockCount));
      clone.find(':input').removeAttr("disabled");

      switch (location) {
        case 'above':
          context.before(clone);
          break;
        case 'below':
          context.after(clone);
          break;
        case 'bottom':
          blocks.append(clone);
          break;
      }
      fireEvent("display", clone.find('[data-fieldtype]'));

      //TODO - is this the best way to handle this?  
      txtarea = clone.find('.grid-textarea');
      if( typeof txtarea != undefined && txtarea != '' ){
        if( $.isFunction($.fn.FilePicker) ){
          $('.textarea-field-filepicker, li.html-upload', txtarea).FilePicker({ 
              callback: filePickerCallback
          });
        }
      }
      EE.cp && void 0 !== EE.cp.formValidation && EE.cp.formValidation.bindInputs(clone);
      EE.cp && void 0 !== EE.cp.formValidation.bindCallbackForField && EE.cp.formValidation.bindCallbackForField('field_id_'+block_field_id, _bloqsValidationCallback);

      reorderFields();
      newBlockCount++;
      blocks.blocksSortable('reload');

    } //end createBlock

  // ----------------------------------------------------------------

    // Set the order value for all of the fields.
    function reorderFields() {
      var order = 1;
      blocksft.find('[data-order-field]').each(function() {
        $(this).val(order);
        order++;
      });
    }

  // ----------------------------------------------------------------

    blocks.blocksSortable({
      handle: '.blocksft-block-handle',
      forcePlaceholderSize: true
    });
    blocks.on('sortstart', function(e, ui) {
      var block = $(ui.item);
      block.find('[data-fieldtype]').each(function() {
        fireEvent('beforeSort', $(this));
      });
    });
    blocks.on('sortupdate', reorderFields);
    blocks.on('sortend', function(e, ui) {
      var block = $(ui.item);
      block.find('[data-fieldtype]').each(function() {
        fireEvent('afterSort', $(this));
      });
    });

  // ----------------------------------------------------------------

    // Punt on a fancy re-orderer. We can figure that out later.
    blocks.on('click', 'button.move.up, button.move.down', function(e) {
      e.preventDefault();
      var button = $(this);
      var up = button.is('.up');
      var block = button.closest('.blocksft-block');

      if (up) {
        var prev = block.prev('.blocksft-block');
        if (prev.length) {
          block.find('[data-fieldtype]').each(function() {
            fireEvent('beforeSort', $(this));
          });
          prev.before(block);
          block.find('[data-fieldtype]').each(function() {
            fireEvent('afterSort', $(this));
          });
          reorderFields();
        }
      }
      else {
        var next = block.next('.blocksft-block');
        if (next.length) {
          block.find('[data-fieldtype]').each(function() {
            fireEvent('beforeSort', $(this));
          });
          next.after(block);
          block.find('[data-fieldtype]').each(function() {
            fireEvent('afterSort', $(this));
          });
          reorderFields();
        }
      }
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-remove]', function(e) {
      e.preventDefault();
      var button = $(this);
      var block = button.closest('.blocksft-block');

      var deletedInput = block.find('[data-deleted-field]');
      if (deletedInput.length) {
        deletedInput.val('true');
        block.addClass('deleted');
        clearErrorsOnBlock(block);
        block.find('[data-order-field]').remove();
      }
      else {
        clearErrorsOnBlock(block);
        block.remove();
      }
      reorderFields();
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-context]', function(e) {
      e.preventDefault();
      var button = $(this);
      var block = button.closest('.blocksft-block');

      var menu = block.find('.blocksft-contextmenu');
      menu.show();
      e.stopPropagation();

      $('html').on('click', function(e) {
        menu.hide();
      });
    });

  // ----------------------------------------------------------------

    function collapseBlock(block) {
      block.attr('data-blockvisibility', 'collapsed');
      summarizeBlock(block);
    }

  // ----------------------------------------------------------------

    function expandBlock(block) {
      block.attr('data-blockvisibility', 'expanded');
    }

  // ----------------------------------------------------------------

    blocks.on('click', '[js-nextstep]', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var button = $(this);
      var multistep = button.closest('.multistep');
      var current = parseInt(multistep.attr('data-currentstep'), 10) || 1;
      multistep.attr('data-currentstep', current+1);
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-previousstep]', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var button = $(this);
      var multistep = button.closest('.multistep');
      var current = parseInt(multistep.attr('data-currentstep'), 10) || 1;
      multistep.attr('data-currentstep', current-1);
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-expand]', function(e) {
      e.preventDefault();
      var button = $(this);
      var block = button.closest('.blocksft-block');

      expandBlock(block);
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-collapse]', function(e) {
      e.preventDefault();
      var button = $(this);
      var block = button.closest('.blocksft-block');

      collapseBlock(block);
    });

  // ----------------------------------------------------------------

    blocks.on('mousedown', '.blocksft-header', function(e) {
      // Don't prevent default on the drag handle.
      if ($(e.target).is('.blocksft-block-handle')) {
        return;
      }

      // Prevent default so we don't highlight a bunch of stuff when double-
      // clicking.
      e.preventDefault();
    });

  // ----------------------------------------------------------------

    blocks.on('dblclick', '.blocksft-header', function(e) {
      var block = $(this).closest('.blocksft-block');
      var visibility = block.attr('data-blockvisibility');
      switch (visibility) {
        case 'expanded':
          collapseBlock(block);
          break;
        default:
          expandBlock(block);
          break;
      }
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-expandall]', function(e) {
      e.preventDefault();
      blocks.find('.blocksft-block').each(function() {
        expandBlock($(this));
      });
    });

  // ----------------------------------------------------------------

    blocks.on('click', '[js-collapseall]', function(e) {
      e.preventDefault();
      blocks.find('.blocksft-block').each(function() {
        collapseBlock($(this));
      });
    });

  // ----------------------------------------------------------------

    function summarizeBlock(block) {
      var summarized = '';
      block.find('[data-fieldtype]').each(function() {
        var atom = $(this);
        var fieldtype = atom.attr('data-fieldtype');
        if (summarizers[fieldtype]) {
          var text = summarizers[fieldtype](atom.find('.blocksft-atomcontainer'));
          if (!/^\s*$/.test(text)) {
            summarized += ' \u2013 ' + text;
          }
        }
      });
      summarized = summarized.substring(0, 30)+'...';
      block.find('[js-summary]').text(summarized);
    }

  // ----------------------------------------------------------------

    blocks.find('.blocksft-block').each(function() {
      var block = $(this);
      summarizeBlock(block);
    });


  });

// ----------------------------------------------------------------
// ----------------------------------------------------------------

function fireEvent(eventName, fields) {
  fields.each(function() {
    // Some field types require this.
    window.Grid.Settings.prototype._fireEvent(eventName, $(this));
  });
}

// On occassion, Blocks will load before another field type within a
// block, and so Grid.bind will not have been called yet. So, we need to
// intercept those and initialize them as well. I'm not convinced this is
// the best way to do this, so it may need to be refined in the future.

var g = Grid;
var b = g.bind;
g.bind = function(fieldType, eventName, callback) {
  b.apply(g, [fieldType, eventName, callback]);
  if (eventName === "display") {
    fireEvent("display", $('.blocksft .blocksft-blocks [data-fieldtype="' + fieldType + '"]'));
  }
};

//--------------------------------------------------

 $('a.m-link').click(function (e) {
    var modalIs = $('.' + $(this).attr('rel'));
    $('.checklist', modalIs)
      .html('') // Reset it
      .append('<li>' + $(this).data('confirm') + '</li>');
    $('input[name="blockdefinition"]', modalIs).val($(this).data('blockdefinition'));

    e.preventDefault();
  })

// ----------------------------------------------------------------

  var filePickerCallback = function(i, e) {
      var t = e.input_value;

      // Output as image tag if image
      if (
          // May be a markItUp button
          0 == t.size() && (t = e.source.parents(".markItUpContainer").find("textarea.markItUpEditor")),
          // Close the modal
          e.modal.find(".m-close").click(),
          // Assign the value {filedir_#}filename.ext
          file_string = "{filedir_" + i.upload_location_id + "}" + i.file_name, i.isImage) {
          var a = '<img src="' + file_string + '"';
          a += ' alt=""', i.file_hw_original && (dimensions = i.file_hw_original.split(" "), a = a + ' height="' + dimensions[0] + '" width="' + dimensions[1] + '"'), a += ">", t.insertAtCursor(a)
      } else
      // Output link if non-image
          t.insertAtCursor('<a href="' + file_string + '">' + i.file_name + "</a>")
  }

// ----------------------------------------------------------------


  function _bloqsValidationCallback(success, message, input)
  {
    var form = input.parents('form'),
        blocks_error_element = 'em.blocks-ee-form-error-message',
        blocksft_atomcontainer = input.closest('.blocksft-atomcontainer'),
        blocksft_atom = blocksft_atomcontainer.parent('.blocksft-atom'),
        fieldset = blocksft_atom.closest('fieldset.col-group');

    //The ajax-validate toggleErrorFields function is a bit ambitious when it comes to removing field errors,
    //especially with the way blocks is built. To keep it from adding/removing errors unnecessasrily, we change the 
    //class name on the error message html element. We'll take care of that ourselves.
    message = $(message).removeClass('ee-form-error-message').addClass('blocks-ee-form-error-message no');

    if( success === false )
    {
      fieldset.find('em.ee-form-error-message').remove();
      blocksft_atom.addClass('invalid');
      if( blocksft_atomcontainer.has(blocks_error_element).length ){
        blocksft_atomcontainer.find(blocks_error_element).remove();
      }
      blocksft_atomcontainer.append(message);
    }
    else
    {
      fieldset.find('em.ee-form-error-message').remove();
      blocksft_atom.removeClass('invalid');
      blocksft_atomcontainer.find(blocks_error_element).remove();

      if( fieldset.find('.invalid').length && !fieldset.hasClass('invalid') ){
        fieldset.addClass('invalid');
        _disablePublishFormButtons( form, input );
      }
    }
    return;
  }


  function clearErrorsOnBlock( block )
  {
      console.log('hit');
      var blocksft_atomcontainer = block.find('.blocksft-atoms');
      if( EE.cp && EE.cp.formValidation !== false ) {
        EE.cp.formValidation.markFieldValid( blocksft_atomcontainer.find("input, select, textarea") );
      }
  }


  function _bloqsCheckboxValidationCallback(success, message, input)
  {
    var form = input.parents('form'),
        blocks_error_element = 'em.blocks-ee-form-error-message',
        blocksft_atom = $(input).closest('.blocksft-atom'),
        blocksft_atomcontainer = input.closest('.blocksft-atomcontainer'),
        fieldset = blocksft_atom.closest('fieldset'),
        checkboxes = blocksft_atom.find('input[type="checkbox"]'),
        has_selected_value = false;

    $.each(checkboxes, function(){
      if( this.checked ){
        has_selected_value = true;
      }
    });

    if( has_selected_value )
    {
      blocksft_atom.removeClass('invalid');
      blocksft_atom.find(blocks_error_element).remove();
      if( fieldset.find('.invalid').length > 0 && !fieldset.hasClass('invalid') )
      {
        fieldset.addClass('invalid');
      }
      else
      {
        fieldset.removeClass('invalid'); 
      }
    }
    else
    {
      fieldset.addClass('invalid');
      if( !blocksft_atom.hasClass('invalid') )
      {
        blocksft_atom.addClass('invalid');
      }
      if( typeof message != 'undefined' )
      {
        message = $(message).removeClass('ee-form-error-message').addClass('blocks-ee-form-error-message no');
        blocksft_atomcontainer.append(message);
      }
      _disablePublishFormButtons( form, input );
    }
  }


  function _disablePublishFormButtons( form, input )
  {
    var tab_container = input.parents('.tab'),
      tab_rel = (tab_container.size() > 0) ? tab_container.attr('class').match(/t-\d+/) : '', // Grabs the tab identifier (ex: t-2)
      tab = $(tab_container).parents('.tab-wrap').find('a[rel="'+tab_rel+'"]'), // Tab link
      // See if this tab has its own submit button
      tab_has_own_button = (tab_container.size() > 0 && tab_container.find('.form-ctrls input.btn').size() > 0),
      // Finally, grab the button of the current form
      button = (tab_has_own_button) ? tab_container.find('.form-ctrls .btn') : form.find('.form-ctrls .btn');

    // Disable submit button
    button.addClass('disable').attr('disabled', 'disabled');
    if (button.is('input')) {
      button.attr('value', EE.lang.btn_fix_errors);
    } else if (button.is('button')) {
      button.text(EE.lang.btn_fix_errors);
    }
  }


});
