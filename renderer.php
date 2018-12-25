<?php
if (!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * Auto-Tooltip DokuWiki plugin
 *
 * @license    MIT
 * @author     Eli Fenton
 */
class renderer_plugin_autotooltip extends Doku_Renderer_xhtml {
	/** @type helper_plugin_autotooltip m_helper */
	private $m_helper;
	private $m_disable;

	public function __construct() {
		global $ID;
		$this->m_helper = plugin_load('helper', 'autotooltip');

		// Exclude some pages.
		$exclusions = $this->getConf('linkall_exclusions');
		$this->m_disable = !empty($exclusions) && preg_match($exclusions, $ID);
	}


	/**
	 * @param $format
	 * @return bool
	 */
	function canRender($format) {
		return $format == 'xhtml';
	}


	/**
	 * Intercept Doku_Renderer_xhtml:internallink to give every wikilink a tooltip!
	 *
	 * @param string $id
	 * @param null $name
	 * @param null $search
	 * @param bool $returnonly
	 * @param string $linktype
	 * @return string
	 */
	function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content') {
		global $ID;
		if (!$this->m_disable && page_exists($id) && $id != $ID) {
			$title = p_get_metadata($id, 'title');
			$abstract = $this->m_helper->getAbstract($id, $title);

			$link = parent::internallink($id, $name, $search, true, $linktype);
			$link = $this->m_helper->stripNativeTooltip($link);
			$link = $this->m_helper->forText($link, $abstract, $title);

			if (!$returnonly) {
				$this->doc .= $link;
			}
			return $link;
		}
		return parent::internallink($id, $name, $search, $returnonly, $linktype);
	}
}
