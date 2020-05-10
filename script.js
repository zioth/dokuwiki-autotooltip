/**
 * Javascript for the autotooltip plugin.
 *
 * @type {{show:function, hide:function}}
 */
var autotooltip = function($) {
	var timer;
	var MAX_WIDTH = 500;
	var moveCount = 0;
	var isVisible;
	var tt;

	/**
	 * Initialize the module.
	 *
	 * @private
	 */
	var _init = function() {
		if (!tt) {
			tt = $('<div class="plugin-autotooltip_tip" role="tooltip"></div>');
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
			container.first().append(tt);
		}

		$(document).on('mousemove', _moveThrottled);
		_init = function() {}; // Only once.
	};


	/**
	 * Move the the tooltip to the current mouse position.
	 *
	 * @param {MouseEvent} e
	 * @private
	 */
	var _move = function(e) {
		var top = Math.max(e.pageY - window.scrollY - tt.outerHeight() - 4, 8);
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

		tt.css({top: top + 'px', left: left, right: right, width: 'auto', 'max-width': width + 'px'});
	};

	/**
	 * Mousemove handler. When the mouse moves, so does the tooltip.
	 *
	 * @param {MouseEvent} e
	 * @private
	 */
	var _moveThrottled = function(e) {
		if (isVisible) {
			var localMoveCount = ++moveCount;
			requestAnimationFrame(function() {
				if (localMoveCount == moveCount) {
					_move(e);
				}
			});
		}
	};


	/**
	 * Show the tooltip with the given HTML.
	 *
	 * @param {MouseEvent} evt
	 * @param {String} html - The HTML content of the tooltip.
	 * @param {String} classes - CSS classes to add.
	 * @param {int} delay - Delay, in ms.
	 * @private
	 */
	var _show = function(evt, html, classes, delay) {
		delay = parseInt(delay) || 50;
		tt.html(html).attr('class', 'plugin-autotooltip_tip ' + classes);
		_move(evt);
		isVisible = true;
		clearInterval(timer);
		timer = setTimeout(function() {
			tt.addClass('plugin-autotooltip--visible');
		}, delay);
	};


	return {
		/**
		 * Show a tooltip.
		 *
		 * @param {MouseEvent} evt
		 */
		show: function(evt) {
			var elt = evt.currentTarget;
			_init();
			_show(evt, $('.plugin-autotooltip-hidden-tip', elt).html(), $('.plugin-autotooltip-hidden-classes', elt).text(), $(elt).attr('data-delay'));
		},


		/**
		 * Hide the tooltip.
		 */
		hide: function() {
			isVisible = false;
			timer = setTimeout(function() {
				tt.removeClass('plugin-autotooltip--visible');
			}, 50);
		}
	};
}(jQuery);
