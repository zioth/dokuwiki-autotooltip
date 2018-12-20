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
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $textStyle - CSS styles for the linked content
	 * @return string
	 */
	function forText($content, $tooltip, $classes = '', $textStyle = '') {
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

		return '<span class="' . $textClass . '" style="' . $textStyle . '" onmouseover="autotooltip.show(this)" onmouseout="autotooltip.hide()">' .
			$content .
			'<span class="plugin-autotooltip-hidden-classes">' . $classes . '</span>' .
			'<span class="plugin-autotooltip-hidden-tip">' . $this->_formatTT($tooltip) . '</span>' .
		'</span>';
	}


	/**
	 * Render a tooltip, with the title and abstract of a page.
	 *
	 * @param string $id - A page id.
	 * @param string $content - The on-page content. May contain newlines.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @param string $linkStyle - Style attribute for the link.
	 * @return string
	 */
	function forWikilink($id, $content = null, $classes = '', $linkStyle = '') {
		if (!$classes) {
			$classes = 'plugin-autotooltip__default';
		}

		$title = p_get_metadata($id, 'title');
		$abstract = p_get_metadata($id, 'description abstract');
		try {
			// By default, the abstract starts with the title. Remove it so it's not displayed twice, but still fetch
			// both pieces of metadata, in case another plugin rewrote the abstract.
			$abstract = preg_replace('/^' . preg_quote($title) . '(\r?\n)+/', '', $abstract);
		} catch(\Exception $e) {
			// Ignore.
		}

		$link = $this->localRenderer->internallink($id, $content ?: $title, null, true);

		if (!empty($linkStyle)) {
			$link = preg_replace('/<a /', '<a style="' . $linkStyle . '" ', $link);
		}

		if (page_exists($id)) {
			// Remove the title attribute, since we have a better tooltip.
			$link = preg_replace('/title="[^"]*"/', '', $link);

			return '<span class="plugin-autotooltip_linked" onmouseover="autotooltip.show(this)" onmouseout="autotooltip.hide()">' .
				$link .
				'<span class="plugin-autotooltip-hidden-classes">plugin-autotooltip_big ' . $classes . '</span>' .
				'<span class="plugin-autotooltip-hidden-tip">' .
				'  <span class="plugin-autotooltip-title">' . $title . '</span>' .
				($abstract ? '  <br><br><span class="plugin-autotooltip_abstract">' . $this->_formatTT($abstract) . '</span>' : '') .
				'</span>' .
				'</span>';
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
}
