<?php

class API {
	private $error = null;

	protected $pheal = null;
	
	function __construct() {
		// Loads pheal, so we can do some stuff with it, 
		require_once("common/pheal/Pheal.php");
		spl_autoload_register("Pheal::classload");

                // init API connection method
                API_Helpers::autoSetApiConnectionMethod();
                if(config::get('apiConnectionMethod') == 'curl')
                {
                    PhealConfig::getInstance()->http_method = 'curl';
                }
                else
                {
                    PhealConfig::getInstance()->http_method = 'file';
                }
                
                if(!defined(KB_CACHEDIR))
                {
                    define(KB_CACHEDIR, 'cache');
                }
		PhealConfig::getInstance()->http_post = false;
		PhealConfig::getInstance()->http_keepalive = true;
		// default 15 seconds
		PhealConfig::getInstance()->http_keepalive = 10; 
		// KeepAliveTimeout in seconds
		PhealConfig::getInstance()->http_timeout = 60;
		//PhealConfig::getInstance()->cache = new PhealMemcache(array('port' => 11211));
                // the default delimiter may cause problems on some file system, we want to use a dash for this
		PhealConfig::getInstance()->cache = new PhealFileCache(KB_CACHEDIR.'/api/', array('delimiter' => '-'));
		PhealConfig::getInstance()->api_customkeys = true;
		PhealConfig::getInstance()->log = new PhealFileLog(KB_CACHEDIR.'/api/');
                PhealConfig::getInstance()->api_base = API_SERVER.'/';
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