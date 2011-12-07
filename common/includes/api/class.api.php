<?php

class API {
	private $error = null;

	protected $pheal = null;
	
	function API() {
		// Loads pheal, so we can do some stuff with it, 
		require_once("common/pheal/Pheal.php");
		spl_autoload_register("Pheal::classload");

		PhealConfig::getInstance()->http_method = 'curl';
		PhealConfig::getInstance()->http_post = false;
		PhealConfig::getInstance()->http_keepalive = true;
		// default 15 seconds
		PhealConfig::getInstance()->http_keepalive = 10; 
		// KeepAliveTimeout in seconds
		PhealConfig::getInstance()->http_timeout = 60;
		//PhealConfig::getInstance()->cache = new PhealMemcache(array('port' => 11211));
		PhealConfig::getInstance()->cache = new PhealFileCache('cache/api/');
		PhealConfig::getInstance()->api_customkeys = true;
		PhealConfig::getInstance()->log = new PhealFileLog('cache/api/');
	}

	function IsCached() {
		$isCached = (bool)PhealConfig::getInstance()->cache->load($keyID, $vCode, $this->options['scope'], $this->options['name'], $api['args']); 
	}
	
	function CallAPI( $scope, $call, $data, $userid, $key ) {
		//echo $scope, $call, $userid, $key;
		//PhealConfig::getInstance()->api_customkeys = false;
		$this->pheal = new Pheal($userid, $key, $scope);

		$this->error = null;

		try {
			if( is_array( $data ) ) {
				$result = $this->pheal->{$call}($data);
			} else {
				$result = $this->pheal->{$call}();
			}
		} catch(PhealException $e) {
			$this->error = $e->getCode();
			$this->message = $e->getMessage();
			return false;
		}
		
		return $result;
	}

	/**
	* Return any error codes encountered or null if none.
	 *
	 * @return integer
	 */
	function getError()
	{
		return $this->error;
	}
	/**
	* Return any error messages encountered or null if none.
	 *
	 * @return string
	 */
	function getMessage()
	{
		return $this->message;
	}
}