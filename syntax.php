<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_REL')) define('DOKU_REL', '/dokuwiki/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * Auto-Tooltip DokuWiki plugin
 *
 * @license    MIT
 * @author     Eli Fenton
 */
class syntax_plugin_autotooltip extends DokuWiki_Syntax_Plugin {
	/** @type helper_plugin_autotooltip m_helper */
	private $m_helper;

	public function __construct() {
		$this->m_helper = plugin_load('helper', 'autotooltip');
	}


	/**
	 * @return string
	 */
	function getType() {
		return 'substition';
	}


	/**
	 * @return string
	 */
	function getPType() {
		return 'normal';
	}


	/**
	 * @return int
	 */
	function getSort() {
		return 165;
	}


	/**
	 * @param $mode
	 */
	function connectTo($mode) {
		$this->Lexer->addSpecialPattern('<autott[^>]*>(?:[\s\S]*?</autott>)', $mode, 'plugin_autotooltip');
	}


	/**
	 * @param string $match - The match from addEntryPattern.
	 * @param int $state - The DokuWiki event state.
	 * @param int $pos - The position in the full text.
	 * @param Doku_Handler $handler
	 * @return array|string
	 */
	function handle($match, $state, $pos, Doku_Handler $handler) {
		$inner = [];
		$classes = [];
		$content = [];
		$tip = [];
		$pageid = [];
		preg_match('/<autott\s*([^>]+?)\s*>/', $match, $classes);
		preg_match('/<autott[^>]*>\s*([\s\S]+)\s*<\/autott>/', $match, $inner);
		if (count($inner) < 1) {
			return 'ERROR';
		}
		$inner = $inner[1];

		$data = [];
		$classes = count($classes) >= 1 ? preg_split('/\s+/', $classes[1]) : [];
		$classes = implode(' ', array_map(function ($c) {
			return 'plugin-autotooltip__' . $c;
		}, $classes));
		$data['classes'] = strlen($classes) ? $classes : 'plugin-autotooltip__default';

		// <autott class1 class2>wikilink|Desc</autott>
		if (strchr($inner, '<') === FALSE) {
			$parts = array_map(function($s) {return trim($s);}, explode('|', $inner));
			if (cleanID($parts[0]) == $parts[0]) {
				$data['pageid'] = $parts[0];
				if (count($parts) > 1) {
					$data['content'] = $parts[1];
				}
				return $data;
			}
		}
		// <autott class1 class2><content></content><tip></tip><pageid></pageid></autott>
		else {
			preg_match('/<content>(.+)<\/content>/', $inner, $content);
			preg_match('/<tip>(.+)<\/tip>/', $inner, $tip);

			if (count($content) >= 1 || count($pageid) >= 1) {
				$data['content'] = count($content) >= 1 ? $content[1] : '';

				$data['tip'] = count($tip) >= 1 ? $tip[1] : null;

				return $data;
			}
		}

		return 'ERROR';
	}


	/**
	 * @param string $mode
	 * @param Doku_Renderer $renderer
	 * @param array|string $data - Data from handle()
	 * @return bool|void
	 */
	function render($mode, Doku_Renderer $renderer, $data) {
		if ($mode == 'xhtml') {
			if ($data == 'ERROR') {
				msg('Error: Invalid instantiation of autotooltip plugin');
			}
			else if ($data['pageid']) {
				$renderer->doc .= $this->m_helper->forWikilink($data['pageid'], $data['content'], $data['classes']);
			}
			else {
				$renderer->doc .= $this->m_helper->forText($data['content'], $data['tip'], $data['classes']);
			}
		}
		else {
			if ($data == 'ERROR') {
				$renderer->doc .= 'Error: Invalid instantiation of autotooltip plugin';
			}
			else {
				$renderer->doc .= $data['content'];
			}
		}
	}
}
