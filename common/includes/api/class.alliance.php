<?php
/*
 * $Date: 2010-05-30 19:38:00 +1000 (Sun, 30 May 2010) $
 * $Revision: 732 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.eveapi.php $
*/

// **********************************************************************************************************************************************
// ****************                                 API Alliance list - /eve/AllianceList.xml.aspx                               ****************
// **********************************************************************************************************************************************

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
		if(!$this->sxe)
		{
			$this->errormsg = "XML error:\n";
			foreach(libxml_get_errors() as $error)
			{
				$this->errormsg .= "\t".$error->message."\n";
			}
			return false;
		}
		$this->CurrentTime_ = strval($this->sxe->currentTime);
		
		if(config::get('API_extendedtimer_alliancelist') == 0)
			$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
		else
			$this->CachedUntil_ = strval(cachedUntil);
		ApiCache::set('API_eve_AllianceList' , $this->CachedUntil_);
		return true;
	}

	function fetchalliances($overide=false)
	{
		if (!isset($this->alliances_))
			$this->initXML($overide);

		$myalliancelist = array();
		$myalliancelist['Name'] = array();
		$myalliancelist['allianceID'] = array();

		foreach($this->sxe->result->rowset->row as $row)
		{
			$myalliancelist['Name'][] = $row['allianceName'];
			$myalliancelist['allianceID'][] = $row['allianceID'];
		}
		return $myalliancelist;
	}

	function updatealliancetable()
	{
		if (!isset($this->sxe))
			$this->initXML();

		if (!isset($this->sxe))
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
//		$alliances = $this->alliances_;
//
//		foreach ($alliances as $arraykey => $arrayvalue)
//		{
//			$tempally = $arrayvalue;
//
//			foreach ($tempally as $key => $value)
//			{
//				switch ($key)
//				{
//					case "allianceName":
//						$allyname = $value;
//						break;
//					case "allianceID":
//						$allyid = $value;
//						break;
//					case "memberCorps":
//						$allycorps = $value;
//						$q='';
//						foreach ($allycorps as $corpkey => $corpvalue)
//						{
//							$tempcorp = $corpvalue;
//							foreach ($tempcorp as $tempkey => $tempvalue)
//							{
//								switch ($tempkey)
//								{
//									case "corporationID":
//										$q.="(".$allyid.",".$tempvalue.",'".slashfix($allyname)."'),";
//										break;
//								}
//							}
//						}
//						if (strlen($q)>0)
//							$qry->execute("INSERT INTO kb3_all_corp values ".substr($q,0,strlen($q)-1));
//						break;
//				}
//			}
//		}
		return true;
	}

	function LocateAlliance($name)
	{
		if (!isset($this->sxe))
			$this->initXML();

		if (!isset($this->sxe))
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

		$alliances = $this->alliances_;

		foreach ($alliances as $arraykey => $arrayvalue)
		{
			$tempally = $arrayvalue;
			if($tempally['allianceName'] == $name) return $tempally;

		}
		return false;
	}

	function LocateAllianceID($id)
	{
		if (!isset($this->sxe))
			$this->initXML();

		if (!isset($this->sxe))
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

		if (!isset($this->sxe))
			return false;

//		if ($andCorps)
//		{
//			// Remove every single corp in the Killboard DB from their current Alliance
//			$db = DBFactory::getDBQuery(true);
//			$db->execute("SELECT all_id FROM kb3_alliances WHERE all_name LIKE 'None'");
//			$row = $db->getRow();
//			$db->execute("UPDATE kb3_corps
//							SET crp_all_id = ".$row['all_id']);
//		}

//		$alliances = $this->alliances_;
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
//		foreach ($alliances as $arraykey => $arrayvalue)
//		{
//			$tempally = $arrayvalue;
//			$NumberOfAlliances++;
//
//			foreach ($tempally as $key => $value)
//			{
//				switch ($key)
//				{
//					case "allianceName":
//						$alliance->add($value);
//						break;
//					case "memberCorps":
//					// if $andCorps = true then add each and every single corp to the evekb db - resolving each name (expect this to be slow)
//					// WARNING: Processing 5000+ corps this way is extremely slow and is almost guaranteed not to complete
//						if ($andCorps)
//						{
//							foreach ($value as $tempcorp)
//							{
//								$NumberOfCorps++;
//
//								$myCorpAPI->setCorpID($tempcorp["corporationID"]);
//								$result .= $myCorpAPI->fetchXML();
//
//								//$NumberOfCorpsAdded++;
//								$tempMyCorp->add($myCorpAPI->getCorporationName(), $alliance , gmdate("Y-m-d H:i:s"));
//
//							}
//
//						}
//						break;
//				}
//			}
//		}
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