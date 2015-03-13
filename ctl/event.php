<?php

require_once DOKU_PLUGIN."proza/mdl/events.php";

$db = new DB();
$events = $db->spawn('events');
$categories = $db->spawn('categories');

try {
	$categories = $categories->select('name', array('group_n' => $this->params['group']));

	$this->t['categories'] = array();
	while ($row = $categories->fetchArray())
		$this->t['categories'][] = $row['name'];

	$this->t['helper'] = $this->loadHelper('proza');
	$this->t['coordinators'] = $this->t['helper']->users();

} catch (Proza_ValException $e) {
	$this->errors = $e->getErrors();
	$this->preventDefault();
}

if ($this->params['action'] == 'add')
	try {
		$data = $_POST;
		$data['group_n'] = $this->params['group'];
		$events->insert($data);
		header('Location: ?id='.$this->id('events', 'group', $this->params['group']));
	} catch (Proza_ValException $e) {
		$this->t['errors']['events'] = $e->getErrors();
		$this->t['values'] = $_POST;
	}
if ($this->params['action'] == 'edit')
	try {
		$id = $this->params['id']; 
		$event = $events->select(
			array('name', 'assumptions', 'plan_date', 'coordinator', 'summary', 'finish_date'),
			array('id' => $id, 'group_n' => $this->params['group']));

		$this->t['values'] = $event->fetchArray();

	/*błędne id - błąd na górę*/
	} catch (Proza_ValException $e) {
		$this->errors = $e->getErrors();
		$this->preventDefault();
	}
elseif ($this->params['action'] == 'update')
	try {
		$data = $_POST;
		$data['group_n'] = $this->params['group'];
		$events->update($data, $this->params['id']);
		header('Location: ?id='.$this->id('events', 'group', $this->params['group']));
	} catch (Proza_ValException $e) {
		$this->t['errors']['events'] = $e->getErrors();
		$this->t['values'] = $_POST;
		$this->params['action'] = 'edit';
	}
elseif ($this->params['action'] == 'duplicate')
	try {
		$id = $this->params['id']; 
		$event = $events->select(
			array('name', 'assumptions', 'coordinator'),
			array('id' => $id, 'group_n' => $this->params['group']));

		$this->t['values'] = $event->fetchArray();

	/*błędne id - błąd na górę*/
	} catch (Proza_ValException $e) {
		$this->errors = $e->getErrors();
		$this->preventDefault();
	}