<?php
if (!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
//require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * Auto-Tooltip DokuWiki plugin, for use with the ActionRenderer plugin. This will run only if
 * ActionRenderer is the current renderer plugin. For improved performance, use the AutoTooltip
 * renderer plugin instead.
 *
 * @license    MIT
 * @author     Eli Fenton
 */
class action_plugin_autotooltip extends DokuWiki_Action_Plugin {
	/** @type helper_plugin_autotooltip m_helper */
	private $m_helper;
	private $m_disable;

	public function __construct() {
		$this->m_disable = !plugin_load('renderer', 'actionrenderer');
		if (!$this->m_disable) {
			global $ID;
			$this->m_helper = plugin_load('helper', 'autotooltip');
		}
	}

	public function register(Doku_Event_Handler $controller) {
		if ($this->m_disable) {
			return;
		}
		$controller->register_hook('PLUGIN_ACTIONRENDERER_METHOD_EXECUTE', 'BEFORE', $this, 'actionrenderer');
	}

	/**
	 * Intercept Doku_Renderer_xhtml:internallink to give every wikilink a tooltip!
	 *
	 * @param Doku_Event $event
	 * @param array $param
	 */
	function actionrenderer(Doku_Event $event, $param) {
		$renderer = $event->data['renderer'];

		if (is_a($renderer, 'renderer_plugin_dw2pdf')) {
			// dw2pdf should not render expanded tooltips.
			return;
		}

		if ($event->data['method'] == 'internallink') {
			$args = $event->data['arguments'];

			$id = $args[0];
			$name = $args[1] ?: 'null';
			$search = $args[2] ?: null;
			$returnonly = $args[3] ?: false;
			$linktype = $args[4] ?: 'content';

			global $ID;
			if (!$this->m_helper->isExcluded($ID) && page_exists($id) && $id != $ID) {
				$event->preventDefault();

				// If we call $renderer->internallink directly here, it will cause infinite recursion,
				// so we need this call_user_func_array hack.
				$link = call_user_func_array(
					array($renderer, 'parent::internallink'),
					array($id, $name, $search, true, $linktype)
				);

				$meta = $this->m_helper->read_meta_fast($id);
				$abstract = $meta['abstract'];
				$link = $this->m_helper->stripNativeTooltip($link);
				$link = $this->m_helper->forText($link, $abstract, $meta['title']);

				if (!$returnonly) {
					$renderer->doc .= $link;
				}
				return $link;
			}
		}
	}
}
