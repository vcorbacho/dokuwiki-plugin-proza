<?php
 
if(!defined('DOKU_INC')) die();
 
class action_plugin_proza extends DokuWiki_Action_Plugin {

	private $action = '';
	private $params = array();
	private $norender = false;
	private $lang_code = '';

	/**
	 * Register its handlers with the DokuWiki's event controller
	 */
	function register(Doku_Event_Handler $controller) {
		$controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'action_act_preprocess');
		$controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'tpl_act_render');
		$controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'tpl_pagetools_display');
	}

	function __construct() {
		global $ACT;

		$id = $_GET['id'];
		$ex = explode(':', $id);
		if ($ex[0] == 'proza' && $ACT == 'show') {
			$this->action = $ex[1];
			$this->params = array_slice($ex, 2);
		/*BEZ w innym języku*/
		} else if ($ex[1] == 'proza' && $ACT == 'show') {
			/*$l = $ex[0];
			$p = DOKU_PLUGIN.'proza/lang/';
			$f = $p.$ex[0].'/lang.php';
			if ( ! file_exists($f))
			$f = $p.'en/lang.php';*/

			$this->action = $ex[2];
			$this->params = array_slice($ex, 3);
		}
		$this->setupLocale();
	}

	function display_error($error) {
		echo '<div class="error">';
		echo $error;
		echo '</div>';
	}

	function tpl_pagetools_display($event, $param) {
		if ($this->action == '')  return;
		$event->preventDefault();
	}

	function action_act_preprocess($event, $param) {
		if ($this->action == '') return;

		$ctl = DOKU_PLUGIN."proza/ctl/".str_replace('/', '', $this->action).".php";
		if (file_exists($ctl)) {
			//wczytaj konfigurację
			$this->loadConfig();
			try {
				require $ctl;
			} catch(Proza_DBException $e) {
				$this->error = $e->getMessage();
				$this->action = 'error';
			}
		}
	}

	function tpl_act_render($event, $param) {
		if ($this->action == '') return;
		elseif ($this->action == 'error') { $this->display_error($this->error); $event->preventDefault(); return; }
		$tpl = DOKU_PLUGIN."proza/tpl/".str_replace('/', '', $this->action).".php";
		if (file_exists($tpl))
			require $tpl;
		$event->preventDefault();
	}
}
