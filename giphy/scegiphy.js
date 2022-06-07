/*
 * Add full giphy functionality to SCEditor
 */

$(function() {
    // return if scegiphy has been initialized
    if (window.scegiphy) {
        if (window.console && console.warn) {
            console.warn('scegiphy has already been initialized');
        }
        return;
    }


    giphyapikey = "dc6zaTOxFJmzC";

    if (window.giphykey)
    {
		giphyapikey = window.giphykey;
	}



    // setup global object
    window.scegiphy = {
        key: giphyapikey, // PUBLIC BETA KEY @TODO Jonathan change this to your personal api key to avoid rate limits
        limit: 26, // max image results
        delay: 200, // delay before searches commence (200ms)
        auto_close: true,

        // general language settings
        lang: {
            searching: 'Searching...',
            not_found: 'No results found... <img src="https://cdn.smfboards.com/caf/giphy/icon_sad.gif" style="margin:0;vertical-align:middle;"/>'
        },

        // dropdown markup
        dropDown: $(
            '<div>' +
            '<input type="text" id="scegiphy_search" placeholder="Search for a GIF..." style="width:90%;">' +
            '<div id="scegiphy_results" onscroll="scegiphy.scrolling(this);"><div id="scegiphy_images"></div></div>' +
            '<div id="giphy_attribution_mark"></div>' +
            '</div>'
        )[0],

        // initial setup of the scegiphy command
        init: function () {
            if ($.sceditor && window.toolbar) {
                // set the scegiphy command
                $.sceditor.command.set('scegiphy', {
                    tooltip: 'Find a GIF',

                    // Dropdown and general functionality for scegiphy
                    dropDown: function (editor, caller, callback) {
                        scegiphy.reset();
                        scegiphy.editor = editor;
                        scegiphy.callback = callback;
                        editor.createDropDown(caller, 'scegiphy', scegiphy.dropDown);

                        $('#scegiphy_search', scegiphy.dropDown)[0].focus(); // focus the search area
                    },

                    // WYSIWYG MODE
                    exec: function (caller) {
                        var editor = this;

                        $.sceditor.command.get('scegiphy').dropDown(editor, caller, function (gif) {
                            editor.insert('[img]' + gif + '[/img]');
                        });
                    },

                    // SOURCE MODE
                    txtExec: function (caller) {
                        var editor = this;

                        $.sceditor.command.get('scegiphy').dropDown(editor, caller, function (gif) {
                            editor.insertText('[img]' + gif + '[/img]');
                        });
                    }
                });

                // add CSS for button image and dropdown
                $('head').append(
                    '<style type="text/css">' +
                    '.sceditor-button-scegiphy div { background-image:url(https://cdn.smfboards.com/caf/giphy/giphy10.png) !important; }' +
                    '.sceditor-button-scegiphy:after, .sceditor-button-scegiphy:before { content:""; }' + // Forumactif Edge override
                    '#scegiphy_results { width:300px; margin:10px auto; min-height:30px; max-height:300px; overflow-x:hidden; overflow-y:auto; }' +
                    '.scegiphy_imagelist { line-height:0; column-count:2; column-gap:3px; }' +
                    '.scegiphy_imagelist img { margin-bottom:3px; cursor:pointer; width:100%; }' +
                    'html #giphy_attribution_mark { background:url(https://cdn.smfboards.com/caf/giphy/powere11.png) no-repeat 50% 50% transparent !important; height:22px !important; width:100%; !important; min-width:200px !important; display:block !important; visibility:visible !important; opacity:1 !important; }' +
                    '</style>'
                );
            }
        },

        // search for a GIPHY gif
        search: function (query) {
            if (scegiphy.timeout) {
                scegiphy.abort(); // abort ongoing searches and requests
            }

            if (query) {
                // set a small timeout in case the user is still typing
                scegiphy.timeout = window.setTimeout(function () {
                    scegiphy.reset(true, scegiphy.lang.searching);
                    scegiphy.query = encodeURIComponent(query);

                    scegiphy.request = $.get('https://api.giphy.com/v1/gifs/search?q=' + scegiphy.query + '&limit=' + scegiphy.limit + '&rating=pg-13&api_key=' + scegiphy.key, function (data) {
                        // update global data such as page offsets for scrolling
                        scegiphy.request = null;
                        scegiphy.offset = data.pagination.offset + scegiphy.limit;
                        scegiphy.offset_total = data.pagination.total_count;

                        scegiphy.reset(true); // reset HTML content
                        scegiphy.addGIF(data); // send data to be parsed
                    });

                }, scegiphy.delay);

            } else {
                scegiphy.reset(true);
            }
        },

        // abort ongoing searches and requests
        abort: function () {
            if (scegiphy.timeout) {
                window.clearInterval(scegiphy.timeout);
                scegiphy.timeout = null;
            }

            if (scegiphy.request) {
                scegiphy.request.abort();
                scegiphy.request = null;
            }
        },

        // add gifs to the result list
        addGIF: function (data, loadMore) {
            // setup data and begin parsing results
            var gif = data.data,
                i = 0,
                j = gif.length,
                list = $('<div class="scegiphy_imagelist" />')[0];

            if (j) {
                for (; i < j; i++) {
                    list.appendChild($('<img id="' + gif[i].id + '" src="' + gif[i].images.fixed_width.url + '" />').click(scegiphy.insert)[0]);
                }
            } else if (!loadMore) {
                scegiphy.reset(true, scegiphy.lang.not_found);
            }

            // add results to the result list
            $('#scegiphy_results', scegiphy.dropDown).append(list);
        },

        // listen to the scrolling so we can add more gifs when the user reaches the bottom
        scrolling: function (that) {
            if (that.scrollHeight - that.scrollTop === that.clientHeight) {
                scegiphy.loadMore();
            }
        },

        // load more results once the user has scrolled through the last results
        loadMore: function () {
            if (scegiphy.offset < scegiphy.offset_total) {
                scegiphy.request = $.get('https://api.giphy.com/v1/gifs/search?q=' + scegiphy.query + '&offset=' + scegiphy.offset + '&limit=' + scegiphy.limit + '&rating=pg-13&api_key=' + scegiphy.key, function (data) {
                    scegiphy.request = null;
                    scegiphy.offset = data.pagination.offset + scegiphy.limit;
                    scegiphy.offset_total = data.pagination.total_count;

                    scegiphy.addGIF(data, true); // send data to be parsed
                });
            }
        },

        // inserts the gif into the editor
        insert: function () {
            // add the gif to the editor and close the dropdown
            scegiphy.callback('https://media0.giphy.com/media/' + this.id + '/giphy.gif');

            if (scegiphy.auto_close) {
                scegiphy.editor.closeDropDown(true);
                scegiphy.reset();
            }
        },

        // reset the dropdown fields
        reset: function (resultsOnly, newContent) {
            $('#scegiphy_results', scegiphy.dropDown).html(newContent ? newContent : '');

            if (!resultsOnly) {
                $('#scegiphy_search', scegiphy.dropDown).val('');
            }
        }
    };

    // bind keyup event to search input
    $('#scegiphy_search', scegiphy.dropDown)[0].onkeyup = function (e) {
        var k = e.keyCode;

        // ignore specific key inputs to prevent unnecessary requests
        if (k && (k == 16 || k == 17 || k == 18 || k == 20 || k == 37 || k == 38 || k == 39 || k == 40)) {
            return;
        } else {
            scegiphy.search(this.value);
        }
    };

    // initilize scegiphy
    scegiphy.init();
});