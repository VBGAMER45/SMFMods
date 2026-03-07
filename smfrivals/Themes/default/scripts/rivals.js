/**
 * SMF Rivals - JavaScript
 * Tab switching, bracket interaction, AJAX chat.
 *
 * @package SMFRivals
 * @version 1.0.0
 */

(function() {
	'use strict';

	// ===== Tab Switching =====
	document.addEventListener('click', function(e) {
		var tab = e.target.closest('.rivals_tab');
		if (!tab) return;

		var tabGroup = tab.parentElement;
		var targetId = tab.getAttribute('data-tab');
		if (!targetId) return;

		// Deactivate all tabs in this group
		tabGroup.querySelectorAll('.rivals_tab').forEach(function(t) {
			t.classList.remove('active');
		});

		// Deactivate all tab content siblings
		var container = tabGroup.parentElement;
		container.querySelectorAll('.rivals_tab_content').forEach(function(c) {
			c.classList.remove('active');
		});

		// Activate clicked tab and its content
		tab.classList.add('active');
		var content = document.getElementById(targetId);
		if (content) {
			content.classList.add('active');
		}
	});

	// ===== Match Chat AJAX =====
	var RivalsChat = {
		matchId: 0,
		lastMessageId: 0,
		pollInterval: null,
		sessionVar: '',
		sessionId: '',

		init: function(matchId, sessionVar, sessionId) {
			this.matchId = matchId;
			this.sessionVar = sessionVar;
			this.sessionId = sessionId;
			this.fetchMessages();
			this.pollInterval = setInterval(this.fetchMessages.bind(this), 5000);

			var form = document.getElementById('rivals_chat_form');
			if (form) {
				form.addEventListener('submit', this.sendMessage.bind(this));
			}
		},

		fetchMessages: function() {
			var self = this;
			var url = smf_scripturl + '?action=rivals;sa=ajax;do=fetchchat;match=' + this.matchId + ';last=' + this.lastMessageId;

			fetch(url)
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (data.messages && data.messages.length > 0) {
						var container = document.getElementById('rivals_chat_messages');
						if (!container) return;

						data.messages.forEach(function(msg) {
							var div = document.createElement('div');
							div.className = 'rivals_chat_message';
							div.innerHTML = '<span class="author">' + msg.member + ':</span>' +
								'<span class="body">' + msg.body + '</span>' +
								'<span class="time">' + msg.time + '</span>';
							container.appendChild(div);
							self.lastMessageId = Math.max(self.lastMessageId, msg.id);
						});

						container.scrollTop = container.scrollHeight;
					}
				})
				.catch(function() {});
		},

		sendMessage: function(e) {
			e.preventDefault();
			var input = document.getElementById('rivals_chat_input');
			if (!input || !input.value.trim()) return;

			var body = input.value.trim();
			input.value = '';

			var formData = new FormData();
			formData.append('match', this.matchId);
			formData.append('body', body);
			formData.append(this.sessionVar, this.sessionId);

			fetch(smf_scripturl + '?action=rivals;sa=ajax;do=sendchat;' + this.sessionVar + '=' + this.sessionId, {
				method: 'POST',
				body: formData
			})
			.then(function() {
				// Will pick up the message on next poll
			})
			.catch(function() {});
		},

		destroy: function() {
			if (this.pollInterval) {
				clearInterval(this.pollInterval);
			}
		}
	};

	// ===== Clan/User Search (for challenge popup) =====
	var RivalsSearch = {
		timer: null,

		init: function(inputId, resultsId, type) {
			var input = document.getElementById(inputId);
			var results = document.getElementById(resultsId);
			if (!input || !results) return;

			var self = this;

			input.addEventListener('input', function() {
				clearTimeout(self.timer);
				var query = input.value.trim();

				if (query.length < 2) {
					results.innerHTML = '';
					results.style.display = 'none';
					return;
				}

				self.timer = setTimeout(function() {
					var url = smf_scripturl + '?action=rivals;sa=ajax;do=find' + type + ';q=' + encodeURIComponent(query);

					fetch(url)
						.then(function(response) { return response.json(); })
						.then(function(data) {
							results.innerHTML = '';
							if (data.length === 0) {
								results.style.display = 'none';
								return;
							}

							data.forEach(function(item) {
								var div = document.createElement('div');
								div.className = 'rivals_search_result';
								div.textContent = item.name;
								div.setAttribute('data-id', item.id);
								div.addEventListener('click', function() {
									input.value = item.name;
									var hiddenInput = document.getElementById(inputId + '_id');
									if (hiddenInput) hiddenInput.value = item.id;
									results.style.display = 'none';
								});
								results.appendChild(div);
							});

							results.style.display = 'block';
						})
						.catch(function() {});
				}, 300);
			});

			// Hide results when clicking outside
			document.addEventListener('click', function(e) {
				if (!results.contains(e.target) && e.target !== input) {
					results.style.display = 'none';
				}
			});
		}
	};

	// ===== Logo Upload Preview =====
	var RivalsLogoPreview = {
		init: function(inputId, previewId) {
			var input = document.getElementById(inputId);
			if (!input) return;

			input.addEventListener('change', function() {
				var file = input.files[0];
				if (!file) return;

				// Validate type
				var allowed = ['image/jpeg', 'image/png', 'image/gif'];
				if (allowed.indexOf(file.type) === -1) {
					alert('Only JPG, PNG, and GIF files are allowed.');
					input.value = '';
					return;
				}

				// Show preview
				var reader = new FileReader();
				reader.onload = function(e) {
					var preview = document.getElementById(previewId);
					if (!preview) {
						preview = document.createElement('img');
						preview.id = previewId;
						preview.className = 'rivals_logo_preview';
						input.parentNode.appendChild(preview);
					}
					preview.src = e.target.result;
					preview.style.display = 'block';
				};
				reader.readAsDataURL(file);
			});
		}
	};

	// ===== Confirm Delete =====
	document.addEventListener('click', function(e) {
		var btn = e.target.closest('.rivals_confirm_action');
		if (!btn) return;

		var msg = btn.getAttribute('data-confirm') || 'Are you sure?';
		if (!confirm(msg)) {
			e.preventDefault();
		}
	});

	// Expose to global scope
	window.RivalsChat = RivalsChat;
	window.RivalsSearch = RivalsSearch;
	window.RivalsLogoPreview = RivalsLogoPreview;

})();