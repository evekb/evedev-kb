<?php
/**
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

 require_once("class.api.php");
/**
 * Retrieve Alliance list from CCP to find alliance details.
 * @package EDK
 */
class API_Alliance extends API
{
	protected $sxe = null;
	protected $CachedUntil_ = null;
	protected $CurrentTime_ = null;
	protected $data = null;

	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function fetchalliances($overide=false)
	{
		$this->data = $this->CallAPI( "eve", "AllianceList", null, null, null );
	}

	function LocateAlliance($name)
	{
		$res = array();
		foreach( $this->data->alliances as $alliance ) {
			if( $alliance->name != $name ) {
				continue;
			}
			$res['name'] = $alliance->name;
			$res['shortName'] = $alliance->shortName;
			$res['allianceID'] = $alliance->allianceID;
			$res['executorCorpID'] = $alliance->executorCorpID;
			$res['memberCount'] = $alliance->memberCount;
			$res['startDate'] = $alliance->startDate;
			$res['allianceName'] = $alliance->name; // @todo wtf?
			
			$res['memberCorps'] = array();
			foreach( $alliance->memberCorporations as $corp ) {
				$res['memberCorps'][] = array('corporationID'=>$corp->corporationID, 
											  'startDate'=>$corp->startDate);
			}
			return $res;
		}
		return false;	
	}

	function LocateAllianceID($id)
	{
		$res = array();
		foreach( $this->data->alliances as $alliance ) {
			if( $alliance->allianceID != $id ) {
				continue;
			}
			$res['name'] = $alliance->name;
			$res['shortName'] = $alliance->shortName;
			$res['allianceID'] = $alliance->allianceID;
			$res['executorCorpID'] = $alliance->executorCorpID;
			$res['memberCount'] = $alliance->memberCount;
			$res['startDate'] = $alliance->startDate;
			$res['allianceName'] = $alliance->name; // @todo wtf?
			
			$res['memberCorps'] = array();
			foreach( $alliance->memberCorporations as $corp ) {
				$res['memberCorps'][] = array('corporationID'=>$corp->corporationID, 
											  'startDate'=>$corp->startDate);
			}
			return $res;
		}
		return false;
	}
}