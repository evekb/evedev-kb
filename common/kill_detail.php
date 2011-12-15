<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

if (config::get('comments')) {
	require_once('common/includes/xajax.functions.php');
}

/**
 * @package EDK
 */
class pKillDetail extends pageAssembly
{

	/** @var integer The id of the kill this page is for. */
	public $kll_id;
	/** @var integer The external id of the kill this page is for. */
	public $kll_external_id;
	/** @var Kill The Kill for the page's kill. */
	protected $kill;
	/** @var Page The Page used to create this page.*/
	public $page;
	/** @var array */
	protected $menuOptions = array();
	/** @var boolean */
	protected $nolimit = false;
	/** @var array Array of all involved Alliances*/
	protected $invAllies = array();
	/** @var array Array of all involved Ships */
	protected $invShips = array();
	/** @var array */
	protected $ammo_array = array();
	/** @var array */
	protected $fitting_array = array();
	/** @var float */
	protected $dropvalue = 0;
	/** @var float */
	protected $totalValue = 0;
	/** @var float */
	protected $bp_value = 0;
	/** @var array Array of destroyed items*/
	protected $dest_array = array();
	/** @var array Array of dropped items*/
	protected $drop_array = array();

	/**
	 * Construct the Pilot Details object.
	 * Add the functions to the build queue.
	 */
	function __construct()
	{
		parent::__construct();
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

	/**
	 *  Reset the assembly object to prepare for creating the context.
	 */
	function context()
	{
		parent::__construct();
		$this->queue("menuSetup");
		$this->queue("menu");
		$this->queue("points");
		$this->queue("damageBox");
		$this->queue("map");
	}

	/**
	 * Start constructing the page.
	 *
	 * Prepare all the shared variables such as dates and check alliance ID.
	 */
	function start()
	{
		$this->kll_id = (int) edkURI::getArg('kll_id');
		$this->kll_external_id = 0;
		if (!$this->kll_id) {
			$this->kll_external_id = (int) edkURI::getArg('kll_ext_id');
			if (!$this->kll_external_id) {
				// internal and external ids easily overlap so we can't guess which
				$this->kll_id = (int) edkURI::getArg('id', 1);
			}
		}
		$this->nolimit = edkURI::getArg('nolimit', 2);

		$this->menuOptions = array();

		$this->page = new Page('Kill details');

		if (!$this->kll_id && !$this->kll_external_id) {
			$html = "No kill id specified.";
			$this->page->setContent($html);
			$this->page->generate($html);
			exit;
		}

		if ($this->kll_id) {
			$this->kill = Cacheable::factory('Kill', $this->kll_id);
		} else {
			$this->kill = new Kill($this->kll_external_id, true);
			$this->kll_id = $this->kill->getID();
		}

		if (!$this->kill->exists()) {
			$html = "That kill doesn't exist.";
			$this->page->setContent($html);
			$this->page->generate($html);
			exit;
		}

		if ($this->kll_external_id) {
			$this->page->addHeader("<link rel='canonical' href='"
					.edkURI::build(array('kll_ext_id', $this->kll_external_id,
						true))."' />");
		} else {
			$this->page->addHeader("<link rel='canonical' href='"
					.edkURI::build(array('kll_id', $this->kll_id, true))
					."' />");
		}

		$this->finalblow = false;

		$this->commenthtml = '';
		// Check for posted comments.
		// If a comment is being posted then we won't exit this block.
		if (isset($_POST['comment']) && config::get('comments')) {
			$comments = new Comments($this->kll_id);
			$pw = false;
			if (!config::get('comments_pw') || $this->page->isAdmin()) {
				$pw = true;
			}
			if ($pw || crypt($_POST['password'],
					config::get("comment_password"))
							== config::get("comment_password")) {
				if ($_POST['comment'] == '') {
					$this->commenthtml = 'Error: The silent type, hey? Good for'
							.' you, bad for a comment.';
				} else {
					$comment = $_POST['comment'];
					$name = $_POST['name'];
					if ($name == null) {
						$name = 'Anonymous';
					}
					$comments->addComment($name, $comment);
					//Remove cached file.
					if (config::get('cache_enabled')) {
						cache::deleteCache();
					}
					//Redirect to avoid refresh reposting comments.
					header('Location: '.$_SERVER['REQUEST_URI'], TRUE, 303);
					die();
				}
			}
			else {
				// Password is wrong
				$this->commenthtml = 'Error: Wrong Password';
			}
		}
		// Check admin update options.
		if ($this->page->isAdmin()) {
			$this->updatePrices();
			$this->fixSlots();
		}

		global $smarty;
		if (!file_exists('img/panel/'.config::get('fp_theme').'.png')) {
				config::set('fp_theme', 'tyrannis');
		}
		$smarty->assign('panel_colour', config::get('fp_theme'));
		$smarty->assign('showiskd', config::get('kd_showiskd'));
		$smarty->assign('formURL', edkURI::build(edkURI::parseURI()));

		$this->involvedSetup();
		$this->fittingSetup();
	}

	function fittingSetup()
	{
		// ship fitting
		if (count($this->kill->destroyeditems_) > 0) {
			$this->dest_array = array();

			foreach ($this->kill->destroyeditems_ as $destroyed) {
				$item = $destroyed->getItem();
				$i_qty = $destroyed->getQuantity();

				if (config::get('item_values')) {
					$value = $destroyed->getValue();
					$this->totalValue += $value * $i_qty;
					$formatted = $destroyed->getFormattedValue();

					if (strpos($item->getName(), 'Blueprint') !== false)
							$this->bp_value += $value * $i_qty;
				}

				$i_name = $item->getName();
				$i_location = $destroyed->getLocationID();
				$i_id = $item->getID();
				$i_usedgroup = $item->get_used_launcher_group();
				
				// BPCs
				if($i_location == 9) {
					$i_location = 4;
					$i_name = $i_name." (Copy)";
					$value = $formatted = 0;
					$bpc = true;
				} else {
					$bpc = false;
				}

				$this->dest_array[$i_location][] = array
					(
					'Icon' => $item->getIcon(32),
					'Name' => $i_name,
					'url' => edkURI::page('invtype', $i_id),
					'Quantity' => $i_qty,
					'Value' => $formatted,
					'single_unit' => $value,
					'itemID' => $i_id,
					'slotID' => $i_location,
					'bpc' => $bpc
				);

				//Fitting, KE - add destroyed items to an array of all fitted items.
				if ($i_location <= 3 || $i_location == 7 || $i_location == 5) {
					if (($i_usedgroup != 0)) {
						if ($i_location == 1) {
							$i_ammo = $item->get_ammo_size($i_name);
							if ($i_usedgroup == 481) {
								$i_ammo = 0;
							}
						} else {
							$i_ammo = 0;
						}

						$this->ammo_array[$i_location][] = array
							(
							'Name' => $i_name,
							'Icon' => $item->getIcon(24),
							'itemID' => $i_id,
							'usedgroupID' => $i_usedgroup,
							'size' => $i_ammo,
							'destroyed' => true
						);
					} else {
						// Use a max of 8 as a sanity check.
						// Avoids timeouts on badly faked mails.
						// TODO: Refuse to show fitting and display an invalid message.
						for ($count = 0; $count < min($i_qty, 8); $count++) {
							if ($i_location == 1) {
								$i_charge = $item->get_used_charge_size();
							} else {
								$i_charge = 0;
							}

							$this->fitting_array[$i_location][] = array(
								'Name' => $i_name,
								'Icon' => $item->getIcon(32),
								'itemID' => $i_id,
								'groupID' => $item->get_group_id(),
								'chargeSize' => $i_charge,
								'destroyed' => true
							);
						}
					}
				}
				//fitting thing end
			}
		}

		if (count($this->kill->droppeditems_) > 0) {
			$this->drop_array = array();

			foreach ($this->kill->droppeditems_ as $dropped) {
				$item = $dropped->getItem();
				$i_qty = $dropped->getQuantity();

				if (config::get('item_values')) {
					$value = $dropped->getValue();
					$this->dropvalue+=$value * $i_qty;
					$formatted = $dropped->getFormattedValue();

					if (config::get('kd_droptototal') && strpos(
							$item->getName(), 'Blueprint') !== false) {
						$this->bp_value += $value * $i_qty;
					}
				}

				$i_name = $item->getName();
				$i_location = $dropped->getLocationID();
				$i_id = $item->getID();
				$i_usedgroup = $item->get_used_launcher_group();

				// BPCs
				if($i_location == 9) {
					$i_location = 4;
					$i_name = $i_name." (Copy)";
					$value = $formatted = 0;
					$bpc = true;
				} else {
					$bpc = false;
				}

				$this->drop_array[$i_location][] = array(
					'Icon' => $item->getIcon(32),
					'Name' => $i_name,
					'url' => edkURI::page('invtype', $i_id),
					'Quantity' => $i_qty,
					'Value' => $formatted,
					'single_unit' => $value,
					'itemID' => $i_id,
					'slotID' => $i_location,
					'bpc' => $bpc
				);

				//Fitting -KE, add dropped items to the list
				if (($i_location != 4) && ($i_location != 6)) {
					if (($i_usedgroup != 0)) {
						if ($i_location == 1) {
							$i_ammo = $item->get_ammo_size($i_name);

							if ($i_usedgroup == 481) {
								$i_ammo = 0;
							}
						} else {
							$i_ammo = 0;
						}

						$this->ammo_array[$i_location][] = array(
							'Name' => $i_name,
							'Icon' => $item->getIcon(24),
							'itemID' => $i_id,
							'usedgroupID' => $i_usedgroup,
							'size' => $i_ammo,
							'destroyed' => false
						);
					} else {
						// Use a max of 8 as a sanity check.
						// Avoids timeouts on badly faked mails.
						for ($count = 0; $count < min($i_qty, 8); $count++) {
							if ($i_location == 1) {
								$i_charge = $item->get_used_charge_size();
							} else {
								$i_charge = 0;
							}

							$this->fitting_array[$i_location][] = array(
								'Name' => $i_name,
								'Icon' => $item->getIcon(32),
								'itemID' => $i_id,
								'groupID' => $item->get_group_id(),
								'chargeSize' => $i_charge,
								'destroyed' => false
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
		$i = 1;

		$this->involved = array();

		$this->ownKill = false;
		$invlimit = config::get('kd_involvedlimit');
		if (!is_numeric($invlimit)) $this->nolimit = 1;
		foreach ($this->kill->getInvolved() as $inv) {
			$corp = Corporation::getByID($inv->getCorpID());
			$alliance = Alliance::getByID($inv->getAllianceID());
			$ship = Ship::getByID( $inv->getShipID());

			$alliance_name = $alliance->getName();
			if (!isset($this->invAllies[$alliance_name])) {
				$this->invAllies[$alliance_name] = Array('quantity' => 1,
					'corps' => Array());
			} else {
				$this->invAllies[$alliance_name]["quantity"] += 1;
			}
			$corp_name = $corp->getName();
			if (!isset($this->invAllies[$alliance_name]["corps"][$corp_name])) {
				$this->invAllies[$alliance_name]["corps"][$corp_name] = 1;
			} else {
				$this->invAllies[$alliance_name]["corps"][$corp_name] += 1;
			}

			$ship_name = $ship->getName();
			if (!isset($this->invShips[$ship_name])) {
				$this->invShips[$ship_name] = 1;
			} else {
				$this->invShips[$ship_name] += 1;
			}
			if (in_array($alliance->getID(), config::get('cfg_allianceid'))) {
				$this->ownKill = true;
			} else if (in_array($corp->getID(), config::get('cfg_corpid'))) {
				$this->ownKill = true;
			} else if (in_array($inv->getPilotID(), config::get('cfg_pilotid'))) {
				$this->ownKill = true;
			}


			if (!$this->nolimit && $i > $invlimit) {
				if ($i == $invlimit + 1) {
					$smarty->assign('limited', true);
					$smarty->assign('moreInvolved',
							$this->kill->getInvolvedPartyCount() - $invlimit);
					$smarty->assign('unlimitURL',
							'?'.htmlentities($_SERVER['QUERY_STRING'])
							.'&amp;nolimit');
				}

				// include the final blow pilot
				if (!config::get('kd_showbox')
						|| $inv->getPilotID() != $this->kill->getFBPilotID()) {
					continue;
				}
			}
			$pilot = Pilot::getByID($inv->getPilotID());
			$weapon = Item::getByID($inv->getWeaponID());

			$this->involved[$i]['shipImage'] = $ship->getImage(64);
			$this->involved[$i]['shipName'] = $ship->getName();
			$this->involved[$i]['shipID'] = $ship->getID();
			if($ship->getID()) {
				$this->involved[$i]['shipURL'] = edkURI::page('invtype', $ship->getID());
				$this->involved[$i]['shipClass'] = $ship->getClass()->getName();
			} else {
				$this->involved[$i]['shipURL'] = false;
				$this->involved[$i]['shipClass'] = false;
			}

			$this->involved[$i]['corpURL'] =
					edkURI::build(array('a', 'corp_detail', true),
							array('crp_id', $corp->getID(), true));
			$this->involved[$i]['corpName'] = $corp->getName();

			if($alliance && strcasecmp($alliance->getName(), "None") != 0) {
				$this->involved[$i]['alliURL'] =
						edkURI::build(array('a', 'alliance_detail', true),
								array('all_id', $alliance->getID(), true));
			} else {
				$this->involved[$i]['alliURL'] = false;
			}
			$this->involved[$i]['alliName'] = $alliance->getName();
			$this->involved[$i]['damageDone'] = $inv->getDamageDone();

			//detects NPC type things and runs a few conversions (Rats, Towers, Bubbles)
			$tpilot = $pilot->getName();
			if (preg_match("/-/", $tpilot)) { // a tower or bubble. But! Since we have placed the corp name in front of the
				// item's name, we need to quickly check which base item it was again.
				$namestart = strripos($tpilot, '-') + 2; //we're interested in the last dash
				$tpilot = substr($tpilot, $namestart);
			}


			if (!$pilot->getID() || $tpilot == $weapon->getName()) {
				$this->involved[$i]['pilotURL'] =
						edkURI::page('invtype', $weapon->getID());
				$this->involved[$i]['pilotName'] = $weapon->getName();
				$this->involved[$i]['secStatus'] = 0;
				$this->involved[$i]['portrait'] = $corp->getPortraitURL(64);
				$this->involved[$i]['externalID'] = $corp->getExternalID(true);

				if ($this->involved[$i]['externalID'] == 0) {
					$fetchExternalIDs[] = $corp->getName();
				}

				$this->involved[$i]['typeID'] = 2; //type number for corporations.

				$this->involved[$i]['pilotURL'] =
						edkURI::page('invtype', $weapon->getID());
				$this->involved[$i]['shipImage'] = imageURL::getURL('Ship',
						$weapon->getID(), 64);
				$this->involved[$i]['shipURL'] = false;
				$this->involved[$i]['shipName'] = $weapon->getName();
				$this->involved[$i]['weaponURL'] = false;
				$this->involved[$i]['weaponID'] = false;
				$this->involved[$i]['weaponName'] = "Unknown";
			} else {
				if ($pilot->getExternalID(true)) {
					$this->involved[$i]['pilotURL'] = edkURI::build(
							array('a', 'pilot_detail', true),
							array('plt_ext_id', $pilot->getExternalID(), true));
				} else {
					$this->involved[$i]['pilotURL'] =
							edkURI::build(array('a', 'pilot_detail', true),
									array('plt_id', $pilot->getID(), true));
				}
				$this->involved[$i]['typeID'] = 1377; //type number for characters.

				$this->involved[$i]['pilotName'] = $pilot->getName();
				$this->involved[$i]['secStatus'] = $inv->getSecStatus();

				$this->involved[$i]['portrait'] = $pilot->getPortraitURL(64);
				$this->involved[$i]['externalID'] = $pilot->getExternalID(true);

				//get the external ID from the pilot class - if not found then add it to a list of pilots
				//and check the api in bulk
				if (!$this->involved[$i]['externalID']) {
					$fetchExternalIDs[] = $pilot->getName();
				}

				if ($weapon->getName() != "Unknown"
						&& $weapon->getName() != $ship->getName()) {
					$this->involved[$i]['weaponName'] = $weapon->getName();
					$this->involved[$i]['weaponID'] = $weapon->getID();
					$this->involved[$i]['weaponURL'] = edkURI::page('invtype', $weapon->getID());
				} else {
					$this->involved[$i]['weaponName'] = "Unknown";
				}
			}


			if (!$this->finalblow && ($pilot->getID()
					&& $pilot->getID() == $this->kill->getFBPilotID()
					|| $this->kill->getInvolvedPartyCount() == 1)) {
				$this->involved[$i]['finalBlow'] = true;
				$this->finalblow = $this->involved[$i];
				// If we're only here to get the final blow box details then remove this pilot.
				if (!$this->nolimit && $i > $invlimit && $i == $invlimit + 1)
						array_pop($this->involved);
			} else {
				$this->involved[$i]['finalBlow'] = false;
			}
			++$i;
		}

		//prod CCP for the entire list of names
		if (count($fetchExternalIDs) > 0) {
			$names = new API_NametoID();
			$names->setNames(implode(',', $fetchExternalIDs));
			$names->fetchXML();
			$nameIDPair = $names->getNameData();

			//fill in the pilot external IDs.. could potentially be slow
			//but it beats the alternative. Do nothing if no names need loading.
			if (count($nameIDPair) > 0) {
				foreach ($nameIDPair as $idpair) {
					//store the IDs
					foreach ($this->kill->getInvolved() as $inv) {
						$pilot = Cacheable::factory('Pilot', $inv->getPilotID());
						$corp = Cacheable::factory('Corporation', $inv->getCorpID());

						if ($idpair['name'] == $corp->getName()) {
							$corp->setExternalID($idpair['characterID']);
						} else if ($idpair['name'] == $pilot->getName()) {
							$pilot->setCharacterID($idpair['characterID']);
						}
					}

					//as we've already populated the structures for the template
					//we need to quickly retrofit it.
					foreach ($this->involved as $inv) {
						$pname = $inv['pilotName'];
						$cname = $inv['corpName'];

						if ($cname == $idpair['name']) {
							$inv['externalID'] = $idpair['characterID'];
						} else if ($pname == $idpair['name']) {
							$inv['externalID'] = $idpair['characterID'];
						}
					}
				}
			}
		}
	}

	/**
	 * Return HTML for the summary of involved parties.
	 * @global Smarty $smarty
	 * @return string HTML for the summary of involved parties.
	 */
	function involvedSummary()
	{
		global $smarty;
		$smarty->assignByRef('invAllies', $this->invAllies);
		$smarty->assignByRef('invShips', $this->invShips);
		$smarty->assign('alliesCount', count($this->invAllies));
		$smarty->assign('kill', $this->ownKill);
		$smarty->assign('involvedPartyCount', $this->kill->getInvolvedPartyCount());
		$smarty->assign('showext', config::get('kd_showext'));

		return $smarty->fetch(get_tpl('kill_detail_inv_sum'));
	}

	/**
	 * Return HTML for the list of involved parties.
	 * @global Smarty $smarty
	 * @return string HTML for the list of involved parties.
	 */
	function involved()
	{
		global $smarty;
		$smarty->assignByRef('involved', $this->involved);
		return $smarty->fetch(get_tpl('kill_detail_inv'));
	}

	/**
	 * Return HTML for the top of the kill details page.
	 *
	 * Used to provide a two column layout.
	 *
	 * @global Smarty $smarty
	 * @return string HTML for the top of the kill details page.
	 */
	function top()
	{
		global $smarty;
		$smarty->assign('kd_col', 'start');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}

	/**
	 * Return HTML for the middle of the kill details page.
	 *
	 * Used to provide a two column layout.
	 *
	 * @global Smarty $smarty
	 * @return string HTML for the middle of the kill details page.
	 */
	function middle()
	{
		global $smarty;
		$smarty->assign('kd_col', 'middle');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}

	/**
	 * Return HTML for the bottom of the kill details page.
	 *
	 * Used to provide a two column layout.
	 *
	 * @global Smarty $smarty
	 * @return string HTML for the bottom of the kill details page.
	 */
	function bottom()
	{
		global $smarty;
		$smarty->assign('kd_col', 'bottom');
		return $smarty->fetch(get_tpl('kill_detail_layout'));
	}

	/**
	 * Return HTML to describe the victim
	 *
	 * @global Smarty $smarty
	 * @return string HTML to describe the victim
	 */
	function victim()
	{
		global $smarty;
		$smarty->assign('killID', $this->kill->getID());
		$plt = new Pilot($this->kill->getVictimID());
		$item = new dogma($this->kill->getVictimShip()->getID());
		// itt_cat = 6 for ships. Assume != 6 is a structure.
		if ($item->get('itt_cat') != 6) {
			$corp = new Corporation($this->kill->getVictimCorpID());
			$smarty->assign('victimPortrait', $corp->getPortraitURL(64));
			$smarty->assign('victimExtID', 0);
			$smarty->assign('victimURL', edkURI::page('invtype',
					$item->getID()));
		} else {
			$smarty->assign('victimPortrait', $plt->getPortraitURL(64));
			$smarty->assign('victimExtID', $plt->getExternalID());
			$smarty->assign('victimURL', edkURI::page('pilot_detail',
					$this->kill->getVictimID(), 'plt_id'));
		}
		$smarty->assign('victimName', $this->kill->getVictimName());
		$smarty->assign('victimCorpURL', edkURI::page('corp_detail',
				$this->kill->getVictimCorpID(), 'crp_id'));
		$smarty->assign('victimCorpName', $this->kill->getVictimCorpName());
		$smarty->assign('victimAllianceURL', edkURI::page('alliance_detail',
				$this->kill->getVictimAllianceID(), 'all_id'));
		$smarty->assign('victimAllianceName',
				$this->kill->getVictimAllianceName());
		$smarty->assign('victimDamageTaken', $this->kill->getDamageTaken());

		return $smarty->fetch(get_tpl('kill_detail_victim'));
	}

	/**
	 * Return HTML for comments on this kill.
	 *
	 * @global Smarty $smarty
	 * @return string HTML for comments on this kill
	 */
	function comments()
	{
		if (config::get('comments')) {
			$this->page->addOnLoad("xajax_getComments({$this->kll_id});");
			$comments = new Comments(0);

			global $smarty;
			$smarty->assignByRef('page', $this->page);
			$smarty->assign("kll_id", $this->kll_id);

			return $this->commenthtml.$comments->getComments();
		}
	}

	/**
	 * Returns HTML for items dropped or destroyed.
	 * @global Smarty $smarty
	 * @return string HTML for items dropped or destroyed.
	 */
	function itemsLost()
	{
		global $smarty;

		if (config::get('item_values')) {
			$smarty->assign('item_values', 'true');
		}
		// preparing slot layout
		$slot_array = array();

		$slot_array[1] = array(
			'img' => 'icon08_11.png',
			'text' => 'Fitted - High slot',
			'items' => array()
		);

		$slot_array[2] = array(
			'img' => 'icon08_10.png',
			'text' => 'Fitted - Mid slot',
			'items' => array()
		);

		$slot_array[3] = array(
			'img' => 'icon08_09.png',
			'text' => 'Fitted - Low slot',
			'items' => array()
		);

		$slot_array[5] = array(
			'img' => 'icon68_01.png',
			'text' => 'Fitted - Rig slot',
			'items' => array()
		);

		$slot_array[7] = array(
			'img' => 'icon76_04.png',
			'text' => 'Fitted - Subsystems',
			'items' => array()
		);

		$slot_array[6] = array(
			'img' => 'icon02_10.png',
			'text' => 'Drone bay',
			'items' => array()
		);

		$slot_array[4] = array(
			'img' => 'icon03_14.png',
			'text' => 'Cargo Bay',
			'items' => array()
		);

		$slot_array[8] = array(
			'img' => 'icon03_14.png',
			'text' => 'Implants',
			'items' => array()
		);
		$smarty->assignByRef('slots', $slot_array);

		$smarty->assignByRef('destroyed', $this->dest_array);
		$smarty->assignByRef('dropped', $this->drop_array);

		if ($this->totalValue >= 0) {
			$Formatted = number_format($this->totalValue, 2);
		}

		// Get Ship Value
		$this->ShipValue = $this->kill->getVictimShip()->getPrice();

		if (config::get('kd_droptototal')) {
			$this->totalValue += $this->dropvalue;
		}

		$TotalLoss = number_format($this->totalValue + $this->ShipValue, 2);
		$this->ShipValue = number_format($this->ShipValue, 2);
		$this->dropvalue = number_format($this->dropvalue, 2);
		$this->bp_value = number_format($this->bp_value, 2);

		$smarty->assign('itemValue', $Formatted);
		$smarty->assign('dropValue', $this->dropvalue);
		$smarty->assign('shipValue', $this->ShipValue);
		$smarty->assign('totalLoss', $TotalLoss);
		$smarty->assign('BPOValue', $this->bp_value);

		return $smarty->fetch(get_tpl('kill_detail_items_lost'));
	}

	/**
	 * Return HTML to describe the victim's ship
	 *
	 * @global Smarty $smarty
	 * @return string HTML to describe the victim's ship
	 */
	function victimShip()
	{
		global $smarty;
		// Ship details
		$ship = $this->kill->getVictimShip();
		$shipclass = $ship->getClass();

		$smarty->assign('victimShipImage', $ship->getImage(64));
		$smarty->assign('victimShipTechLevel', $ship->getTechLevel());
		$smarty->assign('victimShipIsFaction', $ship->isFaction());
		$smarty->assign('victimShipName', $ship->getName());
		$smarty->assign('victimShipID', $ship->getID());
		$smarty->assign('victimShipURL', edkURI::page('invtype', $ship->getID()));
		$smarty->assign('victimShipClassName', $shipclass->getName());
		if ($this->page->isAdmin()) $smarty->assign('ship', $ship);

		$ssc = new dogma($ship->getID());

		$smarty->assignByRef('ssc', $ssc);

		if ($this->kill->isClassified()) {
			//Admin is able to see classified Systems
			if ($this->page->isAdmin()) {
				$smarty->assign('systemID', $this->kill->getSystem()->getID());
				$smarty->assign('system', $this->kill->getSystem()->getName()
						.' (Classified)');
				$smarty->assign('systemURL', KB_HOST
						."/?a=system_detail&amp;sys_id="
						.$this->kill->getSystem()->getID());
				$smarty->assign('systemSecurity',
						$this->kill->getSystem()->getSecurity(true));
			} else {
				$smarty->assign('system', 'Classified');
				$smarty->assign('systemURL', "");
				$smarty->assign('systemSecurity', '0.0');
			}
		} else {
			$smarty->assign('systemID', $this->kill->getSystem()->getID());
			$smarty->assign('system', $this->kill->getSystem()->getName());
			$smarty->assign('systemURL',
					edkURI::build(
					array('a', 'system_detail', true),
					array('sys_id', $this->kill->getSystem()->getID(), true)));
			$smarty->assign('systemSecurity',
					$this->kill->getSystem()->getSecurity(true));
		}

		$smarty->assign('timeStamp', $this->kill->getTimeStamp());
		$smarty->assign('victimShipImg', $ship->getImage(64));

		$smarty->assign('totalLoss', number_format($this->kill->getISKLoss()));
		return $smarty->fetch(get_tpl('kill_detail_victim_ship'));
	}

	/**
	 * Return HTML to describe the victim's fitting
	 *
	 * @global Smarty $smarty
	 * @return string HTML to describe the victim's fitting
	 */
	function fitting()
	{
		global $smarty;

		if (is_array($this->fitting_array[1])) {
			foreach ($this->fitting_array[1] as $array_rowh) {
				$sort_by_nameh["groupID"][] = $array_rowh["groupID"];
			}

			array_multisort($sort_by_nameh["groupID"], SORT_ASC,
					$this->fitting_array[1]);
		}

		if (is_array($this->fitting_array[2])) {
			foreach ($this->fitting_array[2] as $array_rowm) {
				$sort_by_namem["groupID"][] = $array_rowm["groupID"];
			}

			array_multisort($sort_by_namem["groupID"], SORT_ASC,
					$this->fitting_array[2]);
		}

		if (is_array($this->fitting_array[3])) {
			foreach ($this->fitting_array[3] as $array_rowl) {
				$sort_by_namel["groupID"][] = $array_rowl["Name"];
			}

			array_multisort($sort_by_namel["groupID"], SORT_ASC,
					$this->fitting_array[3]);
		}

		if (is_array($this->fitting_array[5])) {
			foreach ($this->fitting_array[5] as $array_rowr) {
				$sort_by_namer["Name"][] = $array_rowr["Name"];
			}

			array_multisort($sort_by_namer["Name"], SORT_ASC,
					$this->fitting_array[5]);
		}

		if (is_array($this->fitting_array[7])) {
			foreach ($this->fitting_array[7] as $array_rowr) {
				$sort_by_namer["groupID"][] = $array_rowr["groupID"];
			}

			array_multisort($sort_by_namer["groupID"], SORT_ASC,
					$this->fitting_array[7]);
		}

		//Fitting - KE, sort the fitted items into name order, so that several of the same item apear next to each other. -end

		$length = count($this->ammo_array[1]);

		$temp = array();

		if (is_array($this->fitting_array[1])) {
			$hiammo = array();

			foreach ($this->fitting_array[1] as $highfit) {
				$group = $highfit["groupID"];
				$size = $highfit["chargeSize"];

				if ($group
						== 483						  // Modulated Deep Core Miner II, Modulated Strip Miner II and Modulated Deep Core Strip Miner II
						|| $group == 53					 // Laser Turrets
						|| $group == 55					 // Projectile Turrets
						|| $group == 74					 // Hybrid Turrets
						|| ($group >= 506 && $group <= 511) // Some Missile Lauchers
						|| $group == 481					// Probe Launchers
						|| $group == 899					// Warp Disruption Field Generator I
						|| $group == 771					// Heavy Assault Missile Launchers
						|| $group == 589					// Interdiction Sphere Lauchers
						|| $group == 524					// Citadel Torpedo Launchers
				) {
					$found = 0;

					if ($group == 511) {
						$group = 509;
					} // Assault Missile Lauchers uses same ammo as Standard Missile Lauchers

					if (is_array($this->ammo_array[1])) {
						$i = 0;

						while (!($found) && $i < $length) {
							$temp = array_shift($this->ammo_array[1]);

							if (($temp["usedgroupID"] == $group)
									&& ($temp["size"] == $size)) {
								$hiammo[] = array('type' => $temp["Icon"]	);

								$found = 1;
							}

							$this->ammo_array[1][] = $temp;
							$i++;
						}
					}

					if (!($found)) {
						$hiammo[] = array('type' => "<img src='".IMG_URL
									."/items/24_24/icon09_13.png' alt='' />");
					}
				} else {
					$hiammo[] = array('type' => $smarty->fetch(get_tpl('blank')));
				}
			}
		}

		$length = count($this->ammo_array[2]);

		if (is_array($this->fitting_array[2])) {
			$midammo = array();

			foreach ($this->fitting_array[2] as $midfit) {
				$group = $midfit["groupID"];

				if ($group == 76 // Capacitor Boosters
						|| $group == 208 // Remote Sensor Dampeners
						|| $group == 212 // Sensor Boosters
						|| $group == 291 // Tracking Disruptors
						|| $group == 213 // Tracking Computers
						|| $group == 209 // Tracking Links
						|| $group == 290 // Remote Sensor Boosters
				) {
					$found = 0;

					if (is_array($this->ammo_array[2])) {
						$i = 0;

						while (!$found && $i < $length) {
							$temp = array_shift($this->ammo_array[2]);

							if ($temp["usedgroupID"] == $group) {
								$midammo[] = array('type' => $temp["Icon"]);

								$found = 1;
							}

							$this->ammo_array[2][] = $temp;
							$i++;
						}
					}

					if (!$found) {
						$midammo[] = array('type' => "<img src='".IMG_URL
									."/items/24_24/icon09_13.png' alt='' />");
					}
				} else {
					$midammo[] = array('type' => $smarty->fetch(get_tpl('blank')));
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

		$smarty->assign('victimShipBigImage',
				$this->kill->getVictimShip()->getImage(256));

		if ($this->kill->getExternalID() != 0) {
			$this->verification = true;
			$smarty->assign('verify_id', $this->kill->getExternalID());
		} else {
			$this->verification = false;
		}
		$smarty->assign('verify_yesno', $this->verification);

		//get the actual slot count for each vessel - for the fitting panel
		$dogma = Cacheable::factory('dogma',
				$this->kill->getVictimShipExternalID());
		$lowcount = (int) $dogma->attrib['lowSlots']['value'];
		$medcount = (int) $dogma->attrib['medSlots']['value'];
		$hicount = (int) $dogma->attrib['hiSlots']['value'];
		$rigcount = (int) $dogma->attrib['rigSlots']['value'];

		$subcount = count($this->fitting_array[7]);

		//This code counts the slots granted by subsystem modules for the fitting panel
		if ($subcount > 0) {
			foreach ($this->fitting_array[7] as $subfit) {
				$lookupRef = $subfit["itemID"];
				$sql = 'SELECT `attributeID`, `value` FROM `kb3_dgmtypeattributes` WHERE '.
						'`attributeID` IN (1374, 1375, 1376) AND `typeID` = '.$lookupRef.';';
				$qry = DBFactory::getDBQuery();
				$qry->execute($sql);
				while ($row = $qry->getRow()) {
					switch ($row["attributeID"]) {
						case '1374': {
								$hicount += $row["value"];
								break;
							}
						case '1375': {
								$medcount += $row["value"];
								break;
							}
						case '1376': {
								$lowcount += $row["value"];
								break;
							}
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

	/**
	 * Return HTML to describe the final blow and top damage dealers
	 *
	 * @global Smarty $smarty
	 * @return string HTML to describe the final blow and top damage dealers
	 */
	function damageBox()
	{
		global $smarty;
		if (!config::get('kd_showbox')) {
			return '';
		}
		$maxdamage = -1;
		foreach($this->involved as $inv) {
			if($inv['damageDone'] > $maxdamage) {
				$maxdamage = $inv['damageDone'];
				$topdamage = $inv;
			}
		}

		$smarty->assign('topdamage', array($topdamage));
		$smarty->assign('finalblow', $this->finalblow);

		return $smarty->fetch(get_tpl('kill_detail_damage_box'));
	}

	function menuSetup()
	{
		$this->addMenuItem("caption", "View");
		$this->addMenuItem("link", "Killmail", edkURI::page(
				'kill_mail', $this->kill->getID(), 'kll_id'), 0, 0,
				"sndReq('".edkURI::page(
						'kill_mail', $this->kill->getID(), 'kll_id')
				."');ReverseContentDisplay('popup')");

		if (config::get('kd_EFT')) {
			$this->addMenuItem("link", "EFT Fitting", edkURI::page(
					'eft_fitting', $this->kill->getID(), 'kll_id'), 0, 0,
					"sndReq('".edkURI::page(
							'eft_fitting', $this->kill->getID(),'kll_id')
					."');ReverseContentDisplay('popup')");
			$this->addMenuItem("link", "EvE Fitting", edkURI::page(
					'eve_fitting', $this->kill->getID(), 'kll_id'));
		}

		if ($this->kill->relatedKillCount() > 1
				|| $this->kill->relatedLossCount() > 1
				|| ((config::get('cfg_allianceid')
						|| config::get('cfg_corpid')
						|| config::get('cfg_pilotid'))
				&& $this->kill->relatedKillCount()
						+ $this->kill->relatedLossCount() > 1)) {
			$this->addMenuItem("link", "Related kills ("
					.$this->kill->relatedKillCount()."/"
					.$this->kill->relatedLossCount().")",
					edkURI::build(array('a', 'kill_related', true),
							array('kll_id', $this->kill->getID(), true)));
		}

		if ($this->page->isAdmin()) {
			$this->addMenuItem("caption", "Admin");
			$this->addMenuItem("link", "Delete", edkURI::page(
					'admin_kill_delete', $this->kill->getID(), 'kll_id'), 0, 0,
					"openWindow('".edkURI::page(
						'admin_kill_delete', $this->kill->getID(), 'kll_id')
					."', null, 420, 300, '' );");

			if (isset($_GET['view']) && $_GET['view'] == 'FixSlot') {
				$this->addMenuItem("link", "Adjust Values", edkURI::page(
						'kill_detail', $this->kill->getID(), 'kll_id'));
			} else {
				$url = edkURI::build(
						array('kll_id', $this->kill->getID(), true),
						array('view', 'FixSlot', false));
				$this->addMenuItem("link", "Fix Slots", $url);
			}
		}
		return "";
	}

	/**
	 * Build the menu.
	 *
	 *  Add all preset options to the menu.
	 *
	 * @return string HTML for the menus
	 */
	function menu()
	{
		$menubox = new Box("Menu");
		$menubox->setIcon("menu-item.gif");
		foreach ($this->menuOptions as $options) {
			call_user_func_array(array($menubox, 'addOption'), $options);
//			if(isset($options[2]))
//				$menubox->addOption($options[0],$options[1], $options[2]);
//			else
//				$menubox->addOption($options[0],$options[1]);
		}

		return $menubox->generate();
	}

	/**
	 * Returns HTML for the points for this kill
	 *
	 * @return string HTML for the points for this kill
	 */
	function points()
	{
		if (!config::get('kill_points')) return '';

		$scorebox = new Box("Points");
		$scorebox->addOption("points", $this->kill->getKillPoints());
		return $scorebox->generate();
	}

	/**
	 * Returns HTML for the map where this kill took place
	 *
	 * @return string HTML for the map where this kill took place
	 */
	function map()
	{
		//Admin is able to see classsified systems
		if ((!$this->kill->isClassified()) || ($this->page->isAdmin())) {
			$mapbox = new Box("Map");
			if (IS_IGB) {
				$mapbox->addOption("img", imageURL::getURL('map',
						$this->kill->getSystem()->getID(), 145),
						"javascript:CCPEVE.showInfo(3, "
						.$this->kill->getSystem()->getRegionID().")");
				$mapbox->addOption("img", imageURL::getURL('region',
						$this->kill->getSystem()->getID(), 145),
						"javascript:CCPEVE.showInfo(4, "
						.$this->kill->getSystem()->getConstellationID().")");
				$mapbox->addOption("img", imageURL::getURL('cons',
						$this->kill->getSystem()->getID(), 145),
						"javascript:CCPEVE.showInfo(5, "
						.$this->kill->getSystem()->getExternalID().")");
			} else {
				$mapbox->addOption("img", imageURL::getURL('map',
						$this->kill->getSystem()->getID(), 145));
				$mapbox->addOption("img", imageURL::getURL('region',
						$this->kill->getSystem()->getID(), 145));
				$mapbox->addOption("img", imageURL::getURL('cons',
						$this->kill->getSystem()->getID(), 145));
			}
			return $mapbox->generate();
		}
		return '';
	}

	/**
	 * Add an item to the menu in standard box format.
	 *
	 *  Only links need all 3 attributes
	 * @param string $type Types can be caption, img, link, points.
	 * @param string $name The name to display.
	 * @param string $url Only needed for URLs.
	 */
	function addMenuItem($type, $name, $url = '')
	{
		$this->menuOptions[] = func_get_args();
	}

	/**
	 * Update the stored value of an item and the total value of this kill.
	 *
	 *  Input values are taken from the query string.
	 */
	private function updatePrices()
	{
		if (config::get('item_values')) {
			if (isset($_POST['submit']) && $_POST['submit'] == 'UpdateValue') {
				// Send new value for item to the database
				$qry = DBFactory::getDBQuery();
				$qry->autocommit(false);
				if (isset($_POST['SID'])) {
					$SID = intval($_POST['SID']);
					$Val = preg_replace('/[^0-9]/', '', $_POST[$SID]);
					$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$SID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
					Ship::delCache($this->kill->getVictimShip());
				} else {
					$IID = intval($_POST['IID']);
					$Val = preg_replace('/[^0-9]/', '', $_POST[$IID]);
					$qry->execute("INSERT INTO kb3_item_price (typeID, price) VALUES ('".$IID."', '".$Val."') ON DUPLICATE KEY UPDATE price = '".$Val."'");
					Item::delCache(Item::getByID($IID));
				}
				Kill::delCache($this->kill);
				$this->kill = Kill::getByID($this->kill->getID());
				$this->kill->calculateISKLoss(true);
				$qry->autocommit(true);
			}
		}
	}

	private function fixSlots()
	{
		global $smarty;
		if (isset($_GET['view']) && $_GET['view'] == 'FixSlot') {
			$smarty->assign('fixSlot', 'true');
		}

		$smarty->assign('admin', 'true');

		if (isset($_POST['submit']) && $_POST['submit'] == 'UpdateSlot') {
			$IID = (int) $_POST['IID'];
			$KID = (int) $_POST['KID'];
			$val = (int) $_POST[$IID];
			$table = ($_POST['TYPE'] == 'dropped' ? 'dropped' : 'destroyed');
			$old = (int) $_POST['OLDSLOT'];
			$qry = DBFactory::getDBQuery();
			$qry->execute("UPDATE kb3_items_".$table." SET itd_itl_id ='".$val."' WHERE itd_itm_id=".$IID
					." AND itd_kll_id = ".$KID." AND itd_itl_id = ".$old);
		}
	}

	/**
	 * Returns HTML describing where this killmail was sourced from.
	 * @global Smarty $smarty
	 * @return string HTML describing where this killmail was sourced from.
	 */
	public function source()
	{
		global $smarty;
		$qry = DBFactory::getDBQuery();
		$sql = "SELECT log_ip_address, log_timestamp FROM kb3_log WHERE"
				." log_kll_id = ".$this->kll_id;
		$qry->execute($sql);
		if (!$row = $qry->getRow()) {
			return "";
		}
		$source = $row['log_ip_address'];
		$posteddate = $row['log_timestamp'];

		if (preg_match("/^\d+/", $source)
				|| preg_match("/^IP/", $source)) {
			$type = "IP";
			$source = substr($source, 3);
			// No posting IPs publicly.
			if (!$this->page->isAdmin()) {
				$source = "";
			}
		} else if (preg_match("/^API/", $source)) {
			$type = "API";
			$source = $this->kill->getExternalID();
		} else if (preg_match("/^http/", $source)) {
			$type = "URL";
		} else if (preg_match("/^ID:http/", $source)) {
			$type = "URL";
			$source = substr($source, 3);
		} else {
			$type = "unknown";
		}

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