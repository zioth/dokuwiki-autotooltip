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
	 * Return methods of this helper
	 *
	 * @return array with methods description
	 */
	function getMethods() {
		$result = array();
		$result[] = array(
			'name' => 'forText',
			'desc' => 'Manually construct a tooltip',
			'params' => array(
				'content' => 'string',
				'tooltip' => 'string',
				'title (optional)' => 'string',
				'preTitle (optional)' => 'string',
				'classes (optional)' => 'string',
				'textStyles (optional)' => 'string',
			),
			'return' => array('result' => 'string')
		);
		$result[] = array(
			'name' => 'forWikilink',
			'desc' => 'Generate a tooltip from a wikilink',
			'params' => array(
				'id' => 'string',
				'content (optional)' => 'string',
				'preTitle (optional)' => 'string',
				'classes (optional)' => 'string',
				'textStyles (optional)' => 'string',
			),
			'return' => array('result' => 'string')
		);
		return $result;
	}


	/**
	 * Return a simple tooltip.
	 *
	 * @param string $content - The on-page content. May contain newlines.
	 * @param string $tooltip - The tooltip content. Newlines will be rendered as line breaks.
	 * @param string $title - The title inside the tooltip.
	 * @param string $preTitle - Text to display before the title. Newlines will be rendered as line breaks.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $textStyle - CSS styles for the linked content
	 * @return string
	 */
	function forText($content, $tooltip, $title='', $preTitle = '', $classes = '', $textStyle = '') {
		if (empty($classes)) {
			$classes = $this->getConf('style');
		}
		if (empty($classes)) {
			$classes = 'default';
		}

		// Sanitize
		$classes = htmlspecialchars($classes);
		// Add the plugin prefix to all classes.
		$classes = preg_replace('/(\w+)/', 'plugin-autotooltip__$1', $classes);

		$partCount = (empty($title) ? 0 : 1) + (empty($preTitle) ? 0 : 1) + (empty($tooltip) ? 0 : 1);
		if ($partCount > 1 || strchr($tooltip, "\n") !== FALSE || strlen($tooltip) > 40) {
			$classes .= ' plugin-autotooltip_big';
		}

		$textClass = '';
		if (empty($textStyle)) {
			$textClass = 'plugin-autotooltip_linked';
			if (strstr($content, '<a ') === FALSE) {
				$textClass .= ' plugin-autotooltip_simple';
			}
		}

		$contentParts = [];
		if (!empty($preTitle)) {
			$contentParts[] = $this->_formatTT($preTitle);
		}
		if (!empty($title)) {
			$contentParts[] = '<span class="plugin-autotooltip-title">' . $title . '</span>';
		}
		if (!empty($tooltip)) {
			$contentParts[] = $this->_formatTT($tooltip);
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
	 * @param string $content - The on-page content. Newlines will be rendered as line breaks. Omit to use the page's title.
	 * @param string $preTitle - Text to display before the title in the tooltip. Newlines will be rendered as line breaks.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $linkStyle - Style attribute for the link.
	 * @return string
	 */
	function forWikilink($id, $content = null, $preTitle = '', $classes = '', $linkStyle = '') {
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
			return $this->forText($link, $abstract, $title, $preTitle, $classes);
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
		// Convert double-newlines into vertical space.
		$tt = preg_replace('/(\r?\n){2,}/', '<br><br>', $tt);
		// Single newlines get collapsed, just like in HTML.
		return preg_replace('/(\r?\n)/', ' ', $tt);
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
