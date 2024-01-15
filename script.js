/**
 * Javascript for the autotooltip plugin.
 *
 * @type {{show:function, hide:function}}
 */
var autotooltip = function($) {
	var MAX_WIDTH = 500;
	var m_tt;
	var m_visible;
	var m_timer;
	var m_moveCounter = 0;

	/**
	 * Initialize the module.
	 *
	 * @private
	 */
	var _init = function() {
		if (m_tt) {
			return;
		}

		m_tt = $('<div class="plugin-autotooltip_tip" role="tooltip"></div>');
		// Cover the various templates.
		var container = $('.dokuwiki .bodyContent, .dokuwiki .wiki-content, #dokuwiki__content');
		// Use the root .dokuwiki if we have to, though we might lose some font information.
		if (!container.length) {
			container = $('.dokuwiki');
		}
		// In case the template is really strange.
		if (!container.length) {
			container = $('body');
		}
		container.first().append(m_tt);

		$(document).on('mousemove', _moveDebounced);
	};


	/**
	 * Move the the tooltip to the current mouse position.
	 *
	 * @param {MouseEvent} e
	 * @private
	 */
	var _move = function(e) {
		if (!m_visible) {
			return;
		}
		var top = Math.max(e.pageY - window.scrollY - m_tt.outerHeight() - 4, 8);
		var left = Math.max(e.pageX + 4, 8);
		var right = '';
		var winWidth = window.innerWidth;
		var width;
		if (winWidth - left < MAX_WIDTH && left > winWidth / 2) {
			// Show left of the cursor.
			left = '';
			right = winWidth - e.pageX - 4;
			width = Math.min(e.pageX - 4, MAX_WIDTH);
		} else {
			// Show right of the cursor.
			left = left + 'px';
			width = Math.min(winWidth - e.pageX - 4, MAX_WIDTH);
		}

		m_tt.css({top: top + 'px', left: left, right: right, width: 'auto', 'max-width': width + 'px'});
	};

	/**
	 * Mousemove handler. When the mouse moves, so does the tooltip.
	 *
	 * @param {MouseEvent} e
	 * @private
	 */
	var _moveDebounced = function(e) {
		if (!m_visible) {
			return;
		}
		var closureCounter = ++m_moveCounter;
		requestAnimationFrame(function() {
			if (closureCounter == m_moveCounter) {
				_move(e);
			}
		});
	};

	return {
		/**
		 * Show a tooltip.
		 *
		 * @param {MouseEvent} evt
		 */
		show: function(evt) {
			m_visible = true;
			_init();

			var elt = evt.currentTarget;
			m_tt
				.html($('.plugin-autotooltip-hidden-tip', elt).html())
				.attr('class', 'plugin-autotooltip_tip .plugin-autotooltip-hidden-classes');
			// This isn't strictly needed because of the attachment to document.mousemove,
			// but it forces proper initial placement when the mouse is moving rapidly (so
			// move is throttled).
			_move(evt);
			clearInterval(m_timer);
			m_timer = setTimeout(function() {
				if (m_visible) {
					m_tt.addClass('plugin-autotooltip--visible');
				}
			}, parseInt($(elt).attr('data-delay')) || 50);
		},


		/**
		 * Hide the tooltip.
		 */
		hide: function() {
			m_visible = false;
			m_timer = setTimeout(function() {
				m_tt.removeClass('plugin-autotooltip--visible');
			}, 50);
		}
	};
}(jQuery);
