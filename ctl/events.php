<?php

require_once DOKU_PLUGIN."proza/mdl/events.php";
require_once DOKU_PLUGIN."proza/mdl/groups.php";

$helper = $this->loadHelper('proza');
if (!$helper->user_viewer()) 
	throw new Proza_DBException($this->getLang('e_access_denied'));

$filters = array('group_n', 'coordinator', 'state', 'year');

if (count($_POST) > 0) {

	$query = array('events');
	foreach ($filters as $f) {
		if ($_POST[$f] != '-all')
			array_push($query, $f, $_POST[$f]);
	}

	header('Location: ?id='.$this->id($query));
}

$db = new DB();
$events = $db->spawn('events');
$groups = $db->spawn('groups');

try {

	foreach ($filters as $f) {
		if (isset($this->params[$f]))
			$where[$f] = $this->params[$f];
	}

	/*year jest traktowany specjalnie*/
	if (isset($where['year'])) {
		$year = $where['year'];
		unset($where['year']);

		if (isset($where['state']) && $where['state'] != '0')
			$field = 'finish_date';
		else
			$field = 'plan_date';

		$where[$field] = array('BETWEEN', $year.'-01-01', $year.'-12-31');
	}

	$this->t['events'] = $events->select(
		array('events.id', "groups.$this->lang_code as group_n", 'state',
		'plan_date', 'assumptions_cache', 'coordinator', 'summary_cache', 'finish_date'),
		$where, 'events.id', 'DESC');

	$this->t['groups'] = $groups->groups($this->lang_code);

	$this->t['helper'] = plugin_load('helper', 'proza');
	$this->t['coordinators'] = $this->t['helper']->users();

	$this->t['years'] = $events->years($this->params['group']);

} catch (Proza_ValException $e) {
	$this->errors = $e->getErrors();
	$this->preventDefault();
}

