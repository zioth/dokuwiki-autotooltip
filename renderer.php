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
	private $m_exclude;
	private $m_points;

	public function __construct() {
		global $ID;
		$this->m_helper = plugin_load('helper', 'autotooltip');

		// Include and exclude pages.
		$inclusions = $this->getConf('linkall_inclusions');
		$exclusions = $this->getConf('linkall_exclusions');
		$this->m_exclude =
			(!empty($inclusions) && !preg_match("/$inclusions/", $ID)) ||
			(!empty($exclusions) && preg_match("/$exclusions/", $ID));

		// Set the regex for filtering link destinations
		$points = $this->getConf('linkall_points_to');
		if (empty($points)) {
			$points = ".*";
		}
		$this->m_points = "/$points/";
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
		if (!$this->m_exclude && page_exists($id) && $id != $ID && preg_match($this->m_points,$id)) {
			$meta = $this->m_helper->read_meta_fast($id);
			$abstract = $meta['abstract'];

			$link = parent::internallink($id, $name, $search, true, $linktype);
			$link = $this->m_helper->stripNativeTooltip($link);
			$link = $this->m_helper->forText($link, $abstract, $meta['title']);

			if (!$returnonly) {
				$this->doc .= $link;
			}
			return $link;
		}
		return parent::internallink($id, $name, $search, $returnonly, $linktype);
	}
}
