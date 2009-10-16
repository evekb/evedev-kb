<?php
require_once("class.alliance.php");
require_once("class.corp.php");
require_once("class.pilot.php");
require_once("class.kill.php");
require_once("class.item.php");
require_once("class.parser.translate.php");

class Parser
{
    function uchr ($codes) { //converts characterset code-pages to ascii-compatible types
        if (is_scalar($codes)) $codes= func_get_args();
        $str= '';
        foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
        return $str;
    }

    function Parser($killmail)
    {
		if( phpversion() >= '5.0.0' ) { //lousy but necessary
            $canUnicode = true; //if this is unset, Russian will not parse, but English will atleast.
        }
        $this->error_ = array();
        $this->killmail_ = trim(str_replace("\r", '', $killmail));
        $this->returnmail = false;

        $pos = 0;

        if($canUnicode) {
            $russian = $this->uchr(1042).$this->uchr(1086).$this->uchr(1074).$this->uchr(1083).$this->uchr(1077).$this->uchr(1095)
                    .$this->uchr(1077).$this->uchr(1085).$this->uchr(1086).' '.$this->uchr(1087).$this->uchr(1080).$this->uchr(1088)
                    .$this->uchr(1072).$this->uchr(1090).$this->uchr(1086).$this->uchr(1074).':'; //involved party
        }

        //Fraktion added for mails that originate from griefwatch / battle-clinic that do nothing with this info
        if (strpos($this->killmail_, 'Beteiligte Parteien:') || (strpos($this->killmail_, 'Fraktion:')))
        {
            $this->preparse('german');
        }
        elseif (strpos($this->killmail_, $russian) && $canUnicode)
        {
            $this->preparse('russian');

        }
        elseif (strpos($this->killmail_, 'System Security Level:'))
        {
            // this converts the killmail internally from pre-rmr to kali format
            $this->preparse('prermr');
        }

        //get the mail's timestamp - if older than QR, then preparse for scrambler translation
        $timestamp = substr($this->killmail_, 0, 16);
        $timestamp = str_replace('.', '-', $timestamp);

        $apoc_release = '2009-03-10 03:00:00';

        if(strtotime($timestamp) < strtotime($apoc_release))
        {
            $this->preparse('apoc');
        }

        $apoc15_release = '2009-08-20 12:00:00';

        if(strtotime($timestamp) < strtotime($apoc15_release))
        {
            $this->preparse('apoc15');
        }

        $qr_release = '2008-11-11 00:00:00';

        if(strtotime($timestamp) < strtotime($qr_release))
        {
            $this->preparse('preqr');
        }

        if (strpos($this->killmail_, '**** Truncated - mail is too large ****') > 0)
        {
            $this->killmail_ = str_replace('**** Truncated - mail is too large ****', '', $this->killmail_);
        }

        // Parser fix, since some killmails don't have a final blow, they would break the KB.
        //On mails without final blow info, the first name on the list becomes the final blow holder
        if (strpos($this->killmail_, 'laid the final blow') < 1)
        {
            $this->needs_final_blow_ = 1;
        }
    }

    function parse($checkauth = true)
    {
        //trim out any multiple spaces that may exist
        $this->killmail_ = preg_replace('/ +/', ' ', $this->killmail_);

        // header section
        $involvedpos = strpos($this->killmail_, "Involved parties:");
        if($involvedpos == 0)
        {
            $this->error("Mail lacks Involved parties header.");
            return 0;
        }

        $header = substr($this->killmail_, 0, $involvedpos);
        $timestamp = substr($header, 0, 16);

        $victim = explode("\n", trim(substr($this->killmail_, 0, $involvedpos)));
        $upper_limit =  count($victim);

        $victimname = "Unknown"; //lovely default values
        $factionname = "None";
        $alliancename = "None";
        $corpname = "Unknown";
        $shipname = "Unknown";
        $systemname = "Unknown";
        $systemsec = "0.0";
        $dmgtaken = '0';
        $this->dmgtaken = '0';
        $pos = 0;
        $moon = "";

        for($counter = 0; $counter <= $upper_limit; $counter++)
        {
            if(preg_match("/Victim: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                    $victimname = $matches[1];
            }
            elseif (preg_match("/Corp: (.*)/", $victim[$counter], $matches))
            {
                 if($matches[1])
                    $corpname = $matches[1];
            }
            elseif (preg_match("/Alliance: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                    $alliancename = $matches[1];
            }
            elseif (preg_match("/Faction: (.*)/", $victim[$counter], $matches))
            {
                if(strlen($matches[1]) > 5) //catches faction mails from -A-
                    $factionname = $matches[1];
                else $factionname = "None";
            }
            elseif (preg_match("/Destroyed: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                    $shipname = $matches[1];
            }
            elseif (preg_match("/System: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
				{   //bad assumption here - moon has to come before security.
                    $systemname = $matches[1];
                    if ((strcmp($moon, 'Unknown') == 0) && ($pos == 1))
            		{
               			$moon = $matches[1];
               			$victimname = $matches[1];
                    }
				}
			}
            elseif (preg_match("/Security: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                    $systemsec = $matches[1];
            }
            elseif (preg_match("/Damage Taken: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                {
                    $dmgtaken = $matches[1];
                    $this->dmgtaken = $dmgtaken;
                }
            }
            elseif (preg_match("/Moon: (.*)/", $victim[$counter], $matches))
            {
                if($matches[1])
                {
                    $moon = $matches[1];
                    $victimname = $matches[1];
                    $pos = 1;
                }
                else
                { //if the system is valid, it will pick this up, provided it features after
                  //the moon is listed - which is unlikely unless the mail format
                  //drastically changes... again :)
                    $moon = 'Unknown';
                    $victimname = 'Unknown';
                    $pos = 1;
                }
            }
        }

		//faction warfare stuff
		if (strcasecmp($alliancename, 'None') == 0)
		{
			$alliancename = $factionname;
		}

        //report the errors for the things that make sense.
        //we need pilot names, corp names, ship types, and the system to be sure
        //the rest aren't required but for completeness, you'd want them in :)
        if (strcmp($victimname, 'Unknown') == 0)
        {
            $this->error('Victim has no name.');
            unset($victimname); //we unset the variable so that it fails the next check
        }

        if (strcmp($corpname, 'Unknown') == 0)
        {
            $this->error('Victim has no corp.');
            unset($corpname);
        }

        if (strcmp($shipname, 'Unknown') == 0)
        {
            $this->error('Victim has no ship type.');
            unset($shipname);
        }

        if (strcmp($systemname, 'Unknown') == 0)
        {
            $this->error('Killmail lacks solar system information.');
            unset($systemname);
        }

        if ($pos == 1)
        {
                $victimname = $moon;
        }

        if (!isset($timestamp) ||
                !isset($factionname) ||
                !isset($alliancename) ||
                !isset($corpname) ||
                !isset($victimname) ||
                !isset($shipname) ||
                !isset($systemname) ||
                !isset($systemsec))
            return 0;

        if ($checkauth)
            $authorized = false;
        else $authorized = true;

        // populate/update database
        $alliance = new Alliance();
        $alliance->add($alliancename);
        $corp = new Corporation();
        $corp->add($corpname, $alliance, $timestamp);
        $victim = new Pilot();
        $victim->add($victimname, $corp, $timestamp);
        $system = new SolarSystem();
        $system->lookup($systemname);
        if (!$system->getID())
        {
            $this->error('System not found.', $systemname);
        }
        $ship = new Ship();
        $ship->lookup($shipname);
        if (!$ship->getID())
        {
            $this->error('Ship not found.', $shipname);
        }
        $kill = new Kill();
        $kill->setTimeStamp($timestamp);
        $kill->setVictimID($victim->getID());
        $kill->setVictimCorpID($corp->getID());
        $kill->setVictimAllianceID($alliance->getID());
		$kill->setVictimShip($ship);
        $kill->setSolarSystem($system);
        if ($dmgtaken)
        {
            $kill->set('dmgtaken', $dmgtaken);
        }

        if (ALLIANCE_ID != 0 && $alliance->getID() == ALLIANCE_ID)
        {
            $authorized = true;
        }
        elseif (CORP_ID != 0)
        {
            $corps = explode(",", CORP_ID);
            foreach($corps as $checkcorp)
            {
                if ($corp->getID() == $checkcorp)
                    $authorized = true;
            }
        }

        // involved parties section
        $end = strpos($this->killmail_, "Destroyed items:");
        if ($end == 0)
        {
            $end = strpos($this->killmail_, "Dropped items:");
            if ($end == 0)
            { //try to parse to the end of the mail in the event sections are missing
                $end = strlen($this->killmail_);
            }
        }
        $involved = explode("\n", trim(substr($this->killmail_, strpos($this->killmail_, "Involved parties:") + 17, $end - (strpos($this->killmail_, "Involved parties:") + 17))));

        $ipilot_count = 0; //allows us to be a bit more specific when errors strike
        $i = 0;

        $order = 0;
        while ($i < count($involved))
        {
            $iparts = count($involved);
            $finalblow = 0;

            while($i < $iparts) {
                $ipilot_count++;

                $ipname = "Unknown";
                $ianame = "None";
                $ifname = "None";
                $icname = "None";
                $isname = "Unknown";
                $iwname = "Unknown";
                $idmgdone = '0';
                $secstatus = "0.0";


                while($involved[$i] == '')
                { //compensates for multiple blank lines between involved parties
                    $i++;
                    if($i > count($involved))
                    {
                        $this->error("Involved parties section prematurely ends.");
                        return 0;

                    }
                }

                for($counter = $i; $counter <= $iparts; $counter++)
                {
                    if(preg_match("/Name: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                        {
                            if(stristr($involved[$counter], '/'))
                            {
                                $slash = strpos($involved[$counter], '/');
                                $name = trim(substr($involved[$counter], 5, $slash-5));
                                $corporation = trim(substr($involved[$counter], $slash+1, strlen($involved[$counter])- $slash+1));

                                //now if the corp bit has final blow info, note it
                                preg_match("/(.*) \\(laid the final blow\\)/", $corporation, $matched);
                                if($matched[1])
                                {
                                    $finalblow = 1;
                                    $iwname = $name;
                                    $end = strpos($corporation, '(') -1;
                                    $corporation = substr($corporation, 0, $end);
                                }
                                else
                                {
                                    $finalblow = 0;
                                    $iwname = $name;
                                }
                                //alliance lookup for warp disruptors - normal NPCs aren't to be bundled in
                                $crp = new Corporation();
                                $crp->lookup($corporation);
                                if($crp->getID() > 0 && ( stristr($name, ' warp ') || stristr($name, ' control ')))
                                {
                                    $al = $crp->getAlliance();
                                    $ianame = $al->getName();
                                }

                                $ipname = $name;
                                $icname = $corporation;
                            }
                            else
                            {
                                $ipname = $matches[1];
                                preg_match("/(.*) \\(laid the final blow\\)/", $ipname, $matches);
                                if ($matches[1])
                                {
                                    $ipname = $matches[1];
                                    $finalblow = 1;
                                }
                                else $finalblow = 0;
                            }
                        }
                    }
                    else if(preg_match("/Alliance: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $ianame = $matches[1];
                    }
                    else if(preg_match("/Faction: (.*)/", $involved[$counter], $matches))
                    {
                        if(strlen($matches[1]) > 5) //catches faction mails from -A-
                            $ifname = $matches[1];
                        else $ifname = "NONE";
                    }
                    else if(preg_match("/Corp: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $icname = $matches[1];
                    }
                    else if(preg_match("/Ship: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $isname = $matches[1];
                    }
                    else if(preg_match("/Weapon: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $iwname = $matches[1];
                    }
                    else if(preg_match("/Security: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $secstatus = $matches[1];
                    }
                    else if(preg_match("/Damage Done: (.*)/", $involved[$counter], $matches))
                    {
                        if($matches[1])
                            $idmgdone = $matches[1];
                    }
                    else if($involved[$counter] == '')
                    { //allows us to process the involved party. This is the empty line after the
                      //involved party section
                        $counter++;
                        $i = $counter;
                        break;
                    }
                    else { //skip over this entry, it could read anything, we don't care. Handy if/when
                           //new mail fields get added and we aren't handling them yet.
                        $counter++;
                        $i = $counter;
                    }

                    if ($this->needs_final_blow_)
                    {
                        $finalblow = 1;
                        $this->needs_final_blow_ = 0;
                    }
                }

				// Faction Warfare stuff
				if (strcasecmp($ianame, "None") == 0)
				{
					$ianame = $ifname;
				}
				// end faction warfare stuff

                $ialliance = new Alliance();
                $ialliance->add($ianame);

                $icorp = new Corporation();
                if (strcmp($icname, 'None') == 0)
                {
                    $this->error('Involved party has no corp. (Party No. '.$ipilot_count.')');
                }
                else
                {   //don't add corp, because pilots have to be in corps.
                    $icorp->add($icname, $ialliance, $kill->getTimeStamp());
                }

                $ipilot = new Pilot();

                if (strcmp($ipname, 'Unknown') == 0)
                {
                    if (preg_match("/Mobile/", $iwname) || preg_match("/Control Tower/", $iwname))
                    { //for involved parties parsed that lack a pilot, but are actually POS or mobile warp disruptors
                        $ipname = $iwname;
                        $ipilot->add($ipname, $icorp, $timestamp);
                    }
                    else $this->error('Involved party has no name. (Party No. '.$ipilot_count.')');
                }
                else
                {
                    //don't add pilot if the pilot's unknown or dud
                    $ipilot->add($ipname, $icorp, $timestamp);
                }

                $iship = new Ship();
                $iship->lookup($isname);
                if (!$iship->getID())
                {
                    $this->error('Ship not found.', $isname);
                }

                $iweapon = new Item();
                $iweapon->lookup($iwname);
                if (strcmp($iwname, 'Unknown') == 0)
                {
                    $this->error('No weapon found for pilot "'.$ipname .'"');
                } elseif (!$iweapon->getID())
                {
                    $this->error('Weapon not found.', $iwname);
                }

                if (ALLIANCE_ID != 0 && $ialliance->getID() == ALLIANCE_ID)
                {
                    $authorized = true;
                }
                elseif (CORP_ID != 0)
                {
                    $corps = explode(",", CORP_ID);
                    foreach($corps as $corp)
                    {
                        if ($icorp->getID() == $corp)
                            $authorized = true;
                    }
                }
                if (!$authorized)
                {
                    if ($string = config::get('post_permission'))
                    {
                        if ($string == 'all')
                        {
                            $authorized = true;
                        }
                        else
                        {
                            $tmp = explode(',', $string);
                            foreach ($tmp as $item)
                            {
                                if (!$item)
                                {
                                    continue;
                                }
                                $typ = substr($item, 0, 1);
                                $id = substr($item, 1);
                                if ($typ == 'a')
                                {
                                    if ($ialliance->getID() == $id || $kill->getVictimAllianceID() == $id)
                                    {
                                        $authorized = true;
                                        break;
                                    }
                                }
                                elseif ($typ == 'c')
                                {
                                    if ($icorp->getID() == $id || $kill->getVictimCorpID() == $id)
                                    {
                                        $authorized = true;
                                        break;
                                    }
                                }
                                elseif ($typ == 'p')
                                {
                                    if ($ipilot->getID() == $id || $kill->getVictimID() == $id)
                                    {
                                        $authorized = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                $iparty = new InvolvedParty($ipilot->getID(), $icorp->getID(),
                    $ialliance->getID(), $secstatus, $iship, $iweapon);
                if ($dmgtaken)
                {
                    $iparty->dmgdone_ = $idmgdone;
                }
                $kill->addInvolvedParty($iparty);

                if ($finalblow == 1)
                {
                    $kill->setFBPilotID($ipilot->getID());
                    $kill->setFBCorpID($icorp->getID());
                    $kill->setFBAllianceID($ialliance->getID());
					if($id = $kill->getDupe(true))
					{
						$this->dupeid_ = $id;
						return -1;
					}
                }
            }
        }

        // destroyed items section
        $destroyedpos = strpos($this->killmail_, "Destroyed items:");

        if ($destroyedpos)
        {
            $endpos = strlen($this->killmail_) - $destroyedpos + 16;
            $pos = strpos($this->killmail_, "Dropped items:");
            if ($pos === false)
            {
                $pos = strlen($this->killmail_);
            }
            $endpos = $pos - $destroyedpos - 16;

            $destroyed = explode("\n", trim(substr($this->killmail_, $destroyedpos + 16, $endpos)));
            #var_dump($destroyed); exit;
            $destroyed_items = $this->scanForItems($destroyed);
            foreach ($destroyed_items as $item)
            {
                $ditem = new DestroyedItem($item['item'], $item['quantity'], $item['location']);
                $kill->addDestroyedItem($ditem);
            }
        }

        $startpos = strpos($this->killmail_, "Dropped items:");
        if ($startpos)
        {
            $endpos = strlen($this->killmail_) - $startpos + 14;

            $dropped = explode("\n", trim(substr($this->killmail_, $startpos + 14, $endpos)));
            #var_dump($dropped); exit;

            $dropped_items = $this->scanForItems($dropped);
            foreach ($dropped_items as $item)
            {
                $ditem = new DroppedItem($item['item'], $item['quantity'], $item['location']);
                $kill->addDroppedItem($ditem);
            }
        }

        if (!$authorized)
        {
            return -2;
        }
        if ($this->getError())
        {
            return 0;
        }

        if ($this->returnmail)
        {
            return $kill;
        }
        $id = $kill->add();
        if ($id == -1)
        {
            $this->dupeid_ = $kill->dupeid_;
        }
        elseif ($id == -2) {
            $this->error("An error has occurred. Please try again later.");
            $id = 0;
        }
        return $id;
    }

    function scanForItems($destroyed)
    {
        $i = 0;
        $num = count($destroyed);
        while ($i < $num)
        {
            $destroyed[$i] = trim($destroyed[$i]);

            $itemname = substr($destroyed[$i], 0, strlen($destroyed[$i]));
            //API mod will return null when it can't lookup an item, so filter these
            if($itemname == '(Cargo)' || (strpos($itemname, ', Qty:') === 0))
            {
                $this->error('Item name missing, yet item has quantity. If you have used the API mod for this kill, you are missing items from your dabase.',$itemname);
                $i++;
                continue; //continue to get rest of the mail's possible errors
            }

            if ($destroyed[$i] == "")
            {
                $i++;
                continue;
            }

            if ($destroyed[$i] == "Empty.")
            {
                $container = false;
                $i++;
                continue;
            }

            $qtypos = 0;
            $locpos = 0;
            $itemname = "";
            $quantity = "";
            $location = "";

            $qtypos = strpos($destroyed[$i], ", Qty: ");
            $locpos = strrpos($destroyed[$i], "(");

            if ($container && $locpos != false)
            {
                $container = false;
            }
            if (strpos($destroyed[$i], "Container"))
            {
                $container = true;
            }
            if ($qtypos <= 0 && !$locpos)
            {
                $itemlen = strlen($destroyed[$i]);
                if ($container) $location = "Cargo";
            }
            if ($qtypos > 0 && !$locpos)
            {
                $itemlen = $qtypos;
                $qtylen = strlen($destroyed[$i]) - $qtypos;
                if ($container) $location = "Cargo";
            }
            if ($locpos > 0 && $qtypos <= 0)
            {
                $itemlen = $locpos - 1;
                $qtylen = 0;
                $loclen = strlen($destroyed[$i]) - $locpos - 2;
                if (!$locpos) $container = false;
            }
            if ($locpos > 0 && $qtypos > 0)
            {
                $itemlen = $qtypos;
                $qtylen = $locpos - $qtypos - 7;
                $loclen = strlen($destroyed[$i]) - $locpos - 2;
                if (!$locpos) $container = false;
            }

            $itemname = substr($destroyed[$i], 0, $itemlen);
            if ($qtypos) $quantity = substr($destroyed[$i], $qtypos + 6, $qtylen);
            if ($locpos) $location = substr($destroyed[$i], $locpos + 1, $loclen);

            if ($quantity == "")
            {
                $quantity = 1;
            }

            $item = new Item();
            $item->lookup(trim($itemname));
            if (!$item->getID())
            {
                $this->error('Item not found.', trim($itemname));
            }
            if ($location == 'In Container')
            {
                $location = 'Cargo';
            }

            $items[] = array('item' => $item, 'quantity' => $quantity, 'location' => $location);
            $i++;
        }

        return $items;
    }

    function error($message, $debugtext = null)
    {
        $this->error_[] = array($message, $debugtext);
    }

    function getError()
    {
        if (count($this->error_))
        {
            return $this->error_;
        }
        return false;
    }

    function preparse($set)
    {
        $translate = new Translate($set);
        $this->killmail_ = $translate->getTranslation($this->killmail_);
    }
}
//Currently maintained by FriedRoadKill