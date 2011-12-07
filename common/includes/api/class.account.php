<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

 require_once("class.api.php");
/**
 * Retrieve Character list from CCP API
 * @package EDK
 */
class API_Account extends API
{
	protected static $data = array();

	public function fetch($userID, $APIKey)
	{
		if(!isset(self::$data[$userID][$APIKey])) {
			self::$data[$userID][$APIKey] = $this->CallAPI( "account", "APIKeyInfo", null,
					$userID, $APIKey );
		}
		if( self::$data[$userID][$APIKey] == false ) {
			return false;
		}
		return self::$data[$userID][$APIKey]->key->characters->toArray();
	}

	public function isOldKey($userID, $APIKey) {
		PhealConfig::getInstance()->api_customkeys = false;
		$data = $this->CallAPI( "account", "Characters", null, $userID, $APIKey );
		if( $data == false ) {
			return false;
		}
		return true;
	}
	
	public function CheckAccess($userID, $APIKey, $mask) {
		$data = $this->CallAPI( "account", "APIKeyInfo", null, $userID, $APIKey );
		if (($data->key->accessMask & $mask)) 
			return true;
		return false;
	}
	
	public function GetType($userID, $APIKey) {
		$data = $this->CallAPI( "account", "APIKeyInfo", null, $userID, $APIKey );
		return $data->key->type;
	}
}