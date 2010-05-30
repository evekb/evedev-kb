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
		global $myalliancelist;

		$data = API_Helpers::LoadGlobalData('/eve/AllianceList.xml.aspx');

		$xml_parser = xml_parser_create();
		xml_set_object ( $xml_parser, $this );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler ( $xml_parser, 'characterData' );

		if (!xml_parse($xml_parser, $data, true))
			return false;

		xml_parser_free($xml_parser);
		return true;
	}

	function fetchalliances($overide=false)
	{
		global $myalliancelist;

		if (!isset($this->alliances_))
			$this->initXML($overide);

		return $myalliancelist;
	}

	function startElement($parser, $name, $attribs)
	{
		global $myalliancelist, $alliancedetail, $membercorps, $membercorp, $iscorpsection;

		if ($name == "ROW")
		{
			if (count($attribs))
			{
				foreach ($attribs as $k => $v)
				{
					switch ($k)
					{
						case "NAME":
							$alliancedetail['allianceName'] = $v;
							break;
						case "SHORTNAME":
							$alliancedetail['shortName'] = $v;
							break;
						case "ALLIANCEID":
							$alliancedetail['allianceID'] = $v;
							break;
						case "EXECUTORCORPID":
							$alliancedetail['executorCorpID'] = $v;
							break;
						case "MEMBERCOUNT":
							$alliancedetail['memberCount'] = $v;
							break;
						case "STARTDATE":
							if (!$iscorpsection)
							{
								$alliancedetail['startDate'] = $v;
							} else
							{
								$membercorp['startDate'] = $v;
								$membercorps[] = $membercorp;
							}
							break;
						case "CORPORATIONID":
							$membercorp['corporationID'] = $v;
							$iscorpsection = true;
							break;
					}
				}
			}
		}
	}

	function endElement($parser, $name)
	{
		global $myalliancelist, $alliancedetail, $membercorps, $membercorp, $iscorpsection;
		global $tempvalue;

		if ($name == "CURRENTTIME")
			$this->CurrentTime_ = $tempvalue;
		if ($name == "CACHEDUNTIL")
		{
			if  (config::get('API_extendedtimer_alliancelist') == 0)
			{
				$this->CachedUntil_ = date("Y-m-d H:i:s", (strtotime($this->CurrentTime_)) + 85500);
			} else
			{
				$this->CachedUntil_ = $tempvalue;
			}
			ApiCache::set('API_eve_AllianceList' , $this->CachedUntil_);
		}

		switch ($name)
		{
			case "ROWSET":
				if ($alliancedetail['allianceName'] != "" && $alliancedetail['allianceID'] != "0")
				{
					$myalliancelist['Name'][] = $alliancedetail['allianceName'];
					$myalliancelist['allianceID'][] = $alliancedetail['allianceID'];
				}
				$alliancedetail['memberCorps'] = $membercorps;
				$this->alliances_[] = $alliancedetail;

				$alliancedetail['allianceName'] = "";
				$alliancedetail['shortName'] = "";
				$alliancedetail['allianceID'] = "";
				$alliancedetail['executorCorpID'] = "";
				$alliancedetail['memberCount'] = "";
				$alliancedetail['startDate'] = "";
				$alliancedetail['memberCorps'] = array();
				$membercorps = array();
				$membercorp = array();
				unset($alliancedetail['memberCorps']);
				unset($membercorps);
				unset($membercorp);
				$iscorpsection = false;
				break;
		}
	}

	function characterData($parser, $data)
	{
		global $tempvalue;

		$tempvalue = $data;
	}

	function updatealliancetable()
	{
		if (!isset($this->alliances_))
			$this->initXML();

		if (!isset($this->alliances_))
			return false;

		$qry = DBFactory::getDBQuery();
		$qry->execute("DROP TABLE IF EXISTS `kb3_all_corp`;");
		$qry->execute("CREATE TABLE kb3_all_corp (
              all_id bigint(3) unsigned default '0',
              corp_id bigint(3) unsigned default '0',
              all_name varchar(200) default NULL
            ) ");

		$alliances = $this->alliances_;

		foreach ($alliances as $arraykey => $arrayvalue)
		{
			$tempally = $arrayvalue;

			foreach ($tempally as $key => $value)
			{
				switch ($key)
				{
					case "allianceName":
						$allyname = $value;
						break;
					case "allianceID":
						$allyid = $value;
						break;
					case "memberCorps":
						$allycorps = $value;
						$q='';
						foreach ($allycorps as $corpkey => $corpvalue)
						{
							$tempcorp = $corpvalue;
							foreach ($tempcorp as $tempkey => $tempvalue)
							{
								switch ($tempkey)
								{
									case "corporationID":
										$q.="(".$allyid.",".$tempvalue.",'".slashfix($allyname)."'),";
										break;
								}
							}
						}
						if (strlen($q)>0)
							$qry->execute("INSERT INTO kb3_all_corp values ".substr($q,0,strlen($q)-1));
						break;
				}
			}
		}
		return true;
	}

	function LocateAlliance($name)
	{
		if (!isset($this->alliances_))
			$this->initXML();

		if (!isset($this->alliances_))
			return false;

		$alliances = $this->alliances_;

		foreach ($alliances as $arraykey => $arrayvalue)
		{
			$tempally = $arrayvalue;
			if($tempally['allianceName'] == $name) return $tempally;

//            foreach ($tempally as $key => $value)
//            {
//                switch ($key)
//                {
//                    case "allianceName":
//                        //return $tempally;
//						if ( $value == $name )
//						{
//							return $tempally;
//						}
//                        break;
//                }
//            }
		}
		return false;
	}

	function LocateAllianceID($id)
	{
		if (!isset($this->alliances_))
			$this->initXML();

		if (!isset($this->alliances_))
			return false;

		$alliances = $this->alliances_;

		foreach ($alliances as $arraykey => $arrayvalue)
		{
			$tempally = $arrayvalue;
			if($tempally['allianceID'] == $id) return $tempally;
//            foreach ($tempally as $key => $value)
//            {
//                switch ($key)
//                {
//                    case "allianceID":
//                        //return $tempally;
//						if ( $value == $id )
//						{
//							return $tempally;
//						}
//                        break;
//                }
//            }
		}
		return false;
	}

	function UpdateAlliances($andCorps = false)
	{
		if (!isset($this->alliances_))
			$this->initXML();

		if (!isset($this->alliances_))
			return false;

		if ($andCorps)
		{
			// Remove every single corp in the Killboard DB from their current Alliance
			$db = DBFactory::getDBQuery(true);
			$db->execute("SELECT all_id FROM kb3_alliances WHERE all_name LIKE 'None'");
			$row = $db->getRow();
			$db->execute("UPDATE kb3_corps
							SET crp_all_id = ".$row['all_id']);
		}

		$alliances = $this->alliances_;
		$alliance = new Alliance();
		$tempMyCorp = new Corporation();
		$myCorpAPI = new API_CorporationSheet();

		$NumberOfAlliances = 0;
		$NumberOfCorps = 0;
		$NumberOfAlliancesAdded = 0; // we won't know this
		$NumberOfCorpsAdded = 0;

		foreach ($alliances as $arraykey => $arrayvalue)
		{
			$tempally = $arrayvalue;
			$NumberOfAlliances++;

			foreach ($tempally as $key => $value)
			{
				switch ($key)
				{
					case "allianceName":
						$alliance->add($value);
						break;
					case "memberCorps":
					// if $andCorps = true then add each and every single corp to the evekb db - resolving each name (expect this to be slow)
					// WARNING: Processing 5000+ corps this way is extremely slow and is almost guaranteed not to complete
						if ($andCorps)
						{
							foreach ($value as $tempcorp)
							{
								$NumberOfCorps++;

								$myCorpAPI->setCorpID($tempcorp["corporationID"]);
								$result .= $myCorpAPI->fetchXML();

								//$NumberOfCorpsAdded++;
								$tempMyCorp->add($myCorpAPI->getCorporationName(), $alliance , gmdate("Y-m-d H:i:s"));

							}

						}
						break;
				}
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