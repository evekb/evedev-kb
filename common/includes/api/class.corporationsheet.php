<?php
/**
 * $Date: 2010-05-30 13:44:06 +1000 (Sun, 30 May 2010) $
 * $Revision: 721 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
 * @package EDK
 */

 require_once("class.api.php");
/**
 * API Corporation Sheet - /corp/CorporationSheet
 *
 * @package EDK
 */
class API_CorporationSheet extends API
{
	private $_result;

	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}

	function setAPIKey($key)
	{
		$this->API_apiKey_ = $key;
	}

	function setUserID($uid)
	{
		$this->API_userID_ = $uid;
	}

	function setCharacterID($cid)
	{
		$this->API_charID_ = $cid;
	}

	function setCorpID($corpid)
	{
		$this->API_corpID_ = $corpid;
	}
	// ===================================
	
	function getAllianceID()
	{
		return $this->_result->allianceID;
	}

	function getAllianceName()
	{
		return $this->_result->allianceName;
	}

	function getCorporationID()
	{
		return $this->_result->corporationID;
	}

	function getCorporationName()
	{
		return $this->_result->corporationName;
	}

	function getTicker()
	{
		return $this->_result->ticker;
	}

	function getCeoID()
	{
		return $this->_result->ceoID;
	}

	function getCeoName()
	{
		return $this->_result->ceoName;
	}

	function getStationID()
	{
		return $this->_result->stationID;
	}

	function getStationName()
	{
		return $this->_result->stationName;
	}

	function getDescription()
	{
		return $this->_result->description;
	}

	function getUrl()
	{
		return $this->_result->url;
	}

	function getLogo()
	{
	echo "TODO LOGO";
	
	/*
				case "GRAPHICID":
				$this->logo_["graphicID"] = $this->characterDataValue;
                break;
			case "SHAPE1":
				$this->logo_["shape1"] = $this->characterDataValue;
                break;
			case "SHAPE2":
				$this->logo_["shape2"] = $this->characterDataValue;
                break;
			case "SHAPE3":
				$this->logo_["shape3"] = $this->characterDataValue;
                break;
			case "COLOR1":
				$this->logo_["colour1"] = $this->characterDataValue;
                break;
			case "COLOR2":
				$this->logo_["colour2"] = $this->characterDataValue;
                break;
			case "COLOR3":
				$this->logo_["colour3"] = $this->characterDataValue;
                break;
				*/
	
		return $this->logo_;
	}

	function getTaxRate()
	{
		return $this->_result->taxRate;
	}

	function getMemberCount()
	{
		return $this->_result->memberCount;
	}

	function getMemberLimit()
	{
		return $this->_result->memberLimit;
	}

	function getShares()
	{
		return $this->_result->shares;
	}

	function fetchXML()
	{
		$myKeyString = array();
		// is a player feed - take details from logged in user
		if ($this->API_corpID_ != "")
		{
			$myKeyString["corporationID"] = $this->API_corpID_;
			$this->CharName_ = $this->API_corpID_;			
		} elseif (user::get('usr_pilot_id')) {
			$plt = new pilot(user::get('usr_pilot_id'));
			$usersname = $plt->getName();

			$this->CharName_ = $usersname;

			$sql = 'select plts.plt_id, plts.plt_externalid from kb3_pilots plts where plts.plt_name = "' . $usersname . '"';

    		$qry = DBFactory::getDBQuery();;
			$qry->execute($sql);
   		 	$row = $qry->getRow();

    		$pilot_id = $row['plt_id'];
    		$API_charID = $row['plt_externalid'];

    		if ( $pilot_id == 0 )
			{
        		return "Something went wrong with finding pilots external ID<br>";
    		}

			$newsql = 'SELECT userID , apiKey FROM kb3_api_user WHERE charID = "' . $API_charID . '"';
			$qry->execute($newsql);
    		$userrow = $qry->getRow();

			$API_userID = $userrow['userID'];
			$API_apiKey = $userrow['apiKey'];

			$myKeyString["userID"] = $API_userID;
			$myKeyString["apiKey"] = $API_apiKey;
			$myKeyString["characterID"] = $API_charID;
		} else {
			if (($this->API_userID_ != "") && ($this->API_apiKey_ != "") && ($this->API_charID_ != ""))
			{
				$myKeyString["userID"] = $this->API_userID_;
				$myKeyString["apiKey"] = $this->API_apiKey_;
				$myKeyString["characterID"] = $this->API_charID_;

				$this->CharName_ = $this->API_charID_;
			} else {
				return "You are not logged in and have not set API details.";
			}
		}

		$this->_result = $this->CallAPI( "corp", "CorporationSheet", array( 'corporationID' => $myKeyString['corporationID'] ), $this->API_userID_, $this->API_apiKey_ );
		if( $this->_result == false ) {
			return false;
		}
		
		$this->CurrentTime_ = $this->_result->request_time;
		$this->CachedUntil_ = $this->_result->cached_until;

		return true;
	}
}
