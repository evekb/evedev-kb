<?php
/*
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
*/

//! Retrieve Alliance list from CCP to find alliance details.
class AllianceAPI
{
	protected $sxe = null;
	protected $CachedUntil_ = null;
	protected $CurrentTime_ = null;

	function getCachedUntil()
	{
		return $this->CachedUntil_;
	}

	function getCurrentTime()
	{
		return $this->CurrentTime_;
	}


	function initXML()
	{
		$data = API_Helpers::LoadGlobalData('/eve/AllianceList.xml.aspx');

		$this->sxe = simplexml_load_string($data);
		if(!$this->sxe || strval($this->sxe->error))
		{
			if(strval($this->sxe->error))
			{
				$this->error = array();
				$this->error['code'] = strval($this->sxe->error['code']);
				$this->error['message'] = strval($this->sxe->error);
			}
			else
			{
				$this->errormsg = "XML error:\n";
				foreach(libxml_get_errors() as $error)
				{
					$this->errormsg .= "\t".$error->message."\n";
				}
			}	
			return false;
		}
		$this->CurrentTime_ = strval($this->sxe->currentTime);
		
		if(config::get('API_extendedtimer_alliancelist') == 0)
			$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
		else
			$this->CachedUntil_ = strval($this->sxe->cachedUntil);
		ApiCache::set('API_eve_AllianceList' , $this->CachedUntil_);
		return true;
	}

	function fetchalliances($overide=false)
	{
		if (isset($this->alliances))
				return $this->alliances;

		$this->initXML($overide);

		if(!isset($this->sxe->result->rowset->row[0]))
			return false;

		$this->alliances = array();
		$this->alliances['Name'] = array();
		$this->alliances['allianceID'] = array();

		foreach($this->sxe->result->rowset->row as $row)
		{
			$this->alliances['Name'][] = $row['allianceName'];
			$this->alliances['allianceID'][] = $row['allianceID'];
		}
		return $this->alliances;
	}

	function updatealliancetable()
	{
		if (!isset($this->sxe))
			$this->initXML();

		if(!isset($this->sxe->result->rowset->row[0]))
			return false;

		$qry = DBFactory::getDBQuery();
		$qry->execute("DROP TABLE IF EXISTS `kb3_all_corp`;");
		$qry->execute("CREATE TABLE kb3_all_corp (
              all_id bigint(3) unsigned default '0',
              corp_id bigint(3) unsigned default '0',
              all_name varchar(200) default NULL
            ) ");

		foreach($this->sxe->result->rowset->row as $row)
		{
			$allID = intval($row['allianceID']);
			foreach($row->rowset->row as $corpRow)
			{
				$res['memberCorps'][] = array('corporationID'=>intval($corpRow['corporationID']), 'startDate'=>strval($corpRow['startDate']));
				$qry->execute("INSERT INTO kb3_all_corp values ($allID, ".intval($corpRow['corporationID']).", ".strval($corpRow['startDate']).")");
			}
		}
		return true;
	}

	function LocateAlliance($name)
	{
		if (!isset($this->sxe))
			$this->initXML();

		if(!isset($this->sxe->result->rowset->row[0]))
			return false;

		foreach($this->sxe->result->rowset->row as $row)
		{
			if($row['name'] != $name) continue;
			foreach($row->attributes() as $key=>$val)
				$res[strval($key)] = strval($val);
			$res['allianceName'] = $res['name'];
			$res['memberCorps'] = array();
			foreach($row->rowset->row as $corpRow)
			{
				$res['memberCorps'][] = array('corporationID'=>intval($corpRow['corporationID']), 'startDate'=>strval($corpRow['startDate']));
			}
			return $res;
		}
		return false;
	}

	function LocateAllianceID($id)
	{
		if (!isset($this->sxe))
			$this->initXML();

		if(!isset($this->sxe->result->rowset->row[0]))
			return false;

		foreach($this->sxe->result->rowset->row as $row)
		{
			if($row['allianceID'] != $id) continue;
			foreach($row->attributes() as $key=>$val)
				$res[strval($key)] = strval($val);
			$res['allianceName'] = $res['name'];
			$res['memberCorps'] = array();
			foreach($row->rowset->row as $corpRow)
			{
				$res['memberCorps'][] = array('corporationID'=>intval($corpRow['corporationID']), 'startDate'=>strval($corpRow['startDate']));
			}
			return $res;
		}
		return false;
	}

	function UpdateAlliances($andCorps = false)
	{
		if (!isset($this->sxe))
			$this->initXML();

		if(!isset($this->sxe->result->rowset->row[0]))
			return array();

		$alliance = new Alliance();
		$tempMyCorp = new Corporation();
		$myCorpAPI = new API_CorporationSheet();

		$NumberOfAlliances = 0;
		$NumberOfCorps = 0;
		$NumberOfAlliancesAdded = 0; // we won't know this
		$NumberOfCorpsAdded = 0;

		foreach($this->sxe->result->rowset->row as $row)
		{
			$NumberOfAlliances++;
			$alliance->add(strval($row['name']), intval($row['allianceID']));
			if($andCorps)
				foreach($row->rowset->row as $corpRow)
				{
					$NumberOfCorps++;
					$res['memberCorps'][] = array('corporationID'=>intval($corpRow['corporationID']), 'startDate'=>strval($corpRow['startDate']));
					$myCorpAPI->setCorpID(intval($corpRow['corporationID']));
					$result .= $myCorpAPI->fetchXML();

					$tempMyCorp->add($myCorpAPI->getCorporationName(), $alliance , gmdate("Y-m-d H:i:s"));
				}

		}
		$returnarray["NumAlliances"] = $NumberOfAlliances;
		$returnarray["NumCorps"] = $NumberOfCorps;
		$returnarray["NumAlliancesAdded"] = $NumberOfAlliancesAdded;
		$returnarray["NumCorpsAdded"] = $NumberOfCorpsAdded;
		return $returnarray;

	}
}

class API_Alliance extends AllianceAPI
{
	
}