<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

/*
 * Based on work by unknown, Sapyx, Rostik, Tribalize, Ben Thomas, KE and Kovell
 */

class pKillDetail extends pageAssembly
{
//! Construct the Pilot Details object.

/** Set up the basic variables of the class and add the functions to the
 *  build queue.
 */
	function __construct()
	{
		parent::__construct();
		if(isset($_GET['kll_id'])) $this->kll_id = intval($_GET['kll_id']);
		else $this->kll_id = 0;
		if(isset($_GET['kll_external_id'])) $this->kll_external_id = intval($_GET['kll_external_id']);
		elseif(isset($_GET['kll_ext_id'])) $this->kll_external_id = intval($_GET['kll_ext_id']);
		else $this->kll_external_id = 0;
		if(isset($_GET['nolimit'])) $this->nolimit = true;
		else $this->nolimit = false;

		$this->menuOptions = array();

		$this->queue("start");
		$this->queue("top");
		$this->queue("victim");
		$this->queue("involvedSummary");
		$this->queue("involved");
		$this->queue("comments");
		$this->queue("source");
		$this->queue("middle");
		$this->queue("victimShip");
		$this->queue("fitting");
		$this->queue("itemsLost");
		$this->queue("bottom");

	}

	//! Reset the assembly object to prepare for creating the context.
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
		$this->queue("points");
		$this->queue("damageBox");
		$this->queue("map");
	}

	//! Start constructing the page.

	/*! Prepare all the shared variables such as dates and check alliance ID.
	 *
	 */
	function start()
	{
		$this->page = new Page('Kill details');

		if (!$this->kll_id && !$this->kll_external_id)
		{
			$html = "No kill id specified.";
			$this->page->setContent($html);
			$this->page->generate($html);
			exit;
		}

		if($this->kll_id)
		{
			$this->kill = new Kill($this->kll_id);
		}
		else
		{
			$this->kill = new Kill($this->kll_external_id, true);
			$this->kll_id = $this->kill->getID();
		}
		$this->kill->setDetailedInvolved();

		if (!$this->kill->exists())
		{
			$html="That kill doesn't exist.";
			$this->page->setContent($html);
			$this->page->generate($html);
			exit;
		}
		$this->system = $this->kill->getSystem();
		$this->finalblow = false;

		$this->commenthtml = '';
		// Check for posted comments.
		// If a comment is being posted then we won't exit this block.
		if(isset($_POST['comment']) && config::get('comments'))
		{

			$comments = new Comments($this->kll_id);
			$pw = false;
			if (!config::get('comments_pw') || $this->page->isAdmin())
			{
				$pw = true;
			}
			if ($pw || crypt($_POST['password'],config::get("comment_password")) == config::get("comment_password"))
			{
				if ($_POST['comment'] == '')
				{
					$this->commenthtml = 'Error: The silent type, hey? Good for you, bad for a comment.';
				}
				else
				{
					$comment = $_POST['comment'];
					$name = $_POST['name'];
					if ($name == null)
					{
						$name = 'Anonymous';
					}
					$comments->addComment($name, $comment);
					//Remove cached file.
					if(config::get('cache_enabled')) cache::deleteCache();
					//Redirect to avoid refresh reposting comments.
					header('Location: '.$_SERVER['REQUEST_URI'],TRUE,303);
					die();
				}
			}
			else
			{
			// Password is wrong
				$this->commenthtml = 'Error: Wrong Password';
			}
		}
		// Check admin update options.
		if ($this->page->isAdmin())
		{
			$this->updatePrices();
			$this->fixSlots();
		}

		global $smarty;
		if(!file_exists('img/panel/'.config::get('fp_theme').'.png')) config::set('fp_theme','tyrannis');
		$smarty->assign('panel_colour', config::get('fp_theme'));
		$smarty->assign('showiskd', config::get('kd_showiskd'));

		$this->involvedSetup();
		$this->fittingSetup();
	}
	function fittingSetup()
	{
		// ship fitting
		if (count($this->kill->destroyeditems_) > 0)
		{
			$this->dest_array=array();

			foreach ($this->kill->destroyeditems_ as $destroyed)
			{
				$item = $destroyed->getItem();
				$i_qty=$destroyed->getQuantity();

				if (config::get('item_values'))
				{
					$value=$destroyed->getValue();
					$this->TotalValue+=$value * $i_qty;
					$formatted=$destroyed->getFormattedValue();

					if(strpos($item->getName(), 'Blueprint') !== false) $this->bp_value += $value * $i_qty;
				}

				$i_name     =$item->getName();
				$i_location =$destroyed->getLocationID();
				$i_id       =$item->getID();
				$i_usedgroup=$item->get_used_launcher_group();

				$this->dest_array[$i_location][]=array
					(
					'Icon'        => $item->getIcon(32),
					'Name'        => $i_name,
					'Quantity'    => $i_qty,
					'Value'       => $formatted,
					'single_unit' => $value,
					'itemID'      => $i_id,
					'slotID'      => $i_location
				);

				//Fitting, KE - add destroyed items to an array of all fitted items.
				if (($i_location != 4) && ($i_location != 5) && ($i_location != 6))
				{
					if (($i_usedgroup != 0))
					{
						if ($i_location == 1)
						{
							$i_ammo=$item->get_ammo_size($i_name);

							if ($i_usedgroup == 481)
							{
								$i_ammo=0;
							}
						}
						else
						{
							$i_ammo=0;
						}

						$this->ammo_array[$i_location][]=array
							(
							'Name'        => $i_name,
							'Icon'        => $item->getIcon(24),
							'itemID'      => $i_id,
							'usedgroupID' => $i_usedgroup,
							'size'        => $i_ammo
						);
					}
					else
					{
						for ($count=0; $count < $i_qty; $count++)
						{
							if ($i_location == 1)
							{
								$i_charge=$item->get_used_charge_size();
							}
							else
							{
								$i_charge=0;
							}

							$this->fitting_array[$i_location][]=array
								(
								'Name'       => $i_name,
								'Icon'       => $item->getIcon(32),
								'itemID'     => $i_id,
								'groupID'    => $item->get_group_id(),
								'chargeSize' => $i_charge
							);
						}
					}
				}
				else if (($destroyed->getLocationID() == 5))
					{
						for ($count=0; $count < $i_qty; $count++)
						{
							$this->fitting_array[$i_location][]=array
								(
								'Name'   => $i_name,
								'Icon'   => $item->getIcon(32),
								'itemID' => $i_id
							);
						}
					}
			//fitting thing end
			}
		}

		if (count($this->kill->droppeditems_) > 0)
		{
			$this->drop_array=array();

			foreach ($this->kill->droppeditems_ as $dropped)
			{
				$item = $dropped->getItem();
				$i_qty=$dropped->getQuantity();

				if (config::get('item_values'))
				{
					$value=$dropped->getValue();
					$this->dropvalue+=$value * $i_qty;
					$formatted=$dropped->getFormattedValue();

					if(config::get('kd_droptototal') && strpos($item->getName(), 'Blueprint') !== false) $this->bp_value += $value * $i_qty;
				}

				$i_name     =$item->getName();
				$i_location =$dropped->getLocationID();
				$i_id       =$item->getID();
				$i_usedgroup=$item->get_used_launcher_group();

				$this->drop_array[$i_location][]=array
					(
					'Icon'        => $item->getIcon(32),
					'Name'        => $i_name,
					'Quantity'    => $i_qty,
					'Value'       => $formatted,
					'single_unit' => $value,
					'itemID'      => $i_id,
					'slotID'      => $i_location
				);

				//Fitting -KE, add dropped items to the list
				if (($i_location != 4) && ($i_location != 6))
				{
					if (($i_usedgroup != 0))
					{
						if ($i_location == 1)
						{
							$i_ammo=$item->get_ammo_size($i_name);

							if ($i_usedgroup == 481)
							{
								$i_ammo=0;
							}
						}
						else
						{
							$i_ammo=0;
						}

						$this->ammo_array[$i_location][]=array
							(
							'Name'        => $i_name,
							'Icon'        => $item->getIcon(24),
							'itemID'      => $i_id,
							'usedgroupID' => $i_usedgroup,
							'size'        => $i_ammo
						);
					}
					else
					{
						for ($count=0; $count < $i_qty; $count++)
						{
							if ($i_location == 1)
							{
								$i_charge=$item->get_used_charge_size();
							}
							else
							{
								$i_charge=0;
							}

							$this->fitting_array[$i_location][]=array
								(
								'Name'       => $i_name,
								'Icon'       => $item->getIcon(32),
								'itemID'     => $i_id,
								'groupID'    => $item->get_group_id(),
								'chargeSize' => $i_charge
							);
						}
					}
				}
			//fitting thing end

			}
		}
	}
	function involvedSetup()
	{
		global $smarty;
		$fetchExternalIDs = array();
		// involved
		$i=1;

		$this->involved=array();

		$this->ownKill= false;
		$invlimit = config::get('kd_involvedlimit');
		if(!is_numeric($invlimit)) $this->nolimit = 1;
		foreach ($this->kill->involvedparties_ as $inv)
		{
			$pilot                     =$inv->getPilot();
			$corp                      =$inv->getCorp();
			$alliance                  =$inv->getAlliance();
			$ship                      =$inv->getShip();

			$this->InvAllies[$alliance->getName()]["quantity"]+=1;
			$this->InvAllies[$alliance->getName()]["corps"][$corp->getName()]+=1;
			$this->InvShips[$ship->getName()] += 1;
			if(config::get('cfg_allianceid') && in_array($alliance->getID(), config::get('cfg_allianceid'))) $this->ownKill = true;
			elseif(config::get('cfg_corpid') && in_array($corp->getID(), config::get('cfg_corpid'))) $this->ownKill = true;
			elseif(config::get('cfg_pilotid') && in_array($pilot->getID(), config::get('cfg_pilotid'))) $this->ownKill = true;


			if(!$this->nolimit && $i > $invlimit)
			{
				if($i == $invlimit + 1)
				{
					$smarty->assign('limited', true);
					$smarty->assign('moreInvolved', count($this->kill->involvedparties_) - $invlimit);
					$smarty->assign('unlimitURL', '?'.htmlentities($_SERVER['QUERY_STRING']).'&amp;nolimit');
				}

				// include the final blow pilot
				if (!config::get('kd_showbox') || $pilot->getID() != $this->kill->getFBPilotID())
				{
					continue;
				}
			}

			$weapon                    =$inv->getWeapon();

			$this->involved[$i]['shipImage'] =$ship->getImage(64);
			$this->involved[$i]['shipTechLevel'] = $ship->getTechLevel();
			$this->involved[$i]['shipIsFaction'] = $ship->isFaction();		
			$this->involved[$i]['pilotURL']  ="?a=pilot_detail&amp;plt_id=" . $pilot->getID();
			$this->involved[$i]['pilotName'] =$pilot->getName();
			$this->involved[$i]['corpURL']   ="?a=corp_detail&amp;crp_id=" . $corp->getID();
			$this->involved[$i]['corpName']  =$corp->getName();
			$this->involved[$i]['alliURL']   ="?a=alliance_detail&amp;all_id=" . $alliance->getID();
			$this->involved[$i]['alliName']  =$alliance->getName();
			$this->involved[$i]['shipName']  =$ship->getName();
			$this->involved[$i]['shipID']    =$ship->getExternalID();
			$this->involved[$i]['damageDone']=$inv->dmgdone_;
			$shipclass                 =$ship->getClass();

			$this->involved[$i]['shipClass'] =$shipclass->getName();

			//detects NPC type things and runs a few conversions (Rats, Towers, Bubbles)
			$tpilot = $pilot->getName();
			if(preg_match("/-/", $tpilot))
			{ // a tower or bubble. But! Since we have placed the corp name in front of the
			  // item's name, we need to quickly check which base item it was again.

				$namestart = strripos($tpilot, '-') +2; //we're interested in the last dash
				$tpilot = substr($tpilot, $namestart);
			}


			if ($tpilot == $weapon->getName())
			{
				$this->involved[$i]['portrait'] = $corp->getPortraitURL(64);
				$this->involved[$i]['externalID'] = $corp->getExternalID(true);

				if($this->involved[$i]['externalID'] == 0)
				{
					$corpname = str_replace(" ", "%20", $corp->getName());
					$fetchExternalIDs[] = $corpname;
				}

				$this->involved[$i]['typeID'] = 2; //type number for corporations.

				if(!file_exists("img/types/64_64/".$weapon->getID().".png"))
					$this->involved[$i]['shipImage'] = $this->involved[$i]['portrait'];
				else
					$this->involved[$i]['shipImage'] = KB_HOST."/thumb.php?type=ship&amp;id=".$weapon->getID()."&amp;size=64";
			}
			else
			{
				$this->involved[$i]['portrait']=$pilot->getPortraitURL(64);
				$this->involved[$i]['externalID'] = $pilot->getExternalID(true);

				//get the external ID from the pilot class - if not found then add it to a list of pilots
				//and check the api in bulk
				if($this->involved[$i]['externalID'] == 0)
				{
					$pilotname = str_replace(" ", "%20", $pilot->getName());
					$fetchExternalIDs[] = $pilotname;
				}

				$this->involved[$i]['typeID'] = 1377; //type number for characters.
			}

			if ($weapon->getName() != "Unknown" && $weapon->getName() != $ship->getName())
			{
				$this->involved[$i]['weaponName']=$weapon->getName();
				$this->involved[$i]['weaponID']  =$weapon->getID();
			}
			else
				$this->involved[$i]['weaponName']="Unknown";

			if (!$this->finalblow && $pilot->getID() == $this->kill->getFBPilotID())
			{
				$this->involved[$i]['finalBlow']="true";
				$this->finalblow = $this->involved[$i];
				// If we're only here to get the final blow box details then remove this pilot.
				if(!$this->nolimit && $i > $invlimit && $i == $invlimit + 1) array_pop($this->involved);
			}
			else
			{
				$this->involved[$i]['finalBlow']="false";
			}

			++$i;
		}

		//prod CCP for the entire list of names
		if(count($fetchExternalIDs) > 0)
		{
			$names = new API_NametoID();
			$names->setNames(implode(',', $fetchExternalIDs));
			$names->fetchXML();
			$nameIDPair = $names->getNameData();

			//fill in the pilot external IDs.. could potentially be slow
			//but it beats the alternative. Do nothing if no names need loading.
			if(count($nameIDPair) > 0)
			{
				foreach($nameIDPair as $idpair)
				{
				//store the IDs
					foreach ($this->kill->involvedparties_ as $inv)
					{
						$pilot = $inv->getPilot();
						$corp = $inv->getCorp();
						$pname = $pilot->getName();
						$cname = $corp->getName();

						if($idpair['name'] == $cname)
						{
							$corp->setExternalID($idpair['characterID']);
						}
						elseif($idpair['name'] == $pname)
						{
							$pilot->setCharacterID($idpair['characterID']);
						}
					}

					//as we've already populated the structures for the template
					//we need to quickly retrofit it.
					foreach ($this->involved as $inv)
					{
						$pname = $inv['pilotName'];
						$cname = $inv['corpName'];

						if($cname == $idpair['name'])
						{
							$inv['externalID'] = $idpair['characterID'];
						}
						else if($pname == $idpair['name'])
							{
								$inv['externalID'] = $idpair['characterID'];
							}
					}
				}
			}
		}
	}
	function involvedSummary()
	{
		global $smarty;
		$smarty->assignByRef('invAllies', $this->InvAllies);
		$smarty->assignByRef('invShips', $this->InvShips);
		$smarty->assign('alliesCount', count($this->InvAllies));
		if($this->ownKill) $smarty->assign('kill',true);
		else $smarty->assign('kill',false);
		$smarty->assign('involvedPartyCount', $this->kill->getInvolvedPartyCount());
		$smarty->assign('showext', config::get('kd_showext'));

		return $smarty->fetch(get_tpl('kill_detail_inv_sum'));
	}
	function involved()
	{
		global $smarty;
		$smarty->assignByRef('involved', $this->involved);
		return $smarty->fetch(get_tpl('kill_detail_inv'));
	}

	function top()
	{
		global $smarty;
		$smarty->assign('kd_col', 'start');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}
	function middle()
	{
		global $smarty;
		$smarty->assign('kd_col', 'middle');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}
	function bottom()
	{
		global $smarty;
		$smarty->assign('kd_col', 'bottom');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}
	function victim()
	{
		global $smarty;
		$smarty->assign('killID', $this->kill->getID());
		$plt = new Pilot($this->kill->getVictimID());
		$item = new dogma($this->kill->getVictimShip()->getExternalID());
		// itt_cat = 6 for ships. Assume != 6 is a structure.
		if($item->get('itt_cat') != 6)
		{
			$corp = new Corporation($this->kill->getVictimCorpID());
			$smarty->assign('victimPortrait', $corp->getPortraitURL(64));
			$smarty->assign('victimExtID', 0);
		}
		else
		{
			$smarty->assign('victimPortrait', $plt->getPortraitURL(64));
			$smarty->assign('victimExtID', $plt->getExternalID());
		}
		$smarty->assign('victimURL', "?a=pilot_detail&amp;plt_id=" . $this->kill->getVictimID());
		$smarty->assign('victimName', $this->kill->getVictimName());
		$smarty->assign('victimCorpURL', "?a=corp_detail&amp;crp_id=" . $this->kill->getVictimCorpID());
		$smarty->assign('victimCorpName', $this->kill->getVictimCorpName());
		$smarty->assign('victimAllianceURL', "?a=alliance_detail&amp;all_id=" . $this->kill->getVictimAllianceID());
		$smarty->assign('victimAllianceName', $this->kill->getVictimAllianceName());
		$smarty->assign('victimDamageTaken', $this->kill->getDamageTaken());

		return $smarty->fetch(get_tpl('kill_detail_victim'));
	}
	function comments()
	{
		if (config::get('comments'))
		{
			global $smarty;

			$comments = new Comments($this->kll_id);

			$smarty->assignByRef('page', $this->page);

			return $this->commenthtml.$comments->getComments();
		}
		else return '';
	}
	function itemsLost()
	{
		global $smarty;

		if (config::get('item_values'))
		{
			$smarty->assign('item_values', 'true');
		}
		// preparing slot layout
		$slot_array=array();

		$slot_array[1]=array
			(
			'img'   => 'icon08_11.png',
			'text'  => 'Fitted - High slot',
			'items' => array()
		);

		$slot_array[2]=array
			(
			'img'   => 'icon08_10.png',
			'text'  => 'Fitted - Mid slot',
			'items' => array()
		);

		$slot_array[3]=array
			(
			'img'   => 'icon08_09.png',
			'text'  => 'Fitted - Low slot',
			'items' => array()
		);

		$slot_array[5]=array
			(
			'img'   => 'icon68_01.png',
			'text'  => 'Fitted - Rig slot',
			'items' => array()
		);

		$slot_array[6]=array
			(
			'img'   => 'icon02_10.png',
			'text'  => 'Drone bay',
			'items' => array()
		);

		$slot_array[4]=array
			(
			'img'   => 'icon03_14.png',
			'text'  => 'Cargo Bay',
			'items' => array()
		);
		$slot_array[7]=array
			(
			'img'   => 'icon76_04.png',
			'text'  => 'Fitted - Subsystems',
			'items' => array()
		);
		$smarty->assignByRef('slots', $slot_array);

		$smarty->assignByRef('destroyed', $this->dest_array);
		$smarty->assignByRef('dropped', $this->drop_array);

		if ($this->TotalValue >= 0)
		{
			$Formatted=number_format($this->TotalValue, 2);
		}

		// Get Ship Value
		$this->ShipValue=$this->kill->getVictimShip()->getPrice();

		if (config::get('kd_droptototal'))
		{
			$this->TotalValue+=$this->dropvalue;
		}

		$TotalLoss=number_format($this->TotalValue + $this->ShipValue, 2);
		$this->ShipValue=number_format($this->ShipValue, 2);
		$this->dropvalue=number_format($this->dropvalue, 2);
		$this->bp_value = number_format($this->bp_value, 2);

		$smarty->assign('itemValue', $Formatted);
		$smarty->assign('dropValue', $this->dropvalue);
		$smarty->assign('shipValue', $this->ShipValue);
		$smarty->assign('totalLoss', $TotalLoss);
		$smarty->assign('BPOValue', $this->bp_value);

		return $smarty->fetch(get_tpl('kill_detail_items_lost'));

	}
	function victimShip()
	{
		global $smarty;
		// Ship details
		$ship=$this->kill->getVictimShip();
		$shipclass=$ship->getClass();

		$smarty->assign('victimShip', $this->kill->getVictimShip());
		$smarty->assign('victimShipClass', $ship->getClass());
		$smarty->assign('victimShipImage', $ship->getImage(64));
		$smarty->assign('victimShipTechLevel', $ship->getTechLevel());
		$smarty->assign('victimShipIsFaction', $ship->isFaction());
		$smarty->assign('victimShipName', $ship->getName());
		$smarty->assign('victimShipID', $ship->getExternalID());
		$smarty->assign('victimShipClassName', $shipclass->getName());
		$smarty->assignByRef('victimShipIcon', $smarty->fetch(get_tpl('ship_victim_64')));
		if($this->page->isAdmin()) $smarty->assign('ship', $ship);

		$ssc=new dogma($ship->getExternalID());

		$smarty->assignByRef('ssc', $ssc);

		if ($this->kill->isClassified())
		{
		//Admin is able to see classified Systems
			if ($this->page->isAdmin())
			{
				$smarty->assign('systemID', $this->system->getID());
				$smarty->assign('system', $this->system->getName() . ' (Classified)');
				$smarty->assign('systemURL', "?a=system_detail&amp;sys_id=" . $this->system->getID());
				$smarty->assign('systemSecurity', $this->system->getSecurity(true));
			}
			else
			{
				$smarty->assign('system', 'Classified');
				$smarty->assign('systemURL', "");
				$smarty->assign('systemSecurity', '0.0');
			}
		}
		else
		{
			$smarty->assign('systemID', $this->system->getID());
			$smarty->assign('system', $this->system->getName());
			$smarty->assign('systemURL', "?a=system_detail&amp;sys_id=" . $this->system->getID());
			$smarty->assign('systemSecurity', $this->system->getSecurity(true));
		}

		$smarty->assign('timeStamp', $this->kill->getTimeStamp());
		$smarty->assign('victimShipImg', $ship->getImage(64));

		$smarty->assign('totalLoss', number_format($this->kill->getISKLoss()));
		return $smarty->fetch(get_tpl('kill_detail_victim_ship'));
	}
	function fitting()
	{
		global $smarty;

		if (is_array($this->fitting_array[1]))
		{
			foreach ($this->fitting_array[1] as $array_rowh)
			{
				$sort_by_nameh["groupID"][]=$array_rowh["groupID"];
			}

			array_multisort($sort_by_nameh["groupID"], SORT_ASC, $this->fitting_array[1]);
		}

		if (is_array($this->fitting_array[2]))
		{
			foreach ($this->fitting_array[2] as $array_rowm)
			{
				$sort_by_namem["groupID"][]=$array_rowm["groupID"];
			}

			array_multisort($sort_by_namem["groupID"], SORT_ASC, $this->fitting_array[2]);
		}

		if (is_array($this->fitting_array[3]))
		{
			foreach ($this->fitting_array[3] as $array_rowl)
			{
				$sort_by_namel["groupID"][]=$array_rowl["Name"];
			}

			array_multisort($sort_by_namel["groupID"], SORT_ASC, $this->fitting_array[3]);
		}

		if (is_array($this->fitting_array[5]))
		{
			foreach ($this->fitting_array[5] as $array_rowr)
			{
				$sort_by_namer["Name"][]=$array_rowr["Name"];
			}

			array_multisort($sort_by_namer["Name"], SORT_ASC, $this->fitting_array[5]);
		}

		if (is_array($this->fitting_array[7]))
		{
			foreach ($this->fitting_array[7] as $array_rowr)
			{
				$sort_by_namer["groupID"][]=$array_rowr["groupID"];
			}

			array_multisort($sort_by_namer["groupID"], SORT_ASC, $this->fitting_array[7]);
		}

		//Fitting - KE, sort the fitted items into name order, so that several of the same item apear next to each other. -end

		$length=count($this->ammo_array[1]);

		$temp=array();

		if (is_array($this->fitting_array[1]))
		{
			$hiammo=array();

			foreach ($this->fitting_array[1] as $highfit)
			{
				$group = $highfit["groupID"];
				$size  =$highfit["chargeSize"];

				if ($group
					== 483                          // Modulated Deep Core Miner II, Modulated Strip Miner II and Modulated Deep Core Strip Miner II
					|| $group == 53                     // Laser Turrets
					|| $group == 55                     // Projectile Turrets
					|| $group == 74                     // Hybrid Turrets
					|| ($group >= 506 && $group <= 511) // Some Missile Lauchers
					|| $group == 481                    // Probe Launchers
					|| $group == 899                    // Warp Disruption Field Generator I
					|| $group == 771                    // Heavy Assault Missile Launchers
					|| $group == 589                    // Interdiction Sphere Lauchers
					|| $group == 524                    // Citadel Torpedo Launchers
				)
				{
					$found=0;

					if ($group == 511)
					{
						$group=509;
					} // Assault Missile Lauchers uses same ammo as Standard Missile Lauchers

					if (is_array($this->ammo_array[1]))
					{
						$i=0;

						while (!($found) && $i < $length)
						{
							$temp = array_shift($this->ammo_array[1]);

							if (($temp["usedgroupID"] == $group) && ($temp["size"] == $size))
							{
								$hiammo[]=array
									(
									'show' => $smarty->fetch(get_tpl('ammo')),
									'type' => $temp["Icon"]
								);

								$found=1;
							}

							array_push($this->ammo_array[1], $temp);
							$i++;
						}
					}

					if (!($found))
					{
						$hiammo[]=array
							(
							'show' => $smarty->fetch(get_tpl('ammo')),
							'type' => $smarty->fetch(get_tpl('noicon'))
						);
					}
				}
				else
				{
					$hiammo[]=array
						(
						'show' => $smarty->fetch(get_tpl('blank')),
						'type' => $smarty->fetch(get_tpl('blank'))
					);
				}
			}
		}

		$length=count($this->ammo_array[2]);

		if (is_array($this->fitting_array[2]))
		{
			$midammo=array();

			foreach ($this->fitting_array[2] as $midfit)
			{
				$group = $midfit["groupID"];

				if ($group == 76 // Capacitor Boosters
					|| $group == 208 // Remote Sensor Dampeners
					|| $group == 212 // Sensor Boosters
					|| $group == 291 // Tracking Disruptors
					|| $group == 213 // Tracking Computers
					|| $group == 209 // Tracking Links
					|| $group == 290 // Remote Sensor Boosters
				)
				{
					$found=0;

					if (is_array($this->ammo_array[2]))
					{
						$i=0;

						while (!($found) && $i < $length)
						{
							$temp = array_shift($this->ammo_array[2]);

							if ($temp["usedgroupID"] == $group)
							{
								$midammo[]=array
									(
									'show' => $smarty->fetch(get_tpl('ammo')),
									'type' => $temp["Icon"]
								);

								$found=1;
							}

							array_push($this->ammo_array[2], $temp);
							$i++;
						}
					}

					if (!($found))
					{
						$midammo[]=array
							(
							'show' => $smarty->fetch(get_tpl('ammo')),
							'type' => $smarty->fetch(get_tpl('noicon'))
						);
					}
				}
				else
				{
					$midammo[]=array
						(
						'show' => $smarty->fetch(get_tpl('blank')),
						'type' => $smarty->fetch(get_tpl('blank'))
					);
				}
			}
		}
		$smarty->assignByRef('fitting_high', $this->fitting_array[1]);
		$smarty->assignByRef('fitting_med', $this->fitting_array[2]);
		$smarty->assignByRef('fitting_low', $this->fitting_array[3]);
		$smarty->assignByRef('fitting_rig', $this->fitting_array[5]);
		$smarty->assignByRef('fitting_sub', $this->fitting_array[7]);
		$smarty->assignByRef('fitting_ammo_high', $hiammo);
		$smarty->assignByRef('fitting_ammo_mid', $midammo);
		$smarty->assign('showammo', config::get('fp_showammo'));

		if(file_exists("img/ships/256_256/".$this->kill->getVictimShip()->getExternalID().".png"))
			$smarty->assign('victimShipBigImage', $this->kill->getVictimShip()->getImage(256));
		else $smarty->assign('noBigImage', true);

		if(config::get('kd_verify'))
		{
			$this->verification = false;
			if($this->kill->getExternalID() != 0)
			{
				$this->verification = true;
				$smarty->assign('verify_id', $this->kill->getExternalID());
			}
			$smarty->assign('verify_yesno', $this->verification);
		}
		$smarty->assign('showverify', config::get('kd_verify'));

		//$hicount =count($this->fitting_array[1]);
		//$medcount=count($this->fitting_array[2]);
		//$lowcount=count($this->fitting_array[3]);
		//$rigcount=count($this->fitting_array[5]);

		$hicount = 0; //zero the values;
		$medcount = 0;
		$lowcount = 0;
		$rigcount = 0;


		//get the actual slot count for each vessel - for the fitting panel
		$ship = $this->kill->getVictimShip();
		$sql = 'SELECT `attributeID`, `value` FROM `kb3_dgmtypeattributes` WHERE '.
			    '`attributeID` IN ( 12, 13, 14, 1137) AND `typeID` = '. $ship->getExternalID(). ';';
		$qry = DBFactory::getDBQuery();
		$qry->execute($sql);
		while($row = $qry->getRow())
		{
		    switch($row["attributeID"])
		    {
			case '12': { $lowcount = $row["value"]; break; }
			case '13': { $medcount = $row["value"]; break; }
			case '14': { $hicount = $row["value"]; break; }
			case '1137': { $rigcount = $row["value"]; break; }
		    }
		}

		$subcount = count($this->fitting_array[7]);

		//This code counts the slots granted by subsystem modules for the fitting panel
		if($subcount > 0)
		{
		    foreach ($this->fitting_array[7] as $subfit)
		    {
			$lookupRef = $subfit["itemID"];
			$sql = 'SELECT `attributeID`, `value` FROM `kb3_dgmtypeattributes` WHERE '.
			    '`attributeID` IN (1374, 1375, 1376) AND `typeID` = '. $lookupRef. ';';
			$qry = DBFactory::getDBQuery();
			$qry->execute($sql);
			while($row = $qry->getRow())
			{
			    switch($row["attributeID"])
			    {
				case '1374': { $hicount += $row["value"]; break; }
				case '1375': { $medcount += $row["value"]; break; }
				case '1376': { $lowcount += $row["value"]; break; }
			    }
			}
		    }
		}

		$smarty->assign('hic', $hicount);
		$smarty->assign('medc', $medcount);
		$smarty->assign('lowc', $lowcount);
		$smarty->assign('rigc', $rigcount);
		$smarty->assign('subc', $subcount);

		return $smarty->fetch(get_tpl('kill_detail_fitting'));
	}
	function damageBox()
	{
		global $smarty;
		if (!config::get('kd_showbox')) return '';

		$topdamage = $this->involved;

		function multi_sort(&$array, $key)
		{
			usort($array,create_function('$a,$b','if ($a["'.$key.'"] == $b["'.$key.'"]) return 0;' .'return ($a["'.$key.'"] > $b["'.$key.'"]) ? -1 : 1;'));
		}

		multi_sort($topdamage,"damageDone");
		$topdamage = array_slice($topdamage, 0, 1);
		$smarty->assign('topdamage', $topdamage);
		$smarty->assign('finalblow', $this->finalblow);

		return $smarty->fetch(get_tpl('kill_detail_damage_box'));
	}
	function menuSetup()
	{
		$this->addMenuItem("caption", "View");
		$this->addMenuItem("link",
			"Killmail",
			"?a=kill_mail&amp;kll_id=" . $this->kill->getID(),
			0,0,
			"sndReq('?a=kill_mail&amp;kll_id=" . $this->kill->getID()
			. "');ReverseContentDisplay('popup')");

		if (config::get('kd_EFT'))
		{
			$this->addMenuItem("link",
				"EFT Fitting",
				"?a=eft_fitting&amp;kll_id=" . $this->kill->getID(),
				0,0,
				"sndReq('?a=eft_fitting&amp;kll_id=" . $this->kill->getID()
				. "');ReverseContentDisplay('popup')");
			$this->addMenuItem("link",
				"EvE Fitting",
				"?a=eve_fitting&amp;kll_id=" . $this->kill->getID());
		}

		if ($this->kill->relatedKillCount() > 1 || $this->kill->relatedLossCount() > 1 ||
			((config::get('cfg_allianceid') || config::get('cfg_corpid') || config::get('cfg_pilotid'))
				&& $this->kill->relatedKillCount() + $this->kill->relatedLossCount() > 1))
		{
			$this->addMenuItem("link", "Related kills (" . $this->kill->relatedKillCount() . "/" . $this->kill->relatedLossCount() . ")",
				"?a=kill_related&amp;kll_id=" . $this->kill->getID());
		}

		if ($this->page->isAdmin())
		{
			$this->addMenuItem("caption", "Admin");
			$this->addMenuItem("link",
				"Delete",
				"?a=admin_kill_delete&amp;kll_id=" . $this->kill->getID(),
				0,0,
				"openWindow('?a=admin_kill_delete&amp;kll_id=" . $this->kill->getID()
				. "', null, 420, 300, '' );");

			if (isset($_GET['view']) && $_GET['view'] == 'FixSlot')
			{
				$this->addMenuItem("link", "Adjust Values", "?a=kill_detail&amp;kll_id=" . $this->kill->getID() . "");
			}
			else
			{
				$this->addMenuItem("link", "Fix Slots", "?a=kill_detail&amp;kll_id=" . $this->kill->getID() . "&amp;view=FixSlot");
			}
		}
		return "";
	}
	//! Build the menu.

	//! Add all preset options to the menu.
	function menu()
	{
		$menubox=new Box("Menu");
		$menubox->setIcon("menu-item.gif");
		foreach($this->menuOptions as $options)
		{
			call_user_func_array(array($menubox,'addOption'), $options);
//			if(isset($options[2]))
//				$menubox->addOption($options[0],$options[1], $options[2]);
//			else
//				$menubox->addOption($options[0],$options[1]);
		}

		return $menubox->generate();
	}

	function points()
	{
		if (!config::get('kill_points')) return '';

		$scorebox=new Box("Points");
		$scorebox->addOption("points", $this->kill->getKillPoints());
		return $scorebox->generate();
	}

	function map()
	{
	//Admin is able to see classsified systems
		if ((!$this->kill->isClassified()) || ($this->page->isAdmin()))
		{
			$mapbox=new Box("Map");
			if(IS_IGB)
			{
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=map&amp;id=".$this->system->getID()."&amp;size=145", "javascript:CCPEVE.showInfo(3, ".$this->system->getRegionID().")");
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=region&amp;id=".$this->system->getID()."&amp;size=145", "javascript:CCPEVE.showInfo(4, ".$this->system->getConstellationID().")");
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=cons&amp;id=".$this->system->getID()."&amp;size=145", "javascript:CCPEVE.showInfo(5, ".$this->system->getExternalID().")");
			}
			else
			{
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=map&amp;id=".$this->system->getID()."&amp;size=145");
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=region&amp;id=".$this->system->getID()."&amp;size=145");
				$mapbox->addOption("img", KB_HOST."/thumb.php?type=cons&amp;id=".$this->system->getID()."&amp;size=145");
			}
			return $mapbox->generate();
		}
		return '';
	}
	//! Add an item to the menu in standard box format.

	/*!
	 *  Only links need all 3 attributes
	 * \param type Types can be caption, img, link, points.
	 * \param name The name to display.
	 * \param url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = func_get_args();
	}
	//! Update the stored value of an item and the total value of this kill.

	//! Input values are taken from the query string.
	private function updatePrices()
	{
		global $smarty;
		if (config::get('item_values'))
		{
			if (isset($_POST['submit']) && $_POST['submit'] == 'UpdateValue')
			{
				// Send new value for item to the database
				$qry = DBFactory::getDBQuery();;
				$qry->autocommit(false);
				if(isset($_POST['SID']))
				{
					$SID = intval($_POST['SID']);
					$Val = preg_replace('/[^0-9]/','',$_POST[$SID]);
					$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$SID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
				}
				else
				{
					$IID = intval($_POST['IID']);
					$Val = preg_replace('/[^0-9]/','',$_POST[$IID]);
					$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$IID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
					foreach($this->kill->destroyeditems_ as $i => $ditem)
					{
						if($ditem->getItem()->getID() == $IID) $this->kill->destroyeditems_[$i]->value = $Val;
					}
					foreach($this->kill->droppeditems_ as $i=> $ditem)
					{
						if($ditem->getItem()->getID() == $IID) $this->kill->droppeditems_[$i]->value = $Val;
					}
				}
				$this->kill->calculateISKLoss(true);
				$qry->autocommit(true);
			}

		}
	}
	private function fixSlots()
	{
		global $smarty;
		if (isset($_GET['view']) && $_GET['view'] == 'FixSlot')
		{
			$smarty->assign('fixSlot', 'true');
		}

		$smarty->assign('admin', 'true');

		if (isset($_POST['submit']) && $_POST['submit'] == 'UpdateSlot')
		{
			$IID  =$_POST['IID'];
			$KID  =$_POST['KID'];
			$Val  =$_POST[$IID];
			$table=$_POST['TYPE'];
			$old  =$_POST['OLDSLOT'];
			$qry  =DBFactory::getDBQuery();;
			$qry->execute("UPDATE kb3_items_" . $table . " SET itd_itl_id ='" . $Val . "' WHERE itd_itm_id=" . $IID
				. " AND itd_kll_id = " . $KID . " AND itd_itl_id = " . $old);
		}
	}
	public function source()
	{
		global $smarty;
		$qry = DBFactory::getDBQuery();
		$sql = "SELECT log_ip_address, log_timestamp FROM kb3_log WHERE log_kll_id = ".$this->kll_id;
		$qry->execute($sql);
		if(!$row=$qry->getRow()) return "";
		$source = $row['log_ip_address'];
		$posteddate = $row['log_timestamp'];

		if(preg_match("/^\d+/", $source))
		{
			$type = "IP";
			// No posting IPs publicly.
			if(!$this->page->isAdmin()) $source = "";
		}
		elseif(preg_match("/^API/", $source))
		{
			$type="API";
			$source = $this->kill->getExternalID();
		}
		elseif(preg_match("/^http/", $source)) $type="URL";
		elseif(preg_match("/^ID:http/", $source))
		{
			$type="URL";
			$source = substr($source, 3);
		}
		else $type = "unknown";
		
		$smarty->assign("source", htmlentities($source));
		$smarty->assign("type", $type);
		$smarty->assign("postedDate", $posteddate);
		return $smarty->fetch(get_tpl("sourcedFrom"));
	}
}

$killDetail = new pKillDetail();
event::call("killDetail_assembling", $killDetail);
$html = $killDetail->assemble();
$killDetail->page->setContent($html);

$killDetail->context();
event::call("killDetail_context_assembling", $killDetail);
$context = $killDetail->assemble();
$killDetail->page->addContext($context);

$killDetail->page->generate();