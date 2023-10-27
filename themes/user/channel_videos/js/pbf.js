(function(global, $) {
    "use strict";
    var ChannelVideos = global.ChannelVideos = global.ChannelVideos || {};
    var fieldsElem;
    var fields = {};
    var youtubeKey = "AIzaSyD-ew6Tm9wIJIoMCMZuReb3xvs0ccNHvl4";
    var youtubeDurationRegex = /P((([0-9]*\.?[0-9]*)Y)?(([0-9]*\.?[0-9]*)M)?(([0-9]*\.?[0-9]*)W)?(([0-9]*\.?[0-9]*)D)?)?(T(([0-9]*\.?[0-9]*)H)?(([0-9]*\.?[0-9]*)M)?(([0-9]*\.?[0-9]*)S)?)?/;
    ChannelVideos.Init = function() {
        fieldsElem = $(".CVField");
        fieldsElem.each(function(i, elem) {
            elem = $(elem);
            fields["field_" + elem.data("field_id")] = JSON.parse(elem.find(".jsondata").html());
        });
        fieldsElem.find(".SearchVideos").click(toggleSearchVideos);
        fieldsElem.find(".SearchVideos input[type=text]").keypress(disableEnter);
        fieldsElem.find(".SVWrapper .Button").click(searchForVideos);
        fieldsElem.find(".SubmitVideoUrl").click(submitVideoUrl);
        fieldsElem.delegate(".VideosResults .video .add", "click", addVideo);
        fieldsElem.delegate(".DelVideo", "click", delVideo);
        fieldsElem.delegate(".ClearVideoSearch", "click", clearVideoSearch);
        fieldsElem.find(".AssignedVideos").sortable({
            cursor: "move",
            opacity: .6,
            handle: ".MoveVideo",
            update: syncOrderNumbers,
            helper: function(event, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            },
            forcePlaceholderSize: true,
            start: function(event, ui) {
                ui.placeholder.html('<td colspan="20"></td>');
            },
            placeholder: "cvideo-reorder-state-highlight"
        });
        fieldsElem.find(".AssignedVideos .CVItem .PlayVideo").colorbox({
            iframe: true,
            width: 450,
            height: 375
        });
    };
    function toggleSearchVideos(e) {
        $(e.target).closest(".CVTable").find(".SVWrapperTR").toggle();
        return false;
    }
    function disableEnter(e) {
        if (e.which == 13) {
            $(e.target).closest(".CVField").find(".searchbutton").click();
            return false;
        }
    }
    function syncOrderNumbers() {
        var attr;
        fieldsElem.each(function(FieldIndex, VideoField) {
            $(VideoField).find(".CVItem").each(function(VideoIndex, VideoItem) {
                $(VideoItem).find("input, textarea, select").each(function() {
                    attr = $(this).attr("name").replace(/\[videos\]\[.*?\]/, "[videos][" + (VideoIndex + 1) + "]");
                    $(this).attr("name", attr);
                });
            });
            $(VideoField).find(".CVItem").removeClass("odd");
            $(VideoField).find(".CVItem:odd").addClass("odd");
        });
    }
    function delVideo(e) {
        var VideoID = $(e.target).data("id");
        if (VideoID) {
            $.get(ChannelVideos.ACT_URL, {
                video_id: VideoID,
                ajax_method: "delete_video"
            }, function() {});
        }
        $(e.target).closest(".CVItem").fadeOut("slow", function() {
            $(this).remove();
            syncOrderNumbers();
        });
    }
    function clearVideoSearch(e) {
        var customField = jQuery(e.target).closest(".CVField");
        //TargetBox.find(".VideosResults .inner").empty();
       customField.find(".VideosResults .inner").empty();
        return false;
    }
    function searchForVideos(e) {
        e.preventDefault();
        var results = {};
        var params = {};
        var customField = $(e.target).closest(".CVField");
        var videoServices = fields["field_" + customField.data("field_id")].services;

        customField.find(".SVWrapper .cvsearch").find("input[type=text], input[type=hidden]").each(function() {
            params[$(this).attr("rel")] = jQuery(this).val();
        });

        for (var i = 0; i < videoServices.length; i++) {
            customField.find(".VideosResults .results-" + videoServices[i]).show().find(".LoadingVideos").show().siblings(".inner").empty();
            if (videoServices[i] == "youtube") {
                youtubeSearchVideos(params, customField);
            } else {
                vimeoSearchVideos(params, customField);
            }
        }
    }
    function addVideoResults(service, items, customField) {
        var Label = service == "youtube" ? "Youtube" : "Vimeo";
        var html = "";
        if (items.length === 0) {
            html += "<p>No Results Found...</p>";
        }
        for (var i = 0; i < items.length; i++) {
            html += '<div class="video" rel="' + service + "|" + items[i].id + '" id="' + service + "__" + items[i].id + '">';
            html += '<img src="' + items[i].img_url + '" width="100px" height="75px">';
            html += "<small>" + items[i].title + "</small>";
            html += "<span>";
            html += '<a href="' + items[i].vid_url + '" class="play">&nbsp;</a>';
            html += '<a href="#" class="add">&nbsp;</a>';
            html += "</span>";
            html += "</div>";
        }
        html += '<br clear="all"></div>';
        customField.find(".VideosResults .results-" + service).find(".LoadingVideos").hide().siblings(".inner").show().html(html);
        customField.find(".VideosResults .video .play").colorbox({
            iframe: true,
            width: 450,
            height: 375
        });
    }
    function submitVideoUrl(e) {
        var videoUrl = prompt("Video URL?", "");
        if (videoUrl === null) return false;
        var customField = $(e.target).closest("div.CVField");
        var videoServices = fields["field_" + customField.data("field_id")].services;
        customField.find(".SVWrapperTR").show();

        for (var i = 0; i < videoServices.length; i++) {
            customField.find(".VideosResults .results-" + videoServices[i]).show().find(".LoadingVideos").show().siblings(".inner").empty();
            if (videoServices[i] == "youtube") {
                youtubeSubmitUrl(videoUrl, customField);
            } else {
                vimeoSubmitUrl(videoUrl, customField);
            }
        }

        return false;
    }
    function addVideoToTable(video, field_id) {
        var field_data = fields["field_" + field_id];
        var customField = $("#ChannelVideos" + field_id);
        var html = "";
        customField.find("#" + video.service + "__" + video.service_video_id).slideUp();
        var video_date = new Date();
        video_date.setTime(video.video_date * 1e3);
        if (field_data.layout == "table") {
            html += '<tr class="CVItem">';
            html += '<td><a href="' + video.video_url + '" class="PlayVideo"><img src="' + video.video_img_url + '" width="100px" height="75px"></a></td>';
            html += "<td>" + video.video_title + "</td>";
            html += "<td>" + video.video_author + "</td>";
            html += "<td>" + (video.video_duration / 60).toFixed(2) + " min</td>";
            html += "<td>" + video.video_views + "</td>";
            html += "<td>" + video_date.toDateString() + "</td>";
            html += "<td>";
            html += '<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
            html += '<a href="javascript:void(0)" class="DelVideo" data-id="' + video.video_id + '">&nbsp;</a>';
            if (video.video_id > 0) {
                html += '<input name="' + field_data.field_name + '[videos][0][video_id]" type="hidden" value="' + video.video_id + '">';
            } else {
                html += '<textarea name="' + field_data.field_name + '[videos][0][data]" style="display:none">' + JSON.stringify(video) + "</textarea>";
            }
            html += "</td>";
            html += "</tr>";
        } else {
            html += '<div class="CVItem VideoTile">';
            html += '<a href="' + video.video_url + '" class="PlayVideo"><img src="' + video.video_img_url + '" width="100px" height="75px"></a>';
            html += "<small>" + video.video_title + "</small>";
            html += "<span>";
            html += '<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
            html += '<a href="javascript:void(0)" class="DelVideo" data-id="' + video.video_id + '">&nbsp;</a>';
            if (video.video_id > 0) {
                html += '<input name="' + field_data.field_name + '[videos][0][video_id]" type="hidden" value="' + video.video_id + '">';
            } else {
                html += '<textarea name="' + field_data.field_name + '[videos][0][data]" style="display:none">' + JSON.stringify(video) + "</textarea>";
            }
            html += "</span>";
            html += "</div>";
        }
        customField.find(".AssignedVideos .NoVideos").hide();
        customField.find(".AssignedVideos").append(html);
        syncOrderNumbers();
        customField.find(".AssignedVideos .CVItem .PlayVideo").colorbox({
            iframe: true,
            width: 450,
            height: 375
        });
    }
    function addVideo(e) {
        var Parent = jQuery(e.target).closest("div.video");
        var customField = jQuery(e.target).closest("div.CVField");
        var field_id = customField.data("field_id");
        var video_id = Parent.attr("rel").split("|")[1];
        var service = Parent.attr("rel").split("|")[0];

        jQuery(e.target).addClass("loading");

        if (service == "youtube") {
            youtubeGetVideo(video_id, field_id, addVideoToTable);
            return false;
        } else {
            vimeoGetVideo(video_id, field_id, addVideoToTable);
        }
        return false;
    }
    function youtubeSearchVideos(params, customField) {
        var i, entry, video_id;
        if (params.author) params.keywords += " " + params.author;
        $.ajax({
            crossDomain: true,
            dataType: "json",
            type: "GET",
            url: "https://www.googleapis.com/youtube/v3/search",
            data: {
                q: params.keywords,
                maxResults: params.limit,
                type: "video",
                part: "snippet",
                key: youtubeKey
            },
            success: function(rdata) {
                var videos = [];
                for (var i = 0; i < rdata.items.length; i++) {
                    var video = rdata.items[i];
                    var videoID = video.id.videoId;
                    videos.push({
                        id: videoID,
                        title: video.snippet.title,
                        img_url: "https://i.ytimg.com/vi/" + videoID + "/sddefault.jpg",
                        vid_url: "https://www.youtube.com/embed/" + videoID
                    });
                }
                addVideoResults("youtube", videos, customField);
            }
        });
    }
    function youtubeSubmitUrl(url, customField) {
        var servicebox = customField.find(".VideosResults .results-youtube").show();
        var inner = servicebox.find(".inner");
        var loading = servicebox.find(".LoadingVideos");
        var id = null;
        var parts;
        if (url.indexOf("youtube") === -1 && url.indexOf("youtu.be") === -1) {
            loading.hide();
            inner.html("<p>Not a valid Youtube URL</p>");
            return;
        }
        if (url.indexOf("youtube.com/watch") > 0) {
            parts = parseUrl(url);
            id = getQueryVariable(parts.query, "v");
        } else if (url.indexOf("youtu.be") > 0) {
            parts = url.split("/");
            id = parts[parts.length - 1];
        } else if (url.indexOf("youtube.com/embed") > 0) {
            parts = url.split("/");
            id = parts[parts.length - 1];
        }
        if (id === null) {
            loading.hide();
            inner.html("<p>Could not parse Youtube ID from that URL</p>");
            return;
        }
        $.ajax({
            crossDomain: true,
            dataType: "json",
            type: "GET",
            url: "https://www.googleapis.com/youtube/v3/videos",
            data: {
                id: id,
                part: "snippet",
                key: youtubeKey
            },
            success: function(rdata) {
                loading.hide();
                if (typeof rdata.pageInfo === "undefined" || typeof rdata.pageInfo.totalResults != 1) {
                    inner.html("<p>Youtube could not find the video. (ID: " + id + ")</p>");
                }
                var video = rdata.items[0];
                var videoId = video.id;
                addVideoResults("youtube", [ {
                    id: videoId,
                    title: video.snippet.title,
                    img_url: "https://i.ytimg.com/vi/" + videoId + "/sddefault.jpg",
                    vid_url: "https://www.youtube.com/embed/" + videoId
                } ], customField);
            }
        });
    }
    function youtubeGetVideo(id, field_id, callback) {
        $.ajax({
            crossDomain: true,
            dataType: "json",
            type: "GET",
            url: "https://www.googleapis.com/youtube/v3/videos",
            data: {
                id: id,
                part: "snippet,statistics,contentDetails",
                key: youtubeKey
            },
            success: function(rdata) {
                var videoID = id;
                var video = {};
                var entry = rdata.items[0];
                video.service = "youtube";
                video.service_video_id = videoID;
                video.video_id = 0;
                video.video_url = "http://www.youtube.com/embed/" + videoID;
                video.video_img_url = "http://i.ytimg.com/vi/" + videoID + "/sddefault.jpg";
                video.video_title = entry.snippet.title;
                video.video_desc = entry.snippet.description;
                video.video_username = entry.snippet.channelTitle;
                video.video_author = entry.snippet.channelTitle;
                video.video_author_id = 0;
                video.video_duration = nezasa.iso8601.Period.parseToTotalSeconds(entry.contentDetails.duration);
                video.video_date = new Date(entry.publishedAt).getTime() / 1e3;
                video.video_views = 0;
                if (typeof entry.statistics != "undefined" && typeof entry.statistics.viewCount != "undefined") {
                    video.video_views = entry.statistics.viewCount;
                }
                callback(video, field_id);
            }
        });
    }
    function vimeoSearchVideos(params, customField) {
        var servicebox = customField.find(".VideosResults .results-vimeo").show();
        var inner = servicebox.find(".inner");
        var loading = servicebox.find(".LoadingVideos");

        $.ajax({
            url: 'https://api.vimeo.com/videos?query=' + params.keywords + '&per_page=' + params.limit + (params.author ? '&user_id=' + params.author : ''),
            headers: {"Authorization": "Bearer " + ChannelVideos.VIMEO_ACCESS_TOKEN},
            success: function(response) {
                if (typeof response.data == 'undefined') {
                    loading.hide();
                    inner.html("<p>The vimeo request failed!</p>");
                    return;
                }

                if (response.data.length === 0) {
                    loading.hide();
                    inner.html("<p>No results found..</p>");
                    return;
                }

                // Fetch data for each video to display
                var Videos = [],
                    video_info;

                for (var i = 0; i < response.data.length; i++) {
                    video_info = response.data[i];

                    // Find the thumbnail URL
                    var thumb,
                        img_url;

                    for (var ii = video_info.pictures.sizes.length - 1; ii >= 0; ii--) {
                        thumb = video_info.pictures.sizes[ii];
                        if (thumb.width === 100) {
                            img_url = thumb.link;
                            break;
                        }
                    }

                    var video_id = video_info.uri.match(/(\d+)$/)[0];

                    Videos.push({
                        id: video_id,
                        title: video_info.name,
                        img_url: img_url,
                        vid_url: "https://player.vimeo.com/video/" + video_id + "?title=0&byline=0&portrait=0"
                    });
                }

                addVideoResults("vimeo", Videos, customField);
            }
        });
    }
    function vimeoSubmitUrl(url, customField) {
        var servicebox = customField.find(".VideosResults .results-vimeo").show();
        var inner = servicebox.find(".inner");
        var loading = servicebox.find(".LoadingVideos");
        var id = null;
        var parts;

        if (url.indexOf("vimeo") === -1) {
            loading.hide();
            inner.html("<p>Not a valid Vimeo URL</p>");
            return;
        }

        if (url.indexOf("vimeo.com/") > 0) {
            parts = url.split("/");
            id = parts[parts.length - 1];
        }

        if (id === null) {
            loading.hide();
            inner.html("<p>Could not parse Vimeo ID from that URL</p>");
            return;
        }

        $.ajax({
            url: 'https://api.vimeo.com/videos/' + id,
            headers: {"Authorization": "Bearer " + ChannelVideos.VIMEO_ACCESS_TOKEN},
            success: function(video_info) {
                if (typeof video_info.name == 'undefined') {
                    loading.hide();
                    inner.html("<p>The vimeo request failed!</p>");
                    return;
                }

                // Find the thumbnail URL
                var thumb,
                    img_url;

                for (var ii = video_info.pictures.sizes.length - 1; ii >= 0; ii--) {
                    thumb = video_info.pictures.sizes[ii];
                    if (thumb.width === 100) {
                        img_url = thumb.link;
                        break;
                    }
                }

                var Video = {
                    id: id,
                    title: video_info.name,
                    img_url: img_url,
                    vid_url: "https://player.vimeo.com/video/" + id + "?title=0&byline=0&portrait=0"
                };

                addVideoResults("vimeo", [Video], customField);
            }
        });
    }
    function vimeoGetVideo(id, field_id, callback) {
        $.ajax({
            url: 'https://api.vimeo.com/videos/' + id,
            headers: {"Authorization": "Bearer " + ChannelVideos.VIMEO_ACCESS_TOKEN},
            success: function(video_info) {
                if (typeof video_info.name == 'undefined') {
                    loading.hide();
                    inner.html("<p>The vimeo request failed!</p>");
                    return;
                }

                // Find the thumbnail URL
                var thumb,
                    img_url;

                for (var ii = video_info.pictures.sizes.length - 1; ii >= 0; ii--) {
                    thumb = video_info.pictures.sizes[ii];
                    if (thumb.width === 100) {
                        img_url = thumb.link;
                        break;
                    }
                }

                var Video = {
                    service: "vimeo",
                    service_video_id: id,
                    video_id: 0,
                    video_url: "https://player.vimeo.com/video/" + id + "?title=0&byline=0&portrait=0",
                    video_img_url: img_url,
                    video_title: video_info.name,
                    video_desc: video_info.description,
                    video_username: video_info.user.name,
                    video_author: video_info.user.name,
                    video_author_id: 0,
                    video_duration: video_info.duration,
                    video_views: video_info.stats.plays,
                    video_date: new Date(video_info.created_time.replace(" ", "T") + "-00:00").getTime() / 1e3
                };

                callback(Video, field_id);
            }
        });
    }
    function parseUrl(str, component) {
        var key = [ "source", "scheme", "authority", "userInfo", "user", "pass", "host", "port", "relative", "path", "directory", "file", "query", "fragment" ], ini = {}, mode = ini["phpjs.parse_url.mode"] && ini["phpjs.parse_url.mode"].local_value || "php", parser = {
            php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
            loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
        };
        var m = parser[mode].exec(str), uri = {}, i = 14;
        while (i--) {
            if (m[i]) {
                uri[key[i]] = m[i];
            }
        }
        if (component) {
            return uri[component.replace("PHP_URL_", "").toLowerCase()];
        }
        if (mode !== "php") {
            var name = ini["phpjs.parse_url.queryKey"] && ini["phpjs.parse_url.queryKey"].local_value || "queryKey";
            parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
            uri[name] = {};
            uri[key[12]].replace(parser, function($0, $1, $2) {
                if ($1) {
                    uri[name][$1] = $2;
                }
            });
        }
        delete uri.source;
        return uri;
    }
    function getQueryVariable(url, variable) {
        var query = url;
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (decodeURIComponent(pair[0]) == variable) {
                return decodeURIComponent(pair[1]);
            }
        }
        return null;
    }
})(window, jQuery);

(function(nezasa, undefined) {
    if (!nezasa.iso8601) nezasa.iso8601 = {};
    if (!nezasa.iso8601.Period) nezasa.iso8601.Period = {};
    nezasa.iso8601.version = "0.2";
    nezasa.iso8601.Period.parse = function(period, distributeOverflow) {
        return parsePeriodString(period, distributeOverflow);
    };
    nezasa.iso8601.Period.parseToTotalSeconds = function(period) {
        var multiplicators = [ 31104e3, 2592e3, 604800, 86400, 3600, 60, 1 ];
        var durationPerUnit = parsePeriodString(period);
        var durationInSeconds = 0;
        for (var i = 0; i < durationPerUnit.length; i++) {
            durationInSeconds += durationPerUnit[i] * multiplicators[i];
        }
        return durationInSeconds;
    };
    nezasa.iso8601.Period.isValid = function(period) {
        try {
            parsePeriodString(period);
            return true;
        } catch (e) {
            return false;
        }
    };
    nezasa.iso8601.Period.parseToString = function(period, unitNames, unitNamesPlural, distributeOverflow) {
        var result = [ "", "", "", "", "", "", "" ];
        var durationPerUnit = parsePeriodString(period, distributeOverflow);
        if (!unitNames) unitNames = [ "year", "month", "week", "day", "hour", "minute", "second" ];
        if (!unitNamesPlural) unitNamesPlural = [ "years", "months", "weeks", "days", "hours", "minutes", "seconds" ];
        for (var i = 0; i < durationPerUnit.length; i++) {
            if (durationPerUnit[i] > 0) {
                if (durationPerUnit[i] == 1) result[i] = durationPerUnit[i] + " " + unitNames[i]; else result[i] = durationPerUnit[i] + " " + unitNamesPlural[i];
            }
        }
        return result.join(" ").trim().replace(/[ ]{2,}/g, " ");
    };
    function parsePeriodString(period, _distributeOverflow) {
        var distributeOverflow = _distributeOverflow ? _distributeOverflow : false;
        var valueIndexes = [ 2, 3, 4, 5, 7, 8, 9 ];
        var duration = [ 0, 0, 0, 0, 0, 0, 0 ];
        var overflowLimits = [ 0, 12, 4, 7, 24, 60, 60 ];
        var struct;
        period = period.toUpperCase();
        if (!period) return duration; else if (typeof period !== "string") throw new Error("Invalid iso8601 period string '" + period + "'");
        if (struct = /^P((\d+Y)?(\d+M)?(\d+W)?(\d+D)?)?(T(\d+H)?(\d+M)?(\d+S)?)?$/.exec(period)) {
            for (var i = 0; i < valueIndexes.length; i++) {
                var structIndex = valueIndexes[i];
                duration[i] = struct[structIndex] ? +struct[structIndex].replace(/[A-Za-z]+/g, "") : 0;
            }
        } else {
            throw new Error("String '" + period + "' is not a valid ISO8601 period.");
        }
        if (distributeOverflow) {
            for (var i = duration.length - 1; i > 0; i--) {
                if (duration[i] >= overflowLimits[i]) {
                    duration[i - 1] = duration[i - 1] + Math.floor(duration[i] / overflowLimits[i]);
                    duration[i] = duration[i] % overflowLimits[i];
                }
            }
        }
        return duration;
    }
})(window.nezasa = window.nezasa || {});

$(document).ready(function() {
    ChannelVideos.Init();
});