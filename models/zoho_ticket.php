<?php
/* Import Zoho library to use behind the scenes */
App::import('Lib', 'Zoho.Zoho');
App::import('Core', 'Xml');

/**
 * Model used by Zoho plugin to save ticket information
 *
 * @package zoho
 * @author David Kullmann
 */
class ZohoTicket extends ZohoAppModel {
	
/**
 * Model name
 *
 * @var string
 */
	public $name = 'ZohoTicket';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'ticket_number';

/**
 * Expiration time for tickets
 *
 * @var string
 */
	public $expirationDays = 6;

/**
 * Custom methods 
 *
 * @var string
 */
	public $_findMethods = array('active' => true);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'id' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
			),
		),
		'ticket_number' => array(
			'alphanumeric' => array(
				'rule' => array('alphanumeric'),
			),
			'isUnique' => array(
				'rule' => array('isUnique'),
			)
		),
	);

/**
 * Constructor - setup Zoho library to use
 *
 * @param string $id 
 * @param string $table 
 * @param string $ds 
 * @author David Kullmann
 */	
	public function __construct($id = false, $table = NULL, $ds = NULL) {
		parent::__construct($id, $table, $ds);
		$this->Zoho = new Zoho();
		$ticket = $this->find('active');
		if (!empty($ticket)) {
			$this->Zoho->config['ticket'] = $ticket['ZohoTicket']['ticket_number'];			
		}
	}
	
/**
 * Retrieve an active ticket to use for API
 *
 * @param $state String 'before' or 'after' find
 * @param $query Array of query information
 * @param $results Array of find results, only present in 'after' state
 * @see http://www.codeforest.net/cakephp-tip-custom-find-types
 * @author David Kullmann
 */
	protected function _findActive($state, $query, $results = array()) {
		if ($state == 'before') {
			$expires = strtotime(sprintf('-%s days', $this->expirationDays));
			$query['limit'] = 1;
			$query['conditions'][] = array('created >' => date('Y-m-d H:i:s', $expires));
			return $query;
		} else if ($state == 'after') {
			if (!empty($results[0])) {
				return $results[0];				
			} else {
				return array();
			}
		}
		
	}

/**
 * Accepts a record or list of records of *one* type to update in the CRM
 *
 * @param array $data Array of CakePHP-like data
 * @return void
 * @author David Kullmann
 */
	public function saveRecords($module = null, $fields = array(), $data = array()) {
		
		$raw = $this->Zoho->mapData($fields, $data);

		$xml = $this->Zoho->toZohoXml($module, $raw);
				
		$this->Zoho->insertRecords($module, $xml);
	}
	
}
?>