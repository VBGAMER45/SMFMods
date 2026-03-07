/**
 * SMF Links - Drag and Drop Category Reordering
 * Version 5.0
 * Uses HTML5 Drag and Drop API - no dependencies required.
 */
(function() {
	var table = document.getElementById('sortable_cats');
	if (!table) return;

	var tbody = table.querySelector('tbody');
	var dragRow = null;

	// Hide the Up/Down links when JS is active
	var noJsElems = table.querySelectorAll('.links-nojsonly');
	for (var i = 0; i < noJsElems.length; i++) {
		noJsElems[i].style.display = 'none';
	}

	// Show drag hint
	var hint = document.getElementById('drag_hint');
	if (hint) hint.style.display = '';

	var rows = tbody.querySelectorAll('tr[draggable="true"]');
	for (var i = 0; i < rows.length; i++) {
		attachEvents(rows[i]);
	}

	function attachEvents(row) {
		row.addEventListener('dragstart', function(e) {
			dragRow = this;
			this.style.opacity = '0.4';
			e.dataTransfer.effectAllowed = 'move';
			e.dataTransfer.setData('text/plain', this.getAttribute('data-cat-id'));
		});

		row.addEventListener('dragover', function(e) {
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
		});

		row.addEventListener('dragenter', function(e) {
			e.preventDefault();
			this.classList.add('links-drag-over');
		});

		row.addEventListener('dragleave', function(e) {
			this.classList.remove('links-drag-over');
		});

		row.addEventListener('drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
			this.classList.remove('links-drag-over');

			if (dragRow && dragRow !== this) {
				// Determine position: insert before or after
				var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr[draggable="true"]'));
				var dragIdx = allRows.indexOf(dragRow);
				var dropIdx = allRows.indexOf(this);

				if (dragIdx < dropIdx) {
					tbody.insertBefore(dragRow, this.nextSibling);
				} else {
					tbody.insertBefore(dragRow, this);
				}

				saveOrder();
			}
		});

		row.addEventListener('dragend', function(e) {
			this.style.opacity = '1';
			var allRows = tbody.querySelectorAll('tr[draggable="true"]');
			for (var j = 0; j < allRows.length; j++) {
				allRows[j].classList.remove('links-drag-over');
			}
		});
	}

	function saveOrder() {
		var allRows = tbody.querySelectorAll('tr[draggable="true"]');
		var data = [];
		for (var i = 0; i < allRows.length; i++) {
			data.push({
				id: parseInt(allRows[i].getAttribute('data-cat-id'), 10),
				order: i + 1,
				parent: parseInt(allRows[i].getAttribute('data-parent') || '0', 10)
			});
		}

		var statusEl = document.getElementById('sort_status');

		var xhr = new XMLHttpRequest();
		xhr.open('POST', smf_scripturl + '?action=links;sa=reordercat;' + smf_session_var + '=' + smf_session_id, true);
		xhr.setRequestHeader('Content-Type', 'application/json');
		xhr.onreadystatechange = function() {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					var resp = JSON.parse(xhr.responseText);
					if (resp.success && statusEl) {
						statusEl.textContent = smflinks_order_saved || 'Order saved.';
						statusEl.style.color = 'green';
						setTimeout(function() { statusEl.textContent = ''; }, 2000);
					}
				} else if (statusEl) {
					statusEl.textContent = smflinks_order_error || 'Error saving order.';
					statusEl.style.color = 'red';
				}
			}
		};
		xhr.send(JSON.stringify(data));
	}
})();
