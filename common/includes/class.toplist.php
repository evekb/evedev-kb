<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

/**
 * @package EDK
 */
class TopList extends TopList_Base
{}

/**
 * @package EDK
 */
class TopKillsList extends TopList_Kills
{}

/**
 * @package EDK
 */
class TopCorpKillsList extends TopList_CorpKills
{}

/**
 * @package EDK
 */
class TopScoreList extends TopList_Score
{}

/**
 * @package EDK
 */
class TopLossesList extends TopList_Losses
{}

/**
 * @package EDK
 */
class TopCorpLossesList extends TopList_CorpLosses
{}

/**
 * @package EDK
 */
class TopFinalBlowList extends TopList_FinalBlow
{}

/**
 * @package EDK
 */
class TopDamageDealerList extends TopList_DamageDealer
{}

/**
 * @package EDK
 */
class TopSoloKillerList extends TopList_SoloKiller
{}

/**
 * @package EDK
 */
class TopPodKillerList extends TopList_PodKiller
{}

/**
 * @package EDK
 */
class TopGrieferList extends TopList_Griefer
{}

/**
 * @package EDK
 */
class TopCapitalShipKillerList extends TopList_CapitalShipKiller
{}

/**
 * @package EDK
 */
class TopContractKillsList extends TopList_ContractKills
{}

/**
 * @package EDK
 */
class TopContractScoreList extends TopList_ContractScore
{}

/**
 * @package EDK
 */
class TopPilotTable extends TopTable_Pilot
{}

/**
 * @package EDK
 */
class TopCorpTable extends TopTable_Corp
{}

/**
 * @package EDK
 */
class TopShipList extends TopList_Ship
{}

/**
 * @package EDK
 */
class TopShipListTable extends TopTable_Ship
{}

/**
 * @package EDK
 */
class TopWeaponList extends TopList_Weapon
{}

/**
 * @package EDK
 */
class TopWeaponListTable extends TopTable_Weapon
{}
