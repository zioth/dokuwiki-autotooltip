<?php
if(!defined('DOKU_INC')) die();

/**
 * Auto-Tooltip DokuWiki plugin
 *
 * @license    MIT
 * @author     Eli Fenton
 */
class helper_plugin_autotooltip extends DokuWiki_Admin_Plugin {
	private $localRenderer;

	public function __construct() {
		$this->localRenderer = new Doku_Renderer_xhtml;
	}


	/**
	 * Return a simple tooltip.
	 *
	 * @param string $content - The on-page content. May contain newlines.
	 * @param string $tooltip - Tooltip content. May contain newlines.
	 * @param string $title - Tooltip title.
	 * @param string $preTitle - Text to display before the title.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $textStyle - CSS styles for the linked content
	 * @return string
	 */
	function forText($content, $tooltip, $title='', $preTitle = '', $classes = '', $textStyle = '') {
		if (!$classes) {
			$classes = 'plugin-autotooltip__default';
		}

		$textClass = '';
		if (empty($textStyle)) {
			$textClass = 'plugin-autotooltip_linked';
			if (strstr($content, '<a ') === FALSE) {
				$textClass .= ' plugin-autotooltip__simple';
			}
		}

		$contentParts = [];
		if (!empty($preTitle)) {
			$contentParts[] = '<span>' . $this->_formatTT($preTitle) . '</span>';
		}
		if (!empty($title)) {
			$contentParts[] = '<span class="plugin-autotooltip-title">' . $title . '</span>';
		}
		if (!empty($tooltip)) {
			$contentParts[] = '<span>' . $this->_formatTT($tooltip) . '</span>';
		}

		return '<span class="' . $textClass . '" style="' . $textStyle . '" onmouseover="autotooltip.show(this)" onmouseout="autotooltip.hide()">' .
			$content .
			'<span class="plugin-autotooltip-hidden-classes">' . $classes . '</span>' .
			'<span class="plugin-autotooltip-hidden-tip">' .
			implode('<br><br>', $contentParts) .
			'</span>' .
		'</span>';
	}


	/**
	 * Render a tooltip, with the title and abstract of a page.
	 *
	 * @param string $id - A page id.
	 * @param string $content - The on-page content. May contain newlines.
	 * @param string $preTitle - Text to display before the title.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $linkStyle - Style attribute for the link.
	 * @return string
	 */
	function forWikilink($id, $content = null, $preTitle = '', $classes = '', $linkStyle = '') {
		if (!$classes) {
			$classes = 'plugin-autotooltip__default';
		}

		$title = p_get_metadata($id, 'title');
		$abstract = p_get_metadata($id, 'description abstract');

		// By default, the abstract starts with the title. Remove it so it's not displayed twice, but still fetch
		// both pieces of metadata, in case another plugin rewrote the abstract.
		$abstract = preg_replace('/^' . $this->_pregEscape($title) . '(\r?\n)+/', '', $abstract);

		$link = $this->localRenderer->internallink($id, $content ?: $title, null, true);

		if (!empty($linkStyle)) {
			$link = preg_replace('/<a /', '<a style="' . $linkStyle . '" ', $link);
		}

		if (page_exists($id)) {
			// Remove the title attribute, since we have a better tooltip.
			$link = preg_replace('/title="[^"]*"/', '', $link);
			return $this->forText($link, $abstract, $title, $preTitle, "plugin-autotooltip_big $classes");
		}
		else {
			return $link;
		}
	}


	/**
	 * Format tooltip text.
	 *
	 * @param string $tt - Tooltip text.
	 * @return string
	 */
	private function _formatTT($tt) {
		$tt = preg_replace('/\r?\n/', '<br>', $tt);
		return preg_replace('/(<br>){3,}/', '<br><br>', $tt);
	}


	/**
	 * Escape a string for inclusion in a regular expression, assuming forward slash is used as the delimiter.
	 *
	 * @param string $r - The regex string, without delimiters.
	 * @return string
	 */
	private function _pregEscape($r) {
		return preg_replace('/\//', '\\/', preg_quote($r));
	}
}
