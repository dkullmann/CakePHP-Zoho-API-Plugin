<?php
App::import('Core', 'Xml');
App::import('Core', 'HttpSocket');
class Zoho {

/**
 * Zoho API URLs
 *
 * Used to create URLs for different actions
 *
 * @access public
 */
	public $url = array(
		'login' => 'https://accounts.zoho.com/login',
		'action' => 'https://crm.zoho.com/crm/private/%s/%s/%s'
	);

/**
 * Default FROM_AGENT setting
 *
 * @var string
 */
	public $fromAgent = 'true'; // Yes, this should be string "true" not the value true -DK

/**
 * default LOGIN_ID
 *
 * @var string
 */
	public $loginId = null;
	
/**
 * default PASSWORD
 *
 * @var string
 */
	public $password = null;
	
/**
 * default servicename
 *
 * @var string
 */
	public $serviceName = 'ZohoCRM';

/**
 * Zoho XML style open tag
 *
 * @var string
 */
	protected $_xmlOpen = '<%s>';

/**
 * Zoho XML style close tag
 *
 * @var string
 */
	protected $_xmlClose = '</%s>';
	
/**
 * Load the configuration, allow overriding of defaults
 *
 * @param array $config Array of configuration options
 * @author David Kullmann
 */
	public function __construct($config = array()) {
		
		$defaults = array(
			'FROM_AGENT' => $this->fromAgent,
			'LOGIN_ID' => $this->loginId,
			'PASSWORD' => $this->password,
			'servicename' => $this->serviceName
		);
		
		Configure::load('Zoho.Zoho');
		
		$this->config = array_merge($defaults, Configure::read('Zoho'), $config);
		$this->Http = new HttpSocket();
	}

/**
 * Login to Zoho and save the ticket ID
 *
 * @param array $options Array of login options
 * @return void
 * @author David Kullmann
 */
	public function login($options = array()) {

		$options = array_merge($this->config, $options);
	
		$uri = $this->url['login'];
	
		$data = array(
			'FROM_AGENT' => $options['FROM_AGENT'],
			'LOGIN_ID' => $options['LOGIN_ID'],
			'PASSWORD' => $options['PASSWORD'],
			'servicename' => $options['servicename']
		);
			
		$result = $this->_zohoRequest($uri, $data);
	
		/* Convert the key => value data in the body into usable format */
		foreach (split("[\r|\n]", $result['body']) as $line) {
			$line = trim($line);
			if (strpos($line, '#') === 0) {
				continue;
			}
			list($key, $value) = split('=', $line);
			$session[$key] = $value;
		}
		
		return $this->session = $session;
	}

/**
 * Send a POST request to the Zoho API and return the response
 *
 * @param mixed $uri 
 * @param mixed $query Array of query data
 * @param array $request Array of request data
 * @return array Array of response data
 * @see http://api.cakephp.org/class/http-socket#method-HttpSocketpost
 * @author David Kullmann
 */
	protected function _zohoRequest($uri = null, $query = array(), $request = array()) {
		
		/* Defaults for request, make sure to use HTTPS */
		$request = array_merge(array(
			'port' => 443,
			'scheme' => 'https'
		), $request);
		
		/* Set API defaults if we are already logged in */
		if (!isset($query['LOGIN_ID'])) {
			$query = array_merge(array('ticket' => $this->config['ticket'],
			'apikey' => $this->config['api_key'],
			'newFormat' => 1,
			'version' => 1,
			'duplicateCheck' => 2
			), $query);
		}

		$this->Http->post($uri, $query, $request);

		/* Could do some post processing here */

		return $this->Http->response;
	}

/**
 * Use a field map to convert CakePHP model data to Zoho friendly format
 *
 * @param array $fields CakePHP model data => Zoho field map
 * @param array $data Array of CakePHP model data
 * @return array Single dimension array of data usable by Zoho::toZohoXml()
 * @author David Kullmann
 * @todo - Add support for multiple rows
 */
	public function mapData($fields = array(), $data = array()) {
		$return = array();

		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$return[] = $this->mapData($fields, $value);
			} else {
				foreach ($fields as $modelField => $zohoField) {
					if (strpos($modelField, '.') !== false) {
						list ($model, $field) = split('\.', $modelField);
					} else {
						$field = $modelField;
					}
					if (isset($model) && !empty($data[$model][$field])) {
						$return[$zohoField] = $data[$model][$field];
					} else if (!empty($data[$field])) {
						$return[$zohoField] = $data[$field];
					}
				}
			}	
		}
				
		return $return;
	}

/**
 * Converts an array of Zoho friendly key value pairs into Zoho friendly xml-like data
 *
 * @param string $module Name of Zoho API module being used 
 * @param array $data Array of key => value Zoho friendly data 
 * @return void
 * @author David Kullmann
 * @todo - Add support for multiple rows
 */
	public function toZohoXml($module = null, $data = array()) {
		
		$xmlOpen    = $this->xmlOpen($module);
		$xmlContent = $this->xmlContent($data);
		$xmlClose   = $this->xmlClose($module);
		
		return implode("\n", array($xmlOpen, $xmlContent, $xmlClose));
	}

/**
 * Create XML open tag
 *
 * @param string $module Zoho module name
 * @return string Xml open tag
 * @author David Kullmann
 */
	public function xmlOpen($module = null) {
		return sprintf($this->_xmlOpen, $module);
	}

/**
 * Converts mapData formatted data to xml content for Zoho API
 *
 * @param array $data mapData formatted array
 * @return string XML content
 * @author David Kullmann
 * @see https://zohocrmapi.wiki.zoho.com/API-Methods.html
 */
	public function xmlContent($data = array()) {
		
		$xml = '';
		$xmlRows = array();
		
		$rows = $this->xmlRows($data);
		
		for ($i = 0; $i < count($rows); $i++) {
			$row = sprintf('<row no="%s">', $i + 1);
			$content = implode("\n", $rows[$i]);
			$xmlRows[] = implode("\n", array($row, $content, '</row>'));
		}
		$xml = implode("\n", $xmlRows);
		return $xml;
	}

/**
 * Recursively creates XML rows, or a single XML row, for the XML content
 *
 * @param array $data mapData formatted array
 * @return array Array of rows, each containing an array of values
 * @author David Kullmann
 */
	public function xmlRows($data = array()) {
		
		$rows = array();
		
		foreach ($data as $field => $value) {
			if (is_numeric($field)) {
				$rows[] = $this->xmlRows($value);
			} else {
				$rows[] = sprintf('<FL val="%s">%s</FL>', $field, $value);
			}
		}
		return $rows;
	}

/**
 * Create XML close tag
 *
 * @param string $module Zoho module name
 * @return string XML close tag
 * @author David Kullmann
 */
	public function xmlClose($module = null) {
		return sprintf($this->_xmlClose, $module);
	}
	
/**
 * Inserts one or more records in ZohoCRM
 *
 * @param string $module Zoho module name  
 * @param string $xml XML string to POST
 * @param array $options Optional settings
 * @return void
 * @author David Kullmann
 */
	public function insertRecords($module = null, $xml = string, $options = array()) {
		if (empty($module)) {
			throw new Exception('No module name provided for update');
		}

		$dataType = 'xml';

		if (isset($options['dataType'])) {
			$dataType = $options['dataType'];
		}

		$uri = sprintf($this->url['action'], $dataType, $module, 'insertRecords');

		$response = $this->_zohoRequest($uri, array('xmlData' => $xml));

		debug($response);
	}
}