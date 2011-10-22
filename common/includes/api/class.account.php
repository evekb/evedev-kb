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
	private $error = false;
	private $chars = array();

	public function fetch($userID, $APIKey)
	{
		$data = self::CallAPI( "account", "Characters", null, $userID, $APIKey );
		
		foreach($data->characters as $character) {
			$this->chars[] = array(
					'Name'=>strval($character->name),
					'corpName'=>strval($character->corporationName),
					'charID'=>strval($character->characterID),
					'corpID'=>strval($character->corporationID));

			// add any characters not already in the kb
			$this->updateChars();
					
			}
		return $this->chars;
	}

	public function isOldKey($userID, $APIKey) {
		PhealConfig::getInstance()->api_customkeys = false;
		$data = self::CallAPI( "account", "Characters", null, $userID, $APIKey );
		if( $data == false ) {
			return false;
		}
		return true;
	}
	
	public function CheckAccess($userID, $APIKey, $mask) {
		$data = self::CallAPI( "account", "APIKeyInfo", null, $userID, $APIKey );
		if (($data->key->accessMask & $mask)) 
			return true;
		return false;
	}
	
	public function GetType($userID, $APIKey) {
		$data = self::CallAPI( "account", "APIKeyInfo", null, $userID, $APIKey );
		return $data->key->type;
	}
}