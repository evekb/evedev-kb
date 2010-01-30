<?php
/*
 * Based on work by unknown, Sapyx, Rostik, Tribalize, Ben Thomas, KE and Kovell
 */
require_once('common/includes/class.pageAssembly.php');
require_once('common/includes/class.kill.php');


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
		$this->page->addHeader('<meta name="robots" content="noindex, nofollow" />');

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
		// If a comment is being posted then we won't exit this block.
		if(isset($_POST['comment']) && config::get('comments'))
		{
			require_once('common/includes/class.comments.php');

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
		require_once('common/includes/class.killsummarytable.php');
		require_once('common/includes/class.pilot.php');
		require_once('common/includes/class.corp.php');
		require_once('common/includes/class.alliance.php');

		global $smarty;
		if(!file_exists('img/panel/'.config::get('fp_theme').'png')) config::set('fp_theme','apoc');
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
			if(ALLIANCE_ID >0 && $alliance->getID()==ALLIANCE_ID) $this->ownKill=true;
			elseif(CORP_ID >0 && $corp->getID()==CORP_ID) $this->ownKill=true;


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
			$this->involved[$i]['pilotURL']  ="?a=pilot_detail&amp;plt_id=" . $pilot->getID();
			$this->involved[$i]['pilotName'] =$pilot->getName();
			$this->involved[$i]['corpURL']   ="?a=corp_detail&amp;crp_id=" . $corp->getID();
			$this->involved[$i]['corpName']  =$corp->getName();
			$this->involved[$i]['alliURL']   ="?a=alliance_detail&amp;all_id=" . $alliance->getID();
			$this->involved[$i]['alliName']  =$alliance->getName();
			$this->involved[$i]['shipName']  =$ship->getName();
			$this->involved[$i]['shipID']    =$ship->externalid_;
			$this->involved[$i]['damageDone']=$inv->dmgdone_;
			$shipclass                 =$ship->getClass();

			$this->involved[$i]['shipClass'] =$shipclass->getName();

			if ($pilot->getName() == $weapon->getName())
			{
				$this->involved[$i]['portrait'] = $corp->getPortraitURL(64);
				$this->involved[$i]['externalID'] = $corp->getExternalID(true);

				if($this->involved[$i]['externalID'] == 0)
				{
					$corpname = str_replace(" ", "%20", $corp->getName());
					$fetchExternalIDs[] = $corpname;
				}

				$this->involved[$i]['typeID'] = 2; //type number for corporations.

				if(!file_exists("img/ships/64_64/".$weapon->getID().".png"))
					$this->involved[$i]['shipImage'] = $this->involved[$i]['portrait'];
				else
					$this->involved[$i]['shipImage'] = IMG_URL."/ships/64_64/".$weapon->getID().".png";
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
				$this->involved[$i]['weaponID']  =$weapon->row_['itm_externalid'];
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
			require_once('common/includes/class.eveapi.php');
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
		$smarty->assign_by_ref('invAllies', $this->InvAllies);
		$smarty->assign_by_ref('invShips', $this->InvShips);
		$smarty->assign_by_ref('alliesCount', count($this->InvAllies));
		if($this->ownKill) $smarty->assign('kill',true);
		else $smarty->assign('kill',false);
		$smarty->assign('involvedPartyCount', $this->kill->getInvolvedPartyCount()); // Anne Sapyx 07/05/2008
		$smarty->assign('showext', config::get('kd_showext'));

		return $smarty->fetch(get_tpl('kill_detail_inv_sum'));
	}
	function involved()
	{
		global $smarty;
		$smarty->assign_by_ref('involved', $this->involved);
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
		require_once("common/includes/class.dogma.php");
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
		$smarty->assign('victimDamageTaken', $this->kill->VictimDamageTaken);

		return $smarty->fetch(get_tpl('kill_detail_victim'));
	}
	function comments()
	{
		if (config::get('comments'))
		{
			global $smarty;
			require_once('common/includes/class.comments.php');

			$comments = new Comments($this->kll_id);

			$smarty->assign_by_ref('page', $this->page);

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

			if ($this->page->isAdmin())
			{
				if (isset($_POST['submit']) && $_POST['submit'] == 'UpdateValue')
				{
				// Send new value for item to the database
					$qry = new DBQuery();
					$qry->autocommit(false);
					if(isset($_POST['SID']))
					{
						$SID = intval($_POST['SID']);
						$Val = preg_replace('/[^0-9]/','',$_POST[$SID]);
						$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$SID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
						$victimship = $this->kill->getVictimShip();
						$this->kill->setVictimShip(new Ship($victimship->getID() ));
					}
					else
					{
						$IID = intval($_POST['IID']);
						$Val = preg_replace('/[^0-9]/','',$_POST[$IID]);
						$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$IID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
						foreach($this->kill->destroyeditems_ as $i => $ditem)
						{
							$item = $ditem->getItem();
							if($item->getID() == $IID) $this->kill->destroyeditems_[$i]->value = $Val;
						}
						foreach($this->kill->droppeditems_ as $i=> $ditem)
						{
							$item = $ditem->getItem();
							if($item->getID() == $IID) $this->kill->droppeditems_[$i]->value = $Val;
						}
					}
					$qry->execute("UPDATE kb3_kills SET kll_isk_loss = ".$this->kill->calculateISKLoss()." WHERE kll_id = ".$this->kill->getID());
					$qry->autocommit(true);
				}
			}
		}

		if ($this->page->isAdmin())
		{
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
				$qry  =new DBQuery();
				$qry->execute("UPDATE kb3_items_" . $table . " SET itd_itl_id ='" . $Val . "' WHERE itd_itm_id=" . $IID
					. " AND itd_kll_id = " . $KID . " AND itd_itl_id = " . $old);
			}
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
		$smarty->assign_by_ref('slots', $slot_array);

		$smarty->assign_by_ref('destroyed', $this->dest_array);
		$smarty->assign_by_ref('dropped', $this->drop_array);

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

		$smarty->assign('itemValue', $Formatted);
		$smarty->assign('dropValue', $this->dropvalue);
		$smarty->assign('shipValue', $this->ShipValue);
		$smarty->assign('totalLoss', $TotalLoss);

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
		$smarty->assign('victimShipName', $ship->getName());
		$smarty->assign('victimShipID', $ship->externalid_);
		$smarty->assign('victimShipClassName', $shipclass->getName());
		if($this->page->isAdmin()) $smarty->assign('ship', $ship);

		include_once('common/includes/class.dogma.php');

		$ssc=new dogma($ship->externalid_);

		$smarty->assign_by_ref('ssc', $ssc);

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
		$smarty->assign_by_ref('fitting_high', $this->fitting_array[1]);
		$smarty->assign_by_ref('fitting_med', $this->fitting_array[2]);
		$smarty->assign_by_ref('fitting_low', $this->fitting_array[3]);
		$smarty->assign_by_ref('fitting_rig', $this->fitting_array[5]);
		$smarty->assign_by_ref('fitting_sub', $this->fitting_array[7]);
		$smarty->assign_by_ref('fitting_ammo_high', $hiammo);
		$smarty->assign_by_ref('fitting_ammo_mid', $midammo);
		$smarty->assign('showammo', config::get('fp_showammo'));

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

		/*
		$hicount =count($this->fitting_array[1]);
		$medcount=count($this->fitting_array[2]);
		$lowcount=count($this->fitting_array[3]);

		$smarty->assign('hic', $hicount);
		$smarty->assign('medc', $medcount);
		$smarty->assign('lowc', $lowcount);
		*/

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
			"javascript:sndReq('index.php?a=kill_mail&amp;kll_id=" . $this->kill->getID()
			. "');ReverseContentDisplay('popup')");

		if (config::get('kd_EFT'))
		{
			$this->addMenuItem("link",
				"EFT Fitting",
				"javascript:sndReq('index.php?a=eft_fitting&amp;kll_id=" . $this->kill->getID()
				. "');ReverseContentDisplay('popup')");
			$this->addMenuItem("link",
				"EvE Fitting",
				"?a=eve_fitting&amp;kll_id=" . $this->kill->getID());
		}

		if ($this->kill->relatedKillCount() > 1 || $this->kill->relatedLossCount() > 1 ||
			((ALLIANCE_ID || CORP_ID || PILOT_ID) && $this->kill->relatedKillCount() + $this->kill->relatedLossCount() > 1))
		{
			$this->addMenuItem("link", "Related kills (" . $this->kill->relatedKillCount() . "/" . $this->kill->relatedLossCount() . ")",
				"?a=kill_related&amp;kll_id=" . $this->kill->getID());
		}

		if ($this->page->isAdmin())
		{
			$this->addMenuItem("caption", "Admin");
			$this->addMenuItem("link",
				"Delete",
				"javascript:openWindow('?a=admin_kill_delete&amp;kll_id=" . $this->kill->getID()
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
			if(isset($options[2]))
				$menubox->addOption($options[0],$options[1], $options[2]);
			else
				$menubox->addOption($options[0],$options[1]);
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
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=map&amp;size=145", "javascript:CCPEVE.showInfo(3, ".$this->system->getRegionID().")");
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=region&amp;size=145", "javascript:CCPEVE.showInfo(4, ".$this->system->getConstellationID().")");
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=cons&amp;size=145", "javascript:CCPEVE.showInfo(5, ".$this->system->getExternalID().")");
			}
			else
			{
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=map&amp;size=145");
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=region&amp;size=145");
				$mapbox->addOption("img", "?a=mapview&amp;sys_id=" . $this->system->getID() . "&amp;mode=cons&amp;size=145");
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
		$this->menuOptions[] = array($type, $name, $url);
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