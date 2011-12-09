<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

/**
 * API Server Status /server/ServerStatus.xml.aspx
 */
class API_ServerStatus extends API
{
	/** @var boolean Whether the server is open. */
	static protected $serverOpen = false;
	/** @var integer How many players are online. */
	static protected $onlinePlayers = 0;
	
	/**
	 * Whether the server is open
	 * 
	 * @return boolean Whether the server is open
	 */
	function getserverOpen()
	{
		return self::$serverOpen;
	}

	/**
	 * How many players are online.
	 * 
	 * @return int How many players are online.
	 */
	function getOnlinePlayers()
	{
		return self::$onlinePlayers;
	}

	function fetchXML()
	{
		$data = $this->CallAPI( "server", "ServerStatus", null , null, null );

		if($data == false) {
			return false;
		}
		self::$serverOpen = (boolean)$data->serverOpen;
		self::$onlinePlayers = (int)$data->onlinePlayers;
		return true;
	}
}