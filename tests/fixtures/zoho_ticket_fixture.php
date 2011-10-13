<?php
/* ZohoTicket Fixture generated on: 2011-10-12 19:10:50 : 1318446770 */
class ZohoTicketFixture extends CakeTestFixture {
	var $name = 'ZohoTicket';

	var $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'ticket_number' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 32, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
	);

	var $records = array(
		array(
			'id' => '4e95e6b2-6124-4278-9e3b-40d179cafc24',
			'ticket_number' => 'Lorem ipsum dolor sit amet',
			'modified' => '2011-10-12 19:12:50',
			'created' => '2011-10-12 19:12:50'
		),
	);
}
?>