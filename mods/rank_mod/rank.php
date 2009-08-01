<?php
require_once('common/includes/class.toplist.php');
require_once('common/includes/class.pilot.php');
require_once('common/includes/class.killlist.php');

class TopWeaponListNoLimit extends TopList
{
    function TopWeaponList()
    {
        $this->TopWeaponListNoLimit();
    }

    function generate()
    {
		$sql = "select count(*) as cnt, ind.ind_wep_id as itm_id, itm.GroupID as itm_grp
				  from kb3_inv_detail ind
				  inner join kb3_invtypes itm on (typeID = ind.ind_wep_id)";

        if ($this->invplt_)
            $sqlbottom .= " and ind.ind_plt_id = ".$this->invplt_->getID();

        if ($this->invcrp_)
            $sqlbottom .= " and ind.ind_crp_id = ".$this->invcrp_->getID();

        if ($this->invall_)
            $sqlbottom .= " and ind.ind_all_id = ".$this->invall_->getID();
		$sqlbottom .= " and itm.volume < '12000'
							  and itm.typeName != 'Unknown'
							  group by ind.ind_wep_id order by 1 desc";
        $this->setSQLTop($sql);
		$this->setSQLBottom($sqlbottom);
    }
}

class TopShipListNoLimit extends TopShipList
{
    function TopShipListNoLimit()
    {
        $this->TopShipList();
    }

    function generate()
    {
        $sql = "select count(1) as cnt, ind.ind_shp_id as shp_id, shp.shp_class as cls_id
              from kb3_inv_detail ind
	      inner join kb3_ships shp on ( shp_id = ind.ind_shp_id )";

        if ($this->invplt_)
            $sqlbottom .= " and ind.ind_plt_id = ".$this->invplt_->getID();

        if ($this->invcrp_)
            $sqlbottom .= " and ind.ind_crp_id = ".$this->invcrp_->getID();

        if ($this->invall_)
            $sqlbottom .= " and ind.ind_all_id = ".$this->invall_->getID();

		$sqlbottom .= " and ind.ind_shp_id != 31
                             and shp.shp_class != 17
                             group by ind.ind_shp_id order by 1 desc";
        $this->setSQLTop($sql);
        $this->setSQLBottom($sqlbottom);
    }
}

function TimeID2Str($time_id)
{
  $div = strtok($time_id," - ");
  switch ($div[1]) {
    case "01": $strtime = "January"; break;
    case "02": $strtime = "February"; break;
    case "03": $strtime = "March"; break;
    case "04": $strtime = "April"; break;
    case "05": $strtime = "May"; break;
    case "06": $strtime = "June"; break;
    case "07": $strtime = "July"; break;
    case "08": $strtime = "August"; break;
    case "09": $strtime = "September"; break;
    case "10": $strtime = "October"; break;
    case "11": $strtime = "November"; break;
    case "12": $strtime = "December"; break;
  }
  $strtime .= " ".$div[0];
  return $strtime;
}

function GetEnabledClasses( &$ship_badges )
{

$shipbadges=array(
array( 'type' => 'frigate', 'name' => 'Frigate', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 0
array( 'type' => 'destroyer', 'name' => 'Destroyer', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 1
array( 'type' => 'cruiser', 'name' => 'Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 2
array( 'type' => 'battlecruiser', 'name' => 'Battlecruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 3	
array( 'type' => 'battleship', 'name' => 'Battleship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 4
array( 'type' => 'capital', 'name' => 'Capital', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 5
array( 'type' => 'industrial', 'name' => 'Industrial', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 6
array( 'type' => 'kamikaze', 'name' => 'Shuttle, Pod & Noobship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)	// 7
);


$ship_sub_badges=array(
array( 'parent' => 0, 'type' => 'assault', 'name' => 'Assault Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 0
array( 'parent' => 0, 'type' => 'interceptor', 'name' => 'Interceptor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 1
array( 'parent' => 0, 'type' => 'covert', 'name' => 'Covert Ops', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 2
array( 'parent' => 0, 'type' => 'electronic', 'name' => 'Electronic Attack Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 3
array( 'parent' => 1, 'type' => 'interdictor', 'name' => 'Interdictor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 4
array( 'parent' => 2, 'type' => 'hac', 'name' => 'Heavy Assault Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 5
array( 'parent' => 2, 'type' => 'hactor', 'name' => 'Heavy Interdictor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 6
array( 'parent' => 2, 'type' => 'logistic', 'name' => 'Logistic Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 7
array( 'parent' => 2, 'type' => 'recon', 'name' => 'Recon Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 8
array( 'parent' => 3, 'type' => 'command', 'name' => 'Command Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 9
array( 'parent' => 4, 'type' => 'blackops', 'name' => 'Black Ops', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 10
array( 'parent' => 4, 'type' => 'marauder', 'name' => 'Marauder', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 11
array( 'parent' => 5, 'type' => 'dread', 'name' => 'Dreadnought', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 12
array( 'parent' => 5, 'type' => 'carrier', 'name' => 'Carrier', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 13
array( 'parent' => 5, 'type' => 'mom', 'name' => 'Mothership', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 14
array( 'parent' => 5, 'type' => 'titan', 'name' => 'Titan', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 15
array( 'parent' => 6, 'type' => 'barge', 'name' => 'Mining Barge', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 16
array( 'parent' => 6, 'type' => 'exhumer', 'name' => 'Exhumer', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 17
array( 'parent' => 6, 'type' => 'transport', 'name' => 'Transport Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 18
array( 'parent' => 6, 'type' => 'capindy', 'name' => 'Cap. Industrial', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 19
array( 'parent' => 7, 'type' => 'noob', 'name' => 'N00bship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)				// 20
);

$enabled = config::get('rankmod_enables');

$ship_badges = array();
$cnt = 0;
$last_parent = -1;
foreach ($ship_sub_badges as $subclass) {echo '<br>'.$subclass['name'];
  if ($subclass['parent'] > $last_parent) {
    $shipbadges[$subclass['parent']]['cnt']=$subclass['parent'];	
    $ship_badges[] = $shipbadges[$subclass['parent']];
    $last_parent = $subclass['parent'];
  }
  if (strpos($enabled, $subclass['name'])) {	echo 's';
    $subclass['cnt'] = $cnt;
    $ship_badges[] = $subclass;
  }
  $cnt++;
}

}

function GetPilotRank($pilot_id, &$killpoints, &$medals, &$ship_badges, &$weaponbadges, &$base_rps, &$bonus_rps, &$rps)
{

$medals=array(
array( 'type' => 'eagle', 'name' => 'Killer', 'cnt' => 0, 'mname' =>'Silver Eagle'),		// 0
array( 'type' => 'redcross', 'name' => 'Scorer', 'cnt' => 0,  'mname' =>'Iron Cross'),		// 1
array( 'type' => 'cross', 'name' => 'Solo Killer', 'cnt' => 0, 'mname' =>'Winged Cross'),	// 2
array( 'type' => 'wing1', 'name' => 'Damagedealer', 'cnt' => 0, 'mname' =>'Diamond Wing'),	// 3	
array( 'type' => 'skull', 'name' => 'Final Blows', 'cnt' => 0, 'mname' =>'Red Skull'),		// 4
array( 'type' => 'globe', 'name' => 'Podkiller', 'cnt' => 0, 'mname' =>'Silver Globe'),		// 5
array( 'type' => 'star', 'name' => 'Griefer', 'cnt' => 0, 'mname' =>'Golden Star'),		// 6
array( 'type' => 'wing2', 'name' => 'ISK Killer', 'cnt' => 0, 'mname' =>'Gold Wing'),		// 7
array( 'type' => 'moon', 'name' => 'Loser', 'cnt' => 0, 'mname' =>'Purple Moon')		// 8
);

$shipbadges=array(
array( 'type' => 'frigate', 'name' => 'Frigate', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 0
array( 'type' => 'destroyer', 'name' => 'Destroyer', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 1
array( 'type' => 'cruiser', 'name' => 'Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 2
array( 'type' => 'battlecruiser', 'name' => 'Battlecruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 3	
array( 'type' => 'battleship', 'name' => 'Battleship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 4
array( 'type' => 'capital', 'name' => 'Capital', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 5
array( 'type' => 'industrial', 'name' => 'Industrial', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 6
array( 'type' => 'kamikaze', 'name' => 'Shuttle, Pod & Noobship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)	// 7
);


$ship_sub_badges=array(
array( 'parent' => 0, 'type' => 'assault', 'name' => 'Assault Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 0
array( 'parent' => 0, 'type' => 'interceptor', 'name' => 'Interceptor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 1
array( 'parent' => 0, 'type' => 'covert', 'name' => 'Covert Ops', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 2
array( 'parent' => 0, 'type' => 'electronic', 'name' => 'Electronic Attack Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 3
array( 'parent' => 1, 'type' => 'interdictor', 'name' => 'Interdictor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 4
array( 'parent' => 2, 'type' => 'hac', 'name' => 'Heavy Assault Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 5
array( 'parent' => 2, 'type' => 'hactor', 'name' => 'Heavy Interdictor', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 6
array( 'parent' => 2, 'type' => 'logistic', 'name' => 'Logistic Cruiser', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 7
array( 'parent' => 2, 'type' => 'recon', 'name' => 'Recon Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 8
array( 'parent' => 3, 'type' => 'command', 'name' => 'Command Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 9
array( 'parent' => 4, 'type' => 'blackops', 'name' => 'Black Ops', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 10
array( 'parent' => 4, 'type' => 'marauder', 'name' => 'Marauder', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 11
array( 'parent' => 5, 'type' => 'dread', 'name' => 'Dreadnought', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 12
array( 'parent' => 5, 'type' => 'carrier', 'name' => 'Carrier', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 13
array( 'parent' => 5, 'type' => 'mom', 'name' => 'Mothership', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 14
array( 'parent' => 5, 'type' => 'titan', 'name' => 'Titan', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 15
array( 'parent' => 6, 'type' => 'barge', 'name' => 'Mining Barge', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 16
array( 'parent' => 6, 'type' => 'exhumer', 'name' => 'Exhumer', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),				// 17
array( 'parent' => 6, 'type' => 'transport', 'name' => 'Transport Ship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),			// 18
array( 'parent' => 6, 'type' => 'capindy', 'name' => 'Cap. Industrial', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 19
array( 'parent' => 7, 'type' => 'noob', 'name' => 'N00bship', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)				// 20
);

$weaponbadges=array(
array( 'type' => 'hybrid', 'name' => 'Hybrid Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 0
array( 'type' => 'laser', 'name' => 'Laser Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 1
array( 'type' => 'projectile', 'name' => 'Projectile Turret', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),	// 2
array( 'type' => 'missile', 'name' => 'Missile Launcher', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 3
array( 'type' => 'ew', 'name' => 'Electronic Warfare', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0),		// 4
array( 'type' => 'drone', 'name' => 'Drone', 'cnt' => 0, 'icon' => 0, 'ribbon' => 0, 'class' =>0)		// 5
);

$pilot = new Pilot($pilot_id);

$klist = new KillList();
$klist->addInvolvedPilot($pilot);
$klist->getAllKills();

$killpoints = $klist->getPoints();

// BEGIN awarded medals code

$qry = new DBQuery();
$query = "SELECT COUNT(*) as cnt, rank.med_id as med_id, rank.time_id as time_id 
	  FROM `kb3_rank_medals` rank 
	  WHERE rank.plt_id =".$pilot_id." 
	  AND rank.med_site ='".KB_SITE."'
	  GROUP BY rank.med_id 
	  ORDER BY 2 ASC";
$qry->execute($query);
while ($row = $qry->getRow()) {
  $medals[$row['med_id']]['cnt']=$row['cnt'];
}

// END awarded medals code

// BEGIN ship ribbons code


$shiplist = new TopShipListNoLimit();
$shiplist->addInvolvedPilot($pilot);
$shiplist->generate();


// modified the topshiplist to show also the class (1 query = all data) XD

$excludes=config::get('rankmod_enables');
while ($row = $shiplist->getRow())
{
  switch ($row['cls_id'])
  {
	case 1: $shipbadges[4]['cnt']+=$row['cnt']; break;
	case 2: $shipbadges[7]['cnt']+=$row['cnt']; break;
	case 3: if (strpos($excludes, 'N00bship')) { $ship_sub_badges[20]['cnt']+=$row['cnt']; } else { $shipbadges[7]['cnt']+=$row['cnt']; } break;
	case 4: $shipbadges[0]['cnt']+=$row['cnt']; break;
	case 5: if (strpos($excludes, 'Interceptor')) { $ship_sub_badges[1]['cnt']+=$row['cnt']; } else { $shipbadges[0]['cnt']+=$row['cnt']; } break;
	case 6: if (strpos($excludes, 'Assault Ship')) { $ship_sub_badges[0]['cnt']+=$row['cnt']; } else { $shipbadges[0]['cnt']+=$row['cnt']; } break;
	case 7: $shipbadges[6]['cnt']+=$row['cnt']; break;
	case 8: $shipbadges[2]['cnt']+=$row['cnt']; break;
	case 9: if (strpos($excludes, 'Heavy Assault Cruiser')) { $ship_sub_badges[5]['cnt']+=$row['cnt']; } else { $shipbadges[2]['cnt']+=$row['cnt']; } break;
	case 10: $shipbadges[3]['cnt']+=$row['cnt']; break;
	case 11: $shipbadges[7]['cnt']+=$row['cnt']; break;
	case 12: if (strpos($excludes, 'Mining Barge')) { $ship_sub_badges[16]['cnt']+=$row['cnt']; } else { $shipbadges[6]['cnt']+=$row['cnt']; } break;
	case 13: if (strpos($excludes, 'Logistic Cruiser')) { $ship_sub_badges[7]['cnt']+=$row['cnt']; } else { $shipbadges[2]['cnt']+=$row['cnt']; } break;
	case 14: if (strpos($excludes, 'Transport Ship')) { $ship_sub_badges[18]['cnt']+=$row['cnt']; } else { $shipbadges[6]['cnt']+=$row['cnt']; } break;
	case 15: $shipbadges[1]['cnt']+=$row['cnt']; break;
	case 16: if (strpos($excludes, 'Covert Ops')) { $ship_sub_badges[2]['cnt']+=$row['cnt']; } else { $shipbadges[0]['cnt']+=$row['cnt']; } break;
	case 19: if (strpos($excludes, 'Dreadnought')) { $ship_sub_badges[12]['cnt']+=$row['cnt']; } else { $shipbadges[5]['cnt']+=$row['cnt']; } break;
	case 21: if (strpos($excludes, 'Command Ship')) { $ship_sub_badges[9]['cnt']+=$row['cnt']; } else { $shipbadges[3]['cnt']+=$row['cnt']; } break;
	case 22: if (strpos($excludes, 'Exhumer')) { $ship_sub_badges[17]['cnt']+=$row['cnt']; } else { $shipbadges[6]['cnt']+=$row['cnt']; } break;
	case 23: if (strpos($excludes, 'Interdictor')) { $ship_sub_badges[4]['cnt']+=$row['cnt']; } else { $shipbadges[1]['cnt']+=$row['cnt']; } break;
	case 24: if (strpos($excludes, 'Recon Ship')) { $ship_sub_badges[8]['cnt']+=$row['cnt']; } else { $shipbadges[2]['cnt']+=$row['cnt']; } break;
	case 26: if (strpos($excludes, 'Titan')) { $ship_sub_badges[15]['cnt']+=$row['cnt']; } else { $shipbadges[5]['cnt']+=$row['cnt']; } break;
	case 27: if (strpos($excludes, 'Carrier')) { $ship_sub_badges[13]['cnt']+=$row['cnt']; } else { $shipbadges[5]['cnt']+=$row['cnt']; } break;
	case 28: if (strpos($excludes, 'Mothership')) { $ship_sub_badges[14]['cnt']+=$row['cnt']; } else { $shipbadges[5]['cnt']+=$row['cnt']; } break;
	case 29: if (strpos($excludes, 'Cap. Industrial')) { $ship_sub_badges[19]['cnt']+=$row['cnt']; } else { $shipbadges[6]['cnt']+=$row['cnt']; } break;
	case 30: if (strpos($excludes, 'Electronic Attack Ship')) { $ship_sub_badges[3]['cnt']+=$row['cnt']; } else { $shipbadges[0]['cnt']+=$row['cnt']; } break;
	case 31: if (strpos($excludes, 'Heavy Interdictor')) { $ship_sub_badges[6]['cnt']+=$row['cnt']; } else { $shipbadges[2]['cnt']+=$row['cnt']; } break;
	case 32: if (strpos($excludes, 'Black Ops')) { $ship_sub_badges[10]['cnt']+=$row['cnt']; } else { $shipbadges[4]['cnt']+=$row['cnt']; } break;
	case 33: if (strpos($excludes, 'Marauder')) { $ship_sub_badges[11]['cnt']+=$row['cnt']; } else { $shipbadges[4]['cnt']+=$row['cnt']; } break;
  }
}
// END ship ribbons code

// BEGIN weapon ribbons code

$weaponlist = new TopWeaponListNoLimit();
$weaponlist->addInvolvedPilot($pilot);
$weaponlist->generate();
while ($row = $weaponlist->getRow())
{
  $group = $row['itm_grp'];
  if($group == 771
	|| ($group >= 506 && $group <= 511)
	|| $group == 524
	|| ($group >= 384 && $group <= 387)
	|| ($group >= 394 && $group <= 396)
	|| ($group >= 862 && $group <= 864)
	|| $group == 772
	|| $group == 89
	|| $group == 90
	|| $group == 476
  ) { $group = 506; }
  if ($group == 544 
	|| $group == 549
	|| $group == 639
	|| $group == 641
  ) { $group = 100; }
  switch ($group)
  {
	case 53: $weaponbadges[1]['cnt']+=$row['cnt']; break;
	case 55: $weaponbadges[2]['cnt']+=$row['cnt']; break;
	case 74: $weaponbadges[0]['cnt']+=$row['cnt']; break;
	case 506: $weaponbadges[3]['cnt']+=$row['cnt']; break;
	case 100: $weaponbadges[5]['cnt']+=$row['cnt']; break;
	default: $weaponbadges[4]['cnt']+=$row['cnt']; break;
  }
}

// END weapon ribbons code

// BEGIN rank points stuff

$bonus_rps=0;
if(config::get(rankmod_convfactor)) $base_rps=round($killpoints/config::get(rankmod_convfactor));
else $base_rps=0;

// MEDALS STUFF
$rank_medbonus=config::getnumerical('rankmod_bonus');
$rank_purplemalus=config::get('rankmod_purplemalus');
foreach ($medals as $i => $med)
{
  if (($i == 8) && $rank_purplemalus) {
    $bonus_rps-=$rank_medbonus[$i]*$med['cnt'];
  } elseif (($i == 8) && !$rank_purplemalus) {
    $bonus_rps+=$rank_medbonus[$i]*$med['cnt'];
  } else {
    $bonus_rps+=$rank_medbonus[$i]*$med['cnt'];
  }
}

// SHIP RIBBONS
$rank_badges = config::getnumerical('rankmod_badreqs');
$rank_badge_value = config::getnumerical('rankmod_badvalues');
foreach ($shipbadges as $i => $ship)
{
  if ($ship['cnt'] > $rank_badges[$i][0]) {
	$bonus_rps+=$rank_badge_value[$i][0];
	$shipbadges[$i]['icon']=$ship['type'].'_elite';
	$shipbadges[$i]['class']=$ship['name'].' Combat 1st Class';
	$shipbadges[$i]['badge']='Elite '.$ship['name'].' Pilot';
  } elseif ($ship['cnt'] > $rank_badges[$i][1]) {
	$bonus_rps+=$rank_badge_value[$i][1];
	$shipbadges[$i]['icon']=$ship['type'].'_veteran';
	$shipbadges[$i]['class']=$ship['name'].' Combat 2nd Class';
	$shipbadges[$i]['badge']='Veteran '.$ship['name'].' Pilot';
  } elseif ($ship['cnt'] > $rank_badges[$i][2]) {
	$bonus_rps+=$rank_badge_value[$i][2];
	$shipbadges[$i]['icon']=$ship['type'].'_expert';
	$shipbadges[$i]['class']=$ship['name'].' Combat 3rd Class';
	$shipbadges[$i]['badge']='Expert '.$ship['name'].' Pilot';
  }
}

// SHIP SUBCLASS RIBBONS
$rank_badges = config::getnumerical('rankmod_sub_badreqs');
$rank_badge_value = config::getnumerical('rankmod_sub_badvalues');
foreach ($ship_sub_badges as $i => $ship)
{
  if ($ship['cnt'] > $rank_badges[$i][0]) {
	$bonus_rps+=$rank_badge_value[$i][0];
	$ship_sub_badges[$i]['icon']=$ship['type'].'_elite';
	$ship_sub_badges[$i]['class']=$ship['name'].' Combat 1st Class';
	$ship_sub_badges[$i]['badge']='Elite '.$ship['name'].' Pilot';
  } elseif ($ship['cnt'] > $rank_badges[$i][1]) {
	$bonus_rps+=$rank_badge_value[$i][1];
	$ship_sub_badges[$i]['icon']=$ship['type'].'_veteran';
	$ship_sub_badges[$i]['class']=$ship['name'].' Combat 2nd Class';
	$ship_sub_badges[$i]['badge']='Veteran '.$ship['name'].' Pilot';
  } elseif ($ship['cnt'] > $rank_badges[$i][2]) {
	$bonus_rps+=$rank_badge_value[$i][2];
	$ship_sub_badges[$i]['icon']=$ship['type'].'_expert';
	$ship_sub_badges[$i]['class']=$ship['name'].' Combat 3rd Class';
	$ship_sub_badges[$i]['badge']='Expert '.$ship['name'].' Pilot';
  }
}

$ship_badges = array();
$last_parent = -1;
foreach ($ship_sub_badges as $subclass) {
  if ($subclass['parent'] > $last_parent) {
    $ship_badges[] = $shipbadges[$subclass['parent']];
    $last_parent = $subclass['parent'];
  }
  $ship_badges[] = $subclass;
}

// WEAPON RIBBONS
$rank_weap_badges = config::getnumerical('rankmod_weapreqs');
$rank_weap_badge_value = config::getnumerical('rankmod_weapvalues');
foreach ($weaponbadges as $i => $weap)
{
  if ($i == 4 || $i == 3 || $i == 5) { $bottom = 'Operator'; } else { $bottom = 'Gunner';}
  if ($weap['cnt']>$rank_weap_badges[$i][0]) {
	$bonus_rps+=$rank_weap_badge_value[$i][0];
	$weaponbadges[$i]['icon']=$weap['type'].'_elite';
	$weaponbadges[$i]['class']=$weap['name'].' Master 1st Class';
	$weaponbadges[$i]['badge']='Elite '.$weap['name'].' '.$bottom;
  } elseif ($weap['cnt']>$rank_weap_badges[$i][1]) {
	$bonus_rps+=$rank_weap_badge_value[$i][1];
	$weaponbadges[$i]['icon']=$weap['type'].'_veteran';
	$weaponbadges[$i]['class']=$weap['name'].' Master 2nd Class';
	$weaponbadges[$i]['badge']='Veteran '.$weap['name'].' '.$bottom;
  } elseif ($weap['cnt']>$rank_weap_badges[$i][2]) {
	$bonus_rps+=$rank_weap_badge_value[$i][2];
	$weaponbadges[$i]['icon']=$weap['type'].'_expert';
	$weaponbadges[$i]['class']=$weap['name'].' Master 3rd Class';
	$weaponbadges[$i]['badge']='Expert '.$weap['name'].' '.$bottom;
  }
}

$rps=$base_rps+$bonus_rps;
$titles = config::getnumerical('rankmod_titles');
$r_type = config::get('rankmod_rtype');

switch ($r_type) {
	case "Enlisted": $limit = 10; break;
	case "Officer": $limit = 11; break;
	case "Enlisted + Officer": $limit = 21; break;
}

$rank=0;

while($rps>$titles[$rank]['reqrp'] && $rank<$limit)
{
  $rank++;
}

if ($rank!=0) { $rank--; }

return $rank;

}

?>