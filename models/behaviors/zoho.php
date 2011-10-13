<?php

class ZohoBehavior extends ModelBehavior {

/**
 * Default values for behavior
 *
 * @var string
 */
	protected $_defaults = array(
		'zohoFields' => array()
	);
	
	public function setup(&$Model, $config = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}
	
	public function afterSave(&$Model, $created = false) {
		if (!empty($this->data)) {
			$this->ZohoTicket = ClassRegistry::init('ZohoTicket');
			$this->ZohoTicket->saveRecords('Leads', $this->User->zohoFields, $this->data);
		}
	}
}
?>