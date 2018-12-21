/**
 * Javascript for the autotooltip plugin.
 *
 * @type {{show:function, hide:function}}
 */
var autotooltip = function($) {
	var timer;
	var TT_DELAY = 50;
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
			tt = $('<div class="plugin-autotooltip_tip"></div>');
			$('.dokuwiki .bodyContent, .dokuwiki .wiki-content').first().append(tt);
		}

		$(document).on('mousemove', _move);
		_init = function() {}; // Only once.
	};


	/**
	 * Mousemove handler. When the mouse moves, so does the tooltip.
	 *
	 * @param {MouseEvent} e
	 * @private
	 */
	var _move = function(e) {
		if (isVisible) {
			var localMoveCount = ++moveCount;
			requestAnimationFrame(function() {
				if (localMoveCount == moveCount) {
					var top = Math.max(e.pageY - window.scrollY - tt.outerHeight() - 4, 8);
					var left = Math.max(e.pageX + 4, 8);
					tt.css({top: top + 'px', left: left + 'px'});
				}
			});
		}
	};


	/**
	 * Show the tooltip with the given HTML.
	 *
	 * @param {String} html - The HTML content of the tooltip.
	 * @param {String} classes - CSS classes to add.
	 * @private
	 */
	var _show = function(html, classes) {
		tt.html(html).css({width: 'auto'}).attr('class', 'plugin-autotooltip_tip ' + classes);
		if (tt.width() > MAX_WIDTH) {
			tt.css({width: MAX_WIDTH + 'px'});
		}
		isVisible = true;
		clearInterval(timer);
		timer = setTimeout(function() {
			tt.addClass('plugin-autotooltip--visible');
		}, TT_DELAY);
	};


	return {
		/**
		 * Show a tooltip.
		 *
		 * @param {Element} elt - Element containing all content.
		 */
		show: function(elt) {
			_init();
			_show($('.plugin-autotooltip-hidden-tip', elt).html(), $('.plugin-autotooltip-hidden-classes', elt).text());
		},


		/**
		 * Hide the tooltip.
		 */
		hide: function() {
			isVisible = false;
			timer = setTimeout(function() {
				tt.removeClass('plugin-autotooltip--visible');
			}, TT_DELAY);
		}
	};
}(jQuery);
