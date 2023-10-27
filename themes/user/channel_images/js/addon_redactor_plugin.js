;(function(global, $){
    //es5 strict mode
    "use strict";

    var ChannelImages = global.ChannelImages = global.ChannelImages || {};

    // Check to see if redactor is loaded

    $.Redactor.prototype.channel_images = function() {
        return {
            init: function() {
                var button = this.button.add('image', 'Images', 'ChannelImages');
                this.button.addCallback(button, this.channel_images.show);
                this.modal.addCallback('channel_images', this.channel_images.load);
            },
            show: function(){
                this.modal.addTemplate('channel_images', this.channel_images.getTemplate());
                this.modal.load('channel_images', 'Channel Images', 700);
                this.modal.getActionButton().on('click', this.channel_images.insert);
                this.modal.show();
            },
            load: function() {
                this.modal.getModal().find('.CImage').on('click', this.channel_images.selectImage);
            },
            selectImage: function(evt) {
                if (typeof evt.target == 'undefined') return;

                var target = $(evt.target);

                // Remove all other
                target.closest('.redactor-modal-tab').find('.CImage').removeClass('Selected');

                target.closest('.CImage').addClass('Selected');
            },
            insert: function(evt) {
                var wrapper = this.modal.getModal().find('.redactor-modal-tab:visible');
                if (wrapper.find('.Selected').length === 0) return;

                var selected = wrapper.find('.Selected img');
                var IMGSRC = selected.attr('src');
                var image_id = selected.data('image_id');
                var filename = selected.data('filename');
                var field_id = selected.data('field_id');
                var image_index = selected.data('index');

                var output_type = 'image_url';

                if (typeof(ChannelImages.Fields['Field_'+field_id]) != 'undefined') {
                    var settings = ChannelImages.Fields['Field_'+field_id].settings;
                    if (typeof(settings.wysiwyg_output) != 'undefined') {
                        output_type = settings.wysiwyg_output;
                    }
                }

                var dot = filename.lastIndexOf('.');
                var extension = filename.substr(dot,filename.length);

                var Size = wrapper.find('.sizeholder input[type=radio]:checked').val();
                var OLDFILENAME = selected.data('filename');

                // Existing Image?
                if (image_id > 0) {
                    var currentFilename = IMGSRC.split('/').pop().split('#').shift().split('?').shift();

                    if (Size != 'original'){
                        var NewName = filename.replace(extension, '__'+Size+extension);
                        IMGSRC = IMGSRC.replace(currentFilename, NewName);
                    }
                    else {
                        IMGSRC = IMGSRC.replace(currentFilename, filename);
                    }
                } else {
                    if (Size != 'original'){
                        var NewName = filename.replace(extension, '__'+Size+extension);
                        IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+NewName+'&');
                    }
                    else {
                        IMGSRC = IMGSRC.replace(/f\=(.*?)\&/, 'f='+filename+'&');
                    }
                }

                var img = '<img src="'+IMGSRC+'" alt="'+selected.attr('alt')+'" class="ci-image ci-'+Size+'">';

                selected.parent().removeClass('Selected');

                if (output_type == 'static_image') {
                    if (Size == 'original') {
                        img = '{image:'+image_index+'}';
                    } else {
                        img = '{image:'+image_index+':'+Size+'}';
                    }
                }


                var $img = $(img);
                var $figure = $('<figure>').append($img);
                var pre = this.utils.isTag(this.selection.current(), 'pre');

                this.modal.close();

                // buffer
                this.buffer.set();

                // insert
                this.air.collapsed();

                if (pre) {
                    $(pre).after($figure);
                } else {
                    this.insert.node($figure);
                }

                this.caret.after($figure);
                this.storage.add({ type: 'image', node: $img[0], url: $img[0].src, id: '' });
            },
            getTemplate: function()
            {
                var html = '';

                for (var handle in ChannelImages.Fields) {
                    var field = ChannelImages.Fields[handle];

                    html += '<div class="redactor-modal-tab WCI_Images" data-title="' + field.field_label + '">';

                    // Images
                    html += '<div class="imageholder">';

                    for (var i = 0; i < field.wimages.length; i++) {
                        var img = field.wimages[i];
                        html += '<div class="CImage">';
                        html += '<img src="' + img.big_img_url + '" title="' + img.title + '" alt="' + img.description + '" data-filename="' + img.filename + '" data-field_id="' + img.field_id + '" data-image_id="' + img.image_id + '" data-index="' + i + '">';
                        html += '</div>';
                    }

                    html += '</div>';
                    html += '<br clear="all">';

                    // Sizes
                    var sizes = this.channel_images.getSizes(field);
                    html += '<div class="sizeholder">';
                    html += '<ul>';
                    for (var i = 0; i < sizes.length; i++) {
                        var checked = sizes[i].checked ? 'checked' : '';
                        html += '<li><input name="size_'+handle+'" type="radio" value="'+sizes[i].name+'" '+checked+'> '+sizes[i].label+'</li>';
                    }
                    html += '</ul>';
                    html += '<br clear="all">';
                    html += '</div>';

                    html += '<section style="margin-top:10px">'
                    html +=     '<button id="redactor-modal-button-action">Insert</button>'
                    html +=     '<button id="redactor-modal-button-cancel">Cancel</button>'
                    html += '</section>'

                    html += '</div>';
                }

                return html;
            },
            getSizes: function(field) {
                var sizes = [];
                var checked = false;

                if (field.settings.wysiwyg_original == 'yes') {
                    checked = true;
                    sizes.push({name: 'original', label: 'ORIGINAL', checked: checked});
                }

                if (typeof field.settings.action_groups == 'undefined') return sizes;

                for (var i in field.settings.action_groups) {
                    if (field.settings.action_groups[i].wysiwyg != 'yes') continue;

                    sizes.push({
                        name: field.settings.action_groups[i].group_name,
                        label: field.settings.action_groups[i].group_name,
                        checked: (checked === false)
                    });
                    checked = true;
                }

                return sizes;
            }
        };
    };


    // ----------------------------------------------------------------------------------------------------------------------------------------

}(window, jQuery));