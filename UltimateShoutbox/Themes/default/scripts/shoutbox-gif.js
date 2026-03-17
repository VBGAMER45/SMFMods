/**
 * Ultimate Shoutbox & Chatroom - GIF Picker Module
 *
 * Lazy-loaded on demand when user clicks the GIF button.
 * Supports Tenor, Giphy, and Klipy via server-side proxy.
 *
 * @package Shoutbox
 * @version 1.0.0
 */

(function($, window) {
	'use strict';

	var config = window.smf_shoutbox_config || {};

	/**
	 * GifPicker - Search, grid, lazy loading, infinite scroll.
	 *
	 * @param {ShoutBox.InputHandler} inputHandler - Reference to insert text.
	 */
	ShoutBox.GifPicker = function(inputHandler) {
		this.inputHandler = inputHandler;
		this.$picker = $('#shoutbox_gif_picker');
		this.$searchInput = $('#shoutbox_gif_search');
		this.$results = $('#shoutbox_gif_results');
		this.$loading = $('#shoutbox_gif_loading');
		this.$closeBtn = $('#shoutbox_gif_close');

		this.currentQuery = '';
		this.nextPos = '';
		this.isLoading = false;
		this.isOpen = false;
		this.debounceTimer = null;
		this.observer = null;
		this.currentXhr = null;

		this.init();
	};

	ShoutBox.GifPicker.prototype = {
		init: function() {
			var self = this;

			// Search input with debounce.
			this.$searchInput.on('input', function() {
				clearTimeout(self.debounceTimer);
				self.debounceTimer = setTimeout(function() {
					self.search(self.$searchInput.val());
				}, 400);
			});

			// Close button.
			this.$closeBtn.on('click', function() {
				self.close();
			});

			// Infinite scroll.
			this.$results.on('scroll', function() {
				var el = this;
				if (el.scrollTop + el.clientHeight >= el.scrollHeight - 60) {
					self.loadMore();
				}
			});

			// Close on click outside.
			$(document).on('click', function(e) {
				if (self.isOpen &&
					!$(e.target).closest('#shoutbox_gif_picker').length &&
					!$(e.target).closest('#shoutbox_gif_btn').length) {
					self.close();
				}
			});

			// Escape key to close.
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && self.isOpen)
					self.close();
			});

			// Reposition on scroll/resize (picker is position:fixed).
			$(window).on('scroll resize', function() {
				if (self.isOpen)
					self.position();
			});

			// Set up IntersectionObserver for lazy loading images.
			if ('IntersectionObserver' in window) {
				this.observer = new IntersectionObserver(function(entries) {
					entries.forEach(function(entry) {
						if (entry.isIntersecting) {
							var img = entry.target;
							if (img.dataset.src) {
								img.src = img.dataset.src;
								img.removeAttribute('data-src');
								self.observer.unobserve(img);
							}
						}
					});
				}, {
					root: self.$results[0],
					rootMargin: '100px'
				});
			}
		},

		toggle: function() {
			if (this.isOpen)
				this.close();
			else
				this.open();
		},

		open: function() {
			this.isOpen = true;
			this.$picker.show();
			this.position();
			this.$searchInput.val('').focus();
			this.search(''); // Load featured/trending.
		},

		position: function() {
			var $btn = $('#shoutbox_gif_btn');
			if (!$btn.length) return;

			var btnRect = $btn[0].getBoundingClientRect();
			var pickerWidth = this.$picker.outerWidth();
			var pickerHeight = this.$picker.outerHeight();

			// Position above the button by default.
			var top = btnRect.top - pickerHeight - 8;
			var left = btnRect.right - pickerWidth;

			// If it goes off the top, position below the button instead.
			if (top < 10)
				top = btnRect.bottom + 8;

			// Keep within horizontal bounds.
			if (left < 10)
				left = 10;
			if (left + pickerWidth > window.innerWidth - 10)
				left = window.innerWidth - pickerWidth - 10;

			this.$picker.css({
				top: top + 'px',
				left: left + 'px',
				bottom: 'auto',
				right: 'auto'
			});
		},

		close: function() {
			this.isOpen = false;
			this.$picker.hide();
			this.$results.empty();
			this.currentQuery = '';
			this.nextPos = '';
		},

		search: function(query) {
			// Abort any pending request so new search takes priority.
			if (this.currentXhr) {
				this.currentXhr.abort();
				this.currentXhr = null;
				this.isLoading = false;
			}

			this.currentQuery = query;
			this.nextPos = '';
			this.$results.empty();
			this.fetchGifs(query, '');
		},

		loadMore: function() {
			if (this.isLoading || !this.nextPos)
				return;

			this.fetchGifs(this.currentQuery, this.nextPos);
		},

		fetchGifs: function(query, pos) {
			var self = this;
			this.isLoading = true;
			this.$loading.show();

			this.currentXhr = $.ajax({
				url: config.ajaxUrl,
				cache: false,
				data: {
					sa: 'gif_proxy',
					q: query,
					pos: pos
				},
				dataType: 'json',
				timeout: 10000
			})
			.done(function(data) {
				if (data.gifs && data.gifs.length > 0) {
					self.renderGifs(data.gifs);
					self.nextPos = data.next || '';
				} else if (!pos) {
					self.$results.html('<div class="gif-no-results">No GIFs found.</div>');
					self.nextPos = '';
				}
			})
			.fail(function(jqXHR, textStatus) {
				if (textStatus !== 'abort' && !pos)
					self.$results.html('<div class="gif-no-results">Search failed. Try again.</div>');
			})
			.always(function() {
				self.currentXhr = null;
				self.isLoading = false;
				self.$loading.hide();
			});
		},

		renderGifs: function(gifs) {
			var self = this;
			var frag = document.createDocumentFragment();

			for (var i = 0; i < gifs.length; i++) {
				var gif = gifs[i];
				var img = document.createElement('img');

				// Use lazy loading.
				if (this.observer) {
					img.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
					img.dataset.src = gif.thumb;
				} else {
					img.src = gif.thumb;
				}

				img.alt = gif.alt;
				img.title = gif.alt;
				img.loading = 'lazy';
				img.dataset.fullUrl = gif.full;
				img.dataset.gifId = gif.id;

				img.addEventListener('click', function() {
					self.selectGif(this.dataset.fullUrl, this.dataset.gifId);
				});

				frag.appendChild(img);

				if (this.observer)
					this.observer.observe(img);
			}

			this.$results.append(frag);
		},

		selectGif: function(fullUrl, gifId) {
			// Insert as [img] BBCode if BBCode is enabled, otherwise raw URL.
			var insertion;
			if (config.enableBBCode)
				insertion = '[img]' + fullUrl + '[/img]';
			else
				insertion = fullUrl;

			this.inputHandler.insertText(insertion);
			this.close();
		}
	};

})(jQuery, window);
