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
	 * @param string $content - The on-page content. May contain HTML.
	 * @param string $tooltip - Tooltip content. May contain HTML.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @return string
	 */
	function forText($content, $tooltip, $classes = '') {
		if (!$classes) {
			$classes = 'plugin-autotooltip__default';
		}

		$textclass = strstr($content, '<a ') !== FALSE ? '' : 'plugin-autotooltip__simple';

		return '<div class="plugin-autotooltip_linked ' . $textclass . '" onmouseover="autotooltip.show(this)" onmouseout="autotooltip.hide()">' .
			$content .
			'<div class="plugin-autotooltip-hidden-classes">' . $classes . '</div>' .
			'<div class="plugin-autotooltip-hidden-tip">' . $this->_formatTT($tooltip) . '</div>' .
		'</div>';
	}


	/**
	 * Render a tooltip, with the title and abstract of a page.
	 *
	 * @param string $id - A page id.
	 * @param string $content - The on-page content. May contain HTML.
	 * @param string $classes - CSS classes to add to this tooltip.
	 * @return string
	 */
	function forWikilink($id, $content, $classes = '') {
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

		$link = $this->localRenderer->internallink($id, $content, null, true);

		if (page_exists($id)) {
			// Remove the title attribute, since we have a better tooltip.
			$link = preg_replace('/title="[^"]*"/', '', $link);

			return '<div class="plugin-autotooltip_linked" onmouseover="autotooltip.show(this)" onmouseout="autotooltip.hide()">' .
				$link .
				'<div class="plugin-autotooltip-hidden-classes">plugin-autotooltip_big ' . $classes . '</div>' .
				'<div class="plugin-autotooltip-hidden-tip">' .
				'  <h3>' . $title . '</h3>' .
				'  <div class="level3">' .
				'    <p class="plugin-autotooltip_abstract">' . $this->_formatTT($abstract) . '</p>' .
				'  </div>' .
				'</div>' .
				'</div>';
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
		return preg_replace('/\r?\n/', '<br>', $tt);
	}
}
