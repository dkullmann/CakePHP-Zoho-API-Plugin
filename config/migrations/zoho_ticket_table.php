<?php
class M4e95e582eedc4e6b857c47a579cafc24 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'zoho_tickets' => array(
					'id' => array('type' => 'string', 'length' => 36, 'null' => false, 'default' => NULL, 'key' => 'primary'),
					'ticket_number' => array('type' => 'string', 'length' => 32, 'null' => false, 'default' => NULL),
					'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
					'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL)
				)
			)
		),
		'down' => array(
			'drop_table' => array('zoho_ticket')
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}
}
?>