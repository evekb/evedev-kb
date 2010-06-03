<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
*/

/*
 * EDK Feed Syndication v1.8
 * based on liq's feed syndication mod v1.5
 *
*/

@set_time_limit(0);
@ini_set('memory_limit',999999999);
$feedversion = "v1.8";

//! EDK Feed Syndication fetcher class.

/*! This class is used to fetch the feed from another EDK board. It adds all
 * fetched kills to the board and returns the id of the highest kill fetched.
*/
class Fetcher
{
	public $lastkllid_ = 0;
	public $finalkllid_ = 0;
	//private $trackurl_ = '';
	private $accepttrust = '';
	private $trackkey_ = '';
	private $tracklast_ = 0;
	public $combined_ = false;
	private $insideitem = false;
	private $tag = "";
	private $title = "";
	private $description = "";
	private $link = "";
	private $killsAdded=0;
	private $killsSkipped=0;
	private $hash = "";
	private $time = "";
	private $trust = "";
//! Construct the Fetcher class and initialise variables.
	function Fetcher()
	{
	}
//! Fetch a new feed.

	/*! Use the input parameters to fetch a feed, parse it and add new kills
	 * to the db.
	 * \param $url The base URL of the feed to fetch
	 * \param $str The query string to add to the base URL.
	 * \param $trackfriend Either 'on' or blank. Defines whether to fetch
	 * friendly kills
	 * \param $trackkey The configuration key to use when storing feed in
	 * the db.
	 * \return HTML output summarising the results of the fetch.
	*/

	function grab($url, $str, $accepttrust = '', $trackkey = '')
	{
		global $feedversion;
//                $this->trackurl_ = $trackurl;
		$this->html = '';
		$this->accepttrust = $accepttrust;
		$this->trackkey_ = $trackkey;
		$this->killsAdded = 0;
		$this->killsSkipped = 0;
		$fetchurl = $url.$str."&board=".urlencode(KB_TITLE);
		if(strpos($fetchurl, 'apikills=1')) $this->apikills = true;
		else $this->apikills = false;
		if(!strpos($fetchurl,'?')) $fetchurl =
				substr_replace($fetchurl,'?', strpos($fetchurl,'&'),0);
		$this->uurl = $url;
// only lists fetched with lastkllid are ordered by id.
		if(strpos($fetchurl, 'lastkllid')) $this->idordered = true;
		else $this->idordered = false;
		$this->feedfilename = KB_CACHEDIR.'/data/feed'.md5($this->uurl).'.xml';
		$xml_parser = xml_parser_create("UTF-8");
		xml_set_object ( $xml_parser, $this );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler ( $xml_parser, 'characterData' );

		if(file_exists($this->feedfilename))
		{
// Give up trying to parse the cached file after a day.
			if (time() - filemtime($this->feedfilename) > 24 * 60 * 60 )
			{
				unlink($this->feedfilename);
				@unlink($this->feedfilename.'.stat');
				@unlink($this->feedfilename.'.tstat');
			}
		}
		if(!file_exists($this->feedfilename))
		{
			$http = new http_request($fetchurl);
			$http->set_useragent("EDK Feedfetcher ".$feedversion);
			$http->set_timeout(60);
			$http->set_header("Accept-Encoding: gzip");
			$data = $http->get_content();
			if($data == '') return "<i>Error getting XML data from ".$fetchurl."</i><br>".$http->getError()."<br>";

			if(strpos($http->get_header(),"Content-Encoding: gzip")
				&& gzinflate(substr($data,10)))
				$data = gzinflate(substr($data,10));
			$data = trim($data); // helps with broken sites that add extra white space.
			file_put_contents($this->feedfilename, $data);

// Process all new pilots and corps
// First check any are present.
			if(strpos($data,"Corp: "))
			{
				$pos = 0;
				$namelist = array();
				$newcorp = new Corporation();
				$newall = new Alliance();
				$newall->add("None");
// Corps
				while($pos = strpos($data, 'Corp: ', $pos + 1))
				{
					$endpos = strpos($data, "\n", $pos);
					$name = substr($data, $pos + 6, $endpos - ($pos + 6));
					$name = trim(str_replace("\r", '', $name));
					$name = preg_replace("/ \(laid the final blow\)/", "", $name);
					if(strpos($name, '/')) continue;
					$namelist[slashfix($name)] = $newall;
				}
				Fetcher::addCorpNames($namelist);

				$pos = 0;
				$namelist = array();
// Corps will repeat a lot so store the ones we find for reuse.
				$corps = array();
// Victims
				while($pos = strpos($data, 'Victim: ', $pos))
				{
					$endpos = strpos($data, "\n", $pos);
					$name = substr($data, $pos + 8, $endpos - ($pos + 8));
					$name = trim(str_replace("\r", '', $name));

					$pos = strpos($data, "Corp: ",$pos);
					if(!$pos) break;
					if(strpos($name, "/")) continue;
					$endpos = strpos($data, "\n", $pos);
					$cname = substr($data, $pos + 6, $endpos - ($pos + 6));
					$cname = trim(str_replace("\r", "", $cname));
					if(!isset($corps[$cname]))
					{
						$newcorp = new Corporation();
						$newcorp->lookup($cname);
						$corps[$cname] = $newcorp;
					}
					$namelist[slashfix($name)] = $corps[$cname];
				}
// Involved parties
				$pos = 0;
				while($pos = strpos($data, 'Name: ', $pos))
				{
					$endpos = strpos($data, "\n", $pos);
					$name = substr($data, $pos + 6, $endpos - ($pos + 6));
					$name = trim(str_replace("\r", '', $name));
					$name = preg_replace("/ \(laid the final blow\)/", "", $name);

					$pos = strpos($data, "Corp: ",$pos);
					if(!$pos) break;
// Skip NPC names with a '/' in them.
					if(strpos($name, "/")) continue;
					$endpos = strpos($data, "\n", $pos);
					$cname = substr($data, $pos + 6, $endpos - ($pos + 6));
					$cname = trim(str_replace("\r", "", $cname));
					if(!isset($corps[$cname]))
					{
						$newcorp = new Corporation();
						$newcorp->lookup($cname);
						$corps[$cname] = $newcorp;
					}
					$namelist[slashfix($name)] = $corps[$cname];
				}
				Fetcher::addPilotNames($namelist);
				unset ($corps);
				unset ($namelist);
			}
		}
		else
		{
			$data = file_get_contents($this->feedfilename);
			if(file_exists($this->feedfilename.'.stat'))
			{
				$this->tracklast_ = intval(file_get_contents($this->feedfilename.'.stat'));
				$this->tracktime_ = 0;
			}
			elseif(file_exists($this->feedfilename.'.tstat'))
			{
				$this->tracklast_ = 0;
				$this->tracktime_ = intval(file_get_contents($this->feedfilename.'.tstat'));
			}
			else
			{
				$this->tracklast_ = 0;
				$this->tracktime_ = 0;
			}
		}
		if (!xml_parse($xml_parser, $data, true))
		{
			unlink($this->feedfilename);
			@unlink($this->feedfilename.'.stat');
			@unlink($this->feedfilename.'.tstat');
			return "<i>Error getting XML data from ".$fetchurl."</i><br><br>\n";
		}

		xml_parser_free($xml_parser);
		unlink($this->feedfilename);
		@unlink($this->feedfilename.'.stat');
		@unlink($this->feedfilename.'.tstat');

		if (config::get('fetch_verbose') )
		{
			$this->html .= "<div class=block-header2>".$this->killsAdded." kills added and ".$this->killsSkipped." kills skipped from feed: ".$url."<br>".$str." <br></div>\n";
		}
		else
		{
			$this->html .= "<div class=block-header2>".$this->killsAdded." kills added and ".$this->killsSkipped." kills skipped from feed: ".$url." <br><br></div>\n";
		}

		return $this->html;
	}
//! XML start of element parser.
	function startElement($parser, $name, $attrs)
	{
//	if ($this->insideitem)
		$this->tag = $name;
//else
		if ($name == "ITEM")
		{
			$this->insideitem = true;
			$this->description = '';
			$this->title = "";
			$this->link = "";
		}
	}

//! XML end of element parser.
	function endElement($parser, $name)
	{
//global $this->html;

		if ($name == "ITEM")
		{
			if ( $this->description !="")
			{
				$this->description = trim(str_replace("\r", '', $this->description));
				$year = substr($this->description, 0, 4);
				$month = substr($this->description, 5, 2);
				$day = substr($this->description, 8, 2);
				$killstamp = mktime(0, 0, 0, $month, $day, $year);
// Not working as intended so removing for now.
				if ( 0 && $this->idordered && $this->tracklast_ > intval($this->title))
				{
					$this->html .= "Killmail ID ".intval($this->title)." already processed <br>";
				}
				elseif (0 && !$this->idordered && $this->tracktime_ > $killstamp)
				{
					$this->html .= "Killmail date ".intval($this->title)." already processed. <br>";
				}
				else
				{
//Check age of mail
					if(config::get('filter_apply')
						&& $killstamp < config::get('filter_date'))
					{
						$killid = -4;
					}
					elseif($this->trust <= $this->accepttrust && $this->apiID = intval($this->apiID))
					{
						$qry = DBFactory::getDBQuery();
						$qry->execute("SELECT 1 FROM kb3_kills WHERE kll_external_id = ".$this->apiID);
						if(!$qry->recordCount())
						{
							// Add external id when known and trusted.
							// For compatibility with old boards accept 0 trust API validated kills
							// but don't store the ID
							if($this->trust > 0)
							{
								$this->trust ++;
								$parser = new Parser( $this->description, $this->apiID);
								$parser->setTrust($this->trust);
							}
							else
							{
								$parser = new Parser( $this->description);
							}
							$killid = $parser->parse( true );
						}
						else $killid = -3;
					}
					elseif($this->hash != '' && !$this->apikills)
					{
						$qry = DBFactory::getDBQuery();
						$qry->execute("SELECT kll_trust FROM kb3_mails WHERE kll_timestamp = '".
							$qry->escape($this->time)."' AND kll_hash = 0x".
							$qry->escape($this->hash)."");
						if(!$qry->recordCount())
						{
							$parser = new Parser( $this->description );
							$killid = $parser->parse( true );
						}
						else
						{
							$row = $qry->getRow();
							if($row['kll_trust'] < 0) $killid = -5;
							else $killid = -1;
						}
					}
					elseif(!$this->apikills)
					{
						$parser = new Parser( $this->description );
						$killid = $parser->parse( true );
					}
					if ( $killid <= 0 )
					{
						if ( $killid == 0 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." is malformed. ".$this->uurl." Kill ID = ".$this->title." <br>\n";
						if ( $killid == -1 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." already posted <a href=\"?a=kill_detail&amp;kll_id=".$parser->getDupeID()."\">here</a>.<br>\n";
						if ( $killid == -2 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." is not related to ".KB_TITLE.".<br>\n";
						if ( $killid == -3 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." already posted <a href=\"?a=kill_detail&amp;kll_external_id=".$this->apiID."\">here</a>.<br>\n";
						if ( $killid == -4 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." too old to post with current settings.<br>\n";
						if ( $killid == -5 && config::get('fetch_verbose') )
							$this->html .= "Killmail ".intval($this->title)." has already been deleted.<br>\n";
						$this->killsSkipped++;
					}
					else
					{
						if(strpos($this->uurl, '?')) $logurl = substr($this->uurl,0,strpos($this->uurl, '?')).'?a=kill_detail&kll_id='.intval($this->title);
						else $logurl = uurl.'?a=kill_detail&kll_id='.intval($this->title);
						logger::logKill($killid, $logurl);

						$this->html .= "Killmail ".intval($this->title)." successfully posted <a href=\"?a=kill_detail&kll_id=".$killid."\">here</a>.<br>";

						if (config::get('fetch_comment'))
						{
							$comments = new Comments($killid);
							$comments->addComment("Feed Syndication ", config::get('fetch_comment')." mail fetched from: ".$this->uurl);
						}
						$this->killsAdded++;
					}
					if( $this->idordered && intval($this->title) > 0)
					{
						$this->tracklast_ = intval($this->title);
						file_put_contents($this->feedfilename.'.stat', strval(intval($this->title)));
					}
					elseif( !$this->idordered && $killstamp > 0)
					{
						$this->tracktime_ = $killstamp;
						file_put_contents($this->feedfilename.'.tstat', strval($killstamp));
					}
				}
			}
			if($this->title && intval($this->title) > $this->lastkllid_) $this->lastkllid_ = intval($this->title);
			$this->title = "";
			$this->description = "";
			$this->link = "";
			$this->insideitem = false;
			$this->apiID = "";
			$this->hash = "";
			$this->time = "";
			$this->trust = "";
		}
	}
//! XML character data parser.
	function characterData($parser, $data)
	{
		if ($this->insideitem)
		{
			switch ($this->tag)
			{
				case "TITLE":
					$this->title .= $data;
					break;
				case "DESCRIPTION":
					$this->description .= $data;
					break;
				case "LINK":
					$this->link .= $data;
					break;
				case "APIID":
					$this->apiID .= $data;
				case "TIME":
					$this->time .= $data;
				case "HASH":
					$this->hash .= $data;
				case "TRUST":
					$this->trust .= $data;
			}
		}
		elseif($this->tag=="FINALKILL")
		{
			if(!($this->finalkllid_ > intval($data))) $this->finalkllid_ = intval($data);
		}
		elseif($this->tag=="COMBINED")
		{
			$this->combined_ = true;
		}
	}
//! Add an array of pilots to be checked.

//! \param $names array of corp names indexed by pilot name.
	function addPilotNames($names)
	{
		$qry = DBFactory::getDBQuery(true);
		$checklist = array();
		foreach($names as $pilot =>$corp)
		{
			$qry->execute("SELECT 1 FROM kb3_pilots WHERE plt_name = '".$pilot."'");
			if(!$qry->recordCount()) $checklist[] = $pilot;
		}
		if(!count($checklist)) return;
		$position = 0;
		$myNames = array();
		$myID = new API_NametoID();
		while($position < count($checklist))
		{
			$namestring = str_replace(" ", "%20", implode(',',array_slice($checklist,$position, 500, true)));
			$namestring = str_replace("\'", "'", $namestring);
			$position +=500;
			$myID->setNames($namestring);
			$myID->fetchXML();
			$tempNames = $myID->getNameData();
			$myID->clear();
			if(!is_array($tempNames)) continue;
			$myNames = array_merge($myNames, $tempNames);
		}
		$newpilot = new Pilot();
//$sql = '';
		if(!is_array($myNames)) die("Name fetch error : ".$myNames);
		foreach($myNames as $name)
		{
			if(isset($names[slashfix($name['name'])]))
			{
				$newpilot->add(slashfix($name['name']), $names[slashfix($name['name'])], '0000-00-00', $name['characterID']);
// Adding all at once is faster but skips checks for name/id clashes.
//if($sql == '') $sql = "INSERT INTO kb3_pilots (plt_name, plt_crp_id, plt_externalid, plt_updated) values ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().', '.$name['characterID'].", '0000-00-00')";
//else $sql .= ", ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().', '.$name['characterID'].", '0000-00-00')";
			}
		}
		if($sql) $qry->execute($sql);
	}
//! Add an array of pilots to be checked.

//! \param $names array of corp names indexed by pilot name.
	function addCorpNames($names)
	{
		$qry = DBFactory::getDBQuery(true);
		$checklist = array();
		foreach($names as $corp =>$all)
		{
			$qry->execute("SELECT 1 FROM kb3_corps WHERE crp_name = '".$corp."'");
			if(!$qry->recordCount()) $checklist[] = $corp;
		}
		if(!count($checklist)) return;
		$position = 0;
		$myNames = array();
		while($position < count($checklist))
		{
			$namestring = str_replace(" ", "%20", implode(',',array_slice($checklist,$position, 500, true)));
			$namestring = str_replace("\'", "'", $namestring);
			$position +=500;
			$myID = new API_NametoID();
			$myID->setNames($namestring);
			$myID->fetchXML();
			$tempNames = $myID->getNameData();
			if(!is_array($tempNames)) continue;
			$myNames = array_merge($myNames, $tempNames);
		}
		$newcorp = new Corporation();
//$sql = '';
		foreach($myNames as $name)
		{
			if(isset($names[slashfix($name['name'])]) && $name['characterID'])
			{
				$newcorp->add(slashfix($name['name']), $names[slashfix($name['name'])], '0000-00-00', $name['characterID']);
// Adding all at once is faster but skips checks for name/id clashes.
//if($sql == '') $sql = "INSERT INTO kb3_corps (crp_name, crp_all_id, crp_updated, crp_external_id) VALUES ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().", '0000-00-00', ".$name['characterID'].")";
//else $sql .= ",\n ('".slashfix($name['name'])."', ".$names[slashfix($name['name'])]->getID().", '0000-00-00', ".$name['characterID'].")";
			}
		}
		if($sql) $qry->execute($sql);
	}
}