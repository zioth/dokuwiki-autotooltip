<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_REL')) define('DOKU_REL', '/dokuwiki/');

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
		// Run this after all internal doku syntax, so formatting can be included in parameters.
		// See https://www.dokuwiki.org/devel:parser:getsort_list
		return 400;
	}

	function getAllowedTypes() {
		return array('formatting', 'substition', 'disabled');
	}

	/**
	 * @param $mode
	 */
	function connectTo($mode) {
		$this->Lexer->addEntryPattern('<autott(?=[^>]*>.*?</autott>)', $mode, 'plugin_autotooltip');
	}

	/**
	 * After the entry pattern is found.
	 */
	public function postConnect() {
		$this->Lexer->addExitPattern('</autott>','plugin_autotooltip');
	}

	/**
	 * @param string $match - The match from addEntryPattern.
	 * @param int $state - The DokuWiki event state.
	 * @param int $pos - The position in the full text.
	 * @param Doku_Handler $handler
	 * @return array|string
	 */
	function handle($match, $state, $pos, Doku_Handler $handler) {
		if ($state === DOKU_LEXER_EXIT) {
			return array($state, '');
		}
		if ($state === DOKU_LEXER_ENTER) {
			return array($state, $match);
		}

		// Content between entry and exit patterns
		if ($state !== DOKU_LEXER_UNMATCHED) {
			msg("unmatched: $match ".rand());
			return array();
		}

		preg_match('/^\s*([^>]+?)\s*>([\s\S]+)\s*<\/autott>/', $match, $main);
		preg_match('/<content>\s*([\s\S]+)\s*<\/content>/', $match, $content);
		preg_match('/<tip>\s*([\s\S]+)\s*<\/tip>/', $match, $tip);
		preg_match('/<title>\s*([\s\S]+)\s*<\/title>/', $match, $title);

		$inner = $main[2] ?? '';
		$data = [
			'classes' => $main[1] ?? '',
			'content' => $content[1] ?? '',
			'tip' => $tip[1] ?? '',
			'title' => $title[1] ?? '',
			'pageid' => '',
		];

		// <autott class1 class2><content></content><tip></tip><title></title><pageid></pageid></autott>
		if ($content || $tip || $title) {
			return $data;
		}
		// <autott class1 class2>wikilink|description</autott>
		else {
			$parts = array_map(function($s) {return trim($s);}, explode('|', $data['inner']));
			if (cleanID($parts[0]) == $parts[0]) {
				$data['pageid'] = $parts[0];
				if (count($parts) > 1) {
					$data['content'] = $parts[1];
				}
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
		if ($data == 'ERROR') {
			msg('Error: Invalid instantiation of autotooltip plugin');
		}
		else if ($mode == 'xhtml') {
			if ($data['pageid'] !== '') {
				$renderer->doc .= $this->m_helper->forWikilink($data['pageid'], $data['content'], '', $data['classes']);
			}
			else {
				$renderer->doc .= $this->m_helper->forText($data['content'], $data['tip'], $data['title'], '', $data['classes']);
			}
		}
		else {
			$renderer->doc .= $data['content'] ?? '';
		}
	}
}
