<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList extends TopList_Base
{}

class TopKillsList extends TopList_Kills
{}

class TopCorpKillsList extends TopList_CorpKills
{}

class TopScoreList extends TopList_Score
{}

class TopLossesList extends TopList_Losses
{}

class TopCorpLossesList extends TopList_CorpLosses
{}

class TopFinalBlowList extends TopList_FinalBlow
{}

class TopDamageDealerList extends TopList_DamageDealer
{}

class TopSoloKillerList extends TopList_SoloKiller
{}

class TopPodKillerList extends TopList_PodKiller
{}

class TopGrieferList extends TopList_Griefer
{}

class TopCapitalShipKillerList extends TopList_CapitalShipKiller
{}

class TopContractKillsList extends TopList_ContractKills
{}

class TopContractScoreList extends TopList_ContractScore
{}

class TopPilotTable extends TopTable_Pilot
{}

class TopCorpTable extends TopTable_Corp
{}

class TopShipList extends TopList_Ship
{}

class TopShipListTable extends TopTable_Ship
{}

class TopWeaponList extends TopList_Weapon
{}

class TopWeaponListTable extends TopTable_Weapon
{}
