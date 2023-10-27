function updateProgressBar(width, value) {
    $('#progressbar').width(width+'%');
    $('#progressbar-text').text(value);
}

function migrate_fields(fields, i, total) {

    if(fields.length > 0) {
        var field = fields.pop();

        updateProgressBar(i/total * 90, 'Convert field: '+field.field_label+' to Gmaps');

        $.get(EE.gmaps_field_type_base_url.base+'?cp/'+EE.gmaps_field_type_base_url.path+'/migration&action=migrate&field_id='+field.field_id, function(result){
            migrate_fields(fields, (i + 1), total);
        });

    } else {
        updateProgressBar(100, 'Done, all Gmap fields converted to Gmaps Fieldtype');
    }

}

$(function(){

    $('#start_migration').click(function(){

        $('.progress-bar').show();

        $(this).remove();

        //show progress bar
        updateProgressBar(10, 'Looking for Google Maps Fieldtypes');

        //looking for the fields
        $.get(EE.gmaps_field_type_base_url.base+'?cp/'+EE.gmaps_field_type_base_url.path+'/migration&action=get_fields', function(fields){
            fields = $.parseJSON(fields);

            var total = fields.length;

            if(typeof fields == 'object') {
                migrate_fields(fields, 1, total);
            } else {
                updateProgressBar(100, 'Done, no Gmap fields found');
            }
        });

        return false;
    });
});