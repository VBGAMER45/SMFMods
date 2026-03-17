/**
 * Ultimate Shoutbox & Chatroom - Core JavaScript
 *
 * IIFE + prototype pattern matching SMF conventions.
 * Uses jQuery (bundled with SMF 2.1) and custom events as event bus.
 *
 * @package Shoutbox
 * @version 1.0.0
 */

(function($, window, document) {
	'use strict';

	// Namespace
	window.ShoutBox = window.ShoutBox || {};

	// Config is read lazily in $(document).ready() because SMF outputs
	// inline JS (where the config is set) AFTER external script tags.
	var config = {};

	// Helper: build POST data with dynamic session variable name.
	function postData(obj) {
		var data = $.extend({}, obj);
		data[config.sessionVar] = config.sessionId;
		return data;
	}

	// =========================================================================
	// State - Centralized state management
	// =========================================================================

	ShoutBox.State = function() {
		this.lastMessageId = 0;
		this.messages = [];
		this.onlineUsers = [];
		this.isCollapsed = false;
		this.soundEnabled = true;
		this.editingMessageId = null;
		this.unreadCount = 0;
		this.isTabVisible = true;
		this.currentRoomId = config.roomId || 0;

		// Load preferences from localStorage.
		this.loadPreferences();
	};

	ShoutBox.State.prototype = {
		loadPreferences: function() {
			try {
				this.isCollapsed = localStorage.getItem('shoutbox_collapsed') === '1';
				this.soundEnabled = localStorage.getItem('shoutbox_sound') !== '0';
			} catch (e) {}
		},

		savePreference: function(key, value) {
			try {
				localStorage.setItem('shoutbox_' + key, value);
			} catch (e) {}
		},

		addMessages: function(newMessages) {
			if (!newMessages || !newMessages.length)
				return;

			for (var i = 0; i < newMessages.length; i++) {
				var msg = newMessages[i];
				if (msg.id > this.lastMessageId)
					this.lastMessageId = msg.id;
				this.messages.push(msg);
			}

			// Cap message count.
			var max = config.maxDisplay || 25;
			if (this.messages.length > max)
				this.messages = this.messages.slice(this.messages.length - max);
		},

		removeMessage: function(id) {
			this.messages = this.messages.filter(function(m) { return m.id !== id; });
		},

		updateMessage: function(id, newBody) {
			for (var i = 0; i < this.messages.length; i++) {
				if (this.messages[i].id === id) {
					this.messages[i].body = newBody;
					this.messages[i].editedAt = Math.floor(Date.now() / 1000);
					break;
				}
			}
		}
	};

	// =========================================================================
	// Poller - AJAX polling with adaptive intervals
	// =========================================================================

	ShoutBox.Poller = function(state) {
		this.state = state;
		this.timer = null;
		this.interval = config.pollInterval || 3000;
		this.baseInterval = this.interval;
		this.maxInterval = 15000;
		this.errorCount = 0;
		this.isRunning = false;
		this.xhr = null;
	};

	ShoutBox.Poller.prototype = {
		start: function() {
			if (this.isRunning)
				return;
			this.isRunning = true;
			this.poll();
		},

		stop: function() {
			this.isRunning = false;
			clearTimeout(this.timer);
			if (this.xhr)
				this.xhr.abort();
		},

		poll: function() {
			var self = this;

			if (!this.isRunning)
				return;

			// Capture room at poll start for stale-response guard.
			var pollRoomId = this.state.currentRoomId;

			this.xhr = $.ajax({
				url: config.ajaxUrl,
				data: {
					sa: 'fetch',
					last_id: this.state.lastMessageId,
					chatroom: config.isChatroom ? 1 : 0,
					room_id: this.state.currentRoomId
				},
				dataType: 'json',
				timeout: 10000
			})
			.done(function(data) {
				// Stale-response guard: discard if room changed during request.
				if (pollRoomId !== self.state.currentRoomId)
					return;

				self.errorCount = 0;
				self.interval = self.state.isTabVisible ? self.baseInterval : self.maxInterval;

				// Check if server redirected us to a different room (access revoked).
				if (data.roomId && data.roomId !== self.state.currentRoomId) {
					$(document).trigger('shoutbox:roomRedirect', [data.roomId]);
					return;
				}

				if (data.messages && data.messages.length > 0) {
					self.state.addMessages(data.messages);
					$(document).trigger('shoutbox:newMessages', [data.messages]);
				}

				if (data.onlineUsers) {
					self.state.onlineUsers = data.onlineUsers;
					$(document).trigger('shoutbox:onlineUsersUpdated', [data.onlineUsers]);
				}
			})
			.fail(function(jqXHR, textStatus) {
				if (textStatus === 'abort')
					return;

				self.errorCount++;
				// Exponential backoff on errors.
				self.interval = Math.min(self.baseInterval * Math.pow(2, self.errorCount), 30000);
				$(document).trigger('shoutbox:pollError', [self.errorCount]);
			})
			.always(function() {
				if (self.isRunning)
					self.timer = setTimeout(function() { self.poll(); }, self.interval);
			});
		},

		resetInterval: function() {
			this.interval = this.baseInterval;
		}
	};

	// =========================================================================
	// MessageRenderer - DOM construction with DocumentFragment
	// =========================================================================

	ShoutBox.MessageRenderer = function(state, container) {
		this.state = state;
		this.$container = $(container);
		this.autoScroll = true;
	};

	ShoutBox.MessageRenderer.prototype = {
		renderInitial: function(messages) {
			this.$container.empty();

			if (!messages || messages.length === 0) {
				this.$container.html('<div class="shoutbox-no-messages">' +
					(window.smf_shoutbox_txt ? smf_shoutbox_txt.no_messages : 'No messages yet.') +
					'</div>');
				return;
			}

			var displayMessages = config.newestFirst ? messages.slice().reverse() : messages;

			var frag = document.createDocumentFragment();
			for (var i = 0; i < displayMessages.length; i++) {
				frag.appendChild(this.createMessageElement(displayMessages[i]));
			}
			this.$container.append(frag);

			if (!config.newestFirst)
				this.scrollToBottom(true);
		},

		appendMessages: function(messages) {
			if (!messages || messages.length === 0)
				return;

			// Remove "no messages" placeholder.
			this.$container.find('.shoutbox-no-messages').remove();

			var max = config.maxDisplay || 25;

			if (config.newestFirst) {
				// Check if user is scrolled near top before prepending.
				var el = this.$container[0];
				this.autoScroll = el.scrollTop < 50;

				var frag = document.createDocumentFragment();
				// Reverse so newest is first in fragment.
				for (var i = messages.length - 1; i >= 0; i--) {
					frag.appendChild(this.createMessageElement(messages[i]));
				}
				this.$container.prepend(frag);

				// Cap from bottom (remove oldest).
				var children = this.$container.children('.shoutbox-message');
				if (children.length > max)
					children.slice(max).remove();

				if (this.autoScroll)
					this.$container[0].scrollTop = 0;
			} else {
				// Default behavior: newest at bottom.
				var el = this.$container[0];
				this.autoScroll = (el.scrollHeight - el.scrollTop - el.clientHeight) < 50;

				var frag = document.createDocumentFragment();
				for (var i = 0; i < messages.length; i++) {
					frag.appendChild(this.createMessageElement(messages[i]));
				}
				this.$container.append(frag);

				// Cap from top (remove oldest).
				var children = this.$container.children('.shoutbox-message');
				if (children.length > max)
					children.slice(0, children.length - max).remove();

				if (this.autoScroll)
					this.scrollToBottom(false);
			}
		},

		createMessageElement: function(msg) {
			var div = document.createElement('div');
			div.className = 'shoutbox-message';
			div.setAttribute('data-id', msg.id);
			div.setAttribute('data-member-id', msg.memberId);

			if (msg.isWhisper)
				div.className += ' shoutbox-message-whisper';
			if (msg.isAdmin)
				div.className += ' shoutbox-message-admin';
			if (msg.isAction)
				div.className += ' shoutbox-message-action';

			var html = '';

			// Avatar.
			if (config.showAvatars && msg.avatar) {
				html += '<div class="shoutbox-message-avatar">' +
					'<img src="' + this.escapeHtml(msg.avatar) + '" alt="" loading="lazy" />' +
					'</div>';
			}

			html += '<div class="shoutbox-message-content">';

			var colorStyle = msg.memberColor ? ' style="color: ' + this.escapeHtml(msg.memberColor) + '"' : '';

			if (msg.isAction) {
				html += '<span class="shoutbox-message-author"' + colorStyle + '>' +
					(msg.profileUrl ? '<a href="' + this.escapeHtml(msg.profileUrl) + '"' + colorStyle + '>' : '') +
					this.escapeHtml(msg.memberName) +
					(msg.profileUrl ? '</a>' : '') +
					'</span> ';
			} else {
				html += '<a href="' + this.escapeHtml(msg.profileUrl || '#') + '" class="shoutbox-message-author"' + colorStyle + '>' +
					this.escapeHtml(msg.memberName) + '</a>';

				if (msg.isWhisper)
					html += '<span class="shoutbox-whisper-badge">whisper</span>';
				if (msg.isAdmin)
					html += '<span class="shoutbox-admin-badge">admin</span>';
			}

			html += '<span class="shoutbox-message-time">' + this.formatTime(msg.createdAt) + '</span>';
			html += '<div class="shoutbox-message-body">' + msg.body + '</div>';

			if (msg.editedAt > 0)
				html += '<span class="shoutbox-message-edited">(edited)</span>';

			// Reaction bar.
			if (config.enableReactions && msg.reactions) {
				var hasReactions = false;
				var reactionTypes = config.reactionTypes || {};
				var rKeys = [];
				for (var rKey in reactionTypes) {
					if (reactionTypes.hasOwnProperty(rKey))
						rKeys.push(rKey);
				}
				// Check if any reactions exist.
				for (var ri = 0; ri < rKeys.length; ri++) {
					if (msg.reactions[rKeys[ri]] && msg.reactions[rKeys[ri]].count > 0) {
						hasReactions = true;
						break;
					}
				}
				if (hasReactions) {
					html += '<div class="shoutbox-reactions-bar">';
					for (var rj = 0; rj < rKeys.length; rj++) {
						var rk = rKeys[rj];
						var rd = msg.reactions[rk];
						if (!rd || !rd.count || rd.count <= 0)
							continue;
						var rType = reactionTypes[rk];
						var pillClass = 'shoutbox-reaction-pill';
						if (rd.reacted)
							pillClass += ' reacted';
						var tooltip = rd.users ? rd.users.join(', ') : '';
						html += '<button class="' + pillClass + '" data-msg-id="' + msg.id + '" data-reaction-type="' + rk + '" title="' + this.escapeHtml(tooltip) + '">';
						html += '<img src="' + config.reactionsImgUrl + rType.icon + '" alt="' + this.escapeHtml(rType.label) + '" width="14" height="14" />';
						html += '<span class="reaction-count">' + rd.count + '</span>';
						html += '</button>';
					}
					html += '</div>';
				}
			}

			// Action buttons (shown on hover).
			if (config.canModerate || (config.userId === msg.memberId && config.canPost) || (!config.isGuest && config.canPost)) {
				html += '<div class="shoutbox-message-actions">';
				if (!config.isGuest && config.canPost)
					html += '<button class="shoutbox-msg-action-btn shoutbox-quote-btn" title="Quote">&#8220;</button>';
				if (config.canModerate || config.userId === msg.memberId)
					html += '<button class="shoutbox-msg-action-btn shoutbox-edit-btn" title="Edit">&#9998;</button>';
				if (config.canModerate || config.userId === msg.memberId)
					html += '<button class="shoutbox-msg-action-btn shoutbox-delete-btn" title="Delete">&times;</button>';
				if (config.canModerate && config.userId !== msg.memberId)
					html += '<button class="shoutbox-msg-action-btn shoutbox-ban-btn" title="Ban User">&#128683;</button>';
				if (config.enableReactions && !config.isGuest && config.canPost)
					html += '<button class="shoutbox-msg-action-btn shoutbox-react-btn" data-msg-id="' + msg.id + '" title="React">&#9786;</button>';
				html += '</div>';
			}

			html += '</div>';
			div.innerHTML = html;
			return div;
		},

		removeMessage: function(id) {
			this.$container.find('[data-id="' + id + '"]').fadeOut(200, function() { $(this).remove(); });
		},

		updateMessage: function(id, newBody) {
			var $msg = this.$container.find('[data-id="' + id + '"]');
			$msg.find('.shoutbox-message-body').html(newBody);
			if (!$msg.find('.shoutbox-message-edited').length)
				$msg.find('.shoutbox-message-content').append('<span class="shoutbox-message-edited">(edited)</span>');
		},

		scrollToBottom: function(instant) {
			var el = this.$container[0];
			if (instant)
				el.scrollTop = el.scrollHeight;
			else
				this.$container.animate({ scrollTop: el.scrollHeight }, 150);
		},

		formatTime: function(timestamp) {
			var now = Math.floor(Date.now() / 1000);
			var diff = now - timestamp;

			if (diff < 60) return 'just now';
			if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
			if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';

			var date = new Date(timestamp * 1000);
			var today = new Date();

			if (date.toDateString() === today.toDateString())
				return this.pad(date.getHours()) + ':' + this.pad(date.getMinutes());

			return (date.getMonth() + 1) + '/' + date.getDate() + ' ' +
				this.pad(date.getHours()) + ':' + this.pad(date.getMinutes());
		},

		pad: function(n) {
			return n < 10 ? '0' + n : '' + n;
		},

		escapeHtml: function(str) {
			if (!str) return '';
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}
	};

	// =========================================================================
	// InputHandler - Enter to send, slash commands, character counter
	// =========================================================================

	ShoutBox.InputHandler = function(state) {
		this.state = state;
		this.$input = $('#shoutbox_input');
		this.$charCount = $('#shoutbox_char_count');
		this.$sendBtn = $('#shoutbox_send_btn');
		this.sending = false;

		if (this.$input.length)
			this.init();
	};

	ShoutBox.InputHandler.prototype = {
		init: function() {
			var self = this;

			this.$input.on('keydown', function(e) {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					self.send();
				}
			});

			this.$input.on('input', function() {
				self.updateCharCount();
				self.autoResize();
			});

			this.$sendBtn.on('click', function() {
				self.send();
			});

			this.updateCharCount();
		},

		send: function() {
			if (this.sending)
				return;

			var body = $.trim(this.$input.val());
			if (!body)
				return;

			// Check for slash commands.
			if (this.handleSlashCommand(body))
				return;

			this.sending = true;
			var self = this;

			var sendData = postData({
				sa: 'send',
				body: body,
				room_id: this.state.currentRoomId
			});

			// Include pending attachment IDs if uploader is available.
			if (ShoutBox.controller && ShoutBox.controller.fileUploader) {
				var attIds = ShoutBox.controller.fileUploader.getPendingAttachments();
				for (var ai = 0; ai < attIds.length; ai++)
					sendData['attachment_ids[' + ai + ']'] = attIds[ai];
			}

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: sendData,
				dataType: 'json'
			})
			.done(function() {
				self.$input.val('');
				self.updateCharCount();
				self.autoResize();
				$(document).trigger('shoutbox:messageSent');
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				var msg = (resp && resp.message) ? resp.message : 'Failed to send message.';
				$(document).trigger('shoutbox:error', [msg]);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		handleSlashCommand: function(body) {
			// /whisper or /w
			var whisperMatch = body.match(/^\/(?:whisper|w)\s+(\S+)\s+([\s\S]+)$/i);
			if (whisperMatch) {
				this.sendWhisper(whisperMatch[1], whisperMatch[2]);
				return true;
			}

			// /prune N (moderators only)
			var pruneMatch = body.match(/^\/prune\s+(\d+)$/i);
			if (pruneMatch && config.canModerate) {
				this.sendPrune(parseInt(pruneMatch[1], 10));
				return true;
			}

			// /clean (admin only)
			if (/^\/clean$/i.test(body) && config.canModerate) {
				this.sendClean();
				return true;
			}

			// /mute username (moderators only)
			var muteMatch = body.match(/^\/mute\s+(\S+)$/i);
			if (muteMatch && config.canModerate) {
				this.sendMute(muteMatch[1]);
				return true;
			}

			// /unmute username (moderators only)
			var unmuteMatch = body.match(/^\/unmute\s+(\S+)$/i);
			if (unmuteMatch && config.canModerate) {
				this.sendUnmute(unmuteMatch[1]);
				return true;
			}

			// /mutelist (moderators only)
			if (/^\/mutelist$/i.test(body) && config.canModerate) {
				this.sendMutelist();
				return true;
			}

			// /a or /admin message (moderators only)
			var adminMatch = body.match(/^\/(?:admin|a)\s+([\s\S]+)$/i);
			if (adminMatch && config.canModerate) {
				this.sendAdminMsg(adminMatch[1]);
				return true;
			}

			return false;
		},

		sendWhisper: function(toName, body) {
			if (!config.canWhisper)
				return;

			this.sending = true;
			var self = this;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'whisper',
					to: toName,
					body: body,
					room_id: this.state.currentRoomId
				}),
				dataType: 'json'
			})
			.done(function() {
				self.$input.val('');
				self.updateCharCount();
				$(document).trigger('shoutbox:messageSent');
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				var msg = (resp && resp.message) ? resp.message : 'Whisper failed.';
				$(document).trigger('shoutbox:error', [msg]);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		sendPrune: function(count) {
			if (!confirm('Are you sure you want to prune ' + count + ' messages?'))
				return;

			var self = this;
			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'prune',
					count: count,
					room_id: this.state.currentRoomId
				}),
				dataType: 'json'
			})
			.done(function() {
				self.$input.val('');
				location.reload();
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Prune failed.']);
			});
		},

		sendClean: function() {
			if (!confirm('Are you sure you want to delete ALL messages? This cannot be undone.'))
				return;

			var self = this;
			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'clean',
					room_id: this.state.currentRoomId
				}),
				dataType: 'json'
			})
			.done(function() {
				self.$input.val('');
				location.reload();
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Clean failed.']);
			});
		},

		sendMute: function(name) {
			var self = this;
			this.sending = true;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'mute',
					username: name
				}),
				dataType: 'json'
			})
			.done(function(data) {
				self.$input.val('');
				self.updateCharCount();
				var mutedName = (data && data.muted_name) ? data.muted_name : name;
				$(document).trigger('shoutbox:notification', ['User "' + mutedName + '" has been muted.']);
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Mute failed.']);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		sendUnmute: function(name) {
			var self = this;
			this.sending = true;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'unmute',
					username: name
				}),
				dataType: 'json'
			})
			.done(function(data) {
				self.$input.val('');
				self.updateCharCount();
				var unmutedName = (data && data.unmuted_name) ? data.unmuted_name : name;
				$(document).trigger('shoutbox:notification', ['User "' + unmutedName + '" has been unmuted.']);
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Unmute failed.']);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		sendMutelist: function() {
			var self = this;
			this.sending = true;

			$.ajax({
				url: config.ajaxUrl,
				data: { sa: 'mutelist' },
				dataType: 'json'
			})
			.done(function(data) {
				self.$input.val('');
				self.updateCharCount();

				var $container = $('#shoutbox_messages');
				// Remove any previous mutelist message.
				$container.find('.shoutbox-system-message').remove();

				var html = '<div class="shoutbox-system-message">';
				if (!data.bans || data.bans.length === 0) {
					html += '<strong>Mute List:</strong> No users are currently muted.';
				} else {
					html += '<strong>Mute List (' + data.bans.length + '):</strong><br>';
					for (var i = 0; i < data.bans.length; i++) {
						var ban = data.bans[i];
						html += '&bull; <strong>' + self.escapeHtml(ban.name) + '</strong>';
						if (ban.banned_by_name)
							html += ' — muted by ' + self.escapeHtml(ban.banned_by_name);
						if (ban.reason)
							html += ' (' + self.escapeHtml(ban.reason) + ')';
						if (ban.expires_at > 0) {
							var expDate = new Date(ban.expires_at * 1000);
							html += ' — expires ' + expDate.toLocaleString();
						} else {
							html += ' — permanent';
						}
						html += '<br>';
					}
				}
				html += '</div>';

				$container.append(html);
				// Scroll to bottom.
				var el = $container[0];
				el.scrollTop = el.scrollHeight;
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Failed to load mute list.']);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		sendAdminMsg: function(body) {
			this.sending = true;
			var self = this;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'admin_msg',
					body: body,
					room_id: this.state.currentRoomId
				}),
				dataType: 'json'
			})
			.done(function() {
				self.$input.val('');
				self.updateCharCount();
				self.autoResize();
				$(document).trigger('shoutbox:messageSent');
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Admin message failed.']);
			})
			.always(function() {
				self.sending = false;
				self.$input.focus();
			});
		},

		escapeHtml: function(str) {
			if (!str) return '';
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		},

		updateCharCount: function() {
			var len = this.$input.val().length;
			var max = config.maxLength || 500;
			var remaining = max - len;

			this.$charCount.text(remaining);
			this.$charCount.toggleClass('warning', remaining < 50 && remaining >= 10);
			this.$charCount.toggleClass('danger', remaining < 10);
		},

		autoResize: function() {
			var el = this.$input[0];
			el.style.height = 'auto';
			el.style.height = Math.min(el.scrollHeight, 80) + 'px';
		},

		insertText: function(text) {
			var el = this.$input[0];
			var start = el.selectionStart;
			var end = el.selectionEnd;
			var val = el.value;
			el.value = val.substring(0, start) + text + val.substring(end);
			el.selectionStart = el.selectionEnd = start + text.length;
			this.$input.trigger('input');
			this.$input.focus();
		},

		surroundText: function(before, after) {
			var el = this.$input[0];
			var start = el.selectionStart;
			var end = el.selectionEnd;
			var val = el.value;
			var selected = val.substring(start, end);
			el.value = val.substring(0, start) + before + selected + after + val.substring(end);
			if (selected.length > 0) {
				el.selectionStart = start;
				el.selectionEnd = start + before.length + selected.length + after.length;
			} else {
				el.selectionStart = el.selectionEnd = start + before.length;
			}
			this.$input.trigger('input');
			this.$input.focus();
		}
	};

	// =========================================================================
	// MentionEngine - @mention autocomplete
	// =========================================================================

	ShoutBox.MentionEngine = function(state) {
		this.state = state;
		this.$input = $('#shoutbox_input');
		this.$dropdown = $('#shoutbox_mention_dropdown');
		this.searchTimer = null;
		this.selectedIndex = -1;
		this.mentionStart = -1;
		this.isActive = false;

		if (config.enableMentions && this.$input.length)
			this.init();
	};

	ShoutBox.MentionEngine.prototype = {
		init: function() {
			var self = this;

			this.$input.on('input', function() {
				self.checkMention();
			});

			this.$input.on('keydown', function(e) {
				if (!self.isActive)
					return;

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					self.moveSelection(1);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					self.moveSelection(-1);
				} else if (e.key === 'Enter' || e.key === 'Tab') {
					if (self.selectedIndex >= 0) {
						e.preventDefault();
						self.selectCurrent();
					}
				} else if (e.key === 'Escape') {
					self.hide();
				}
			});

			$(document).on('click', function(e) {
				if (!$(e.target).closest('.shoutbox-mention-dropdown').length)
					self.hide();
			});
		},

		checkMention: function() {
			var val = this.$input.val();
			var pos = this.$input[0].selectionStart;
			var textBefore = val.substring(0, pos);

			// Find @ that starts a mention.
			var atIndex = textBefore.lastIndexOf('@');

			if (atIndex < 0 || (atIndex > 0 && textBefore[atIndex - 1] !== ' ' && textBefore[atIndex - 1] !== '\n')) {
				this.hide();
				return;
			}

			var query = textBefore.substring(atIndex + 1);
			if (query.length < 1 || query.indexOf(' ') >= 0) {
				this.hide();
				return;
			}

			this.mentionStart = atIndex;
			this.searchUsers(query);
		},

		searchUsers: function(query) {
			var self = this;
			clearTimeout(this.searchTimer);

			this.searchTimer = setTimeout(function() {
				// First check online users from state.
				var results = [];
				var lowerQuery = query.toLowerCase();

				for (var i = 0; i < self.state.onlineUsers.length; i++) {
					var user = self.state.onlineUsers[i];
					if (user.name.toLowerCase().indexOf(lowerQuery) === 0)
						results.push(user);
				}

				if (results.length > 0) {
					self.showResults(results);
				} else {
					// Fall back to SMF suggest endpoint.
					$.ajax({
						url: config.smfSuggestUrl,
						data: { search: query },
						dataType: 'json'
					})
					.done(function(data) {
						if (data && data.length > 0) {
							var users = [];
							for (var i = 0; i < Math.min(data.length, 8); i++) {
								users.push({
									id: data[i].id,
									name: data[i].name || data[i].text || data[i]
								});
							}
							self.showResults(users);
						} else {
							self.hide();
						}
					})
					.fail(function() {
						self.hide();
					});
				}
			}, 200);
		},

		showResults: function(users) {
			this.isActive = true;
			this.selectedIndex = 0;

			var html = '';
			for (var i = 0; i < users.length; i++) {
				html += '<div class="shoutbox-mention-item' + (i === 0 ? ' active' : '') +
					'" data-name="' + this.escapeAttr(users[i].name) + '">' +
					this.escapeHtml(users[i].name) + '</div>';
			}

			this.$dropdown.html(html).show();

			// Position near the cursor.
			this.positionDropdown();

			var self = this;
			this.$dropdown.find('.shoutbox-mention-item').on('click', function() {
				self.selectItem($(this).data('name'));
			}).on('mouseenter', function() {
				self.$dropdown.find('.active').removeClass('active');
				$(this).addClass('active');
				self.selectedIndex = $(this).index();
			});
		},

		positionDropdown: function() {
			var inputPos = this.$input.offset();
			this.$dropdown.css({
				left: inputPos.left + 'px',
				top: (inputPos.top - this.$dropdown.outerHeight() - 4) + 'px'
			});
		},

		moveSelection: function(dir) {
			var items = this.$dropdown.find('.shoutbox-mention-item');
			items.eq(this.selectedIndex).removeClass('active');
			this.selectedIndex = (this.selectedIndex + dir + items.length) % items.length;
			items.eq(this.selectedIndex).addClass('active');
		},

		selectCurrent: function() {
			var items = this.$dropdown.find('.shoutbox-mention-item');
			if (this.selectedIndex >= 0 && this.selectedIndex < items.length) {
				this.selectItem(items.eq(this.selectedIndex).data('name'));
			}
		},

		selectItem: function(name) {
			var val = this.$input.val();
			var before = val.substring(0, this.mentionStart);
			var after = val.substring(this.$input[0].selectionStart);
			var newVal = before + '@' + name + ' ' + after;

			this.$input.val(newVal);
			this.$input[0].selectionStart = this.$input[0].selectionEnd = before.length + name.length + 2;
			this.$input.trigger('input');
			this.hide();
		},

		hide: function() {
			this.isActive = false;
			this.selectedIndex = -1;
			this.$dropdown.hide().empty();
		},

		escapeHtml: function(str) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		},

		escapeAttr: function(str) {
			return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
		}
	};

	// =========================================================================
	// CommandAutocomplete - /slash command dropdown
	// =========================================================================

	ShoutBox.CommandAutocomplete = function(state, mentions) {
		this.state = state;
		this.mentions = mentions;
		this.$input = $('#shoutbox_input');
		this.$dropdown = $('#shoutbox_mention_dropdown');
		this.isActive = false;
		this.selectedIndex = -1;
		this.commands = [];

		// Build command list based on permissions.
		if (config.canWhisper) {
			this.commands.push({ name: '/whisper', insert: '/whisper ', desc: 'Send a private message' });
			this.commands.push({ name: '/w', insert: '/w ', desc: 'Shorthand for /whisper' });
		}
		if (config.canModerate) {
			this.commands.push({ name: '/prune', insert: '/prune ', desc: 'Delete N recent messages' });
			this.commands.push({ name: '/clean', insert: '/clean', desc: 'Clear all messages' });
			this.commands.push({ name: '/mute', insert: '/mute ', desc: 'Mute a user' });
			this.commands.push({ name: '/unmute', insert: '/unmute ', desc: 'Unmute a user' });
			this.commands.push({ name: '/mutelist', insert: '/mutelist', desc: 'Show muted users' });
			this.commands.push({ name: '/admin', insert: '/admin ', desc: 'Message visible to mods only' });
			this.commands.push({ name: '/a', insert: '/a ', desc: 'Shorthand for /admin' });
		}

		if (this.commands.length && this.$input.length)
			this.init();
	};

	ShoutBox.CommandAutocomplete.prototype = {
		init: function() {
			var self = this;

			this.$input.on('input.cmdautocomplete', function() {
				self.checkSlash();
			});

			this.$input.on('keydown.cmdautocomplete', function(e) {
				if (!self.isActive)
					return;

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					self.moveSelection(1);
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					self.moveSelection(-1);
				} else if (e.key === 'Enter' || e.key === 'Tab') {
					if (self.selectedIndex >= 0) {
						e.preventDefault();
						self.selectCurrent();
					}
				} else if (e.key === 'Escape') {
					self.hide();
				}
			});

			$(document).on('click.cmdautocomplete', function(e) {
				if (!$(e.target).closest('.shoutbox-mention-dropdown').length)
					self.hide();
			});
		},

		checkSlash: function() {
			// Don't activate if mentions dropdown is active.
			if (this.mentions && this.mentions.isActive)
				return;

			var val = this.$input.val();

			// Must start with /
			if (val.charAt(0) !== '/') {
				this.hide();
				return;
			}

			// Extract the command-word being typed (everything up to the first space).
			var spaceIdx = val.indexOf(' ');
			var query = spaceIdx >= 0 ? val.substring(0, spaceIdx) : val;

			// If there's already a space, the command word is complete — hide.
			if (spaceIdx >= 0) {
				this.hide();
				return;
			}

			this.showMatches(query.toLowerCase());
		},

		showMatches: function(query) {
			var matches = [];
			for (var i = 0; i < this.commands.length; i++) {
				if (this.commands[i].name.indexOf(query) === 0)
					matches.push(this.commands[i]);
			}

			if (matches.length === 0) {
				this.hide();
				return;
			}

			this.isActive = true;
			this.selectedIndex = 0;

			var html = '';
			for (var i = 0; i < matches.length; i++) {
				html += '<div class="shoutbox-command-item' + (i === 0 ? ' active' : '') +
					'" data-index="' + i + '">' +
					'<div class="shoutbox-command-name">' + this.escapeHtml(matches[i].name) + '</div>' +
					'<div class="shoutbox-command-desc">' + this.escapeHtml(matches[i].desc) + '</div>' +
					'</div>';
			}

			this.$dropdown.html(html).show();
			this.positionDropdown();

			this._matches = matches;

			var self = this;
			this.$dropdown.find('.shoutbox-command-item').on('click', function() {
				var idx = parseInt($(this).data('index'), 10);
				self.selectItem(self._matches[idx]);
			}).on('mouseenter', function() {
				self.$dropdown.find('.active').removeClass('active');
				$(this).addClass('active');
				self.selectedIndex = $(this).index();
			});
		},

		positionDropdown: function() {
			var inputPos = this.$input.offset();
			this.$dropdown.css({
				left: inputPos.left + 'px',
				top: (inputPos.top - this.$dropdown.outerHeight() - 4) + 'px'
			});
		},

		moveSelection: function(dir) {
			var items = this.$dropdown.find('.shoutbox-command-item');
			items.eq(this.selectedIndex).removeClass('active');
			this.selectedIndex = (this.selectedIndex + dir + items.length) % items.length;
			items.eq(this.selectedIndex).addClass('active');
		},

		selectCurrent: function() {
			if (this.selectedIndex >= 0 && this._matches && this.selectedIndex < this._matches.length)
				this.selectItem(this._matches[this.selectedIndex]);
		},

		selectItem: function(cmd) {
			this.$input.val(cmd.insert);
			this.$input[0].selectionStart = this.$input[0].selectionEnd = cmd.insert.length;
			this.$input.focus();
			this.hide();
		},

		hide: function() {
			this.isActive = false;
			this.selectedIndex = -1;
			this._matches = null;
			this.$dropdown.hide().empty();
		},

		escapeHtml: function(str) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}
	};

	// =========================================================================
	// Moderation - Edit/delete/ban via hover actions
	// =========================================================================

	ShoutBox.Moderation = function(state, renderer) {
		this.state = state;
		this.renderer = renderer;

		this.init();
	};

	ShoutBox.Moderation.prototype = {
		init: function() {
			var self = this;

			$(document).on('click', '.shoutbox-delete-btn', function(e) {
				e.stopPropagation();
				var id = parseInt($(this).closest('.shoutbox-message').data('id'), 10);
				self.deleteMessage(id);
			});

			$(document).on('click', '.shoutbox-edit-btn', function(e) {
				e.stopPropagation();
				var $msg = $(this).closest('.shoutbox-message');
				var id = parseInt($msg.data('id'), 10);
				self.startEdit($msg, id);
			});

			$(document).on('click', '.shoutbox-ban-btn', function(e) {
				e.stopPropagation();
				var $msg = $(this).closest('.shoutbox-message');
				var memberId = parseInt($msg.data('member-id'), 10);
				self.banUser(memberId);
			});
		},

		deleteMessage: function(id) {
			if (!confirm('Delete this message?'))
				return;

			var self = this;
			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'delete',
					id_msg: id
				}),
				dataType: 'json'
			})
			.done(function() {
				self.state.removeMessage(id);
				self.renderer.removeMessage(id);
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Delete failed.']);
			});
		},

		startEdit: function($msg, id) {
			// Find the raw body from state.
			var msgData = null;
			for (var i = 0; i < this.state.messages.length; i++) {
				if (this.state.messages[i].id === id) {
					msgData = this.state.messages[i];
					break;
				}
			}

			var $body = $msg.find('.shoutbox-message-body');
			var currentHtml = $body.html();

			// Create inline edit.
			var $edit = $('<div class="shoutbox-inline-edit">' +
				'<input type="text" class="edit-input" />' +
				'<button class="save-btn">Save</button>' +
				'<button class="cancel-btn">Cancel</button>' +
				'</div>');

			// Use text extracted from body or raw body if we can get it.
			$edit.find('.edit-input').val($body.text().trim());

			$body.hide().after($edit);

			var self = this;

			$edit.find('.save-btn').on('click', function() {
				var newBody = $.trim($edit.find('.edit-input').val());
				if (newBody)
					self.saveEdit(id, newBody, $body, $edit);
			});

			$edit.find('.cancel-btn').on('click', function() {
				$edit.remove();
				$body.show();
			});

			$edit.find('.edit-input').on('keydown', function(e) {
				if (e.key === 'Enter') {
					var newBody = $.trim($(this).val());
					if (newBody)
						self.saveEdit(id, newBody, $body, $edit);
				} else if (e.key === 'Escape') {
					$edit.remove();
					$body.show();
				}
			}).focus();
		},

		saveEdit: function(id, newBody, $body, $edit) {
			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'edit',
					id_msg: id,
					body: newBody
				}),
				dataType: 'json'
			})
			.done(function(data) {
				$edit.remove();
				$body.html(data.parsed_body).show();
				if (!$body.siblings('.shoutbox-message-edited').length) {
					$body.after('<span class="shoutbox-message-edited">(edited)</span>');
				}
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Edit failed.']);
				$edit.remove();
				$body.show();
			});
		},

		banUser: function(memberId) {
			var reason = prompt('Reason for ban (optional):');
			if (reason === null)
				return;

			var duration = prompt('Duration in hours (0 = permanent):', '0');
			if (duration === null)
				return;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'ban',
					id_member: memberId,
					reason: reason,
					duration: parseInt(duration, 10) || 0
				}),
				dataType: 'json'
			})
			.done(function() {
				$(document).trigger('shoutbox:notification', ['User banned successfully.']);
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Ban failed.']);
			});
		}
	};

	// =========================================================================
	// Reactions - React popup + toggle
	// =========================================================================

	ShoutBox.Reactions = function(state, renderer) {
		this.state = state;
		this.renderer = renderer;
		this.$popup = null;

		if (config.enableReactions)
			this.init();
	};

	ShoutBox.Reactions.prototype = {
		init: function() {
			var self = this;

			// Click on react button (smiley) -> show popup.
			$(document).on('click', '.shoutbox-react-btn', function(e) {
				e.stopPropagation();
				var $btn = $(this);
				var msgId = parseInt($btn.data('msg-id'), 10);
				self.showPopup($btn, msgId);
			});

			// Click on reaction pill -> toggle that reaction.
			$(document).on('click', '.shoutbox-reaction-pill', function(e) {
				e.stopPropagation();
				if (config.isGuest || !config.canPost)
					return;
				var msgId = parseInt($(this).data('msg-id'), 10);
				var reactionType = $(this).data('reaction-type');
				self.toggleReaction(msgId, reactionType);
			});

			// Close popup on outside click.
			$(document).on('click', function() {
				self.hidePopup();
			});
		},

		showPopup: function($btn, msgId) {
			this.hidePopup();

			var reactionTypes = config.reactionTypes || {};
			var html = '<div class="shoutbox-react-popup">';
			for (var key in reactionTypes) {
				if (reactionTypes.hasOwnProperty(key)) {
					var rType = reactionTypes[key];
					html += '<button class="shoutbox-react-popup-btn" data-msg-id="' + msgId + '" data-reaction-type="' + key + '" title="' + this.escapeHtml(rType.label) + '">';
					html += '<img src="' + config.reactionsImgUrl + rType.icon + '" alt="' + this.escapeHtml(rType.label) + '" width="16" height="16" />';
					html += '</button>';
				}
			}
			html += '</div>';

			this.$popup = $(html);
			$('body').append(this.$popup);

			// Position below the react button.
			var btnOffset = $btn.offset();
			var popupWidth = this.$popup.outerWidth();
			var left = btnOffset.left + ($btn.outerWidth() / 2) - (popupWidth / 2);
			var top = btnOffset.top + $btn.outerHeight() + 4;

			// Keep within viewport.
			if (left < 4) left = 4;
			if (left + popupWidth > $(window).width() - 4)
				left = $(window).width() - popupWidth - 4;

			this.$popup.css({ left: left + 'px', top: top + 'px' });

			var self = this;
			this.$popup.on('click', '.shoutbox-react-popup-btn', function(e) {
				e.stopPropagation();
				var mid = parseInt($(this).data('msg-id'), 10);
				var rt = $(this).data('reaction-type');
				self.hidePopup();
				self.toggleReaction(mid, rt);
			});
		},

		hidePopup: function() {
			if (this.$popup) {
				this.$popup.remove();
				this.$popup = null;
			}
		},

		toggleReaction: function(msgId, reactionType) {
			var self = this;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: postData({
					sa: 'react',
					id_msg: msgId,
					reaction_type: reactionType
				}),
				dataType: 'json'
			})
			.done(function(data) {
				if (data && data.success) {
					// Update state.
					for (var i = 0; i < self.state.messages.length; i++) {
						if (self.state.messages[i].id === msgId) {
							self.state.messages[i].reactions = data.reactions;
							break;
						}
					}
					self.updateReactionBar(msgId, data.reactions);
				}
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				$(document).trigger('shoutbox:error', [resp ? resp.message : 'Reaction failed.']);
			});
		},

		updateReactionBar: function(msgId, reactions) {
			var $msg = this.renderer.$container.find('[data-id="' + msgId + '"]');
			if (!$msg.length)
				return;

			// Remove existing reaction bar.
			$msg.find('.shoutbox-reactions-bar').remove();

			var reactionTypes = config.reactionTypes || {};
			var hasReactions = false;
			var rKeys = [];
			for (var rKey in reactionTypes) {
				if (reactionTypes.hasOwnProperty(rKey))
					rKeys.push(rKey);
			}
			for (var ri = 0; ri < rKeys.length; ri++) {
				if (reactions[rKeys[ri]] && reactions[rKeys[ri]].count > 0) {
					hasReactions = true;
					break;
				}
			}

			if (!hasReactions)
				return;

			var html = '<div class="shoutbox-reactions-bar">';
			for (var rj = 0; rj < rKeys.length; rj++) {
				var rk = rKeys[rj];
				var rd = reactions[rk];
				if (!rd || !rd.count || rd.count <= 0)
					continue;
				var rType = reactionTypes[rk];
				var pillClass = 'shoutbox-reaction-pill';
				if (rd.reacted)
					pillClass += ' reacted';
				var tooltip = rd.users ? rd.users.join(', ') : '';
				html += '<button class="' + pillClass + '" data-msg-id="' + msgId + '" data-reaction-type="' + rk + '" title="' + this.escapeHtml(tooltip) + '">';
				html += '<img src="' + config.reactionsImgUrl + rType.icon + '" alt="' + this.escapeHtml(rType.label) + '" width="14" height="14" />';
				html += '<span class="reaction-count">' + rd.count + '</span>';
				html += '</button>';
			}
			html += '</div>';

			// Insert after .shoutbox-message-edited or .shoutbox-message-body.
			var $edited = $msg.find('.shoutbox-message-edited');
			if ($edited.length)
				$edited.after(html);
			else
				$msg.find('.shoutbox-message-body').after(html);
		},

		escapeHtml: function(str) {
			if (!str) return '';
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}
	};

	// =========================================================================
	// Notifications - Audio + title flash
	// =========================================================================

	ShoutBox.Notifications = function(state) {
		this.state = state;
		this.audio = null;
		this.originalTitle = document.title;
		this.flashTimer = null;
		this.isFlashing = false;

		this.init();
	};

	ShoutBox.Notifications.prototype = {
		init: function() {
			var self = this;

			// Try to load notification sound.
			if (config.enableSounds) {
				this.audio = new Audio();
				this.audio.volume = 0.3;

				// WAV is universally supported; MP3/OGG are optional smaller alternatives.
				var soundBase = smf_default_theme_url + '/scripts/shoutbox-sounds/notification';
				if (this.audio.canPlayType('audio/mpeg'))
					this.audio.src = soundBase + '.mp3';
				else if (this.audio.canPlayType('audio/ogg'))
					this.audio.src = soundBase + '.ogg';
				else
					this.audio.src = soundBase + '.wav';

				// Fallback: if preferred format fails to load, try WAV.
				this.audio.addEventListener('error', function() {
					if (self.audio.src.indexOf('.wav') === -1)
						self.audio.src = soundBase + '.wav';
				});
			}

			// Sound toggle button.
			$('#shoutbox_sound_toggle').on('click', function() {
				self.state.soundEnabled = !self.state.soundEnabled;
				self.state.savePreference('sound', self.state.soundEnabled ? '1' : '0');
				self.updateSoundButton();
			});
			this.updateSoundButton();

			// Visibility change handler.
			$(document).on('visibilitychange', function() {
				self.state.isTabVisible = !document.hidden;
				if (self.state.isTabVisible) {
					self.state.unreadCount = 0;
					self.stopTitleFlash();
				}
			});
		},

		playSound: function() {
			if (!this.state.soundEnabled || !this.audio || !this.audio.src)
				return;

			try {
				this.audio.currentTime = 0;
				this.audio.play().catch(function() {});
			} catch (e) {}
		},

		notify: function(messages) {
			if (!messages || messages.length === 0)
				return;

			// Don't notify for own messages.
			var hasOthers = false;
			for (var i = 0; i < messages.length; i++) {
				if (messages[i].memberId !== config.userId) {
					hasOthers = true;
					break;
				}
			}

			if (!hasOthers)
				return;

			if (!this.state.isTabVisible) {
				this.state.unreadCount += messages.length;
				this.startTitleFlash();
			}

			this.playSound();
		},

		startTitleFlash: function() {
			if (this.isFlashing)
				return;

			this.isFlashing = true;
			var self = this;
			var showOriginal = true;

			this.flashTimer = setInterval(function() {
				document.title = showOriginal
					? '(' + self.state.unreadCount + ') New shouts!'
					: self.originalTitle;
				showOriginal = !showOriginal;
			}, 1500);
		},

		stopTitleFlash: function() {
			this.isFlashing = false;
			clearInterval(this.flashTimer);
			document.title = this.originalTitle;
		},

		updateSoundButton: function() {
			var $btn = $('#shoutbox_sound_toggle');
			if (this.state.soundEnabled) {
				$btn.attr('title', 'Sound On').removeClass('sound-off');
				if ($btn.hasClass('button'))
					$btn.html('&#128264; Sound On');
			} else {
				$btn.attr('title', 'Sound Off').addClass('sound-off');
				if ($btn.hasClass('button'))
					$btn.html('&#128265; Sound Off');
			}
		}
	};

	// =========================================================================
	// OnlineUsers - Sidebar updates
	// =========================================================================

	ShoutBox.OnlineUsers = function() {
		this.$container = $('#shoutbox_online_users');
	};

	ShoutBox.OnlineUsers.prototype = {
		update: function(users) {
			if (!this.$container.length)
				return;

			if (!users || users.length === 0) {
				this.$container.html('<div class="shoutbox-no-messages">No users online</div>');
				return;
			}

			var html = '';
			for (var i = 0; i < users.length; i++) {
				var uColorStyle = users[i].color ? ' style="color: ' + this.escapeHtml(users[i].color) + '"' : '';
				html += '<div class="shoutbox-online-user">' +
					'<span class="shoutbox-online-dot"></span>' +
					'<a href="' + users[i].profileUrl + '"' + uColorStyle + '>' + this.escapeHtml(users[i].name) + '</a>' +
					'</div>';
			}
			html += '<div class="shoutbox-online-count">' + users.length + ' user' + (users.length !== 1 ? 's' : '') + ' online</div>';

			this.$container.html(html);
		},

		escapeHtml: function(str) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}
	};

	// =========================================================================
	// SmileyPicker - Popup smiley grid
	// =========================================================================

	ShoutBox.SmileyPicker = function(inputHandler) {
		this.inputHandler = inputHandler;
		this.$picker = $('#shoutbox_smiley_picker');
		this.$btn = $('#shoutbox_smiley_btn');
		this.isOpen = false;
		this.built = false;

		if (config.showSmileyPicker && this.$btn.length)
			this.init();
	};

	ShoutBox.SmileyPicker.prototype = {
		init: function() {
			var self = this;

			this.$btn.on('click', function(e) {
				e.stopPropagation();
				self.toggle();
			});

			$(document).on('click', function(e) {
				if (self.isOpen && !$(e.target).closest('.shoutbox-smiley-picker').length)
					self.hide();
			});

			$(document).on('keydown', function(e) {
				if (self.isOpen && e.key === 'Escape')
					self.hide();
			});
		},

		buildGrid: function() {
			if (this.built)
				return;

			var smileys = config.smileys || [];
			if (!smileys.length) {
				this.$picker.html('<div style="padding:12px;text-align:center;color:#888;">No smileys available</div>');
				this.built = true;
				return;
			}

			var html = '';
			for (var i = 0; i < smileys.length; i++) {
				var s = smileys[i];
				html += '<img src="' + this.escapeHtml(config.smileysUrl + s.file) + '" alt="' + this.escapeHtml(s.code) + '" title="' + this.escapeHtml(s.desc || s.code) + '" data-code="' + this.escapeHtml(s.code) + '" />';
			}
			this.$picker.html(html);
			this.built = true;

			var self = this;
			this.$picker.on('click', 'img', function(e) {
				e.stopPropagation();
				var code = $(this).data('code');
				if (code) {
					self.inputHandler.insertText(' ' + code + ' ');
					self.hide();
				}
			});
		},

		toggle: function() {
			if (this.isOpen)
				this.hide();
			else
				this.show();
		},

		show: function() {
			this.buildGrid();
			this.$picker.show();
			this.isOpen = true;
			this.position();
		},

		position: function() {
			var btnOffset = this.$btn.offset();
			var btnHeight = this.$btn.outerHeight();
			var pickerWidth = this.$picker.outerWidth();
			var pickerHeight = this.$picker.outerHeight();
			var scrollTop = $(window).scrollTop();

			// Position above the button.
			var top = btnOffset.top - scrollTop - pickerHeight - 4;
			var left = btnOffset.left;

			// If it would go above the viewport, show below the button instead.
			if (top < 4)
				top = btnOffset.top - scrollTop + btnHeight + 4;

			// Keep within viewport horizontally.
			if (left + pickerWidth > $(window).width() - 4)
				left = $(window).width() - pickerWidth - 4;
			if (left < 4)
				left = 4;

			this.$picker.css({ top: top + 'px', left: left + 'px' });
		},

		hide: function() {
			this.$picker.hide();
			this.isOpen = false;
		},

		escapeHtml: function(str) {
			if (!str) return '';
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}
	};

	// =========================================================================
	// BBCodeBar - Formatting toolbar
	// =========================================================================

	ShoutBox.BBCodeBar = function(inputHandler) {
		this.inputHandler = inputHandler;

		if (config.showBBCToolbar)
			this.init();
	};

	ShoutBox.BBCodeBar.prototype = {
		init: function() {
			var self = this;

			$(document).on('click', '.shoutbox-bbc-btn', function(e) {
				e.preventDefault();
				var before = $(this).data('before');
				var after = $(this).data('after');
				if (before && after)
					self.inputHandler.surroundText(before, after);
			});
		}
	};

	// =========================================================================
	// FileUploader - Image upload via button, drag-drop, paste
	// =========================================================================

	ShoutBox.FileUploader = function(inputHandler) {
		this.inputHandler = inputHandler;
		this.$btn = $('#shoutbox_upload_btn');
		this.$fileInput = $('#shoutbox_file_input');
		this.$container = this.$btn.closest('.shoutbox-widget, .shoutbox-chatroom');
		this.$dropOverlay = $('#shoutbox_drop_overlay');
		this.pendingAttachments = [];
		this.uploading = false;

		if (config.enableAttachments && this.$btn.length)
			this.init();
	};

	ShoutBox.FileUploader.prototype = {
		init: function() {
			var self = this;

			// Click upload button -> trigger file input.
			this.$btn.on('click', function(e) {
				e.stopPropagation();
				self.$fileInput.trigger('click');
			});

			// File selected.
			this.$fileInput.on('change', function() {
				if (this.files && this.files[0])
					self.upload(this.files[0]);
				// Reset so same file can be selected again.
				this.value = '';
			});

			// Drag-drop on container.
			if (this.$container.length) {
				this.$container.on('dragover', function(e) {
					e.preventDefault();
					e.stopPropagation();
					self.$dropOverlay.show();
				});

				this.$container.on('dragleave', function(e) {
					e.preventDefault();
					e.stopPropagation();
					// Only hide if we left the container.
					var rect = self.$container[0].getBoundingClientRect();
					if (e.originalEvent.clientX <= rect.left || e.originalEvent.clientX >= rect.right ||
						e.originalEvent.clientY <= rect.top || e.originalEvent.clientY >= rect.bottom)
						self.$dropOverlay.hide();
				});

				this.$container.on('drop', function(e) {
					e.preventDefault();
					e.stopPropagation();
					self.$dropOverlay.hide();

					var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
					if (files && files.length > 0 && files[0].type.indexOf('image/') === 0)
						self.upload(files[0]);
				});
			}

			// Clipboard paste.
			$(document).on('paste', function(e) {
				if (!config.enableAttachments)
					return;

				var items = e.originalEvent.clipboardData && e.originalEvent.clipboardData.items;
				if (!items)
					return;

				for (var i = 0; i < items.length; i++) {
					if (items[i].type.indexOf('image/') === 0) {
						var file = items[i].getAsFile();
						if (file)
							self.upload(file);
						break;
					}
				}
			});
		},

		upload: function(file) {
			if (this.uploading)
				return;

			// Client-side size check.
			var maxKB = config.attachmentMaxSize || 1024;
			if (file.size > maxKB * 1024) {
				$(document).trigger('shoutbox:error', ['File is too large. Max: ' + maxKB + ' KB.']);
				return;
			}

			// Client-side type check.
			var allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
			var typeOk = false;
			for (var i = 0; i < allowed.length; i++) {
				if (file.type === allowed[i]) {
					typeOk = true;
					break;
				}
			}
			if (!typeOk) {
				$(document).trigger('shoutbox:error', ['Invalid file type. Allowed: JPG, PNG, GIF, WebP.']);
				return;
			}

			this.uploading = true;
			this.$btn.addClass('shoutbox-btn-uploading');

			var formData = new FormData();
			formData.append('file', file);
			formData.append('sa', 'upload');
			formData.append(config.sessionVar, config.sessionId);

			var self = this;

			$.ajax({
				url: config.ajaxUrl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json'
			})
			.done(function(data) {
				if (data && data.success) {
					self.pendingAttachments.push(data.id);
					self.inputHandler.insertText('[img]' + data.url + '[/img]');
				} else {
					$(document).trigger('shoutbox:error', ['Upload failed.']);
				}
			})
			.fail(function(jqXHR) {
				var resp = jqXHR.responseJSON;
				var msg = (resp && resp.message) ? resp.message : 'Upload failed.';
				$(document).trigger('shoutbox:error', [msg]);
			})
			.always(function() {
				self.uploading = false;
				self.$btn.removeClass('shoutbox-btn-uploading');
			});
		},

		getPendingAttachments: function() {
			var ids = this.pendingAttachments.slice();
			this.pendingAttachments = [];
			return ids;
		}
	};

	// =========================================================================
	// Controller - Main coordinator
	// =========================================================================

	ShoutBox.Controller = function() {
		if (!config.ajaxUrl)
			return;

		this.state = new ShoutBox.State();
		this.renderer = new ShoutBox.MessageRenderer(this.state, '#shoutbox_messages');
		this.poller = new ShoutBox.Poller(this.state);
		this.input = new ShoutBox.InputHandler(this.state);
		this.mentions = new ShoutBox.MentionEngine(this.state);
		this.commands = new ShoutBox.CommandAutocomplete(this.state, this.mentions);
		this.moderation = new ShoutBox.Moderation(this.state, this.renderer);
		this.reactions = new ShoutBox.Reactions(this.state, this.renderer);
		this.smileyPicker = new ShoutBox.SmileyPicker(this.input);
		this.fileUploader = new ShoutBox.FileUploader(this.input);
		this.bbcToolbar = new ShoutBox.BBCodeBar(this.input);
		this.notifications = new ShoutBox.Notifications(this.state);
		this.onlineUsers = new ShoutBox.OnlineUsers();

		this.init();
	};

	ShoutBox.Controller.prototype = {
		init: function() {
			var self = this;

			// Wire up events.
			$(document).on('shoutbox:newMessages', function(e, messages) {
				if (self.state.messages.length <= messages.length) {
					// Initial load.
					self.renderer.renderInitial(self.state.messages);
				} else {
					self.renderer.appendMessages(messages);
				}
				self.notifications.notify(messages);
			});

			$(document).on('shoutbox:onlineUsersUpdated', function(e, users) {
				self.onlineUsers.update(users);
			});

			$(document).on('shoutbox:messageSent', function() {
				self.poller.resetInterval();
				// Trigger immediate poll.
				self.poller.stop();
				self.poller.start();
			});

			$(document).on('shoutbox:error', function(e, message) {
				self.showToast(message, 'error');
			});

			$(document).on('shoutbox:notification', function(e, message) {
				self.showToast(message, 'success');
			});

			// Room redirect handler (access revoked).
			$(document).on('shoutbox:roomRedirect', function(e, newRoomId) {
				self.switchRoom(newRoomId);
			});

			// Collapse/expand toggle.
			$('#shoutbox_toggle').on('click', function() {
				self.state.isCollapsed = !self.state.isCollapsed;
				self.state.savePreference('collapsed', self.state.isCollapsed ? '1' : '0');
				$('#shoutbox_body').toggleClass('collapsed', self.state.isCollapsed);
			});

			// Apply initial collapsed state.
			if (this.state.isCollapsed)
				$('#shoutbox_body').addClass('collapsed');

			// Chatroom prune/clean buttons.
			$('#shoutbox_prune_btn').on('click', function() {
				var count = prompt('Number of recent messages to delete:');
				if (count && parseInt(count, 10) > 0)
					self.input.sendPrune(parseInt(count, 10));
			});

			$('#shoutbox_clean_btn').on('click', function() {
				self.input.sendClean();
			});

			// Room tab click handlers.
			$('#shoutbox_room_tabs').on('click', '.shoutbox-room-tab', function() {
				var roomId = parseInt($(this).data('room-id'), 10);
				if (roomId && roomId !== self.state.currentRoomId)
					self.switchRoom(roomId);
			});

			// Restore room from localStorage on chatroom page.
			if (config.isChatroom && config.rooms && config.rooms.length > 1) {
				try {
					var savedRoom = parseInt(localStorage.getItem('shoutbox_room'), 10);
					if (savedRoom && savedRoom !== self.state.currentRoomId) {
						// Validate that the saved room is in the accessible rooms list.
						var valid = false;
						for (var i = 0; i < config.rooms.length; i++) {
							if (config.rooms[i].id === savedRoom) {
								valid = true;
								break;
							}
						}
						if (valid)
							self.switchRoom(savedRoom);
					}
				} catch (e) {}
			}

			// Quote button handler.
			$(document).on('click', '.shoutbox-quote-btn', function(e) {
				e.stopPropagation();
				var $msg = $(this).closest('.shoutbox-message');
				var authorName = $msg.find('.shoutbox-message-author').text().trim();
				var bodyText = $msg.find('.shoutbox-message-body').text().trim();

				// Truncate long quotes to 150 chars.
				if (bodyText.length > 150)
					bodyText = bodyText.substring(0, 150) + '...';

				var quoteText = '@' + authorName + ': \u201C' + bodyText + '\u201D ';
				self.input.insertText(quoteText);
				self.input.$input.focus();
			});

			// Lazy-load GIF picker JS if needed.
			if (config.gifProvider !== 'none' && config.canGif) {
				$('#shoutbox_gif_btn').on('click', function() {
					if (typeof ShoutBox.GifPicker === 'undefined') {
						// Load the GIF script dynamically.
						var script = document.createElement('script');
						script.src = smf_default_theme_url + '/scripts/shoutbox-gif.js?v=' + (config.version || '1.0');
						script.onload = function() {
							if (!ShoutBox.gifPicker)
								ShoutBox.gifPicker = new ShoutBox.GifPicker(self.input);
							ShoutBox.gifPicker.toggle();
						};
						document.head.appendChild(script);
					} else {
						if (!ShoutBox.gifPicker)
							ShoutBox.gifPicker = new ShoutBox.GifPicker(self.input);
						ShoutBox.gifPicker.toggle();
					}
				});
			}

			// Start polling.
			this.poller.start();
		},

		switchRoom: function(roomId) {
			// Stop poller.
			this.poller.stop();

			// Reset state.
			this.state.currentRoomId = roomId;
			this.state.lastMessageId = 0;
			this.state.messages = [];
			this.state.onlineUsers = [];

			// Clear message DOM.
			this.renderer.$container.empty().html(
				'<div class="shoutbox-loading"></div>'
			);

			// Clear online users sidebar.
			$('#shoutbox_online_users').html(
				'<div class="shoutbox-loading">Loading...</div>'
			);

			// Update active tab CSS.
			$('#shoutbox_room_tabs .shoutbox-room-tab').removeClass('active');
			$('#shoutbox_room_tabs .shoutbox-room-tab[data-room-id="' + roomId + '"]').addClass('active');

			// Save to localStorage.
			try {
				localStorage.setItem('shoutbox_room', roomId);
			} catch (e) {}

			// Restart poller (will fetch from new room).
			this.poller.start();
		},

		showToast: function(message, type) {
			var $toast = $('<div class="shoutbox-toast shoutbox-toast-' + type + '">' + message + '</div>');
			$toast.css({
				position: 'fixed',
				bottom: '20px',
				right: '20px',
				padding: '10px 20px',
				borderRadius: '6px',
				background: type === 'error' ? '#d32f2f' : '#4caf50',
				color: '#fff',
				fontSize: '13px',
				zIndex: 10000,
				boxShadow: '0 2px 8px rgba(0,0,0,0.3)',
				opacity: 0
			});
			$('body').append($toast);
			$toast.animate({ opacity: 1 }, 200);
			setTimeout(function() {
				$toast.animate({ opacity: 0 }, 300, function() { $toast.remove(); });
			}, 3000);
		}
	};

	// =========================================================================
	// Initialize on document ready
	// =========================================================================

	$(function() {
		// Read config now - by document.ready, the inline config script has executed.
		config = window.smf_shoutbox_config || {};

		// Apply configurable widget height via CSS variable.
		if (config.widgetHeight)
			document.documentElement.style.setProperty('--shoutbox-widget-height', config.widgetHeight + 'px');

		if (config.ajaxUrl)
			ShoutBox.controller = new ShoutBox.Controller();
	});

})(jQuery, window, document);
