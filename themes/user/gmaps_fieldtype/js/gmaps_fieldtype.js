//----------------------------------------------
// Global vars
//----------------------------------------------
var alertTimer;

//the init function
function init_gmaps(map_nr) {
    //----------------------------------------------
    // set the vars
    //----------------------------------------------
    var $map = $('#gmaps_ft_' + map_nr);
    var mapID = $map.data('mapid');
    var map = EE_GMAPS.triggerEvent('getMap', {
        mapID: mapID
    });
    var array_key = 'ee_gmap_' + map_nr;
    // var $marker_elem = $map.find('.selected_markers');
    //var $polyline_elem = $map.find('.selected_polylines');
    // var $polygon_elem = $map.find('.selected_polygons');
    // var $circle_elem = $map.find('.selected_circles');
    // var $rectangle_elem = $map.find('.selected_rectangles');
    var settings = {};

    //----------------------------------------------
    //set the markers sortable
    //----------------------------------------------
    $map.find(".sortable").sortable({
        placeholder: "sortable-placeholder",
        helper: "clone",
        revert: true,
        start: function(event, ui) {
            // $map.find('.selected_markers').height(38)
        },
        stop: function(event, ui) {
            EE_GMAPS_FT.update_markers_position($map);
        }
    });

    //load the settings
    load_settings($map);

    $map.find(".sortable").disableSelection();


    //----------------------------------------------
    // on submit form, save the settings
    //----------------------------------------------
    $('.publish .settings, #low-variables-form').submit(function(e) {
        save_settings($map);

        //e.preventDefault();
        //return false;
    });
    //better workflow
    $('.bwf_save_button').click(function(e) {
        save_settings($map);
    });

    //----------------------------------------------
    //add the context menu on the map
    //----------------------------------------------
    /*EE_GMAPS.triggerEvent('contextMenu', {
     mapID : mapID,
     control: 'map',
     options: [{
     title: 'Add marker',
     name: 'add_marker',
     action: function(e, o) {
     //add marker
     //EE_GMAPS.triggerEvent('geocode', {
     // latlng : e.latLng.lat()+','+e.latLng.lng(),
     //callback : function(result, type, sessionKey){
     //  if(result.length > 0) {
     //    result = $.parseJSON(result);
     add_marker({
     mapID : mapID,
     lat: e.latLng.lat(),
     lng: e.latLng.lng(),
     marker_elem : $marker_elem
     });
     //}
     //}
     //});

     }
     }, {
     title: 'Center here',
     name: 'center_here',
     action: function(e) {
     this.setCenter(e.latLng.lat(), e.latLng.lng());
     }
     }]
     });*/

    //----------------------------------------------
    //Draw manager
    //----------------------------------------------
    var mapObject = EE_GMAPS.api('getMap', {
        mapID: mapID
    });

    //set the permission for the drawing manager
    var drawingModes = [];

    var _show_marker = String($map.data('show-marker-icon')).split('|');
    var _show_circle = String($map.data('show-circle-icon')).split('|');
    var _show_polygon = String($map.data('show-polygon-icon')).split('|');
    var _show_polyline = String($map.data('show-polyline-icon')).split('|');
    var _show_rectangle = String($map.data('show-rectangle-icon')).split('|');
    var _group_id = String($map.data('group-id'));

    if(jQuery.inArray(_group_id, _show_marker) !== -1) {drawingModes.push(google.maps.drawing.OverlayType.MARKER);}
    if(jQuery.inArray(_group_id, _show_circle) !== -1) {drawingModes.push(google.maps.drawing.OverlayType.CIRCLE);}
    if(jQuery.inArray(_group_id, _show_polygon) !== -1) {drawingModes.push(google.maps.drawing.OverlayType.POLYGON);}
    if(jQuery.inArray(_group_id, _show_polyline) !== -1) {drawingModes.push(google.maps.drawing.OverlayType.POLYLINE);}
    if(jQuery.inArray(_group_id, _show_rectangle) !== -1) {drawingModes.push(google.maps.drawing.OverlayType.RECTANGLE);}

    //set the drawing manager
    var drawingManager = new google.maps.drawing.DrawingManager({
        //drawingMode: google.maps.drawing.OverlayType.MARKER,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: drawingModes
        },
        //markerOptions: {
        //icon: 'images/beachflag.png'
        //},
        circleOptions: {
            fillColor: '#ffff00',
            fillOpacity: 1,
            strokeWeight: 5,
            clickable: false,
            editable: true,
            zIndex: 1
        }
    });
    //add to map
    drawingManager.setMap(mapObject.map);

    //WHen an marker has been added
    google.maps.event.addListener(drawingManager, 'markercomplete', function(e) {
        //remove the auto added marker 
        e.setMap(null);
        //add our own marker
        EE_GMAPS_FT.add_marker({
            lat: e.position.lat(),
            lng: e.position.lng(),
            title: e.position.toString()
        }, $map);
    });

    //WHen an pliline has been added
    google.maps.event.addListener(drawingManager, 'polylinecomplete', function(e) {
        //remove from map
        e.setMap(null);

        //save data to the GMAPS object
        EE_GMAPS_FT.add_polyline({
            path: EE_GMAPS.createlatLngArray(e.getPath().getArray())
        }, $map);
    });

    //WHen an polygon has been added
    google.maps.event.addListener(drawingManager, 'polygoncomplete', function(e) {
        //remove from map
        e.setMap(null);

        //save data to the GMAPS object
        EE_GMAPS_FT.add_polygon({
            paths: EE_GMAPS.createlatLngArray(e.getPath().getArray())
        }, $map);
    });

    //WHen an circle has been added
    google.maps.event.addListener(drawingManager, 'circlecomplete', function(e) {
        //remove from map
        e.setMap(null);

        //save data to the GMAPS object
        EE_GMAPS_FT.add_circle({
            lat: e.center.lat(),
            lng: e.center.lng(),
            radius: e.radius,
            strokeWeight: e.strokeWeight,
            fillOpacity: e.fillOpacity,
            fillColor: e.fillColor
        }, $map);
    });

    //WHen an rectangle has been added
    google.maps.event.addListener(drawingManager, 'rectanglecomplete', function(e) {
        //remove from map
        e.setMap(null);

        //save data to the GMAPS object
        EE_GMAPS_FT.add_rectangle({
            bounds: e.bounds,
            strokeWeight: e.strokeWeight,
            fillOpacity: e.fillOpacity,
            fillColor: e.fillColor
        }, $map);
    });

    //----------------------------------------------
    // add event when zoom changed
    //----------------------------------------------
    google.maps.event.addListener(mapObject.map, 'zoom_changed', function() {
        $('#edit_map_' + map_nr + ' [name="zoom_level"]').val(mapObject.map.getZoom())
    });

    //----------------------------------------------
    //add contect menu on marker
    // @ buggy/not working
    //----------------------------------------------
    /*EE_GMAPS.triggerEvent('contextMenu', {
     mapID : mapID,
     control: 'marker',
     options: [{
     title: 'Remove marker',
     name: 'remove_marker',
     action: function(e){
     console.log(e);
     //set the map again
     $map = $(this).parents('.gmap_holder');
     //remove the marker
     remove_marker({
     'mapID' : $map.data('mapid'),
     'marker_number' : e.marker.markerNumber,
     'marker_elem' : $marker_elem
     });
     }
     }]
     });*/


    //----------------------------------------------
    //let the marker bounce when you hover
    //----------------------------------------------
    $(document).on('mouseenter', '#' + $map.attr('id') + ' .selected_markers li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        EE_GMAPS.api('updateMarker', {
            mapID: mapID,
            key: markerNumber,
            animation: google.maps.Animation.BOUNCE
        });
    });
    $(document).on('mouseleave', '#' + $map.attr('id') + ' .selected_markers li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        EE_GMAPS.api('updateMarker', {
            mapID: mapID,
            key: markerNumber,
            animation: null
        });
    });

    //----------------------------------------------
    //let polyline switch to a white line on hover
    //----------------------------------------------
    $(document).on('mouseenter', '#' + $map.attr('id') + ' .selected_polylines li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var polyline = EE_GMAPS.api('getPolyline', {
            mapID: mapID,
            key: markerNumber
        });

        if(polyline.oldStrokeColor == undefined || polyline.oldStrokeColor == null) {
            EE_GMAPS.api('updatePolyline', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: '#ffffff',
                oldStrokeColor: polyline.strokeColor || '#000000'
            });
        }
    });
    $(document).on('mouseleave', '#' + $map.attr('id') + ' .selected_polylines li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var polyline = EE_GMAPS.api('getPolyline', {
            mapID: mapID,
            key: markerNumber
        });

        if(polyline.oldStrokeColor != undefined || polyline.oldStrokeColor != null) {
            EE_GMAPS.api('updatePolyline', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: polyline.oldStrokeColor,
                oldStrokeColor: null
            });
        }
    });

    //----------------------------------------------
    //let polygon switch to a white line on hover
    //----------------------------------------------
    $(document).on('mouseenter', '#' + $map.attr('id') + ' .selected_polygons li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var polygon = EE_GMAPS.api('getPolygon', {
            mapID: mapID,
            key: markerNumber
        });

        if(polygon.oldStrokeColor == undefined || polygon.oldStrokeColor == null) {
            EE_GMAPS.api('updatePolygon', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: '#ffffff',
                oldStrokeColor: polygon.strokeColor || '#000000'
            });
        }
    });
    $(document).on('mouseleave', '#' + $map.attr('id') + ' .selected_polygons li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var polygon = EE_GMAPS.api('getPolygon', {
            mapID: mapID,
            key: markerNumber
        });

        if(polygon.oldStrokeColor != undefined || polygon.oldStrokeColor != null) {
            EE_GMAPS.api('updatePolygon', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: polygon.oldStrokeColor,
                oldStrokeColor: null
            });
        }
    });

    //----------------------------------------------
    //let circle switch to a white line on hover
    //----------------------------------------------
    $(document).on('mouseenter', '#' + $map.attr('id') + ' .selected_circles li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var circle = EE_GMAPS.api('getCircle', {
            mapID: mapID,
            key: markerNumber
        });

        if(circle.oldStrokeColor == undefined || circle.oldStrokeColor == null) {
            EE_GMAPS.api('updateCircle', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: '#ffffff',
                oldStrokeColor: circle.strokeColor || '#000000'
            });
        }
    });
    $(document).on('mouseleave', '#' + $map.attr('id') + ' .selected_circles li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var circle = EE_GMAPS.api('getCircle', {
            mapID: mapID,
            key: markerNumber
        });

        if(circle != undefined && circle.oldStrokeColor != undefined || circle.oldStrokeColor != null) {
            EE_GMAPS.api('updateCircle', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: circle.oldStrokeColor,
                oldStrokeColor: null
            });
        }
    });

    //----------------------------------------------
    //let rectangle switch to a white line on hover
    //----------------------------------------------
    $(document).on('mouseenter', '#' + $map.attr('id') + ' .selected_rectangles li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var rectangle = EE_GMAPS.api('getRectangle', {
            mapID: mapID,
            key: markerNumber
        });

        if(rectangle.oldStrokeColor == undefined || rectangle.oldStrokeColor == null) {
            EE_GMAPS.api('updateRectangle', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: '#ffffff',
                oldStrokeColor: rectangle.strokeColor || '#000000'
            });
        }
    });
    $(document).on('mouseleave', '#' + $map.attr('id') + ' .selected_rectangles li', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().find('li').index($obj);

        var rectangle = EE_GMAPS.api('getRectangle', {
            mapID: mapID,
            key: markerNumber
        });

        if(rectangle != undefined && rectangle.oldStrokeColor != undefined || rectangle.oldStrokeColor != null) {
            EE_GMAPS.api('updateRectangle', {
                mapID: mapID,
                key: markerNumber,
                strokeColor: rectangle.oldStrokeColor,
                oldStrokeColor: null
            });
        }
    });


    //----------------------------------------------
    //zoek function
    //----------------------------------------------
    $map.find('.search_address_input').enterKey(function(e) {
        e.preventDefault();
        //trigger the enter button
        $map.find('.search_address').trigger('click');
        return false;
    });
    //search button
    $map.find('.search_address').click(function(e) {

        //add loader
        $map.find('.search_address_input').attr('disabled', true).after('<span class="fa fa-spinner"/>');

        //check if latlng
        var latlng = $map.find('.search_address_input').val().split(',');
        var isLatlng = false;
        if(typeof latlng === 'object') {
            if(typeof latlng[0] !== 'undefined' && typeof latlng[1] !== 'undefined') {
                latlng = new google.maps.LatLng(latlng[0], latlng[1]);

                if(!isNaN(latlng.lat()) && !isNaN(latlng.lng())) {
                    isLatlng = true;
                }
            }
        }

        //default vars
        var prevSessionKey;
        var search_id = createUUID();

        if($map.find('input[name="geocode_latlng"]:checked').length == 0 && isLatlng) {
            $map.find('.search_holder .markers_holder').removeClass('hidden');
            $map.find('.search_holder .markers_holder .markers').append('<li data-uuid="' + search_id + '" data-city="' + latlng.lat() + ','+latlng.lng() +'" data-lat="' + latlng.lat() + '" data-lng="' + latlng.lng() + '">' + latlng.lat() + ','+latlng.lng() + ' <span class="add_marker fa fa-plus-circle"></span><span class="remove_result fa fa-times"></span></li>');
            $map.find('.search_address_input').val(' ');
            $map.find('.search_address_input').attr('disabled', false);
            $map.find('.fa-spinner').remove();
        }

        else if($map.find('input[name="geocode_latlng"]:checked').length > 0) {

            //default vars
            var search_address = $map.find('.search_address_input').val();
            var search_object = {};

            //latlng? just search latlng
            if(isLatlng) {
                search_object.latlng = search_address;
            } else {
                search_object.address = search_address;
                search_object.latlng = search_address;
                search_object.ip = search_address;
            }

            //geocode address
            EE_GMAPS.api('geocode', $.extend(
                search_object,
                {
                    callback: function(result, type, sessionKey) {

                        var valid = false;

                        //remove the loader
                        $map.find('.search_address_input').attr('disabled', false);
                        $map.find('.fa-spinner').remove();

                        //parse result
                        if(result.length > 0 && result != 'no_post_value' && result != 'no_result') {

                            result = $.parseJSON(result);

                            $.each(result, function(k, v) {

                                if(typeof v == 'object' && v.lat != '' && v.lng != '') {

                                    //set the name
                                    // var name = v.latitude + ', ' + v.longitude; //default
                                    //
                                    // if(v.city !== null && v.city != '') {
                                    //     name = v.city;
                                    // }
                                    // if(v.street_name !== null && v.street_name != '') {
                                    //     name += ' ' + v.street_name;
                                    // }
                                    // if(v.street_number !== null && v.street_number != '') {
                                    //     name += ' ' + v.street_number;
                                    // }
                                    // if(v.zipcode !== null && v.zipcode != '') {
                                    //     name += ' ' + v.zipcode;
                                    // }

                                    var name = search_address; // name is the entered value

                                    if($map.find('.search_holder .markers_holder .markers [data-city="' + name + '"]').length == 0) {
                                        valid = true;
                                        $map.find('.search_holder .markers_holder').removeClass('hidden');
                                        $map.find('.search_holder .markers_holder .markers').append('<li data-uuid="' + search_id + '" data-city="' + name + '" data-lat="' + v.lat + '" data-lng="' + v.lng + '">' + name + ' <span class="add_marker fa fa-plus-circle"></span><span class="remove_result fa fa-times"></span></li>');

                                        //Toon message of duplicate, but only in different search.
                                        //otherwise you get the message when you search on lat,lng
                                    } else if($map.find('.search_holder .markers_holder .markers [data-city="' + name + '"]').data('uuid') != search_id) {
                                        show_alert($map, 'There is already a result for this search action.');
                                    }
                                }

                            });
                        }

                        //clear input value
                        if(valid) {
                            $map.find('.search_address_input').val(' ');
                        }

                        prevSessionKey = sessionKey;
                    }
                }
            ));
        }

        return false;
    });

    //----------------------------------------------
    //zoek function, add onClick on the search result
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .search_holder .markers_holder .markers .add_marker', function() {
        var $obj = $(this).parent();

        EE_GMAPS_FT.add_marker({
            lat: $obj.data('lat'),
            lng: $obj.data('lng'),
            title: $obj.data('city')
        }, $map);

        //Fit the map
        if($map.data('auto-center')) {
            EE_GMAPS.api('center', {
                mapID: $map.data('mapid'),
                lat: $obj.data('lat'),
                lng: $obj.data('lng')
            });
        }

        $(this).parent().fadeOut(function() {
            $(this).remove();
        });

        $map.find('.search_holder .markers_holder').addClass('hidden');
    });
    $(document).on('click', '#' + $map.attr('id') + ' .search_holder .markers_holder .markers .remove_result', function() {
        $(this).parent().fadeOut(function() {
            $(this).remove();
        });
    });

    //----------------------------------------------
    //Remove a marker from the marker tag
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .selected_markers .remove_marker', function() {
        var $obj = $(this);
        var markerNumber = $obj.parent().parent().find('li').index($obj.parent());
        var $elem = $(this).parents('.gmap_holder');
        //remove the marker
        EE_GMAPS_FT.remove_marker({
            'marker_number': markerNumber
        }, $map);

    });

    //----------------------------------------------
    //Remove a polyline
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .selected_polylines .remove_polyline', function() {
        var $obj = $(this);
        var polyNumber = $obj.parent().parent().find('li').index($obj.parent());
        var $elem = $(this).parents('.gmap_holder');
        //remove
        EE_GMAPS_FT.remove_polyline({
            'poly_number': polyNumber
        }, $map);
    });

    //----------------------------------------------
    //Remove a polygon
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .selected_polygons .remove_polygon', function() {
        var $obj = $(this);
        var polyNumber = $obj.parent().parent().find('li').index($obj.parent());
        var $elem = $(this).parents('.gmap_holder');
        //remove
        EE_GMAPS_FT.remove_polygon({
            'poly_number': polyNumber
        }, $map);
    });

    //----------------------------------------------
    //Remove a circle
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .selected_circles .remove_circle', function() {
        var $obj = $(this);
        var circleNumber = $obj.parent().parent().find('li').index($obj.parent());
        var $elem = $(this).parents('.gmap_holder');
        //remove
        EE_GMAPS_FT.remove_circle({
            'circle_number': circleNumber
        }, $map);
    });

    //----------------------------------------------
    //Remove a rectangle
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .selected_rectangles .remove_rectangle', function() {
        var $obj = $(this);
        var rectangleNumber = $obj.parent().parent().find('li').index($obj.parent());
        var $elem = $(this).parents('.gmap_holder');

        //remove
        EE_GMAPS_FT.remove_rectangle({
            'rectangle_number': rectangleNumber
        }, $map);
    });

    //----------------------------------------------
    //Refresh Map
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .refresh_map_wrapper', function() {
        var mapId = $map.attr('id');
        var mapNr = $map.data('fieldname_input');
        $('#' + mapId).remove();
        $('.gmaps_field_type_create_' + mapNr).removeClass('gmaps_loaded');
        get_map($('.gmaps_field_type_create_' + mapNr).eq(0));

    });

    //----------------------------------------------
    //Reset Map
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .reset_map_wrapper', function() {
        var mapId = $map.attr('id');
        var mapNr = $map.data('fieldname_input');
        var r = confirm("Are you sure to reset the map the initial state");
        if(r == true) {
            $('#' + mapId).remove();
            $('.gmaps_field_type_create_' + mapNr).data('data', '');
            $('.gmaps_field_type_create_' + mapNr).removeClass('gmaps_loaded');
            get_map($('.gmaps_field_type_create_' + mapNr).eq(0));
        }
    });

    //----------------------------------------------
    //dialog, Edit Map
    //----------------------------------------------
    //update the modal setting on open
    $('.modal-edit_settings_'+ map_nr).on('modal:open', function(){
        var $this = $(this);

        //get the map settings
        var map_settings = getMapSettings(mapID);

        //set the zoom level
        $this.find('[name="zoom_level"]').val(map_settings.zoom_level);
        $this.find('[name="map_type"]').val(map_settings.map_type);
        $this.find('[name="map_types[]"]').filter(function () {
            return $.inArray(this.value, map_settings.map_types) >= 0;
        }).prop('checked', true);
        $this.find('[name="map_type_control"]').val(map_settings.map_type_control);
        $this.find('[name="map_type_control_style"]').val(map_settings.map_type_control_style);
        $this.find('[name="map_type_control_position"]').val(map_settings.map_type_control_position);
        $this.find('[name="scroll_wheel"]').val(map_settings.scroll_wheel);
        $this.find('[name="zoom_control"]').val(map_settings.zoom_control);
        $this.find('[name="zoom_control_position"]').val(map_settings.zoom_control_position);
        $this.find('[name="street_view_control"]').val(map_settings.street_view_control);
        $this.find('[name="street_view_control_position"]').val(map_settings.street_view_control_position);

        $this.find('[name="google_overlay_html"]').val(map_settings.google_overlay_html);
        $this.find('[name="google_overlay_position"]').val(map_settings.google_overlay_position);
    });

    //save action for the settings
    $(document).on('click', '.modal-edit_settings_'+ map_nr + ' .btn.save', function() {
        var $this = $('.modal-edit_settings_'+ map_nr);

        EE_GMAPS.api('updateMap', {
            mapID: mapID
        });

        //set the maptypes
        var mapTypes = [];
        $this.find('[name="map_types[]"]').each(function() {
            if($(this).is(':checked')) {
                mapTypes.push($(this).val());
            }
        });

        //update the map
        EE_GMAPS.api('updateMap', {
            mapID: mapID,
            zoom: parseInt($this.find('[name="zoom_level"]').val()),

            setMapTypeId: $this.find('[name="map_type"]').val(),
            mapTypeControl: $this.find('[name="map_type_control"]').val().gmaps_bool(),
            mapTypeControlOptions: {
                mapTypeIds: mapTypes,
                style: google.maps.ZoomControlStyle[$this.find('[name="map_type_control_style"]').val()],
                position: google.maps.ControlPosition[$this.find('[name="map_type_control_position"]').val()]
            },

            scrollwheel: $this.find('[name="scroll_wheel"]').val().gmaps_bool(),

            zoomControl: $this.find('[name="zoom_control"]').val().gmaps_bool(),
            zoomControlOptions: {
                position: google.maps.ControlPosition[$this.find('[name="zoom_control_position"]').val()]
            },

            streetViewControl: $this.find('[name="street_view_control"]').val().gmaps_bool(),
            streetViewControlOptions: {
                position: google.maps.ControlPosition[$this.find('[name="street_view_control_position"]').val()]
            }
        });

        EE_GMAPS.api('updateGoogleOverlay', {
            html: $this.find('[name="google_overlay_html"]').val(),
            position: $this.find('[name="google_overlay_position"]').val(),
            mapID: mapID
        });

        //and refresh the map
        EE_GMAPS.api('refresh', {
            mapID: $map.data('mapID')
        });

        $this.trigger('modal:close');

        return false;
    });

    //----------------------------------------------
    //dialog, Edit Marker
    //----------------------------------------------
    //update the modal marker on open
    $(document).on('click', '#' + $map.attr('id') + ' .edit_marker', function() {

        var $obj = $(this);
        var number = $obj.parents('li').data('marker-nr');
        var uuid = $obj.parents('li').data('uuid');
        var $modal = $('.modal-edit_marker_'+ map_nr);

        //open the modal
        $modal.trigger('modal:open');

        //get the marker

        //get the polyline
        var marker = EE_GMAPS.api('getMarker', {
            mapID: mapID,
            key: uuid
        });

        //update the content
        $modal.find('[name="marker_number"]').val(number);
        $modal.find('[name="marker_uuid"]').val(uuid);
        $modal.find('[name="marker_title"]').val(marker.title);
        $modal.find('[name="marker_icon"]').val(marker.icon);
        if(marker.infoWindow != undefined) {
            $modal.find('[name="marker_infowindow"]').val(marker.infoWindow.content);
        }
    });

    //save action for the marker
    $(document).on('click', '.modal-edit_marker_'+ map_nr + ' .btn.save', function() {
        var $modal = $('.modal-edit_marker_'+ map_nr);
        var number = $modal.find('[name="marker_number"]').val();
        var uuid = $modal.find('[name="marker_uuid"]').val();
        var title = $modal.find('[name="marker_title"]').val();
        var icon = $modal.find('[name="marker_icon"] option:selected').val();
        var infoWindowContent = $modal.find('[name="marker_infowindow"]').val();

        //update the marker
        EE_GMAPS.api('updateMarker', {
            mapID: mapID,
            key: uuid,
            title: title,
            icon: icon,
            infoWindow: {
                content: infoWindowContent
            },
            animation: google.maps.Animation.DROP
        });

        //update title
        $('li[data-uuid="' + (uuid) + '"] .marker-title').text(title);

        $modal.trigger('modal:close');

        return false;
    });

    //----------------------------------------------
    //dialog, Edit polyline
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .edit_polyline', function() {

        var $obj = $(this);
        var number = $obj.parents('li').data('poly-nr');
        var uuid = $obj.parents('li').data('uuid');
        var $modal = $('.modal-edit_polyline_'+ map_nr);

        //open the modal
        $modal.trigger('modal:open');

        //get the polyline
        var polyline = EE_GMAPS.api('getPolyline', {
            mapID: mapID,
            key: uuid
        });

        //update the content
        $modal.find('[name="polyline_number"]').val(number);
        $modal.find('[name="polyline_uuid"]').val(uuid);
        $modal.find('[name="polyline_strokecolor"]').val(polyline.oldStrokeColor || polyline.strokeColor);
        $modal.find('[name="polyline_opacity"]').val(polyline.strokeOpacity || 1);
        $modal.find('[name="polyline_weight"]').val(polyline.strokeWeight || 1);
    });

    //save action for the polyline
    $(document).on('click', '.modal-edit_polyline_'+ map_nr + ' .btn.save', function() {
        var $modal = $('.modal-edit_polyline_'+ map_nr);
        var number = $modal.find('[name="polyline_number"]').val();
        var uuid = $modal.find('[name="polyline_uuid"]').val();

        //update polyline
        EE_GMAPS.api('updatePolyline', {
            mapID: mapID,
            key: uuid,
            strokeColor: $modal.find('[name="polyline_strokecolor"]').val(),
            strokeOpacity: $modal.find('[name="polyline_opacity"]').val(),
            strokeWeight: $modal.find('[name="polyline_weight"]').val()
        });

        $modal.trigger('modal:close');

        return false;
    });

    //----------------------------------------------
    //dialog, Edit polygon
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .edit_polygon', function() {

        var $obj = $(this);
        var number = $obj.parents('li').data('poly-nr');
        var uuid = $obj.parents('li').data('uuid');
        var $modal = $('.modal-edit_polygon_'+ map_nr);

        //open the modal
        $modal.trigger('modal:open');

        var polygon = EE_GMAPS.api('getPolygon', {
            mapID: mapID,
            key: uuid
        });

        $modal.find('[name="polygon_number"]').val(number);
        $modal.find('[name="polygon_uuid"]').val(uuid);
        $modal.find('[name="polygon_strokecolor"]').val(polygon.oldStrokeColor || polyline.strokeColor);
        $modal.find('[name="polygon_opacity"]').val(polygon.strokeOpacity || 1);
        $modal.find('[name="polygon_weight"]').val(polygon.strokeWeight || 1);
        $modal.find('[name="polygon_fillcolor"]').val(polygon.fillColor || '#000000');
        $modal.find('[name="polygon_fillopacity"]').val(polygon.fillOpacity || 0.3);
    });

    //save action for the polyline
    $(document).on('click', '.modal-edit_polygon_'+ map_nr + ' .btn.save', function() {

        var $modal = $('.modal-edit_polygon_'+ map_nr);
        var number = $modal.find('[name="polygon_number"]').val();
        var uuid = $modal.find('[name="polygon_uuid"]').val();

        EE_GMAPS.api('updatePolygon', {
            mapID: mapID,
            key: uuid,
            strokeColor: $modal.find('[name="polygon_strokecolor"]').val(),
            strokeOpacity: $modal.find('[name="polygon_opacity"]').val(),
            strokeWeight: $modal.find('[name="polygon_weight"]').val(),
            fillColor: $modal.find('[name="polygon_fillcolor"]').val(),
            fillOpacity: $modal.find('[name="polygon_fillopacity"]').val()
        });

        $modal.trigger('modal:close');

        return false;
    });

    //----------------------------------------------
    //dialog, Edit circle
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .edit_circle', function() {

        var $obj = $(this);
        var number = $obj.parents('li').data('circle-nr');
        var uuid = $obj.parents('li').data('uuid');
        var $modal = $('.modal-edit_circle_'+ map_nr);

        //open the modal
        $modal.trigger('modal:open');

        var circle = EE_GMAPS.api('getCircle', {
            mapID: mapID,
            key: uuid
        });

        $modal.find('[name="circle_number"]').val(number);
        $modal.find('[name="circle_uuid"]').val(uuid);
        $modal.find('[name="circle_strokecolor"]').val(circle.oldStrokeColor || circle.strokeColor);
        $modal.find('[name="circle_opacity"]').val(circle.strokeOpacity || 1);
        $modal.find('[name="circle_weight"]').val(circle.strokeWeight || 1);
        $modal.find('[name="circle_fillcolor"]').val(circle.fillColor || '#000000');
        $modal.find('[name="circle_fillopacity"]').val(circle.fillOpacity || 0.3);
        $modal.find('[name="circle_radius"]').val(circle.radius);
    });

    //save action for the polyline
    $(document).on('click', '.modal-edit_circle_'+ map_nr + ' .btn.save', function() {

        var $modal = $('.modal-edit_circle_'+ map_nr);
        var number = $modal.find('[name="circle_number"]').val();
        var uuid = $modal.find('[name="circle_uuid"]').val();

        EE_GMAPS.api('updateCircle', {
            mapID: mapID,
            key: uuid,
            strokeColor: $modal.find('[name="circle_strokecolor"]').val(),
            strokeOpacity: $modal.find('[name="circle_opacity"]').val(),
            strokeWeight: $modal.find('[name="circle_weight"]').val(),
            fillColor: $modal.find('[name="circle_fillcolor"]').val(),
            fillOpacity: $modal.find('[name="circle_fillopacity"]').val(),
            radius: parseInt($modal.find('[name="circle_radius"]').val())
        });

        $modal.trigger('modal:close');

        return false;
    });

    //----------------------------------------------
    //dialog, Edit Rectangle
    //----------------------------------------------
    $(document).on('click', '#' + $map.attr('id') + ' .edit_rectangle', function() {

        var $obj = $(this);
        var number = $obj.parents('li').data('rectangle-nr');
        var uuid = $obj.parents('li').data('uuid');
        var $modal = $('.modal-edit_rectangle_'+ map_nr);

        //open the modal
        $modal.trigger('modal:open');

        var rectangle = EE_GMAPS.api('getRectangle', {
            mapID: mapID,
            key: uuid
        });

        $modal.find('[name="rectangle_number"]').val(number);
        $modal.find('[name="rectangle_uuid"]').val(uuid);
        $modal.find('[name="rectangle_strokecolor"]').val(rectangle.oldStrokeColor || rectangle.strokeColor);
        $modal.find('[name="rectangle_opacity"]').val(rectangle.strokeOpacity || 1);
        $modal.find('[name="rectangle_weight"]').val(rectangle.strokeWeight || 1);
        $modal.find('[name="rectangle_fillcolor"]').val(rectangle.fillColor || '#000000');
        $modal.find('[name="rectangle_fillopacity"]').val(rectangle.fillOpacity || 0.3);
    });

    //save action for the polyline
    $(document).on('click', '.modal-edit_rectangle_'+ map_nr + ' .btn.save', function() {

        var $modal = $('.modal-edit_rectangle_'+ map_nr);
        var number = $modal.find('[name="rectangle_number"]').val();
        var uuid = $modal.find('[name="rectangle_uuid"]').val();

        EE_GMAPS.api('updateRectangle', {
            mapID: mapID,
            key: uuid,
            strokeColor: $modal.find('[name="rectangle_strokecolor"]').val(),
            strokeOpacity: $modal.find('[name="rectangle_opacity"]').val(),
            strokeWeight: $modal.find('[name="rectangle_weight"]').val(),
            fillColor: $modal.find('[name="rectangle_fillcolor"]').val(),
            fillOpacity: $modal.find('[name="rectangle_fillopacity"]').val()
        });

        $modal.trigger('modal:close');

        return false;
    });
    

    //----------------------------------------------
    // Save the settings
    //----------------------------------------------
    function save_settings($map) {
        var map_nr = $map.data('gmaps-number');
        //the markers
        settings[0] = {};
        settings[0].markers = [];
        if(EE_GMAPS.markers[array_key] != undefined) {
            $.each(EE_GMAPS.markers[array_key], function(k, v) {
                var setting = {};
                setting.lat = v.marker.position.lat();
                setting.lng = v.marker.position.lng();
                setting.title = clean_string(v.marker.title);
                setting.icon = v.marker.icon;
                setting.content = clean_string(v.marker.infoWindow.content);

                settings[0].markers.push(setting);
            });
        }
        //polylines
        settings[0].polylines = [];
        if(EE_GMAPS.polylines[array_key] != undefined) {
            $.each(EE_GMAPS.polylines[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.path = EE_GMAPS.createlatLngArray(v.object.getPath().getArray());

                settings[0].polylines.push(setting);
            });
        }

        //polylines
        settings[0].polygons = [];
        if(EE_GMAPS.polygons[array_key] != undefined) {
            $.each(EE_GMAPS.polygons[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.fillColor = v.object.fillColor;
                setting.fillOpacity = v.object.fillOpacity;
                setting.paths = EE_GMAPS.createlatLngArray(v.object.getPath().getArray());
                settings[0].polygons.push(setting);
            });
        }

        //circles
        settings[0].circles = [];
        if(EE_GMAPS.circles[array_key] != undefined) {
            $.each(EE_GMAPS.circles[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.fillColor = v.object.fillColor;
                setting.fillOpacity = v.object.fillOpacity;
                setting.lat = v.object.center.lat();
                setting.lng = v.object.center.lng();
                setting.radius = v.object.radius;
                settings[0].circles.push(setting);
            });
        }

        //circles
        settings[0].rectangles = [];
        if(EE_GMAPS.rectangles[array_key] != undefined) {
            $.each(EE_GMAPS.rectangles[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.fillColor = v.object.fillColor;
                setting.fillOpacity = v.object.fillOpacity;
                setting.bounds = v.object.bounds.toUrlValue();
                settings[0].rectangles.push(setting);
            });
        }

        //the map settings
        settings[0].map = {};

        //get the settings from the map
        settings[0]['map'] = getMapSettings(array_key);

        //set the coordinates
        var mapID = $map.data('mapid');
        var map = EE_GMAPS.api('getMap', {
            mapID: mapID
        });

        settings[0].map.center = [map.map.getCenter().lat(), map.map.getCenter().lng()];

        //encode to json
        settings = JSON.stringify(settings);

        //encode it with base64
        settings = Base64.encode(settings);

        //save it to the hidden field
        $('input[name="' + $map.data('fieldname_input') + '"]').val(settings);
    }

    //----------------------------------------------
    // Load current settings
    //----------------------------------------------
    function load_settings($map) {

        var mapID = 'ee_gmap_' + $map.data('gmaps-number');
        var map_nr = $map.data('gmaps-number');

        //reload current settings
        var current_settings = $('input[name="' + $map.data('fieldname_input') + '"]').val();

        var zoomLevel = $map.data('zoom-level');
        var scrollwheel = true;

        if(current_settings != '' && current_settings != undefined) {

            //fetch the settings
            current_settings = JSON.parse(Base64.decode(current_settings));

            //defaults
            zoomLevel = (current_settings[0].map.zoom_level != undefined ? parseInt(current_settings[0].map.zoom_level) : $map.data('zoom-level'));
            scrollwheel = (current_settings[0].map.scroll_wheel != undefined ? current_settings[0].map.scroll_wheel.gmaps_bool() : null);

            //set the map settings
            var gmaps_settings = {
                mapID: mapID,
                zoom: zoomLevel,
                setMapTypeId: current_settings[0].map.map_type,

                center: (current_settings[0].map.center != undefined ? new google.maps.LatLng(current_settings[0].map.center[0], current_settings[0].map.center[1]) : null),

                scrollwheel: scrollwheel,
                zoomControl: (current_settings[0].map.zoom_control != undefined ? current_settings[0].map.zoom_control.gmaps_bool() : null),
                zoomControlOptions: {
                    position: (current_settings[0].map.zoom_control_position != undefined ? google.maps.ControlPosition[current_settings[0].map.zoom_control_position] : null)
                },
                mapTypeControl: (current_settings[0].map.map_type_control != undefined ? current_settings[0].map.map_type_control.gmaps_bool() : null),
                mapTypeControlOptions: {
                    mapTypeIds: current_settings[0].map.map_types,
                    style: (current_settings[0].map.map_type_control_style != undefined ? google.maps.ZoomControlStyle[current_settings[0].map.map_type_control_style] : null),
                    position: (current_settings[0].map.map_type_control_position != undefined ? google.maps.ControlPosition[current_settings[0].map.map_type_control_position] : null)
                },

                streetViewControl: (current_settings[0].map.street_view_control != undefined ? current_settings[0].map.street_view_control.gmaps_bool() : null),
                streetViewControlOptions: {
                    position: (current_settings[0].map.street_view_control_position != undefined ? google.maps.ControlPosition[current_settings[0].map.street_view_control_position] : null)
                }
            };

            EE_GMAPS.api('updateMap', gmaps_settings);

            //set the google overlay
            if(current_settings[0].map.google_overlay_html != undefined && current_settings[0].map.google_overlay_position != undefined) {
                EE_GMAPS.api('updateGoogleOverlay', {
                    html: current_settings[0].map.google_overlay_html,
                    mapID: mapID,
                    position: current_settings[0].map.google_overlay_position
                });
            }

            //enable the forms
            set_map_settings_form(current_settings[0].map);

            //set the markers
            $.each(current_settings[0].markers, function(k, v) {

                EE_GMAPS_FT.add_marker({
                    lat: v.lat,
                    lng: v.lng,
                    icon: v.icon,
                    title: v.title,
                    content: v.content
                }, $map);

                //fit the map
                /*if ((k + 1), current_settings[0].markers.length) {
                 EE_GMAPS.api('fitZoom', {
                 mapID: mapID,
                 zoomLevel: $map.data('zoom')
                 });
                 }*/
            });

            //set the polylines
            $.each(current_settings[0].polylines, function(k, v) {
                EE_GMAPS_FT.add_polyline({
                    path: EE_GMAPS.arrayToLatLng(v.path),
                    strokeColor: v.strokeColor || '#000000',
                    strokeOpacity: v.strokeOpacity || 1,
                    strokeWeight: v.strokeWeight || 1
                }, $map);
            });

            //set the polygons
            $.each(current_settings[0].polygons, function(k, v) {
                EE_GMAPS_FT.add_polygon({
                    paths: v.paths,
                    strokeColor: v.strokeColor || '#000000',
                    strokeOpacity: v.strokeOpacity || 1,
                    strokeWeight: v.strokeWeight || 1,
                    fillColor: v.fillColor || '#000000',
                    fillOpacity: v.fillOpacity || 0.3
                }, $map);
            });

            //set the circles
            if(current_settings[0].circles != undefined) {
                $.each(current_settings[0].circles, function(k, v) {
                    EE_GMAPS_FT.add_circle({
                        lat: v.lat,
                        lng: v.lng,
                        strokeColor: v.strokeColor || '#000000',
                        strokeOpacity: v.strokeOpacity || 1,
                        strokeWeight: v.strokeWeight || 5,
                        fillColor: v.fillColor || '#ffff00',
                        fillOpacity: v.fillOpacity || 1,
                        radius: v.radius
                    }, $map);
                });
            }

            //set the rectamg;es
            if(current_settings[0].rectangles != undefined) {
                $.each(current_settings[0].rectangles, function(k, v) {

                    v.bounds = String(v.bounds).split(',');
                    var latLngBounds = new google.maps.LatLngBounds(
                        new google.maps.LatLng(v.bounds[0], v.bounds[1]),
                        new google.maps.LatLng(v.bounds[2], v.bounds[3])
                    );

                    EE_GMAPS_FT.add_rectangle({
                        bounds: latLngBounds,
                        strokeColor: v.strokeColor || '#000000',
                        strokeOpacity: v.strokeOpacity || 1,
                        strokeWeight: v.strokeWeight || 5,
                        fillColor: v.fillColor || '#ffff00',
                        fillOpacity: v.fillOpacity || 1
                    }, $map);
                });
            }

            //if the center is empty, set a new center
            if(gmaps_settings.center == null) {
                EE_GMAPS.api('geocode', {
                    address: $map.data('location'),
                    latlng: $map.data('location'),
                    callback: function(result, type, sessionKey) {
                        //parse result
                        if(result.length > 0 && result != 'no_post_value') {
                            result = $.parseJSON(result);
                            if(result[0] != undefined) {
                                EE_GMAPS.api('center', {
                                    mapID: mapID,
                                    lat: result[0].latitude,
                                    lng: result[0].longitude
                                });
                            }
                        }
                    }
                });
            }

        } else {
            //set the default location
            //default settings
            EE_GMAPS.api('geocode', {
                address: $map.data('location'),
                latlng: $map.data('location'),
                callback: function(result, type, sessionKey) {
                    //parse result
                    if(result.length > 0 && result != 'no_post_value') {
                        result = $.parseJSON(result);

                        if(result[0] != undefined) {
                            EE_GMAPS.api('center', {
                                mapID: mapID,
                                lat: result[0].latitude,
                                lng: result[0].longitude
                            });
                        }
                    }
                }
            });
        }

        //set default settings for the map from 
        //default settings
        EE_GMAPS.api('updateMap', {
            mapID: mapID,
            scrollwheel: scrollwheel,
            zoom: zoomLevel
        });
        EE_GMAPS.api('refresh', {
            mapID: mapID
        });
    }

    //----------------------------------------------
    // set the settings in the form of the map
    //----------------------------------------------
    function set_map_settings_form(settings) {
        $.each(settings, function(k, v) {
            var elem = $('#edit_map_' + map_nr + ' .' + k);

            //checkboxen?
            if(elem.data('type') == 'checkbox') {
                elem.find('input').each(function() {
                    if($.inArray($(this).val(), v) > -1) {
                        $(this).attr('checked', true);
                    } else {
                        $(this).attr('checked', false);
                    }
                });
            } else if(elem.data('type') == 'textarea') {
                elem.find('textarea').val(v);
            } else {
                elem.find('select').val(v);
            }
        });
    }

    //----------------------------------------------
    // remove Control Character
    //----------------------------------------------
    function clean_string(str) {
        //empty?
        str = str || '';

        //remove Control Character
        //http://en.wikipedia.org/wiki/Control_character
        //From https://github.com/slevithan/XRegExp/blob/master/src/addons/unicode/unicode-categories.js#L28
        var re = /[\0-\x1F\x7F-\x9F\xAD\u0378\u0379\u037F-\u0383\u038B\u038D\u03A2\u0528-\u0530\u0557\u0558\u0560\u0588\u058B-\u058E\u0590\u05C8-\u05CF\u05EB-\u05EF\u05F5-\u0605\u061C\u061D\u06DD\u070E\u070F\u074B\u074C\u07B2-\u07BF\u07FB-\u07FF\u082E\u082F\u083F\u085C\u085D\u085F-\u089F\u08A1\u08AD-\u08E3\u08FF\u0978\u0980\u0984\u098D\u098E\u0991\u0992\u09A9\u09B1\u09B3-\u09B5\u09BA\u09BB\u09C5\u09C6\u09C9\u09CA\u09CF-\u09D6\u09D8-\u09DB\u09DE\u09E4\u09E5\u09FC-\u0A00\u0A04\u0A0B-\u0A0E\u0A11\u0A12\u0A29\u0A31\u0A34\u0A37\u0A3A\u0A3B\u0A3D\u0A43-\u0A46\u0A49\u0A4A\u0A4E-\u0A50\u0A52-\u0A58\u0A5D\u0A5F-\u0A65\u0A76-\u0A80\u0A84\u0A8E\u0A92\u0AA9\u0AB1\u0AB4\u0ABA\u0ABB\u0AC6\u0ACA\u0ACE\u0ACF\u0AD1-\u0ADF\u0AE4\u0AE5\u0AF2-\u0B00\u0B04\u0B0D\u0B0E\u0B11\u0B12\u0B29\u0B31\u0B34\u0B3A\u0B3B\u0B45\u0B46\u0B49\u0B4A\u0B4E-\u0B55\u0B58-\u0B5B\u0B5E\u0B64\u0B65\u0B78-\u0B81\u0B84\u0B8B-\u0B8D\u0B91\u0B96-\u0B98\u0B9B\u0B9D\u0BA0-\u0BA2\u0BA5-\u0BA7\u0BAB-\u0BAD\u0BBA-\u0BBD\u0BC3-\u0BC5\u0BC9\u0BCE\u0BCF\u0BD1-\u0BD6\u0BD8-\u0BE5\u0BFB-\u0C00\u0C04\u0C0D\u0C11\u0C29\u0C34\u0C3A-\u0C3C\u0C45\u0C49\u0C4E-\u0C54\u0C57\u0C5A-\u0C5F\u0C64\u0C65\u0C70-\u0C77\u0C80\u0C81\u0C84\u0C8D\u0C91\u0CA9\u0CB4\u0CBA\u0CBB\u0CC5\u0CC9\u0CCE-\u0CD4\u0CD7-\u0CDD\u0CDF\u0CE4\u0CE5\u0CF0\u0CF3-\u0D01\u0D04\u0D0D\u0D11\u0D3B\u0D3C\u0D45\u0D49\u0D4F-\u0D56\u0D58-\u0D5F\u0D64\u0D65\u0D76-\u0D78\u0D80\u0D81\u0D84\u0D97-\u0D99\u0DB2\u0DBC\u0DBE\u0DBF\u0DC7-\u0DC9\u0DCB-\u0DCE\u0DD5\u0DD7\u0DE0-\u0DF1\u0DF5-\u0E00\u0E3B-\u0E3E\u0E5C-\u0E80\u0E83\u0E85\u0E86\u0E89\u0E8B\u0E8C\u0E8E-\u0E93\u0E98\u0EA0\u0EA4\u0EA6\u0EA8\u0EA9\u0EAC\u0EBA\u0EBE\u0EBF\u0EC5\u0EC7\u0ECE\u0ECF\u0EDA\u0EDB\u0EE0-\u0EFF\u0F48\u0F6D-\u0F70\u0F98\u0FBD\u0FCD\u0FDB-\u0FFF\u10C6\u10C8-\u10CC\u10CE\u10CF\u1249\u124E\u124F\u1257\u1259\u125E\u125F\u1289\u128E\u128F\u12B1\u12B6\u12B7\u12BF\u12C1\u12C6\u12C7\u12D7\u1311\u1316\u1317\u135B\u135C\u137D-\u137F\u139A-\u139F\u13F5-\u13FF\u169D-\u169F\u16F1-\u16FF\u170D\u1715-\u171F\u1737-\u173F\u1754-\u175F\u176D\u1771\u1774-\u177F\u17DE\u17DF\u17EA-\u17EF\u17FA-\u17FF\u180F\u181A-\u181F\u1878-\u187F\u18AB-\u18AF\u18F6-\u18FF\u191D-\u191F\u192C-\u192F\u193C-\u193F\u1941-\u1943\u196E\u196F\u1975-\u197F\u19AC-\u19AF\u19CA-\u19CF\u19DB-\u19DD\u1A1C\u1A1D\u1A5F\u1A7D\u1A7E\u1A8A-\u1A8F\u1A9A-\u1A9F\u1AAE-\u1AFF\u1B4C-\u1B4F\u1B7D-\u1B7F\u1BF4-\u1BFB\u1C38-\u1C3A\u1C4A-\u1C4C\u1C80-\u1CBF\u1CC8-\u1CCF\u1CF7-\u1CFF\u1DE7-\u1DFB\u1F16\u1F17\u1F1E\u1F1F\u1F46\u1F47\u1F4E\u1F4F\u1F58\u1F5A\u1F5C\u1F5E\u1F7E\u1F7F\u1FB5\u1FC5\u1FD4\u1FD5\u1FDC\u1FF0\u1FF1\u1FF5\u1FFF\u200B-\u200F\u202A-\u202E\u2060-\u206F\u2072\u2073\u208F\u209D-\u209F\u20BB-\u20CF\u20F1-\u20FF\u218A-\u218F\u23F4-\u23FF\u2427-\u243F\u244B-\u245F\u2700\u2B4D-\u2B4F\u2B5A-\u2BFF\u2C2F\u2C5F\u2CF4-\u2CF8\u2D26\u2D28-\u2D2C\u2D2E\u2D2F\u2D68-\u2D6E\u2D71-\u2D7E\u2D97-\u2D9F\u2DA7\u2DAF\u2DB7\u2DBF\u2DC7\u2DCF\u2DD7\u2DDF\u2E3C-\u2E7F\u2E9A\u2EF4-\u2EFF\u2FD6-\u2FEF\u2FFC-\u2FFF\u3040\u3097\u3098\u3100-\u3104\u312E-\u3130\u318F\u31BB-\u31BF\u31E4-\u31EF\u321F\u32FF\u4DB6-\u4DBF\u9FCD-\u9FFF\uA48D-\uA48F\uA4C7-\uA4CF\uA62C-\uA63F\uA698-\uA69E\uA6F8-\uA6FF\uA78F\uA794-\uA79F\uA7AB-\uA7F7\uA82C-\uA82F\uA83A-\uA83F\uA878-\uA87F\uA8C5-\uA8CD\uA8DA-\uA8DF\uA8FC-\uA8FF\uA954-\uA95E\uA97D-\uA97F\uA9CE\uA9DA-\uA9DD\uA9E0-\uA9FF\uAA37-\uAA3F\uAA4E\uAA4F\uAA5A\uAA5B\uAA7C-\uAA7F\uAAC3-\uAADA\uAAF7-\uAB00\uAB07\uAB08\uAB0F\uAB10\uAB17-\uAB1F\uAB27\uAB2F-\uABBF\uABEE\uABEF\uABFA-\uABFF\uD7A4-\uD7AF\uD7C7-\uD7CA\uD7FC-\uF8FF\uFA6E\uFA6F\uFADA-\uFAFF\uFB07-\uFB12\uFB18-\uFB1C\uFB37\uFB3D\uFB3F\uFB42\uFB45\uFBC2-\uFBD2\uFD40-\uFD4F\uFD90\uFD91\uFDC8-\uFDEF\uFDFE\uFDFF\uFE1A-\uFE1F\uFE27-\uFE2F\uFE53\uFE67\uFE6C-\uFE6F\uFE75\uFEFD-\uFF00\uFFBF-\uFFC1\uFFC8\uFFC9\uFFD0\uFFD1\uFFD8\uFFD9\uFFDD-\uFFDF\uFFE7\uFFEF-\uFFFB\uFFFE\uFFFF]/g;
        return str.replace(re, "");
    }
}

//----------------------------------------------
// Add an alert
//----------------------------------------------
function show_alert(map, message) {
    //clear timer
    clearTimeout(alertTimer);
    //fade old one out
    map.find('.alert').fadeOut(function() {
        //add new one
        map.find('.alert .txt').text(message).parent().stop(true, true).show();

        alertTimer = setTimeout(function() {
            map.find('.alert').hide(function() {
                $(this).find('.txt').text(' ');
            });
        }, 5000);
    });
}

//----------------------------------------------
// Get the map settings from the map
//----------------------------------------------
function getMapSettings(mapID) {
    var _map = EE_GMAPS.api('getMap', {
        mapID: mapID
    });

    return settings = {
        "zoom_level": _map.map.zoom,
        "map_type": _map.map.mapTypeId,
        "map_types": _map.map.mapTypeControlOptions.mapTypeIds,
        "map_type_control": _map.map.mapTypeControl.toString(),
        "map_type_control_style": EE_GMAPS.MapTypeControlStyleI[_map.map.mapTypeControlOptions.style],
        "map_type_control_position": EE_GMAPS.ControlPositionI[_map.map.mapTypeControlOptions.position],
        "scroll_wheel": _map.map.scrollwheel.toString(),
        "zoom_control": _map.map.zoomControl.toString(),
        "zoom_control_position": EE_GMAPS.ControlPositionI[_map.map.zoomControlOptions.position],
        "street_view_control": _map.map.streetViewControl.toString(),
        "street_view_control_position": EE_GMAPS.ControlPositionI[_map.map.streetViewControlOptions.position],
        "google_overlay_html": _map.map.googleOverlayHtml,
        "google_overlay_position": _map.map.googleOverlayPosition
    };
}

//----------------------------------------------
// Close an alert
//----------------------------------------------
$(document).on('click', '.alert .close', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).parent().fadeOut(function() {
        $(this).find('.txt').text(' ');
    });
    return false;
});


//----------------------------------------------
// hit enter
//----------------------------------------------
$.fn.enterKey = function(fnc) {
    return this.each(function() {
        $(this).keypress(function(ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if(keycode == '13') {
                fnc.call(this, ev);
            }
        });
    });
};

//----------------------------------------------
// monitor dom changes
//----------------------------------------------
jQuery.fn.watchInserts = function(fn) {
    var obj = this;
    document.watchInserts = setInterval(function() {
        if($(obj.selector)) {
            clearInterval(document.watchInserts);
            fn.call($(obj.selector));
        }
    }, 100);
};

jQuery.fn.watchChanges = function(id, fn) {
    return this.each(function() {
        var self = this;
        var oldVal = $(self).attr(id);
        $(self).data(
            'watch_timer',
            setInterval(function() {
                if($(self).attr(id) !== oldVal) {
                    fn.call(self, id, oldVal, $(self).attr(id));
                    oldVal = $(self).attr(id);
                }
            }, 100)
        );
    });
    return self;
};

jQuery.fn.unwatch = function(id) {
    return this.each(function() {
        clearInterval($(this).data('watch_timer'));
    });
};

//----------------------------------------------
// parse all gmaps fields
//----------------------------------------------*
$(window).load(function() {
    //get the first visible map
    get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));

    //set an monitor EE 2.x
    $('.content_tab').watchChanges('class', function(propName, oldVal, newVal) {
        if(newVal == 'content_tab current') {
            get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
        }
    });

    //set an monitor EE 3.x
    $('.tab-wrap div.tab').watchChanges('class', function(propName, oldVal, newVal) {
        var test = newVal.match(/tab-open/g);
        if(typeof test == 'object' && test != null && test[0] != undefined && test[0] == 'tab-open') {
            get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
        }
    });

    //matrix add event(s)
    $('.hide_field span, .matrix-add, .matrix-firstcell').click(function() {
        get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
    });

    //EE 2.7 Grid add event(s)
    $('.grid-publish .add, .grid-publish .no-results .btn').click(function() {
        get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
    });

    //content elements
    if(typeof ContentElements != 'undefined' && ContentElements != null) {
        ContentElements.bind('gmaps_fieldtype', 'display', function(data) {
            get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
        });
    }
});

//get the map
function get_map(obj) {
    if(obj.length == 1) {

        //make the call
        $.post(obj.data('url'), {
            data: obj.data('data'),
            method: 'gmaps_fieldtype',
            fieldname: obj.data('fieldname'),
            fieldname_input: obj.find('input').prop('name'),
            //zoom: obj.data('zoom'),
            icon_dir: obj.data('icondir'),
            icon_url: obj.data('iconurl'),
            location: obj.data('location'),
            max_markers: obj.data('max-markers'),
            auto_center: obj.data('auto-center'),
            zoom_level: obj.data('zoom-level'),
            show_map_tools: obj.data('show-map-tools'),
            show_search_tools: obj.data('show-search-tools'),
            show_marker_icon: obj.data('show-marker-icon'),
            show_circle_icon: obj.data('show-circle-icon'),
            show_polygon_icon: obj.data('show-polygon-icon'),
            show_polyline_icon: obj.data('show-polyline-icon'),
            show_rectangle_icon: obj.data('show-rectangle-icon'),
            member_group_id: obj.data('group-id'),
            height: obj.data('height'),
            scroll: obj.data('scroll')
        }, function(e) {

            //parse the data
            var data = JSON.parse(Base64.decode(e));

            //place the map data
            obj.before(data.map);

            //set the marked class
            obj.addClass('gmaps_loaded');

            //add the modal to the bottom
            $('body').append(data.modals);

            //fireup!
            init_gmaps(data.map_nr);

            //load next
            if($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0).length == 1) {
                get_map($('.gmaps_field_type_create:visible:not(.gmaps_loaded)').eq(0));
            } else {
                //refresh the map on quick changes
                /*$('.ee_gmap').each(function(){
                 EE_GMAPS.api('refresh', {
                 mapID : $(this).attr('id')
                 });
                 }); */
            }
        });
    }
}


//----------------------------------------------
// Global functions, use for the channel form or safecracker
// to save the settings when ajax is used
//----------------------------------------------
var EE_GMAPS = EE_GMAPS || {};
var EE_GMAPS_FT = EE_GMAPS_FT || {};
(function($) {

    EE_GMAPS_FT.settings = {};

    //----------------------------------------------
    //Add marker function
    //----------------------------------------------
    EE_GMAPS_FT.add_marker = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'lat': '',
            'lng': '',
            'title': '',
            'icon': null,
            'content': null,
            'marker_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.map_nr = $map.data('gmaps-number');
        options.marker_elem = $map.find('.selected_markers');

        //max markers
        if($map.data('max-markers') == '' || typeof (EE_GMAPS.markers[options.mapID]) == 'undefined' || (EE_GMAPS.markers[options.mapID].length < parseInt($map.data('max-markers')))) {

            var map = EE_GMAPS.api('getMap', {
                mapID: options.mapID
            });
            var uuid;

            var markerNumber = EE_GMAPS.api('addMarker', {
                mapID: options.mapID,
                draggable: true,
                lat: options.lat,
                lng: options.lng,
                icon: options.icon,
                title: options.title != '' ? options.title : options.lat + ',' + options.lng,
                infoWindow: {
                    content: options.content
                },
                dragend: function(e) { //drag
                    //update marker information in the list
                    //options.marker_elem.find('li').eq(this.markerNumber).text(e.latLng.lat()+','+e.latLng.lng());
                },
                mouseover: function(e) {
                    options.marker_elem.find('li').eq(this.markerNumber).addClass('hover');
                },
                mouseout: function(e) {
                    options.marker_elem.find('li').eq(this.markerNumber).removeClass('hover');
                }
            });

            //get the uuid from the added marker
            var new_marker = EE_GMAPS.api('getMarker', {
                mapID: options.mapID,
                key: markerNumber
            });

            //Fit the map
            /* EE_GMAPS.api('center', {
             mapID: options.mapID,
             lat: options.lat,
             lng: options.lng
             });*/

            //add marker
            options.marker_elem.append('<li class="marker" data-uuid="' + new_marker.markerUUID + '" data-marker-nr="' + (new_marker.markerNumber) + '"><a class="edit-marker" rel="modal-edit_marker_'+options.map_nr+'"><div class="marker-title">' + options.title + '</div><div class="marker-number">update<span class="marker-nr">' + (new_marker.markerNumber + 1) + '</span></div><span class="edit_marker fa fa-pencil"></span></a><span class="remove_marker fa fa-times"></span></li>');

            //remove default text
            $map.find('.markers_on_map').show();

            //update the sortable
            $(".sortable").sortable("refresh");

            //max?
        } else {
            show_alert($map, 'You have reach your max markers');
        }
    };

    //----------------------------------------------
    //Add Poly function
    //----------------------------------------------
    EE_GMAPS_FT.add_polyline = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'path': [],
            'strokeColor': '#000000',
            'strokeOpacity': 1,
            'strokeWeight': 1,
            'poly_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.poly_elem = $map.find('.selected_polylines');

        var map = EE_GMAPS.api('getMap', {
            mapID: options.mapID
        });
        var uuid;

        var polyNumber = EE_GMAPS.api('addPolyline', {
            mapID: options.mapID,
            strokeColor: options.strokeColor,
            strokeWeight: options.strokeWeight,
            strokeOpacity: options.strokeOpacity,
            path: options.path,
            editable: true,
            mouseover: function(e) {
                options.poly_elem.find('li').eq(this.objectNumber).addClass('hover');
            },
            mouseout: function(e) {
                options.poly_elem.find('li').eq(this.objectNumber).removeClass('hover');
            }
        });

        //get the uuid from the added marker
        var new_poly = EE_GMAPS.api('getPolyline', {
            mapID: options.mapID,
            key: polyNumber
        });

        //add marker
        options.poly_elem.append('<li class="polyline" data-uuid="' + new_poly.objectUUID + '" data-poly-nr="' + (new_poly.objectNumber) + '">Polyline #<span class="poly-nr">' + (new_poly.objectNumber + 1) + '</span><span class="edit_polyline fa fa-pencil"></span><span class="remove_polyline fa fa-times"></span></li>');

        //remove default text
        $map.find('.polylines_on_map').show();
    };
    
    //----------------------------------------------
    //Add Poly function
    //----------------------------------------------
    EE_GMAPS_FT.add_polygon = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'paths': [],
            'strokeColor': '#000000',
            'strokeOpacity': 1,
            'strokeWeight': 1,
            'fillColor': '#000000',
            'fillOpacity': 0.3,
            'poly_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.poly_elem = $map.find('.selected_polygons');

        var map = EE_GMAPS.api('getMap', {
            mapID: options.mapID
        });
        var uuid;

        var polyNumber = EE_GMAPS.api('addPolygon', {
            mapID: options.mapID,
            fillColor: options.fillColor,
            fillOpacity: options.fillOpacity,
            strokeColor: options.strokeColor,
            strokeWeight: options.strokeWeight,
            strokeOpacity: options.strokeOpacity,
            editable: true,
            paths: options.paths,
            mouseover: function(e) {
                options.poly_elem.find('li').eq(this.objectNumber).addClass('hover');
            },
            mouseout: function(e) {
                options.poly_elem.find('li').eq(this.objectNumber).removeClass('hover');
            }
        });

        //get the uuid from the added marker
        var new_poly = EE_GMAPS.api('getPolygon', {
            mapID: options.mapID,
            key: polyNumber
        });

        //add marker
        options.poly_elem.append('<li class="polygon" data-uuid="' + new_poly.objectUUID + '" data-poly-nr="' + (new_poly.objectNumber) + '">Polygon #<span class="poly-nr">' + (new_poly.objectNumber + 1) + '</span><span class="edit_polygon fa fa-pencil"></span><span class="remove_polygon fa fa-times"></span></li>');

        //remove default text
        $map.find('.polygons_on_map').show();
    };

    //----------------------------------------------
    //Add circle function
    //----------------------------------------------
    EE_GMAPS_FT.add_circle = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'radius': '',
            'lat': '',
            'lng': '',
            'strokeColor': '#000000',
            'strokeOpacity': 1,
            'strokeWeight': 1,
            'fillColor': '#000000',
            'fillOpacity': 0.3,
            'circle_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.circle_elem = $map.find('.selected_circles');

        var map = EE_GMAPS.api('getMap', {
            mapID: options.mapID
        });
        var uuid;

        var circleNumber = EE_GMAPS.api('addCircle', {
            mapID: options.mapID,
            fillColor: options.fillColor,
            fillOpacity: options.fillOpacity,
            strokeColor: options.strokeColor,
            strokeWeight: options.strokeWeight,
            strokeOpacity: options.strokeOpacity,
            radius: options.radius,
            lat: options.lat,
            lng: options.lng,
            editable: true,
            mouseover: function(e) {
                options.circle_elem.find('li').eq(this.objectNumber).addClass('hover');
            },
            mouseout: function(e) {
                options.circle_elem.find('li').eq(this.objectNumber).removeClass('hover');
            }
        });

        //get the uuid from the added marker
        var new_circle = EE_GMAPS.api('getCircle', {
            mapID: options.mapID,
            key: circleNumber
        });

        //add marker
        options.circle_elem.append('<li class="circle" data-uuid="' + new_circle.objectUUID + '" data-circle-nr="' + (new_circle.objectNumber) + '">Circle #<span class="circle-nr">' + (new_circle.objectNumber + 1) + '</span><span class="edit_circle fa fa-pencil"></span><span class="remove_circle fa fa-times"></span></li>');

        //remove default text
        $map.find('.circles_on_map').show();
    };

    //----------------------------------------------
    //Add rectangle function
    //----------------------------------------------
    EE_GMAPS_FT.add_rectangle = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'bounds': '',
            'strokeColor': '#000000',
            'strokeOpacity': 1,
            'strokeWeight': 1,
            'fillColor': '#000000',
            'fillOpacity': 0.3,
            'rectangle_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.rectangle_elem = $map.find('.selected_rectangles');
        
        var map = EE_GMAPS.api('getMap', {
            mapID: options.mapID
        });
        var uuid;

        var northEast = options.bounds.getNorthEast();
        var southWest = options.bounds.getSouthWest();

        var rectangleNumber = EE_GMAPS.api('addRectangle', {
            mapID: options.mapID,
            fillColor: options.fillColor,
            fillOpacity: options.fillOpacity,
            strokeColor: options.strokeColor,
            strokeWeight: options.strokeWeight,
            strokeOpacity: options.strokeOpacity,
            bounds: [[southWest.lat(), southWest.lng()], [northEast.lat(), northEast.lng()]],
            editable: true,
            mouseover: function(e) {
                options.rectangle_elem.find('li').eq(this.objectNumber).addClass('hover');
            },
            mouseout: function(e) {
                options.rectangle_elem.find('li').eq(this.objectNumber).removeClass('hover');
            }
        });

        //get the uuid from the added marker
        var new_rectangle = EE_GMAPS.api('getRectangle', {
            mapID: options.mapID,
            key: rectangleNumber
        });

        //add marker
        options.rectangle_elem.append('<li class="rectangle" data-uuid="' + new_rectangle.objectUUID + '" data-rectangle-nr="' + (new_rectangle.objectNumber) + '">Rectangle #<span class="rectangle-nr">' + (new_rectangle.objectNumber + 1) + '</span><span class="edit_rectangle fa fa-pencil"></span><span class="remove_rectangle fa fa-times"></span></li>');

        //remove default text
        $map.find('.rectangles_on_map').show();
    };

    //----------------------------------------------
    //remove a marker
    //----------------------------------------------
    EE_GMAPS_FT.remove_marker = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'marker_number': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.marker_elem = $map.find('.selected_markers');
        
        EE_GMAPS.api('removeMarker', {
            mapID: options.mapID,
            key: options.marker_number
        });

        options.marker_elem.find('li').eq(options.marker_number).fadeOut(function() {
            $(this).remove();

            //update the list with markers and cache
            EE_GMAPS_FT.update_markers_position($map);
        });

        //remove default text
        if(EE_GMAPS.markers[options.mapID].length == 0) {
            $map.find('.markers_on_map').fadeOut();
        }
    };

    //----------------------------------------------
    //remove a polyline
    //----------------------------------------------
    EE_GMAPS_FT.remove_polyline = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'poly_number': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.poly_elem = $map.find('.selected_polylines');

        EE_GMAPS.api('removePolyline', {
            mapID: options.mapID,
            key: options.poly_number
        });

        options.poly_elem.find('li').eq(options.poly_number).fadeOut(function() {
            $(this).remove();
            //update the list with markers and cache
            EE_GMAPS_FT.update_polyline_position($map);
        });

        //remove default text
        if(EE_GMAPS.polylines[options.mapID].length == 0) {
            $map.find('.polylines_on_map').fadeOut();
        }
    };

    //----------------------------------------------
    //remove a polygon
    //----------------------------------------------
    EE_GMAPS_FT.remove_polygon = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'poly_number': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.poly_elem = $map.find('.selected_polygons');

        EE_GMAPS.api('removePolygon', {
            mapID: options.mapID,
            key: options.poly_number
        });

        options.poly_elem.find('li').eq(options.poly_number).fadeOut(function() {
            $(this).remove();
            //update the list with markers and cache
            EE_GMAPS_FT.update_polygon_position($map);
        });

        //remove default text
        if(EE_GMAPS.polygons[options.mapID].length == 0) {
            $map.find('.polygons_on_map').fadeOut();
        }
    };

    //----------------------------------------------
    //remove a circle
    //----------------------------------------------
    EE_GMAPS_FT.remove_circle = function(options, $map) {
        //merge default settings with given settings
        var options = $.extend({
            'circle_number': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.circle_elem = $map.find('.selected_circles');

        EE_GMAPS.api('removeCircle', {
            mapID: options.mapID,
            key: options.circle_number
        });

        options.circle_elem.find('li').eq(options.circle_number).fadeOut(function() {
            $(this).remove();
            //update the list with markers and cache
            EE_GMAPS_FT.update_circle_position($map);
        });

        //remove default text
        if(EE_GMAPS.circles[options.mapID].length == 0) {
            $map.find('.circles_on_map').fadeOut();
        }
    };

    //----------------------------------------------
    //remove a circle
    //----------------------------------------------
    EE_GMAPS_FT.remove_rectangle = function(options, $map) {

        //merge default settings with given settings
        var options = $.extend({
            'rectangle_number': '',
            'rectangle_elem': ''
        }, options);

        //set the map ID
        options.mapID = $map.data('mapid');
        options.rectangle_elem = $map.find('.selected_rectangles');

        EE_GMAPS.api('removeRectangle', {
            mapID: options.mapID,
            key: options.rectangle_number
        });

        options.rectangle_elem.find('li').eq(options.rectangle_number).fadeOut(function() {
            $(this).remove();
            //update the list with markers and cache
            EE_GMAPS_FT.update_rectangle_position($map);
        });

        //remove default text
        if(EE_GMAPS.rectangles[options.mapID].length == 0) {
            $map.find('.rectangles_on_map').fadeOut();
        }
    };

    //----------------------------------------------
    //Update marker position, mostly comes from the sortable
    //----------------------------------------------
    EE_GMAPS_FT.update_markers_position = function($map) {
        //defaults
        var options = {};

        //get object
        options.obj = $map.find('.selected_markers');

        //set the map ID
        options.mapID = $map.data('mapid');

        var new_marker_cache = [];

        //update the markers
        options.obj.find('li').each(function(k, v) {

            new_marker_cache.push($(this).data('uuid'));

            //update the sortable
            $(this).data('marker-nr', (k + 1));
            $(this).find('span.marker-nr').text((k + 1));
        });

        EE_GMAPS.updateMarkerCache(options.mapID, new_marker_cache);

        //update the sortable
        $map.find(".sortable").sortable("refresh");
    };

    //----------------------------------------------
    //Update marker position, mostly comes from the sortable
    //----------------------------------------------
    EE_GMAPS_FT.update_polyline_position = function($map) {
        //default
        var options = {};

        //get obj
        options.obj = $map.find('.selected_polylines');

        //set the map ID
        options.mapID = $map.data('mapid');

        var new_poly_cache = [];

        //update the markers
        options.obj.find('li').each(function(k, v) {

            new_poly_cache.push($(this).data('uuid'));

            //update the sortable
            $(this).data('poly-nr', (k + 1));
            $(this).find('span.poly-nr').text((k + 1));
        });

        EE_GMAPS.updateArtOverlayCache(options.mapID, new_poly_cache, 'polyline');
    };

    //----------------------------------------------
    //Update marker position, mostly comes from the sortable
    //----------------------------------------------
    EE_GMAPS_FT.update_polygon_position = function($map) {
        //default
        var options = {};

        //get obj
        options.obj = $map.find('.selected_polygons');

        //set the map ID
        options.mapID = $map.data('mapid');

        var new_poly_cache = [];

        //update the markers
        options.obj.find('li').each(function(k, v) {

            new_poly_cache.push($(this).data('uuid'));

            //update the sortable
            $(this).data('poly-nr', (k + 1));
            $(this).find('span.poly-nr').text((k + 1));
        });

        EE_GMAPS.updateArtOverlayCache(options.mapID, new_poly_cache, 'polygon');
    };

    //----------------------------------------------
    //Update marker position, mostly comes from the sortable
    //----------------------------------------------
    EE_GMAPS_FT.update_circle_position = function($map) {
        //default
        var options = {};

        //get obj
        options.obj = $map.find('.selected_circles');

        //set the map ID
        options.mapID = $map.data('mapid');

        var new_circle_cache = [];

        //update the markers
        options.obj.find('li').each(function(k, v) {

            new_circle_cache.push($(this).data('uuid'));

            //update the sortable
            $(this).data('circle-nr', (k + 1));
            $(this).find('span.circle-nr').text((k + 1));
        });

        EE_GMAPS.updateArtOverlayCache(options.mapID, new_circle_cache, 'circle');
    };

    //----------------------------------------------
    //Update marker position, mostly comes from the sortable
    //----------------------------------------------
    EE_GMAPS_FT.update_rectangle_position = function($map) {
        //default
        var options = {};

        //get obj
        options.obj = $map.find('.selected_rectangles');

        //set the map ID
        options.mapID = $map.data('mapid');

        var new_rectangle_cache = [];

        //update the markers
        options.obj.find('li').each(function(k, v) {

            new_rectangle_cache.push($(this).data('uuid'));

            //update the sortable
            $(this).data('rectangle-nr', (k + 1));
            $(this).find('span.rectangle-nr').text((k + 1));
        });

        EE_GMAPS.updateArtOverlayCache(options.mapID, new_rectangle_cache, 'rectangle');
    };
    
    //----------------------------------------------
    // Save the settings
    //----------------------------------------------
    EE_GMAPS_FT.save_settings = function($map) {
        //var $map = $map_holder.find('.ee_gmap')
        var map_nr = $map.data('gmaps-number');
        var array_key = 'ee_gmap_' + map_nr;

        //the markers
        EE_GMAPS_FT.settings[0] = {};
        EE_GMAPS_FT.settings[0].markers = [];
        if(EE_GMAPS.markers[array_key] != undefined) {
            $.each(EE_GMAPS.markers[array_key], function(k, v) {
                var setting = {};
                setting.lat = v.marker.position.lat();
                setting.lng = v.marker.position.lng();
                setting.title = v.marker.title;
                setting.icon = v.marker.icon;
                setting.content = v.marker.infoWindow.content;

                EE_GMAPS_FT.settings[0].markers.push(setting);
            });
        }
        //polylines
        EE_GMAPS_FT.settings[0].polylines = [];
        if(EE_GMAPS.polylines[array_key] != undefined) {
            $.each(EE_GMAPS.polylines[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.path = EE_GMAPS.createlatLngArray(v.object.getPath().getArray());

                EE_GMAPS_FT.settings[0].polylines.push(setting);
            });
        }

        //polylines
        EE_GMAPS_FT.settings[0].polygons = [];
        if(EE_GMAPS.polygons[array_key] != undefined) {
            $.each(EE_GMAPS.polygons[array_key], function(k, v) {
                var setting = {};
                setting.strokeColor = v.object.strokeColor;
                setting.strokeOpacity = v.object.strokeOpacity;
                setting.strokeWeight = v.object.strokeWeight;
                setting.fillColor = v.object.fillColor;
                setting.fillOpacity = v.object.fillOpacity;
                setting.paths = EE_GMAPS.createlatLngArray(v.object.getPath().getArray());

                EE_GMAPS_FT.settings[0].polygons.push(setting);
            });
        }

        //the map settings
        EE_GMAPS_FT.settings[0].map = {};

        //get the settings from the map
        EE_GMAPS_FT.settings[0]['map'] = getMapSettings(array_key);

        $('input[name="' + $map.data('fieldname_input') + '"]').val(Base64.encode(JSON.stringify(EE_GMAPS_FT.settings)));
    }

}(jQuery));