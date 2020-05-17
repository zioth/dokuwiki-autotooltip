<?php
if (!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * Auto-Tooltip DokuWiki renderer plugin. If the current renderer is ActionRenderer, the action
 * plugin will be used instead.
 *
 * @license    MIT
 * @author     Eli Fenton
 */
class renderer_plugin_autotooltip extends Doku_Renderer_xhtml {
	/** @type helper_plugin_autotooltip m_helper */
	private $m_helper;
	private $m_exclude;

	public function __construct() {
		global $ID;
		$this->m_helper = plugin_load('helper', 'autotooltip');
		$this->m_exclude = $this->m_helper->isExcluded($ID);
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
		$fullId = $id;
		$id = preg_replace('/\#.*$/', '', $id);

		if (!$this->m_exclude && page_exists($id) && $id != $ID) {
			$link = parent::internallink($fullId, $name, $search, true, $linktype);

			$meta = $this->m_helper->read_meta_fast($id);
			$abstract = $meta['abstract'];
			$link = $this->m_helper->stripNativeTooltip($link);
			$link = $this->m_helper->forText($link, $abstract, $meta['title']);

			if (!$returnonly) {
				$this->doc .= $link;
			}
			return $link;
		}
		return parent::internallink($fullId, $name, $search, $returnonly, $linktype);
	}
}
