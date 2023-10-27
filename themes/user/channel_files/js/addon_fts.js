;(function(global, $){
    //es5 strict mode
    "use strict";

    var ChannelFiles = global.ChannelFiles = global.ChannelFiles || {};

    $('.cf-test_location').on('click', testLocation);

    // ----------------------------------------------------------------------

    $('select[name="field_type"]').on('change', function() {
        if ($(this).val() == 'channel_files') {
            setTimeout(function(){
                $('.cf-upload_toggle').trigger('change');
            }, 300);
        }
    });

    if ($('select[name="field_type"]').val() == 'channel_files') {
        setTimeout(function(){
            $('.cf-upload_toggle').trigger('change');
        }, 300);
    }

    // ----------------------------------------------------------------------

    $('.cf-upload_toggle').on('change', function(evt){
        var val = $('.cf-upload_toggle input:checked').val();

        $('fieldset[class*="cf_location-"]').hide();
        $('fieldset[class*="cf_location-' + val + '"]').show();

    });

    // ----------------------------------------------------------------------

    function testLocation(e) {
        e.preventDefault();
        var uploadlocation = $('input[name="channel_files\[upload_location\]"]:checked').val();

        // Post Parameters
        var params = $(e.target).closest('fieldset').prevUntil('h2').find(':input').serializeArray();
        params.push({name:'ajax_method', value:'test_location'});
        params.push({name: 'channel_files[upload_location]', value: uploadlocation});

        // Modal
        var modal = $('.modal-cf_test_location');
        modal.trigger('modal:open');

        // Open the spinner
        var spinner = new Spinner({lines:13, length:18, width:12, radius:32}).spin();
        modal.find('.ajax_results').html(spinner.el);

        $.ajax({url: ChannelFiles.AJAX_URL, method: 'post', dataType: 'html', data: params,
            success: function(rdata){
                modal.find('.ajax_results').html(rdata);
            }
        });
    }

    // ----------------------------------------------------------------------

}(window, jQuery));