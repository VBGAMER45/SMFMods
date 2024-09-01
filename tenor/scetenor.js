/*
 * Add full tenor functionality to SCEditor
 */

$(function() {
    // return if scetenor has been initialized
    if (window.scetenor) {
        if (window.console && console.warn) {
            console.warn('scetenor has already been initialized');
        }
        return;
    }


    tenorapikey = "";

    if (window.tenorkey)
    {
		tenorapikey = window.tenorkey;
	}



    // setup global object
    window.scetenor = {
        key: tenorapikey,
        limit: 26, // max image results
        delay: 400, // delay before searches commence (200ms)
        auto_close: true,

        // general language settings
        lang: {
            searching: 'Searching...',
            not_found: 'No results found... <img src="https://cdn.smfboards.com/caf/tenor/icon_sad.gif" style="margin:0;vertical-align:middle;"/>'
        },

        // dropdown markup
        dropDown: $(
            '<div>' +
            '<input type="text" id="scetenor_search" placeholder="Search Tenor for a GIF..." style="width:90%;">' +
            '<div id="scetenor_results" onscroll="scetenor.scrolling(this);"><div id="scetenor_images"></div></div>' +
            '<div id="tenor_attribution_mark"></div>' +
            '</div>'
        )[0],

        // initial setup of the scetenor command
        init: function () {
            if ($.sceditor && window.toolbar) {
                // set the scetenor command
                $.sceditor.command.set('scetenor', {
                    tooltip: 'Find a GIF',

                    // Dropdown and general functionality for scetenor
                    dropDown: function (editor, caller, callback) {
                        scetenor.reset();
                        scetenor.editor = editor;
                        scetenor.callback = callback;
                        editor.createDropDown(caller, 'scetenor', scetenor.dropDown);

                        $('#scetenor_search', scetenor.dropDown)[0].focus(); // focus the search area
                    },

                    // WYSIWYG MODE
                    exec: function (caller) {
                        var editor = this;

                        $.sceditor.command.get('scetenor').dropDown(editor, caller, function (gif) {
                            editor.insert('[img]' + gif + '[/img]');
                        });
                    },

                    // SOURCE MODE
                    txtExec: function (caller) {
                        var editor = this;

                        $.sceditor.command.get('scetenor').dropDown(editor, caller, function (gif) {
                            editor.insertText('[img]' + gif + '[/img]');
                        });
                    }
                });

                // add CSS for button image and dropdown
                $('head').append(
                    '<style type="text/css">' +
                    '.sceditor-button-scetenor div { background-image:url(https://cdn.smfboards.com/caf/tenor/tenor10.png) !important; }' +
                    '.sceditor-button-scetenor:after, .sceditor-button-scetenor:before { content:""; }' + // Forumactif Edge override
                    '#scetenor_results { width:300px; margin:10px auto; min-height:30px; max-height:300px; overflow-x:hidden; overflow-y:auto; }' +
                    '.scetenor_imagelist { line-height:0; column-count:2; column-gap:3px; }' +
                    '.scetenor_imagelist img { margin-bottom:3px; cursor:pointer; width:100%; }' +
                    'html #tenor_attribution_mark { background:url(https://cdn.smfboards.com/caf/tenor/powere11.png) no-repeat 50% 50% transparent !important; height:22px !important; width:100%; !important; min-width:200px !important; display:block !important; visibility:visible !important; opacity:1 !important; }' +
                    '</style>'
                );
            }
        },

        // search for a Tenor gif
        search: function (query) {
            if (scetenor.timeout) {
                scetenor.abort(); // abort ongoing searches and requests
            }

            if (query) {
                // set a small timeout in case the user is still typing
                scetenor.timeout = window.setTimeout(function () {
                    scetenor.reset(true, scetenor.lang.searching);
                    scetenor.query = encodeURIComponent(query);

                    scetenor.request = $.get('https://tenor.googleapis.com/v2/search?q=' + scetenor.query + '&key=' + scetenor.key + '&client_key=smf&locale=en&media_filter=gif&ContentFilter=low&limit=' + scetenor.limit, function (data) {
                        // update global data such as page offsets for scrolling
                        scetenor.request = null;
                        scetenor.offset = data.next;

                        scetenor.reset(true); // reset HTML content
                        scetenor.addGIF(data); // send data to be parsed
                    });

                }, scetenor.delay);

            } else {
                scetenor.reset(true);
            }
        },

        // abort ongoing searches and requests
        abort: function () {
            if (scetenor.timeout) {
                window.clearInterval(scetenor.timeout);
                scetenor.timeout = null;
            }

            if (scetenor.request) {
                scetenor.request.abort();
                scetenor.request = null;
            }
        },

        // add gifs to the result list
        addGIF: function (data, loadMore) {
            // setup data and begin parsing results
            var gif = data.results,
                i = 0,
                j = gif.length,
                list = $('<div class="scetenor_imagelist" />')[0];

            if (j) {
                for (; i < j; i++) {
                    list.appendChild($('<img id="' + gif[i].id + '" src="' + gif[i].media_formats.gif.url + '" onclick="scetenor.insert(\'' + gif[i].media_formats.gif.url +'\')" />')[0]);
                }
            } else if (!loadMore) {
                scetenor.reset(true, scetenor.lang.not_found);
            }

            // add results to the result list
            $('#scetenor_results', scetenor.dropDown).append(list);
        },

        // listen to the scrolling so we can add more gifs when the user reaches the bottom
        scrolling: function (that) {
            if (that.scrollHeight - that.scrollTop === that.clientHeight) {
                scetenor.loadMore();
            }
        },

        // load more results once the user has scrolled through the last results
        loadMore: function () {
            if (scetenor.offset != "") {

                scetenor.request = $.get('https://tenor.googleapis.com/v2/search?q=' + scetenor.query + '&key=' + scetenor.key + '&client_key=smf&locale=en&media_filter=gif&ContentFilter=low&limit=' + scetenor.limit + '&pos=' + scetenor.offset, function (data) {
                    scetenor.request = null;
                    scetenor.offset = data.next;


                    scetenor.addGIF(data, true); // send data to be parsed
                });
            }
        },

        // inserts the gif into the editor
        insert: function (mediaurl) {
            // add the gif to the editor and close the dropdown
            scetenor.callback(mediaurl);

            if (scetenor.auto_close) {
                scetenor.editor.closeDropDown(true);
                scetenor.reset();
            }
        },

        // reset the dropdown fields
        reset: function (resultsOnly, newContent) {
            $('#scetenor_results', scetenor.dropDown).html(newContent ? newContent : '');

            if (!resultsOnly) {
                $('#scetenor_search', scetenor.dropDown).val('');
            }
        }
    };

    // bind keyup event to search input
    $('#scetenor_search', scetenor.dropDown)[0].onkeyup = function (e) {
        var k = e.keyCode;

        // ignore specific key inputs to prevent unnecessary requests
        if (k && (k == 16 || k == 17 || k == 18 || k == 20 || k == 37 || k == 38 || k == 39 || k == 40)) {
            return;
        } else {
            scetenor.search(this.value);
        }
    };

    // initilize scetenor
    scetenor.init();
});