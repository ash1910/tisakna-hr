/*
 * EE_GMaps.js
 * http://reinos.nl
 *
 * Map types (Toner, Terrain and Watercolor) are Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under CC BY SA
 *
 * @package            Gmaps for EE3
 * @author             Rein de Vries (info@reinos.nl)
 * @copyright          Copyright (c) 2013 Rein de Vries
 * @license 		   http://reinos.nl/commercial-license
 * @link               http://reinos.nl/add-ons/gmaps
 */

var EE_GMAPS = EE_GMAPS || {};

//check if jQuery is loaded
EE_GMAPS.jqueryLoaded = function() {
    if(typeof jQuery == 'undefined') {
        console.info('GMAPS ERROR: jQuery is not loaded. Make sure Jquery is loaded before Gmaps is called');
    }
}();

(function($) {

    //default lat lng values
    EE_GMAPS.def = {};
    EE_GMAPS.vars = {}; //default vars, dynamic created by this file
    EE_GMAPS.def.lat = EE_GMAPS.def.Lat = -12.043333;
    EE_GMAPS.def.lng = EE_GMAPS.def.Lng = -77.028333;
    EE_GMAPS.def.circle = {
        'fit_circle': true,
        'stroke_color': '#BBD8E9',
        'stroke_opacity': 1,
        'stroke_weight': 3,
        'fill_color': '#BBD8E9',
        'fill_opacity': 0.6,
        'radius': 1000
    };

    //create the diff types of arrays
    var arrayTypes = ['polylines', 'polygons', 'circles', 'rectangles', 'markers'];
    $.each(arrayTypes, function(k, v) {
        EE_GMAPS[v] = [];
    });

    //marker holder
    EE_GMAPS.markers_address_based = {};
    EE_GMAPS.markers_key_based = {};

    //latlng holder
    EE_GMAPS.latlngs = [];

    // bound stuff, will handle by the API
    EE_GMAPS.fitRoutesOnMap = [];
    EE_GMAPS.fitMarkersOnMap = [];
    EE_GMAPS.fitCirclesOnMap = [];
    EE_GMAPS.fitRectanglesOnMap = [];
    EE_GMAPS.fitPolylinesOnMap = [];
    EE_GMAPS.fitPolygonsOnMap = [];

    //the map
    EE_GMAPS._map_ = [];

    //fitMap default to false
    EE_GMAPS.fitTheMap = false;

    //ready function, when this file is totally ready
    var funcList = [];
    EE_GMAPS.runAll = function() {
        var len = funcList.length,
            index = 0;

        for(; index < len; index++) {
            funcList[index].call();
        }

    };
    EE_GMAPS.ready = function(inFunc) {
        funcList.push(inFunc);
    };

    //cache the native Google lists and invert them also
    EE_GMAPS.cacheLists = function(lists) {
        $.each(lists, function(i, name) {
            var list = google.maps[name];
            var iList = {};
            $.each(list, function(k, v) {
                iList[v] = k;
            });

            EE_GMAPS[name] = list;
            EE_GMAPS[name + 'I'] = iList;
        });
    };

    //cache the Google maps options and also reverse them
    EE_GMAPS.cacheLists(['MapTypeControlStyle', 'ControlPosition']);

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setStreetViewPanorama = function(options) {
        //merge default settings with given settings
        options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'width': '',
            'height': '',
            'address_control': true,
            'click_to_go': true,
            'disable_double_click_zoom': false,
            'enable_close_button': true,
            'image_date_control': true,
            'links_control': true,
            'pan_control': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'checkaround': 50,
            'visible': true,
            'pov': {},
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = GMaps.createPanorama({
            el: options.selector,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            addressControl: options.address_control,
            clickToGo: options.click_to_go,
            disableDoubleClickZoom: options.disable_double_click_zoom,
            enableCloseButton: options.enable_close_button,
            imageDateControl: options.image_date_control,
            linksControl: options.links_control,
            panControl: options.pan_control,
            pov: options.pov,
            scrollwheel: options.scroll_wheel,
            visible: options.visible,
            zoomControl: options.zoom_control,
            enableNewStyle: options.enable_new_style,
            checkaround: options.checkaround
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function() {
                map.refresh();
            });
        }
    };

    // ----------------------------------------------------------------------------------

    EE_GMAPS.setMap = function(options) {
        //merge default settings with given settings
        options = $.extend({
            'selector': '',
            'map_type': '',
            'map_types': [],
            'zoom': '',
            'max_zoom': null,
            'zoom_override': false,
            'center': '',
            'width': '',
            'height': '',
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'zoom_control_position': '',
            'map_type_control': true,
            'map_type_control_style': '',
            'map_type_control_position': '',
            'scale_control': true,
            'street_view_control': true,
            'street_view_control_position': '',
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true,
            'show_marker_cluster': true,
            'marker_cluster_image_path': '',
            'markerClusterer': function(){},
            'gesture_handling': 'auto'
        }, options);

        //default vars
        var lat, lng, map;

        //set default lang, lng
        lat = '34.5133';
        lng = '-94.1629';

        //turn back the address, input_address and latlng
        options.center = EE_GMAPS.parseToJsArray(options.center);

        //override center by setting the center or zoom level
        if(options.center !== undefined && options.center != '') {
            options.center = options.center.toString().split(',');

            var center = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray([options.center]));

            lat = center[0].lat();
            lng = center[0].lng();
        }

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //is this a static map?
        // @todo should work with the data
        // if(options.static) {
        //     EE_GMAPS.setStaticMap({
        //         'selector': options.selector,
        //         'latlng': options.latlng,
        //         'map_type': options.map_type,
        //         'width': options.width.match('%') ? '' : options.width,
        //         'height': options.height.match('%') ? '' : options.height,
        //         'zoom': options.zoom,
        //         'marker': options.marker.show
        //     });
        //     return true;
        // }

        // Clustering
        if(options.show_marker_cluster) {

            options.markerClusterer = function(map) {
                return new MarkerClusterer(map, [], {
                    gridSize: options.marker_cluster_grid_size,
                    maxZoom: 10,
                    styles: options.marker_cluster_style,
                    imagePath: options.marker_cluster_image_path
                });
            }
        }

        //fire up the map
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            lat: lat,
            lng: lng,
            zoom: options.zoom,
            maxZoom: options.max_zoom,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            mapType: EE_GMAPS.setMapTypes(options.map_type),
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                mapTypeIds: EE_GMAPS.setMapTypes(options.map_types),
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style,
            markerClusterer: options.markerClusterer,
            gestureHandling: options.gesture_handling
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map, {
            tag: options.panoramio_tag
        });

        if(options.zoom_override) {
            map.setZoom(options.zoom);
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function() {
                map.refresh();
            });
        }

        //callback function when all things is ready
        setTimeout(function(){
            EE_GMAPS.runAll();
        }, 500);

        //set the markers
        //EE_GMAPS.markers = map.markers;
        //EE_GMAPS.saveMarkers(options.selector, map.markers, options.input_address, options.keys);
    };

    //----------------------------------------------------------------------------------------------------------//
    // Private functions //
    //----------------------------------------------------------------------------------------------------------//

    //get latlong based on address
    EE_GMAPS.setStaticMap = function(options) {
        //merge default settings with given settings
        options = $.extend({
            'selector': '',
            'latlng': '',
            'map_type': '',
            'width': '',
            'height': '',
            'zoom': '',
            'marker': true,
            'polygon': false,
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'fill_color': '',
            'fill_opacity': ''
        }, options);

        // create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));
        var _polygon_latlng = [];
        var _markers = [];
        var bounds = new google.maps.LatLngBounds();

        //set the bounds and the polygon latlng
        $.each(latlng, function(k, v) {
            bounds.extend(v);

            _polygon_latlng.push([v.lat(), v.lng()]);

            if(options.marker.show) {
                _markers.push({
                    lat: v.lat(),
                    lng: v.lng()
                });
            }
        });

        //get the center
        var center = bounds.getCenter();

        //size
        if(options.width == '' || options.height) {
            options.width = '630';
            options.height = '300';
        }

        //set var
        var url;

        if(options.polygon) {
            //create map
            url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center[0],
                lng: center[1],
                zoom: EE_GMAPS.getZoom(options.width, latlng),
                maptype: EE_GMAPS.setMapTypes(options.map_type),
                polyline: {
                    path: _polygon_latlng,
                    strokeColor: options.stroke_color,
                    strokeOpacity: options.stroke_opacity,
                    strokeWeight: options.stroke_weight,
                    fillColor: options.fill_color
                },
                markers: _markers
            });

            //geocoding
        } else {
            //create map
            url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center.lat(),
                lng: center.lng(),
                zoom: options.zoom,
                maptype: EE_GMAPS.setMapTypes(options.map_type),
                markers: _markers
            });
        }

        //place the image
        $(options.selector).html('<img src="' + url + '" alt="Gmaps map from ' + options.address + '" title="Static Gmaps" width="' + options.width + '" height="' + options.height + '" />');
    };

    //add a google like overlay like the iframe
    EE_GMAPS.addGoogleOverlay = function(map, options, direct) {

        var latlng, marker_object;

        //default
        map.map.googleOverlay = false;
        map.map.googleOverlayPosition = 'left';
        map.map.googleOverlayHtml = '';

        //do the checks
        if(options.latlng != undefined && options.latlng[0] != undefined) {
            latlng = options.latlng[0].split(',');
            marker_object = new google.maps.LatLng(latlng[0], latlng[1]);

            options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
            options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
            options.overlay_html = options.overlay_html.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }

        if(options.overlay_html != '') {
            if(direct) {
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; ' + options.overlay_position + ': 0px; top: 0px;"><div style="' + style + '" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">' + options.overlay_html + '</div></div></div>');
                }
            }

            map.on('tilesloaded', function() {
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; ' + options.overlay_position + ': 0px; top: 0px;"><div style="' + style + '" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">' + options.overlay_html + '</div></div></div>');
                }
            });

            map.map.googleOverlay = true;
            map.map.googleOverlayPosition = options.overlay_position;
            map.map.googleOverlayHtml = options.overlay_html;
        }
    };

    //remove a google like overlay like the iframe
    EE_GMAPS.removeGoogleOverlay = function(map, selector) {
        map.map.googleOverlay = false;
        map.map.googleOverlayPosition = '';
        map.map.googleOverlayHtml = '';

        $(selector).find('.google-like-overlay-position').remove();
    };

    //update a google like overlay like the iframe
    EE_GMAPS.updateGoogleOverlay = function(map, options) {
        //no overlay?
        if($(options.selector).find('.google-like-overlay-position').length == 0) {
            EE_GMAPS.addGoogleOverlay(map, options, true);
        }

        if(options.overlay_html != undefined) {
            if(options.overlay_html == '') {
                EE_GMAPS.removeGoogleOverlay(map, options.selector);
            } else {
                $(options.selector).find('.google-like-overlay-content').html(options.overlay_html);
            }
            map.map.googleOverlayHtml = options.overlay_html;
        }

        if(options.overlay_position != undefined) {
            $(options.selector).find('.google-like-overlay-position').css('left', '').css('right', '');
            $(options.selector).find('.google-like-overlay-position').css(options.overlay_position, '0px');
            map.map.googleOverlayPosition = options.overlay_position;
        }
    };

    //add mapTypes
    EE_GMAPS.addCustomMapTypes = function(map, map_types) {
        $.each(map_types, function(k, v) {
            switch(v) {
                //Openstreetmap
                case 'osm':
                    map.addMapType("osm", {
                        getTileUrl: function(coord, zoom) {
                            return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                        },
                        tileSize: new google.maps.Size(256, 256),
                        name: "OpenStreetMap",
                        maxZoom: 18
                    });
                    break;

                //Cloudmade
                //case 'cloudmade':
                //    map.addMapType("cloudmade", {
                //        getTileUrl: function (coord, zoom) {
                //            return "http://b.tile.cloudmade.com/8ee2a50541944fb9bcedded5165f09d9/1/256/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                //        },
                //        tileSize: new google.maps.Size(256, 256),
                //        name: "CloudMade",
                //        maxZoom: 18
                //    });
                //    break;

                //StamenMapType: Toner
                case 'stamentoner':
                    map.addMapType("stamentoner", {
                        getTileUrl: function(coord, zoom) {
                            return "http://tile.stamen.com/toner/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                        },
                        tileSize: new google.maps.Size(256, 256),
                        name: "StamenToner",
                        maxZoom: 18
                    });
                    break;

                //StamenMapType: watercolor
                case 'stamenwatercolor':
                    map.addMapType("stamenwatercolor", {
                        getTileUrl: function(coord, zoom) {
                            return "http://tile.stamen.com/watercolor/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                        },
                        tileSize: new google.maps.Size(256, 256),
                        name: "WaterColor",
                        maxZoom: 18
                    });
                    break;

                case 'stamenterrain':
                    map.addMapType("stamenterrain", {
                        getTileUrl: function(coord, zoom) {
                            return "http://tile.stamen.com/terrain/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                        },
                        tileSize: new google.maps.Size(256, 256),
                        name: "StamenTerrain",
                        maxZoom: 18
                    });
                    break;
            }

        });
    };

    //Set custom maps
    EE_GMAPS.addCustomMapType = function(map, map_type) {
        switch(map_type) {
            //Openstreetmap
            case 'osm':
                map.setMapTypeId("osm");
                break;

            //    //Cloudmade
            //case 'cloudmade':
            //    map.setMapTypeId("cloudmade");
            //    break;

            //StamenMapType: Toner
            case 'stamentoner':
                map.setMapTypeId("stamentoner");
                break;

            //StamenMapType: watercolor
            case 'stamenwatercolor':
                map.setMapTypeId("stamenwatercolor");
                break;

            //StamenMapType: watercolor
            case 'stamenterrain':
                map.setMapTypeId("stamenterrain");
                break;
        }
    };

    //get latlong based on address
    EE_GMAPS.latLongAddress = function(addresses, callback) {

        var latLongAdresses = [];
        var latLongObject = [];

        $.each(addresses, function(key, val) {
            GMaps.geocode({
                address: val,
                callback: function(results, status) {
                    //is there any result
                    if(status == "OK") {
                        latLongAdresses[key] = results[0].geometry.location;
                        latLongObject[key] = results[0];
                    }

                    //return the results
                    if(key == (addresses.length - 1)) {
                        if(callback && typeof (callback) == "function") {
                            //settimeout because the load error
                            setTimeout(function() {
                                callback(latLongAdresses, latLongObject);
                            }, 200);
                        }
                    }
                }
            });
        });
    };

    //flatten an polygon result
    EE_GMAPS.flatten_polygon_result = function(polygon) {
        var new_array = [];

        polygon.getArray().forEach(function(v) {
            new_array.push(v.getArray());
        });

        return _.flatten(new_array);
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.getCenterLatLng = function(latlong) {
        var lat = [];
        var lng = [];

        $.each(latlong, function(key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //set the center x and y points
        var center_lat = lat.gmaps_min() + ((lat.gmaps_max() - lat.gmaps_min()) / 2);
        var center_lng = lng.gmaps_min() + ((lng.gmaps_max() - lng.gmaps_min()) / 2);

        return [center_lat, center_lng];
    };

    //set the styled maps for a map
    EE_GMAPS.setStyledMap = function(styledArray, map) {
        if(Object.keys(styledArray).length > 0) {
            map.addStyle({
                styledMapName: "Styled Map",
                styles: styledArray,
                mapTypeId: "map_style"
            });
            map.setStyle("map_style");
        }
    };

    //set the traffic layer
    EE_GMAPS.setTraffic = function(show_layer, map) {
        if(show_layer) {
            map.addLayer('traffic');
        }
    };

    //set the Transit layer
    EE_GMAPS.setTransit = function(show_layer, map) {
        if(show_layer) {
            map.addLayer('transit');
        }
    };

    //set the Bicycling layer
    EE_GMAPS.setBicycling = function(show_layer, map) {
        if(show_layer) {
            map.addLayer('bicycling');
        }
    };

    //set the Panoramio layer
    EE_GMAPS.setPaoramio = function(show_layer, map, options) {
        if(show_layer) {
            map.addLayer('panoramio', {
                filter: options.tag
            });
        }
    };

    //calculate the zoom
    EE_GMAPS.getZoom = function(map_width, latlong) {
        map_width = map_width / 1.74;
        var lat = [];
        var lng = [];

        $.each(latlong, function(key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //calculate the distance
        var dist = (6371 * Math.acos(Math.sin(lat.gmaps_min() / 57.2958) * Math.sin(lat.gmaps_max() / 57.2958) +
            (Math.cos(lat.gmaps_min() / 57.2958) * Math.cos(lat.gmaps_max() / 57.2958) * Math.cos((lng.gmaps_max() / 57.2958) - (lng.gmaps_min() / 57.2958)))));

        //calculate the zoom
        var zoom = Math.floor(8 - Math.log(1.6446 * dist / Math.sqrt(2 * (map_width * map_width))) / Math.log(2)) - 1;

        return zoom;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createWaypoints = EE_GMAPS.createAddressWaypoints = function(waypoints) {
        var points = [];
        $.each(waypoints, function(key, val) {
            points.push({
                location: val,
                stopover: false
            });
        });
        return points;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createLatLngWaypoints = function(waypoints) {
        var points = [];
        $.each(waypoints, function(key, val) {
            val = val.split(',');
            points.push({
                location: new google.maps.LatLng(val[0], val[1]),
                stopover: false
            });
        });
        return points;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createlatLngArray = function(latlng) {
        var points = [];
        $.each(latlng, function(key, val) {
            points.push([val.lat(), val.lng()]);
        });

        return points;
    };

    //reparse an array that whas generated by php
    EE_GMAPS.reParseLatLngArray = function(array) {
        var points = [];
        $.each(array, function(key, val) {
            points.push([parseFloat(val[0]), parseFloat(val[1])]);
        });
        return points;
    };

    //convert array to latlng 
    EE_GMAPS.arrayToLatLng = function(coords) {
        var new_coords = [];
        $.each(coords, function(key, val) {
            if(typeof val == 'string') {
                val = val.split(',');
            }

            new_coords.push(new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1]))));
        });
        return new_coords;
    };

    //convert string to latlng
    EE_GMAPS.stringToLatLng = function(coords) {
        var val = coords.split(',');
        var new_coords = new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1])));
        return new_coords;
    };

    //remove empty values
    EE_GMAPS.cleanArray = function(arr) {
        return $.grep(arr, function(n) {
            return (n);
        });
    };

    //Parse base64 string to js array
    EE_GMAPS.parseToJsArray = function(string, split) {
        string = Base64.decode(string);
        if(typeof string == 'string') {

            //empty?
            if(string == '[]') {
                return '';
            }

            //disabled because some language give error on this
            //russia for example
            //https://devot-ee.com/add-ons/support/gmaps/viewthread/15970
            // string = decodeURIComponent(escape(string));
            
            if(split || split == undefined) {
                return string.split('|');
            } else {
                return string;
            }
        }
        return '';
    };

    //set the marker Icon 
    EE_GMAPS.setMarkerIcon = function(marker_icon, marker_icon_default, k) {

        //set vars
        var new_marker_icon, url, size, origin, anchor;

        //array of values, mostly geocoding
        if(typeof marker_icon.url == 'object' && marker_icon.url.length > 0) {
            url = marker_icon.url[k] != undefined ? marker_icon.url[k] : marker_icon_default.url;
            size = marker_icon.size[k] != undefined ? marker_icon.size[k] : marker_icon_default.size;
            size = size.split(',');
            origin = marker_icon.origin[k] != undefined ? marker_icon.origin[k] : marker_icon_default.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor[k] != undefined ? marker_icon.anchor[k] : marker_icon_default.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if(url != '') {
                new_marker_icon.url = url;
                if(size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if(origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if(anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default, all others beside geocoding
        } else if(marker_icon_default == undefined) {
            url = marker_icon.url;
            size = marker_icon.size;
            size = size.split(',');
            origin = marker_icon.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if(url != '') {
                new_marker_icon.url = url;
                if(size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if(origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if(anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default marker icon, mostly geocoding
        } else {
            if(marker_icon_default.url != '') {
                url = marker_icon_default.url;
                size = marker_icon_default.size;
                size = size.split(',');
                origin = marker_icon_default.origin;
                origin = origin.split(',');
                anchor = marker_icon_default.anchor;
                anchor = anchor.split(',');

                //set the object
                new_marker_icon = {};
                if(url != '') {
                    new_marker_icon.url = url;
                    if(size != '') {
                        new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                    }
                    if(origin != '') {
                        new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                    }
                    if(anchor != '') {
                        new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                    }
                } else {
                    new_marker_icon = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon = '';
            }
        }
        return new_marker_icon;
    };

    //set the marker shape 
    EE_GMAPS.setMarkerShape = function(marker_icon_shape, marker_icon_shape_default, k) {

        //set vars
        var new_marker_icon_shape, coord, type;

        //array of values, mostly geocoding
        if(typeof marker_icon_shape.coord == 'object' && marker_icon_shape.coord.length > 0) {
            coord = marker_icon_shape.coord[k] != undefined ? marker_icon_shape.coord[k] : marker_icon_shape_default.coord;
            type = marker_icon_shape.type[k] != undefined ? marker_icon_shape.type[k] : marker_icon_shape_default.type;

            //set the object
            new_marker_icon_shape = {};
            if(type != '') {
                new_marker_icon_shape.type = type;
            }
            if(coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default, all others beside geocoding
        } else if(marker_icon_shape_default == undefined) {
            coord = marker_icon_shape.coord;
            type = marker_icon_shape.type;

            //set the object
            new_marker_icon_shape = {};
            if(type != '') {
                new_marker_icon_shape.type = type;
            }
            if(coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default shape, mostly geocoding
        } else {
            if(marker_icon_shape_default.url != '') {
                coord = marker_icon_shape_default.coord;
                type = marker_icon_shape_default.type;

                //set the object
                new_marker_icon_shape = {};
                if(type != '') {
                    new_marker_icon_shape.type = type;
                }
                if(coord != '') {
                    new_marker_icon_shape.coord = coord.split(',');
                } else {
                    new_marker_icon_shape = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon_shape = '';
            }
        }
        return new_marker_icon_shape;
    };

    //set the infowindow content
    //and replace the tokens
    EE_GMAPS.setInfowindowContent = function(content, tokens, marker_object) {
        content = content || '';

        if(content != undefined || content) {
            $.each(tokens, function(k, v) {
                content = content.gmaps_replaceAll('[' + k + ']', v);
            });

            //try creating the urls
            content = content.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
            content = content.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
            content = content.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }

        //set content to null when empty
        content = content != '' ? content : null;

        return content;
    };

    //remove empty properties from an object
    EE_GMAPS.cleanObject = function(object) {
        object = object || {};

        object = gmaps_remove_empty_values(object);

        return object;
    };

    //create the infobox
    EE_GMAPS.addInfobox = function(options, map, marker, marker_number){
        if(options.marker.infobox.content !== '') {
            var content = options.marker.infobox.content.split('|');
            var location = options.address[marker_number] ? options.address[marker_number] : marker.position.toString().replace('(', '').replace(')', '');
            //remove the hash
            var selector = options.selector.replace('#', '');

            marker.infobox_options = {
                boxClass: options.marker.infobox.box_class,
                maxWidth: options.marker.infobox.max_width,
                zIndex: options.marker.infobox.z_index,
                content: content,
                pixelOffset: new google.maps.Size(parseInt(options.marker.infobox.pixel_offset.width), parseInt(options.marker.infobox.pixel_offset.height)),
                boxStyle: options.marker.infobox.box_style,
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            };

            //create a infobox
            marker.infoBox = new InfoBox(marker.infobox_options);

            //save the marker
            EE_GMAPS.saveMarker(selector, marker);

            marker = EE_GMAPS.searchMarker(selector, marker_number);

            google.maps.event.addListener(marker, "click", function () {
                $.each(EE_GMAPS.markers[selector], function(i, _marker){
                    _marker.marker.infoBox.close();
                });

                marker.infoBox.open(map.map, marker);
            });
        }
    };

    //create the infobox
    EE_GMAPS.addInfoboxPerMarker = function(options, map, marker, mapId){
        if(typeof options.infobox == 'object' && options.infobox.content !== '') {

            //set the content
            options.infobox.content = EE_GMAPS.setInfowindowContent(options.infobox.content, {
                'location': location
            }, marker.position);

            marker.infobox_options = {
                boxClass: options.infobox.box_class,
                maxWidth: options.infobox.max_width,
                zIndex: options.infobox.z_index,
                content: options.infobox.content,
                pixelOffset: new google.maps.Size(parseInt(options.infobox.pixel_offset.width), parseInt(options.infobox.pixel_offset.height)),
                boxStyle: options.infobox.box_style,
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            };

            //create a infobox
            marker.infoBox = new InfoBox(marker.infobox_options);

            marker = EE_GMAPS.searchMarker(mapId, marker.markerNumber);

            google.maps.event.addListener(marker, "click", function () {
                $.each(EE_GMAPS.markers[mapId], function(i, _marker){
                    if(typeof _marker.marker.infoBox == 'object') {
                        _marker.marker.infoBox.close();
                    }
                });

                marker.infoBox.open(map.map, marker);
            });
        }
    };

    //set the googlemaps url e.g. route or place
    EE_GMAPS.setInfowindowUrl = function(marker_object, type) {
        var url = '';
        if(marker_object != undefined) {
            switch(type) {
                case 'route_to':
                    url = 'https://maps.google.com/maps?daddr=' + marker_object.lat() + ',' + marker_object.lng();
                    //http://maps.google.com/maps?saddr=start&daddr=end
                    break;

                case 'route_from':
                    url = 'https://maps.google.com/maps?saddr=' + marker_object.lat() + ',' + marker_object.lng();
                    //http://maps.google.com/maps?saddr=start&daddr=end
                    break;

                default:
                case 'map':
                    url = 'https://maps.google.com/maps?q=' + marker_object.lat() + ',' + marker_object.lng();
                    //https://maps.google.com/maps?q=
                    break;
            }
        }
        return url;
    };

    //set the markers to the arrays
    EE_GMAPS.saveMarkers = function(mapID, markers, address_based, keys) {

        //set mapID
        mapID = mapID.replace('#', '');
        //set vars
        var markerNumbers = [];
        var newMarkerData = [];

        if(markers.length > 0) {

            //save all to a latlng array
            $.each(markers, function(k, v) {
                //set the marker number
                v.markerNumber = k;
                //set the uuuid
                v.markerUUID = createUUID();

                markerNumbers.push(v.markerNumber);

                //set the arrays
                newMarkerData[k] = [];
                newMarkerData[k]['marker'] = v;
                newMarkerData[k]['keys'] = [k, v.markerUUID, v.getPosition().lat() + ',' + v.getPosition().lng()];

                //save marker to array
                //EE_GMAPS.markers[k]['index'] = [v];

                //save all to a latlng array
                EE_GMAPS.latlngs.push(v.position.lat() + ',' + v.position.lng());
            });

            //create address based array
            if(typeof address_based == 'object') {
                $.each(address_based, function(k, v) {
                    if(newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        v = $.trim(v);
                        newMarkerData[k]['keys'].push(v);
                        newMarkerData[k]['keys'].push(v.toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_').toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-').toLowerCase());
                        //remove duplicated
                        newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                    }
                });
            }

            //create the custom keys for the marker
            if(typeof keys == 'object') {
                $.each(keys, function(k, v) {
                    if(newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        var _keys = v.split(':');
                        _.each(_keys, function(_key){
                            _key = $.trim(_key);
                            newMarkerData[k]['keys'].push(_key);
                            newMarkerData[k]['keys'].push(_key.toLowerCase());
                            newMarkerData[k]['keys'].push(_key.gmaps_replaceAll(' ', '_'));
                            newMarkerData[k]['keys'].push(_key.gmaps_replaceAll(' ', '_').toLowerCase());
                            newMarkerData[k]['keys'].push(_key.gmaps_replaceAll(' ', '-'));
                            newMarkerData[k]['keys'].push(_key.gmaps_replaceAll(' ', '-').toLowerCase());
                            //remove duplicated
                            newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                        });
                    }
                });
            }

            //save the marker data
            EE_GMAPS.markers[mapID] = newMarkerData;
        }

        //callback function when all things is ready
        EE_GMAPS.runAll();

        return markerNumbers.length == 1 ? markerNumbers[0] : markerNumbers;
    };

    //save single marker
    EE_GMAPS.saveMarker = function(mapID, marker, keys) {
        //set mapID
        mapID = mapID.replace('#', '');

        //set the map array
        if(EE_GMAPS.markers[mapID] == undefined) {
            EE_GMAPS.markers[mapID] = [];
        }

        //get the index
        var index = EE_GMAPS.markers[mapID].length;
        //set markerNumber
        marker.markerNumber = index;
        //set the uuuid
        marker.markerUUID = createUUID();
        //set the arrays
        EE_GMAPS.markers[mapID][index] = [];
        EE_GMAPS.markers[mapID][index]['marker'] = marker;
        EE_GMAPS.markers[mapID][index]['keys'] = [index, marker.markerUUID];
        //update lnglngs array
        EE_GMAPS.latlngs.push(marker.position.lat() + ',' + marker.position.lng());

        //add the extra keys
        //create the custom keys for the marker
        if(typeof keys == 'string') {
            var _keys = keys.split(':');
            _.each(_keys, function(_key){
                _key = $.trim(_key);
                EE_GMAPS.markers[mapID][index]['keys'].push(_key);
                EE_GMAPS.markers[mapID][index]['keys'].push(_key.toLowerCase());
                EE_GMAPS.markers[mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '_'));
                EE_GMAPS.markers[mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '_').toLowerCase());
                EE_GMAPS.markers[mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '-'));
                EE_GMAPS.markers[mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '-').toLowerCase());
                //remove duplicated
                EE_GMAPS.markers[mapID][index]['keys'] = _.uniq(EE_GMAPS.markers[mapID][index]['keys']);
            });
        }

        return marker.markerNumber;
    };

    //set the markers to the arrays
    EE_GMAPS.searchMarker = function(mapID, marker_name) {

        var marker;

        //loop over the markers
        if(EE_GMAPS.markers[mapID] != undefined) {
            $.each(EE_GMAPS.markers[mapID], function(key, val) {
                //search the array
                if(val['keys'] != undefined) {
                    if(jQuery.inArray(marker_name, val['keys']) != -1) {
                        marker = EE_GMAPS.markers[mapID][key]['marker'];
                    }
                }
            });
        }

        return marker;
    };

    //remove single marker
    EE_GMAPS.removeMarker = function(mapID, marker_name) {

        var index;

        //loop over the markers
        $.each(EE_GMAPS.markers[mapID], function(key, val) {
            //search the array
            if(val['keys'] != undefined && index == undefined) {
                if(jQuery.inArray(marker_name, val['keys']) != -1) {
                    //set the index
                    index = key;
                }
            }
        });

        //remove marker
        if(index != undefined) {
            EE_GMAPS.markers[mapID].gmaps_remove(index);
        }

        //remove latlng from array
        $.each(EE_GMAPS.latlngs, function(k, v) {
            if(k == index) {
                EE_GMAPS.latlngs.gmaps_remove(k);
                //delete EE_GMAPS.latlngs[k];
            }
        });

        //update markerNumber
        $.each(EE_GMAPS.markers[mapID], function(key, val) {
            val['marker'].markerNumber = key;
            val['keys'][0] = key;
        });
    };

    //update the marker cache with the new markers
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateMarkerCache = function(mapID, new_order) {
        if($.isArray(new_order)) {
            var new_cache = [];
            $.each(new_order, function(k, v) {
                var marker = EE_GMAPS.searchMarker(mapID, v);
                if(marker != undefined) {
                    var old_markerNumber = marker.markerNumber;
                    //set the new marker
                    marker.markerNumber = k;
                    EE_GMAPS.markers[mapID][old_markerNumber].keys[0] = k;
                    //save to new cache
                    new_cache.push(EE_GMAPS.markers[mapID][old_markerNumber]);
                }
            });

            //set the new cache
            EE_GMAPS.markers[mapID] = new_cache;
        }
    };

    //save polyline or polygon
    EE_GMAPS.saveArtOverlay = function(mapID, object, type, keys) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if(type != undefined && EE_GMAPS[type_array] != undefined && object != undefined) {
            //set the map array
            if(EE_GMAPS[type_array][mapID] == undefined) {
                EE_GMAPS[type_array][mapID] = [];
            }
            //get the index
            var index = EE_GMAPS[type_array][mapID].length;
            //set markerNumber
            object.objectNumber = index;
            //set the uuuid
            object.objectUUID = createUUID();
            //set the arrays
            EE_GMAPS[type_array][mapID][index] = [];
            EE_GMAPS[type_array][mapID][index]['object'] = object;
            EE_GMAPS[type_array][mapID][index]['keys'] = [index, object.objectUUID];

            //add the extra keys
            //create the custom keys for the marker
            if(typeof keys == 'string') {
                var _keys = keys.split(':');
                _.each(_keys, function(_key){
                    _key = $.trim(_key);
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key);
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key.toLowerCase());
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '_'));
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '_').toLowerCase());
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '-'));
                    EE_GMAPS[type_array][mapID][index]['keys'].push(_key.gmaps_replaceAll(' ', '-').toLowerCase());
                    //remove duplicated
                    EE_GMAPS[type_array][mapID][index]['keys'] = _.uniq(EE_GMAPS[type_array][mapID][index]['keys']);
                });
            }

            //return number
            return object.objectNumber;
        }
    };

    //set the poly to the arrays
    EE_GMAPS.searchArtOverlay = function(mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if(type != undefined && EE_GMAPS[type_array] != undefined) {
            var object;

            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function(key, val) {
                //search the array
                if(val['keys'] != undefined) {
                    if(jQuery.inArray(object_name, val['keys']) != -1) {
                        object = EE_GMAPS[type_array][mapID][key]['object'];
                    }
                }
            });

            //return
            return object;
        }
    };

    //remove single poly
    EE_GMAPS.removeArtOverlay = function(mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';
        var index;

        if(type != undefined && EE_GMAPS[type_array] != undefined) {
            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function(key, val) {
                //search the array
                if(val['keys'] != undefined && typeof (index) == 'undefined') {
                    if(jQuery.inArray(object_name, val['keys']) != -1) {
                        //set the index
                        index = key;
                    }
                }
            });

            //remove marker
            if(index != undefined) {
                EE_GMAPS[type_array][mapID].gmaps_remove(index);
            }

            //update markerNumber
            $.each(EE_GMAPS[type_array][mapID], function(key, val) {
                val['object'].objectNumber = key;
                val['keys'][0] = key;
            });
        }
    };

    //update the poly cache with the new polylines
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateArtOverlayCache = function(mapID, new_order, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if(type != undefined && EE_GMAPS[type_array] != undefined) {
            if($.isArray(new_order)) {
                var new_cache = [];
                $.each(new_order, function(k, v) {
                    var object = EE_GMAPS.searchArtOverlay(mapID, v, type);
                    if(object != undefined) {
                        var old_objectNumber = object.objectNumber;
                        //set the new poly
                        object.objectNumber = k;
                        EE_GMAPS[type_array][mapID][old_objectNumber].keys[0] = k;
                        //save to new cache
                        new_cache.push(EE_GMAPS[type_array][mapID][old_objectNumber]);
                    }
                });
                //set the new cache
                EE_GMAPS[type_array][mapID] = new_cache;
            }
        }
    };

    //save Route
    EE_GMAPS.saveRoute = function(mapID, object) {

        //set the map array
        if(EE_GMAPS['routes'] == undefined) {
            EE_GMAPS['routes'] = [];
        }
        if(EE_GMAPS['routes'][mapID] == undefined) {
            EE_GMAPS['routes'][mapID] = [];
        }
        //get the index
        var index = EE_GMAPS['routes'][mapID].length;
        //set markerNumber
        object.objectNumber = index;
        //set the uuuid
        object.objectUUID = createUUID();
        //set the arrays
        EE_GMAPS['routes'][mapID][index] = [];
        EE_GMAPS['routes'][mapID][index]['object'] = object;
        EE_GMAPS['routes'][mapID][index]['keys'] = [index, object.objectUUID];

        //return number
        return object.objectNumber;
    };

    //set the poly to the arrays
    EE_GMAPS.searchRoute = function(mapID, object_name) {

        if(EE_GMAPS['route'] != undefined) {
            var object;

            //loop over the markers
            $.each(EE_GMAPS['route'][mapID], function(key, val) {
                //search the array
                if(val['keys'] != undefined) {
                    if(jQuery.inArray(object_name, val['keys']) != -1) {
                        object = EE_GMAPS['route'][mapID][key]['object'];
                    }
                }
            });

            //return
            return object;
        }
    };

    //get the map
    EE_GMAPS.getMap = function(id) {
        if(EE_GMAPS._map_['#' + id] != undefined) {
            return EE_GMAPS._map_['#' + id];
        }
        return false;
    };

    //bound/fit the map for a specific type (circle, marker, route etc)
    EE_GMAPS.fitMap = function(type, mapID) {
        type = 'fit'+type+'OnMap' || undefined;

        //bound
        if(type != undefined && jQuery.inArray(mapID, EE_GMAPS[type]) !== -1) {
            EE_GMAPS.api(type, {
                mapID : mapID
            });
        }
    };

    //set the correct maptypes
    EE_GMAPS.setMapTypes = function(types) {
        //get the correct ID
        var getType = function(type) {
            switch(type) {
                case 'hybrid':
                    return google.maps.MapTypeId.HYBRID;
                    break;
                case 'roadmap':
                    return google.maps.MapTypeId.ROADMAP;
                    break;
                case 'satellite':
                    return google.maps.MapTypeId.SATELLITE;
                    break;
                case 'terrain':
                    return google.maps.MapTypeId.TERRAIN;
                    break;
            }
        };

        //tmp vars
        var _types = [];

        if(_.isArray(types)) {
            _.each(types, function(v) {
                _types.push(getType(v));
            });

            return _types;
        } else {
            return getType(types);
        }
    };

    //simple Geolocation wrapper
    EE_GMAPS.geolocate = function(callback){
        GMaps.geolocate({
            success: function (position) {
                if (typeof callback === "function") {
                    // Call it, since we have confirmed it is callable​
                    callback(position);
                }
            },
            error: function (error) {
                console.log('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                console.log("Your browser does not support geolocation");
            }
        });
    };

    //----------------------------------------------------------------------------------------------------------//
    // Public functions //
    //----------------------------------------------------------------------------------------------------------//

    ///create an onclick event wrapper for an marker
    EE_GMAPS.triggerEvent = EE_GMAPS.api = function(type, options, callback) {
        //no type
        if(type == '') {
            return false;
        }

        //options 
        options = $.extend({
            mapID: '',
            key: ''
        }, options);

        //set the vars
        var mapID, map, latlng = [];
        var marker;

        //set the mapID
        if(options.mapID != '') {
            //set the mapID
            mapID = options.mapID;
            //delete options.mapID;

            //get the map
            map = EE_GMAPS.getMap(mapID);
        }

        //set a default return value
        var __returnValue;

        //default callback function
        callback = callback || function(){};
        var executeCallback = function(callback) {
            if(typeof callback == 'function') {
                callback();
            }
        };

        //what do we do
        switch(type) {

            //------------------------------------------------------------------------
            // Global map api
            //------------------------------------------------------------------------

            //close infowindow
            case 'refresh':
                //is there a map
                if(map) {
                    if(typeof map.refresh === 'undefined') {
                        google.maps.event.trigger(map, 'resize');
                    } else {
                        map.refresh();
                    }

                    //fitzoom
                    if((typeof options.center == 'boolean' && options.center == true) && typeof map.fitZoom !== 'undefined') {
                        var zoomLevel = map.getZoom();
                        map.fitZoom();
                        map.setZoom(zoomLevel);
                    }

                    //also refresh the clusters
                    if(typeof map.markerClusterer === 'object') {
                        map.markerClusterer.redraw();
                    }
                }
                break;

            //refresh all maps
            case 'refreshAll':
                var maps = _.values(EE_GMAPS._map_);
                _.each(maps, function(map) {
                    if(typeof map.refresh === 'undefined') {
                        google.maps.event.trigger(map, 'resize');
                    } else {
                        map.refresh();
                    }

                    //also refresh the clusters
                    if(typeof map.markerClusterer === 'object') {
                        map.markerClusterer.redraw();
                    }

                    //fitzoom with the current zoomlevel
                    if((typeof options.center == 'boolean' && options.center == true) && typeof map.fitZoom !== 'undefined') {
                        if(typeof options.currentZoom == 'boolean' && options.currentZoom == true) {
                            var zoomLevel = map.getZoom();
                        } else if(typeof options.zoomLevel !== 'undefined') {
                            var zoomLevel = options.zoomLevel;
                        }

                        map.fitZoom();

                        if(typeof zoomLevel !== 'undefined') {
                            map.setZoom(zoomLevel);
                        }
                    }

                    //also refresh the clusters
                    if(typeof map.markerClusterer === 'object') {
                        map.markerClusterer.redraw();
                    }
                });
                break;

            // set Zoom level
            case 'setZoom':
                //is there a map
                if(map) {
                    map.setZoom(options.zoomLevel);
                }
                break;

            // set Zoom level
            case 'fitZoom':
                //is there a map
                if(map) {
                    map.fitZoom();

                    //set the zoom manually
                    if(options.zoomLevel != undefined) {
                        map.setZoom(options.zoomLevel);
                    }
                }
                break;

            // set Zoom level (added 2.9)
            case 'center':
                //is there a map
                if(map) {
                    map.setCenter(options.lat, options.lng);
                }
                break;


            // create the context menu (added 2.9)
            case 'contextMenu':
                //is there a map
                if(map) {
                    map.setContextMenu(options);
                }
                break;

            // Get the map object (added 2.9)
            case 'getMap':
                //is there a map
                if(map) {
                    __returnValue = map;
                }
                break;

            // Update a map with new settings
            case 'updateMap':
                //is there a map
                if(map) {
                    if(options.setMapTypeId != undefined) {
                        map.setMapTypeId(google.maps.MapTypeId[options.setMapTypeId.toUpperCase()]);
                        delete options.setMapTypeId;
                    }
                    map.setOptions(options);
                }
                break;

            // Fit Markers on the map
            case 'fitMap':
            case 'fitMarkersOnMap':
               //is there a map
               if(map) {
                   //get all marker latlngs
                   var markers = EE_GMAPS.markers[mapID];

                   if(markers != undefined) {
                       var latlng = [];
                       _.each(markers, function(marker){
                           var _latlng = new google.maps.LatLng(marker.marker.position.lat(), marker.marker.position.lng());
                           latlng.push(_latlng);
                       });

                       map.fitLatLngBounds(latlng);
                   }
               }
               break;

            // Fit Route on the map
            case 'fitCirclesOnMap':
                //is there a map
                if(map) {
                    //get all marker latlngs
                    var circles = EE_GMAPS.circles[mapID];

                    if(circles != undefined) {

                        var bounds = new google.maps.LatLngBounds();

                        _.each(circles, function(circle){
                            bounds.union(circle.object.getBounds());
                        });

                        map.fitBounds(bounds);
                    }
                }
                break;

            // Fit rectangles on the map
            case 'fitRectanglesOnMap':
                //is there a map
                if(map) {
                    //get all marker latlngs
                    var rectangles = EE_GMAPS.rectangles[mapID];

                    if(rectangles != undefined) {

                        var latlng = [];

                        _.each(rectangles, function(rectangle){
                            var northEast = new google.maps.LatLng(rectangle.object.getBounds().getNorthEast().lat(), rectangle.object.getBounds().getNorthEast().lng());
                            var southWest = new google.maps.LatLng(rectangle.object.getBounds().getSouthWest().lat(), rectangle.object.getBounds().getSouthWest().lng());

                            latlng.push(northEast);
                            latlng.push(southWest);

                        });

                        map.fitLatLngBounds(latlng);
                    }
                }
                break;

            // Fit Polylines on the map
            case 'fitPolylinesOnMap':
                //is there a map
                if(map) {
                    //get all marker latlngs
                    var polylines = EE_GMAPS.polylines[mapID];

                    if(polylines != undefined) {

                        var bounds = new google.maps.LatLngBounds();

                        _.each(polylines, function(polyline){
                            bounds.union(polyline.object.getBounds());
                        });

                        map.fitBounds(bounds);
                    }
                }
                break;

            // Fit Polylines on the map
            case 'fitPolygonsOnMap':
                //is there a map
                if(map) {
                    //get all marker latlngs
                    var polygons = EE_GMAPS.polygons[mapID];

                    if(polygons != undefined) {

                        var bounds = new google.maps.LatLngBounds();

                        _.each(polygons, function(polygon){
                            bounds.union(polygon.object.getBounds());
                        });

                        map.fitBounds(bounds);
                    }
                }
                break;

            //------------------------------------------------------------------------
            // End Global map api
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // Marker api
            //------------------------------------------------------------------------

            // add Marker, and return the marker numbers
            case 'addMarker':
                //is there a map
                if(map) {

                    //private function add marker
                    function _addMarker(options) {

                        //set the correct values for the icon
                        if(options.icon != undefined && typeof options.icon == 'object') {
                            if(options.icon.size != undefined) {
                                var value = options.icon.size.split(',');
                                options.icon.size = new google.maps.Size(parseInt(value[0]), parseInt(value[1]));
                            }
                            if(options.icon.origin != undefined) {
                                var value = options.icon.origin.split(',');
                                options.icon.origin = new google.maps.Point(parseInt(value[0]), parseInt(value[1]))
                            }
                            if(options.icon.anchor != undefined) {
                                var value = options.icon.anchor.split(',');
                                options.icon.anchor = new google.maps.Point(parseInt(value[0]), parseInt(value[1]))
                            }
                            if(options.icon.scaledSize != undefined) {
                                var value = options.icon.scaledSize.split(',');
                                options.icon.scaledSize = new google.maps.Size(parseInt(value[0]), parseInt(value[1]))
                            }
                        }

                        //get the keys
                        var keys = typeof options.keys == 'string' ? options.keys : '';

                        var new_marker = map.addMarker(options);

                        EE_GMAPS.saveMarker(mapID, new_marker, keys);

                        //infobox
                        EE_GMAPS.addInfoboxPerMarker(options, map, new_marker, mapID);

                        return new_marker;
                    }

                    //save the ids
                    var ids = [];

                    //multiple
                    if(options.multi != undefined && _.isArray(options.multi)) {
                        ids = [];
                        $.each(options.multi, function(k, v) {

                            //add and save the marker
                            var new_marker = _addMarker(v);

                            ids.push(new_marker.markerNumber);

                            //open by default?
                            if(v.open_by_default) {
                                google.maps.event.trigger(new_marker, 'click');
                            }

                            //callback
                            if((k + 1) == options.multi.length) {

                                //fit map
                                if(typeof options.multi[k].fitTheMap == 'boolean' && options.multi[k].fitTheMap) {
                                    map.fitZoom();
                                }

                                //callback
                                if(options.callback && typeof (options.callback) == 'function') {
                                    setTimeout(function() {
                                        options.callback();
                                    }, 200);
                                }

                                //fit
                                EE_GMAPS.fitMap('Markers', mapID);
                            }
                        });

                        //single marker
                    } else {

                        //add and save the marker
                        var new_marker = _addMarker(options);

                        //add and save the marker
                        ids = new_marker.markerNumber;

                        //fit map
                        if(typeof options.fitTheMap == 'boolean' && options.fitTheMap) {
                            map.fitZoom();
                        }

                        //callback
                        if(options.callback && typeof (options.callback) == 'function') {
                            setTimeout(function() {
                                options.callback();
                            }, 200);
                        }

                        //fit
                        EE_GMAPS.fitMap('Markers', mapID);
                    }

                    __returnValue = ids;
                }
                break;

            //remove marker
            case 'removeMarker':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //is there a map
                if(map && marker != undefined) {
                    //remove from the gmaps.js
                    map.removeMarker(marker);
                    //remove from the cache
                    EE_GMAPS.removeMarker(mapID, options.key);
                }
                break;

            // hide existing Marker
            case 'hideMarker':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //is there a map
                if(map && marker != undefined) {
                    //remove from map
                    marker.setVisible(false);
                }
                break;

            // show existing Marker
            case 'showMarker':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //is there a map
                if(map && marker != undefined) {
                    //remove from map
                    marker.setVisible(true);
                }
                break;

            // show existing Marker (added 2.9)
            case 'updateMarker':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //is there a map
                if(map && marker != undefined) {
                    //remove key
                    delete options.key;
                    //set infowindow if needed
                    if(options.infoWindow != undefined && options.infoWindow.content != undefined) {
                        if(marker.infoWindow != undefined) {
                            marker.infoWindow.setContent(options.infoWindow.content);
                        }
                        delete options.infoWindow;
                    }

                    //set the new options
                    marker.setOptions(options);
                    //refresh the map
                    map.refresh();
                }
                break;

            //Get the marker array
            case 'getAllMarkers':
                __returnValue = (EE_GMAPS.markers);
                break;

            //Get the marker array
            case 'getMarkers':
                __returnValue = (EE_GMAPS.markers[mapID]);
                break;

            // Get the marker
            case 'getMarker':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //is there a map
                if(map && marker != undefined) {
                    //remove from map
                    __returnValue = marker;
                }
                break;

            // Remove all markers
            case 'removeMarkers':
                //is there a map
                if(map) {
                    map.removeMarkers();
                    //reset marker cache
                    EE_GMAPS.markers = [];
                }
                break;

            // Hide all markers (added 2.12.9))
            case 'hideMarkers':
                //is there a map
                if(map && EE_GMAPS.markers[mapID] != undefined) {
                    $.each(EE_GMAPS.markers[mapID], function(k, v) {
                        //remove from map
                        v.marker.setVisible(false);
                    });
                }
                break;

            // Show all markers (added 2.12.9)
            case 'showMarkers':
                //is there a map
                if(map && EE_GMAPS.markers[mapID] != undefined) {
                    $.each(EE_GMAPS.markers[mapID], function(k, v) {
                        //remove from map
                        v.marker.setVisible(true);
                    });
                }
                break;

            //marker click
            case 'markerClick':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);

                //trigger the click
                if(marker != undefined) {
                    google.maps.event.trigger(marker, 'click');
                    //is there a map
                    if(map) {
                        map.setCenter(marker.position.lat(), marker.position.lng());
                    }
                }
                break;

            //callback for the marker click (added 2.14)
            case 'markerClickCallback':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);

                //trigger the click
                if(marker != undefined && typeof options.callback == 'function') {
                    google.maps.event.addListener(marker, "click", function() {
                        //assign marker and map object
                        options.marker = marker;
                        options.map = map;
                        //call the callback
                        options.callback(map);
                    });
                }
                break;

            //close infowindow
            case 'infowindowClose':
                //get the marker
                marker = EE_GMAPS.searchMarker(mapID, options.key);
                //close infoWindow
                if(marker != undefined) {
                    marker.infoWindow.close();
                }
                break;

            //------------------------------------------------------------------------
            // End Marker api
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // Layers
            //------------------------------------------------------------------------

            // Add a layer
            case 'addLayer':
                //is there a map
                if(map) {
                    if(options.layerName != undefined) {
                        map.addLayer(options.layerName);
                    }
                }
                break;

            // Remove a layer
            case 'removeLayer':
                //is there a map
                if(map) {
                    if(options.layerName != undefined) {
                        map.removeLayer(options.layerName);
                    }
                }
                break;

            //------------------------------------------------------------------------
            // End layers
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // Circle api
            //------------------------------------------------------------------------

            // Create a circle
            case 'addCircle':
                //is there a map
                if(map) {
                    //multiple
                    if(options.multi != undefined && _.isArray(options.multi)) {
                        var ids = [];
                        $.each(options.multi, function(k, v) {
                            var keys = typeof v.keys == 'string' ? v.keys : '';
                            var new_circle = map.drawCircle(v);
                            ids.push(EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle', keys));
                        });
                        __returnValue = ids;

                        //single polygon
                    } else {
                        var keys = typeof options.keys == 'string' ? options.keys : '';
                        var new_circle = map.drawCircle(options);
                        __returnValue = EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle', keys);
                    }

                    //fit
                    EE_GMAPS.fitMap('Circles', mapID);
                }
                break;

            // Get the polygon (added 2.11.1)
            case 'getCircle':
                //get the marker
                var circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
                //is there a map
                if(map && circle != undefined) {
                    //remove from map
                    __returnValue = circle;
                }
                break;

            // update a polygon (added 2.11.1)
            case 'updateCircle':
                //get the marker
                var circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
                //is there a map
                if(map && circle != undefined) {
                    //remove key
                    delete options.key;
                    //set the new options
                    circle.setOptions(options);
                    //refresh the map
                    map.refresh();
                }
                break;

            //remove polygon (added 2.11.1)
            case 'removeCircle':
                //get the marker
                var circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
                //is there a map
                if(map && circle != undefined) {
                    //remove from gmaps.js
                    circle.setMap(null);
                    //remove from the cache
                    EE_GMAPS.removeArtOverlay(mapID, options.key, 'circle');
                }
                break;

            //------------------------------------------------------------------------
            // End circle api
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // Rectangle api
            //------------------------------------------------------------------------

            // Create a Rectangle
            case 'addRectangle':
                //is there a map
                if(map) {
                    //multiple
                    if(options.multi != undefined && _.isArray(options.multi)) {
                        var ids = [];
                        $.each(options.multi, function(k, v) {
                            var keys = typeof v.keys == 'string' ? v.keys : '';
                            var new_rectangle = map.drawRectangle(v);
                            ids.push(EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle', keys));
                        });
                        __returnValue = ids;

                        //single polygon
                    } else {
                        var keys = typeof options.keys == 'string' ? options.keys : '';
                        var new_rectangle = map.drawRectangle(options);
                        __returnValue = EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle', keys);
                    }

                    //fit
                    EE_GMAPS.fitMap('Rectangles', mapID);
                }
                break;

            // Get the polygon (added 2.11.1)
            case 'getRectangle':
                //get the marker
                var rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
                //is there a map
                if(map && rectangle != undefined) {
                    //remove from map
                    __returnValue = rectangle;
                }
                break;

            // update a polygon (added 2.11.1)
            case 'updateRectangle':
                //get the marker
                var rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
                //is there a map
                if(map && rectangle != undefined) {
                    //remove key
                    delete options.key;
                    //set the new options
                    rectangle.setOptions(options);
                    //refresh the map
                    map.refresh();
                }
                break;

            //remove polygon (added 2.11.1)
            case 'removeRectangle':
                //get the marker
                var rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
                //is there a map
                if(map && rectangle != undefined) {
                    //remove from gmaps.js
                    rectangle.setMap(null);
                    //remove from the cache
                    EE_GMAPS.removeArtOverlay(mapID, options.key, 'rectangle');
                }
                break;

            //------------------------------------------------------------------------
            // End rectanble api
            //------------------------------------------------------------------------


            //------------------------------------------------------------------------
            // Polygon api
            //------------------------------------------------------------------------

            // Create a Polygon
            case 'addPolygon':
                //is there a map
                if(map) {
                    //multiple
                    if(options.multi != undefined && _.isArray(options.multi)) {
                        var ids = [];
                        $.each(options.multi, function(k, v) {
                            var keys = typeof v.keys == 'string' ? v.keys : '';
                            var new_polygon = map.drawPolygon(v);
                            ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon', keys));
                        });
                        __returnValue = ids;

                        //single polygon
                    } else {
                        var keys = typeof options.keys == 'string' ? options.keys : '';
                        var new_polygon = map.drawPolygon(options);
                        __returnValue = EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon', keys);
                    }

                    //fit
                    EE_GMAPS.fitMap('Polygons', mapID);
                }
                break;

            // Get the polygon (added 2.11.1)
            case 'getPolygon':
                //get the marker
                var polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
                //is there a map
                if(map && polygon != undefined) {
                    //remove from map
                    __returnValue = polygon;
                }
                break;

            // update a polygon (added 2.11.1)
            case 'updatePolygon':
                //get the marker
                var polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
                //is there a map
                if(map && polygon != undefined) {
                    //remove key
                    delete options.key;
                    //set the new options
                    polygon.setOptions(options);
                    //refresh the map
                    map.refresh();
                }
                break;

            //remove polygon (added 2.11.1)
            case 'removePolygon':
                //get the marker
                var polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
                //is there a map
                if(map && polygon != undefined) {
                    //remove from gmaps.js
                    map.removePolygon(polygon);
                    //remove from the cache
                    EE_GMAPS.removeArtOverlay(mapID, options.key, 'polygon');
                }
                break;

            //------------------------------------------------------------------------
            // End polygon api
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // Polyline api
            //------------------------------------------------------------------------

            // Create a Polyline
            case 'addPolyline':
                //is there a map
                if(map) {
                    //multiple
                    if(options.multi != undefined && _.isArray(options.multi)) {
                        var ids = [];
                        $.each(options.multi, function(k, v) {
                            var keys = typeof v.keys == 'string' ? v.keys : '';
                            var new_polyline = map.drawPolyline(v);
                            ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline', keys));
                        });
                        __returnValue = ids;

                        //single polyline
                    } else {
                        var keys = typeof options.keys == 'string' ? options.keys : '';
                        var new_polyline = map.drawPolyline(options);
                        __returnValue = EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline', keys);
                    }

                    //fit
                    EE_GMAPS.fitMap('Polylines', mapID);
                }
                break;

            // Get the polyline (added 2.11.1)
            case 'getPolyline':
                //get the marker
                var polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
                //is there a map
                if(map && polyline != undefined) {
                    //remove from map
                    __returnValue = polyline;
                }
                break;

            // Get the polyline (added 2.11.1)
            case 'updatePolyline':
                //get the marker
                var polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
                //is there a map
                if(map && polyline != undefined) {
                    //remove key
                    delete options.key;
                    //set the new options
                    polyline.setOptions(options);
                    //refresh the map
                    map.refresh();
                }
                break;

            //remove marker (added 2.11.1)
            case 'removePolyline':
                //get the marker
                var polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
                //is there a map
                if(map && polyline != undefined) {
                    //remove from gmaps.js
                    map.removePolyline(polyline);
                    //remove from the cache
                    EE_GMAPS.removeArtOverlay(mapID, options.key, 'polyline');
                }
                break;

            //------------------------------------------------------------------------
            // End polyline api
            //------------------------------------------------------------------------

            //------------------------------------------------------------------------
            // KML
            //------------------------------------------------------------------------
            case 'loadKML':
                //is there a map
                if(map && options.url != '') {
                    var kml = map.loadFromKML({
                        url: options.url,
                        zIndex: options.zIndex == undefined ? 1 : options.zIndex,
                        suppressInfoWindows: options.suppressInfoWindows == undefined ? false : options.suppressInfoWindows,
                        preserveViewport: options.preserveViewport == undefined ? false : options.preserveViewport,
                        screenOverlays: options.screenOverlays == undefined ? true : options.screenOverlays,
                        clickable: options.clickable == undefined ? true : options.clickable
                    });

                    __returnValue = kml;
                }
                break;

            //------------------------------------------------------------------------
            // Fusion Table
            //------------------------------------------------------------------------
            case 'loadFusionTable':
                //is there a map
                if(map && options.tableID != '') {

                    var infoWindow = new google.maps.InfoWindow({});

                    var fusionTable = map.loadFromFusionTables({
                        query: {
                            from: options.tableID
                        },
                        styles: options.styles == undefined ? null : options.styles,
                        suppressInfoWindows: options.suppressInfoWindows == undefined ? false : options.suppressInfoWindows,
                        clickable: options.clickable == undefined ? true : options.clickable,
                        events: {
                            click: function(point) {
                                infoWindow.setContent(point.infoWindowHtml);
                                infoWindow.setPosition(point.latLng);
                                infoWindow.open(map.map);
                            }
                        },
                        heatmap: {
                            enabled: options.heatmap
                        }
                    });

                    __returnValue = fusionTable;
                }
                break;

            //------------------------------------------------------------------------
            // Google overlay api
            //------------------------------------------------------------------------
            //add the google map like overlay (added 3.0)
            case 'addGoogleOverlay':

                //is there a map
                if(map) {
                    var new_options = {
                        overlay_html: options.html || '',
                        selector: '#' + mapID.replace('#', ''),
                        overlay_position: options.position || 'left'
                    };

                    EE_GMAPS.addGoogleOverlay(map, new_options, true);
                }
                break;

            //add the google map like overlay (added 3.0)
            case 'updateGoogleOverlay':

                //is there a map
                if(map) {
                    var new_options = {
                        overlay_html: options.html || '',
                        selector: '#' + mapID.replace('#', ''),
                        overlay_position: options.position || 'left'
                    };

                    EE_GMAPS.updateGoogleOverlay(map, new_options, true);
                }
                break;

            //add the google map like overlay (added 3.0)
            case 'removeGoogleOverlay':

                //is there a map
                if(map) {
                    EE_GMAPS.removeGoogleOverlay(map, '#' + mapID.replace('#', ''));
                }
                break;

            //------------------------------------------------------------------------
            // End google overlay api
            //------------------------------------------------------------------------
 
            //------------------------------------------------------------------------
            // Geolocation api, only working for SSL sites
            //------------------------------------------------------------------------
            case 'geolocation' :
                GMaps.geolocate({
                    success: function (position) {
                        if (typeof options.callback === "function") {
                            // Call it, since we have confirmed it is callable​
                            options.callback(position);
                        }
                    },
                    error: function (error) {
                        console.log('Geolocation failed: ' + error.message);
                    },
                    not_supported: function () {
                        console.log("Your browser does not support geolocation");
                    }
                });
                break;

            //------------------------------------------------------------------------
            // Calculate polyline path
            //------------------------------------------------------------------------
            case 'polylineLength' :
                //get the marker
                var polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
                __returnValue = google.maps.geometry.spherical.computeLength(polyline.getPath());
                break;

            //------------------------------------------------------------------------
            // Geocode api
            //------------------------------------------------------------------------

            case 'addRoute' :

                //is there a map
                if(map) {

                    //merge default settings with given settings
                    options = $.extend({
                       'origin': [],
                        'destination': [],
                        'waypoints': '',
                        'strokeColor': '#131540',
                        'strokeOpacity': '0.6',
                        'strokeWeight': '6',
                        'startCallback': function(){},
                        'stepCallback': function(){},
                        'endCallback': function(){},
                    }, options);

                    //create waypoints
                    var waypoints = EE_GMAPS.createLatLngWaypoints(options.waypoints, true);

                    //draw route
                    map.drawSteppedRoute({
                        origin: [options.origin[0], options.origin[1]],
                        destination: [options.destination[0], options.destination[1]],
                        waypoints: waypoints,
                        travelMode: options.travelMode,
                        // transitOptions: {
                        //     departureTime: options.departureTime,
                        //     arrivalTime: options.arrivalTime
                        // },
                        strokeColor: options.strokeColor,
                        strokeOpacity: options.strokeOpacity,
                        strokeWeight: options.strokeWeight,
                        start: function(e) {
                            //show elevation by chart
                            options.startCallback(e);

                        },
                        step: function(e, totalSteps) {
                            options.stepCallback(e, totalSteps);

                        },
                        end: function(e) {
                            options.endCallback(e);

                            //save the route
                            EE_GMAPS.saveRoute(mapID, e);

                            //fit bounds if the fitRoutesOnMap is set
                            if(EE_GMAPS.fitRoutesOnMap != undefined && typeof EE_GMAPS.fitRoutesOnMap == 'object') {

                                //first get the other paths
                                if(EE_GMAPS['routes'] != undefined) {

                                    var paths = [];
                                    _.each(EE_GMAPS['routes'][mapID], function(v){
                                        paths = _.union(paths, v.object.overview_path);
                                    });
                                }

                                // console.log(e.overview_path);
                                map.fitLatLngBounds(paths);
                            }

                        },
                        error: function(e) {
                            console.log('Route cannot be generated', e);
                        }
                    });
                }

                break;

            // Geocode using the API way to cache all addresses
            case 'geocode':

                var sessionKey = createUUID();

                //latlng reverse geocoding
                if(options.latlng != undefined) {
                    $.post(EE_GMAPS.api_path + '&type=latlng', {
                        input: options.latlng
                    }, function(result) {
                        if(options.callback && typeof (options.callback) == "function") {
                            options.callback(result, 'latlng', sessionKey);
                        }
                    });
                }

                //address geocoding
                if(options.address != undefined) {
                    $.post(EE_GMAPS.api_path + '&type=address', {
                        input: options.address
                    }, function(result) {
                        if(options.callback && typeof (options.callback) == "function") {
                            options.callback(result, 'address', sessionKey);
                        }
                    });
                }

                //ip geocoding
                if(options.ip != undefined) {
                    $.post(EE_GMAPS.api_path + '&type=ip', {
                        input: options.ip
                    }, function(result) {
                        if(options.callback && typeof (options.callback) == "function") {
                            options.callback(result, 'ip', sessionKey);
                        }
                    });
                }
                break;

            //------------------------------------------------------------------------
            // End geocode api
            //------------------------------------------------------------------------
        }

        executeCallback(callback);

        return __returnValue;
    };

    //create a show trigger 
    $.each(["show", "toggle", "toggleClass", "addClass", "removeClass"], function() {
        var _oldFn = $.fn[this];
        $.fn[this] = function() {
            var hidden = this.find(":hidden").add(this.filter(":hidden"));
            var result = _oldFn.apply(this, arguments);
            hidden.filter(":visible").each(function() {
                $(this).triggerHandler("show"); //No bubbling
            });
            return result;
        };
    });

}(jQuery));