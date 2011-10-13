<?php
/* ZohoTicket Test cases generated on: 2011-10-12 19:10:50 : 1318446770*/
App::import('Model', 'ZohoTicket');

class ZohoTicketTestCase extends CakeTestCase {
	var $fixtures = array('app.zoho_ticket');

	function startTest() {
		$this->ZohoTicket =& ClassRegistry::init('ZohoTicket');
	}

	function endTest() {
		unset($this->ZohoTicket);
		ClassRegistry::flush();
	}

}
?>