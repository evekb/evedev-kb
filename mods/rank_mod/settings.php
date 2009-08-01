<?php
require_once("common/admin/admin_menu.php");
require_once('common/includes/class.toplist.php');
require_once('mods/rank_mod/settings_defines.php');
require_once('mods/rank_mod/settings_shipclasses.php');

$rank_ver = '0.97c';

// CHANGE TITLES & ABBREVIATIONS

if ($_POST['change'])
{
	$html .= "<b>Titles Changed</b>";
}

// SAVE SETTINGS

if ($_POST['submit'])
{
	// prepare rank show data
	$data .= "-";
	if($_POST['r_ins']) {$data .="rank-";}
	if($_POST['r_med']) {$data .="medal-";}
	if($_POST['r_medrib']) {$data .="nomeds-";}		
	if($_POST['r_bad']) {$data .="badge-";}
	if($_POST['r_case']) {$data .="case-";}
	if($_POST['rkd_base']) {$data .="base-";}
	if($_POST['rkd_bonus']) {$data .="bonus-";}	
	if($_POST['rkd_total']) {$data .="total-";}	
	config::set('rankmod_show', $data);

	// prepare known members data
	$data .= "-";
	if($_POST['k_on']) {$data .="enabled-";}
	if($_POST['k_img']) {$data .="portrait-";}
	if($_POST['k_kll_pnts']) {$data .="score-";}		
	if($_POST['k_dmg_dn']) {$data .="done-";}
	if($_POST['k_dmg_rcd']) {$data .="received-";}
	if($_POST['k_kll_rat']) {$data .="ratio-";}	
	if($_POST['k_eff']) {$data .="efficiency-";}	
	if($_POST['k_lst_sn']) {$data .="last_seen-";}	
	config::set('rankmod_known', $data);

	// set rank base data
	config::set('rankmod_expfactor',$_POST['r_exp']);
	config::set('rankmod_expbase',$_POST['r_base']);
	config::set('rankmod_convfactor',$_POST['r_conv']);
	
	// set medals bonuses
	$r_medval=array();
	foreach ($medals_array as $i => $med) {
		$r_medval[$i]=$_POST['rm_'.$i];
	}
	config::set('rankmod_bonus',$r_medval);
	if($_POST['pm_neg']) {
	  config::set('rankmod_purplemalus',TRUE);
	} else {
	  config::set('rankmod_purplemalus',FALSE);
	}

	// set parent ship bonuses & requirements
	$r_badval=array();
	$r_badreq=array();
	foreach ($ribbon_parent as $i => $class) {
		$r_badreq[$i][0]=$_POST['rsc_'.$i.'_elite'];
		$r_badval[$i][0]=$_POST['rsc_'.$i.'_belite'];
		$r_badreq[$i][1]=$_POST['rsc_'.$i.'_veteran'];
		$r_badval[$i][1]=$_POST['rsc_'.$i.'_bveteran'];
		$r_badreq[$i][2]=$_POST['rsc_'.$i.'_expert'];
		$r_badval[$i][2]=$_POST['rsc_'.$i.'_bexpert'];
	}
	config::set('rankmod_badreqs',$r_badreq);
	config::set('rankmod_badvalues',$r_badval);

	// set subclass ship bonuses & requirements
	$r_sub_badval=array();
	$r_sub_badreq=array();
	$data="-";
	foreach ($ribbon_child as $i => $ship) {
		$class=$ship['class'];
		$r_sub_badreq[$i][0]=$_POST['rsbc_'.$i.'_elite'];
		$r_sub_badval[$i][0]=$_POST['rsbc_'.$i.'_belite'];
		$r_sub_badreq[$i][1]=$_POST['rsbc_'.$i.'_veteran'];
		$r_sub_badval[$i][1]=$_POST['rsbc_'.$i.'_bveteran'];
		$r_sub_badreq[$i][2]=$_POST['rsbc_'.$i.'_expert'];
		$r_sub_badval[$i][2]=$_POST['rsbc_'.$i.'_bexpert'];
		if ($_POST['enable_'.$i]) { $data .=$class."-"; }
	}
	config::set('rankmod_sub_badreqs',$r_sub_badreq);
	config::set('rankmod_sub_badvalues',$r_sub_badval);
	config::set('rankmod_enables',$data);
	
	// set weapon bonuses & requirements
	$r_wbadval=array();
	$r_wbadreq=array();
	foreach ($weapon_ribbons_array as $i => $class) {
		$r_wbadreq[$i][0]=$_POST['rwr_'.$i.'_elite'];
		$r_wbadval[$i][0]=$_POST['rwr_'.$i.'_belite'];
		$r_wbadreq[$i][1]=$_POST['rwr_'.$i.'_veteran'];
		$r_wbadval[$i][1]=$_POST['rwr_'.$i.'_bveteran'];
		$r_wbadreq[$i][2]=$_POST['rwr_'.$i.'_expert'];
		$r_wbadval[$i][2]=$_POST['rwr_'.$i.'_bexpert'];
	}
	config::set('rankmod_weapreqs',$r_wbadreq);
	config::set('rankmod_weapvalues',$r_wbadval);

	// set rank titles & images

	switch($_POST['r_group']) {
		case "Enlisted": $a_s = 0; $a_l = 10; 
		break;
		case "Officer": $a_s = 10; $a_l = 21;
		break;
		case "Enlisted + Officer": $a_s = 0; $a_l = 21;
		break;
	}
	$ranklist=array();
	$blankimg = IMG_URL.'/ranks/insignias/army_en0.png';
	$r = 0;
	$uniserv='enlisted';
	for ($i = $a_s; $i<$a_l; $i++) {
	  $j = $i*2;
	  $k = $j+1;
	  if ($i==10) {$uniserv = 'officer';}
	  if ($uniserv == 'officer') { $m = $i - 10; } else { $m = $i; }
	  if ($_POST['k_title']) {
	    $ranktitle= $_POST['title_'.$r];
	    $rankabbr= $_POST['abbr_'.$r];
	  } else {	
 	    $ranktitle= ${$s_rank_specs[$_POST['r_titleset']]['title']}[$j];
	    $rankabbr= ${$s_rank_specs[$_POST['r_titleset']]['title']}[$k];
	  }
          $imglink = 'ranks/insignias/'.$s_rank_specs[$_POST['r_imageset']][$uniserv].$m.'.png';
  	  $imgtemp = IMG_URL.'/'.$imglink;
	  $smarty->assign('img', $imgtemp);
	  $smarty->assign('icon', $blankimg);
	  $smarty->assign('name', $ranktitle);
       	  $img = $smarty->fetch(get_tpl('icon32'));
	  $ranklist[]=array('title' => $ranktitle, 'abbr' => $rankabbr, 'img' => $img, 'reqrp' => 0, 'reqkp' => 0, 'imglink' => $imglink);
	  $r++;
	}
	for ($i=0; $i<$r; $i++)
	{
	    	$rps = ($i*$_POST['r_base'])+round($ranklist[$i-1]['reqrp']*$_POST['r_exp']);
	    	$kps = $rps*$_POST['r_conv'];
		$ranklist[$i]['reqrp'] = $rps;
		$ranklist[$i]['reqkp'] = $kps;
	}
	config::set('rankmod_titles',$ranklist);

	if($_POST['k_title']) {
	  config::set('rankmod_keep',TRUE);
	} else {
	  config::set('rankmod_keep',FALSE);
	}
	config::set('rankmod_rtype',$_POST['r_group']);
	config::set('rankmod_imageset', $_POST['r_imageset']);
	config::set('rankmod_titleset', $_POST['r_titleset']);

	$html .= "<b>Settings Saved</b>";
}

// MEDAL TABLE CTREATION

if ($_POST['drop_tabs'])
{
  config::set('rank_last_update',0);
  $nqry = new DBNormalQuery();
  $query = 'DROP TABLE `kb3_rank_medals`';
  $nqry->execute($query);
  $html .= "<b>MEDAL TABLE DROPPED - PLEASE RECREATE</b>";
}

if ($_POST['create_tabs'])
{
  $curr_kb_year = kbdate('Y');
  $curr_kb_month = kbdate('m') - 1;
  if ($curr_kb_month == 0) {
    $curr_kb_month = 12;
    $curr_kb_year--;
  }
  $month = $_POST['kb_startm'];
  $year = $_POST['kb_starty'];
  if ($year==$curr_kb_year && $month>$curr_kb_month) {
    $html .= "<b>ERROR: </b><i>Wrong date, check start month!</i><br>";
    $html .= "<b>Tables NOT Created</b>";
  } elseif ($year>$curr_kb_year) {
    $html .= "<b>ERROR: </b><i>Wrong date, check start year!</i><br>";
    $html .= "<b>Tables NOT Created</b>";
  } else {
    $qry = new DBQuery();
    if (!config::get('rank_last_update')) {
      $query = 'CREATE TABLE `kb3_rank_medals` (
	  `med_site` varchar(16) NOT NULL ,	  
	  `plt_id` INT NOT NULL ,
    	  `med_id` INT NOT NULL ,
	  `time_id` TEXT NOT NULL
	  ) TYPE = MYISAM';
      $qry->execute($query);    
      $upd .= "".$curr_kb_year." - ".$curr_kb_month;
      config::set('rank_last_update',$upd);
    } else {
      $nqry = new DBNormalQuery();
      $query = "DELETE FROM kb3_rank_medals WHERE med_site = '".KB_SITE."'";
      $nqry->execute($query);  
      $upd .= "".$curr_kb_year." - ".$curr_kb_month;
      config::set('rank_last_update',$upd);  
    }
    while ($year<=$curr_kb_year) {
      while (($month<=$curr_kb_month && $year==$curr_kb_year) || ($month<=12 && $year!=$curr_kb_year)) {
	  if ($month < 10) { $p_month = "0".$month; } else { $p_month = $month; }
	  $date = "".$year." - ".$p_month;

  	  $list = new TopKillsList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Killer ".$date."<br>";
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '0','".$date."');";
	     $qry->execute($query);
 	  }
	  $list = new TopScoreList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Scorer ".$date."<br>";
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '1','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopSoloKillerList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Solo Killer ".$date."<br>";
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '2','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopDamageDealerList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Damagedealer ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '3','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopFinalBlowList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Final Blower ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '4','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopPodKillerList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Pod Killer ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '5','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopGrieferList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Griefer ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '6','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopCapitalShipKillerList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'kill');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top ISK Killer ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '7','".$date."');";
	     $qry->execute($query);
	  }
	  $list = new TopLossesList();
          $list->setMonth($month);
          $list->setYear($year);
	  $list->setPodsNoobShips(true);
	  involved::load($list,'loss');
	  $list->generate();
	  if ($row = $list->getRow()) {
             $html .= "Top Loser ".$date."<br>";;
	     $query = "INSERT INTO kb3_rank_medals (med_site, plt_id, med_id, time_id) VALUES ('".KB_SITE."', '".$row['plt_id']."', '8','".$date."');";
	     $qry->execute($query);
	  }
	  $month++;
      }
      $year++;
      $month = 1;
    }
    $html .= "<b>Tables Created</b>";
  }
}

$page = new Page("Settings - Rank Mod ".$rank_ver);

$rank_imageset = config::get('rankmod_imageset'); // insignia image set
$rank_titleset = config::get('rankmod_titleset'); // title set
$keep_title = config::get('rankmod_keep'); // remember keep titles

$rank_type = config::get('rankmod_rtype'); // type of ranks (Enlisted, Officer, Enlisted + Officer)
if (!($rank_type)) { $rank_type = 'Officer'; }

$rank_ef = config::get('rankmod_expfactor'); // exp factor (1, 1.2, 1.5, 1.7, 2, 2.5)
if (!($rank_ef)) { $rank_ef = 1.2; }

$rank_base = config::get('rankmod_expbase'); // base value (amount of rank points to go to next level)
if (!($rank_base)) { $rank_base = 25; }

$rank_conv = config::get('rankmod_convfactor'); // conversion factor (every how much killpoints gets a rank point)
if (!($rank_conv)) { $rank_conv = 50; }

$rank_badges = config::getnumerical('rankmod_badreqs'); // requirements for badges level (expert, veteran, elite)
if (!($rank_badges)) { $rank_badges = array(10, 25, 40); }

$rank_bonus = config::getnumerical('rankmod_badvalues'); // value for badges level (expert, veteran, elite)
if (!($rank_bonus)) { $rank_bonus = array(25, 50, 100); }

$rank_weap_badges = config::getnumerical('rankmod_weapreqs'); // requirements for badges level (expert, veteran, elite)
if (!($rank_badges)) { $rank_badges = array(10, 25, 40); }

$rank_weap_bonus = config::getnumerical('rankmod_weapvalues'); // value for badges level (expert, veteran, elite)
if (!($rank_bonus)) { $rank_bonus = array(25, 50, 100); }

$rank_sub_badges = config::getnumerical('rankmod_sub_badreqs'); // requirements for badges level (expert, veteran, elite)
if (!($rank_sub_badges)) { $rank_badges = array(10, 25, 40); }

$rank_sub_bonus = config::getnumerical('rankmod_sub_badvalues'); // value for badges level (expert, veteran, elite)
if (!($rank_sub_bonus)) { $rank_sub_bonus = array(25, 50, 100); }


$rank_medvalues = config::getnumerical('rankmod_bonus'); // bonus for each awarded medal
if (!($rank_medalbonus)) { $rank_medalbonus = 50; }

$rank_show = config::get('rankmod_show'); // Show images in the portrait (rank, medals, badges)
if (!($rank_show)) { $rank_show = "-rank-medal-badge-base-case-bonus-total-nomeds"; }

$rank_known = config::get('rankmod_known'); // Show Known Members Mod
if (!($rank_known)) { $rank_known = "-enabled-portrait-score-done-received-ratio-efficiency-last_seen-"; }

$rank_last_update = config::get('rank_last_update'); // gets last update of the medals table
if (!($rank_last_update)) {$rank_last_update="NEVER - Please check tables"; }

$rank_ttl = config::getnumerical('rankmod_titles'); // titles array (array of arrays containing title,abbr,img,reqrp,reqkp)
if (!($rank_ttl)) { $rank_ttl = array( array('title' => 'SAVE SETTINGS'));}

$rank_purplemalus = config::get('rankmod_purplemalus'); // count purple moon as a bonus or a malus

$rank_subenabled=config::get('rankmod_enables');

$curr_kb_year = kbdate('Y');
$curr_kb_month = kbdate('m') - 1;
if ($curr_kb_month == 0) {
  $curr_kb_month = 12;
  $curr_kb_year--;
}

$html .= "<form id=options name=options method=post action=>";

// BEGIN table options
$html .= "<div class=block-header2>Medal Table Generation</div>";
$html .= "<i>Prepares medal table on the database.</i><br>";
$html .= "<i>This is required to make load faster pilot details page.</i><br>";
$html .= "<i>Please note that saving changes will NOT check tables, use the button here instead!</i><br>";
$html .= "<i>When pressed please wait, because it can take a bit longer, it does 9 querys per month, and 108 per year...</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Select Start Year:</b></td><td>";
$html .= '<select name="kb_starty">';
foreach($kb_year as $kby)
{
	$html .='<option value="'.$kby.'"';
	if($kby == $curr_kb_year)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$kby.'</option>';
}
$html .= "</select></td></tr>";
$html .= "<tr><td width=300><b>Select Start Month:</b></td><td>";
$html .= '<select name="kb_startm">';
foreach($kb_month as $kbm)
{
	$html .='<option value="'.$kbm.'"';
	if($kby == $curr_kb_month)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$kbm.'</option>';
}
$html .= "</select></td></tr>";
$html .= "<tr><td><b>Last Update:</b></td><td>".$rank_last_update."</td></tr>";
$html .= "</table>";
$html .= "<table class=kb-subtable><tr><td width=120><input type=submit name=drop_tabs value=\"Drop Table\"></td><td colspan=3 ><input type=submit name=create_tabs value=\"Check Table\"></td></tr>";
$html .= "</table>";
// END table options

// BEGIN general options
$html .= "<div class=block-header2>General Options</div>";

// Known Members section
$html .= "<i>Add data to rank list and known members list.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Enable Known Members:</b></td><td><input type=checkbox name=k_on id=k_on";
if (strpos($rank_known,"enabled"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Char. Portrait:</b></td><td><input type=checkbox name=k_img id=k_img";
if (strpos($rank_known,"portrait"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Kill Points:</b></td><td><input type=checkbox name=k_kll_pnts id=k_kll_pnts";
if (strpos($rank_known,"score"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Damage Done:</b></td><td><input type=checkbox name=k_dmg_dn id=k_dmg_dn";
if (strpos($rank_known,"done"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Damage Received:</b></td><td><input type=checkbox name=k_dmg_rcd id=k_dmg_rcd";
if (strpos($rank_known,"received"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Kill Ratio:</b></td><td><input type=checkbox name=k_kll_rat id=k_kll_rat";
if (strpos($rank_known,"ratio"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";

$html .= "<tr><td><b>Add Efficency:</b></td><td><input type=checkbox name=k_eff id=k_eff";
if (strpos($rank_known,"efficiency"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Add Last Seen In:</b></td><td><input type=checkbox name=k_lst_sn id=k_lst_sn";
if (strpos($rank_known,"last_seen"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";

$html .= "</table><br>";

// Portrait section
$html .= "<i>Show icons on player portrait.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Show Rank Insigna:</b></td><td><input type=checkbox name=r_ins id=r_ins";
if (strpos($rank_show,"rank"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Show Medals:</b></td><td><input type=checkbox name=r_med id=r_med";
if (strpos($rank_show,"medal"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Show Medals as Ribbons:</b></td><td><input type=checkbox name=r_medrib id=r_medrib";
if (strpos($rank_show,"nomeds"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Show Ribbons:</b></td><td><input type=checkbox name=r_bad id=r_bad";
if (strpos($rank_show,"badge"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table><br>";

// Small showcase
$html .= "<i>Adds a small showcase under pilot data showing all of above.</i><br>";
$html .= "<i>Please note that if this option is enabled the rank will be still in the portrait.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Add Small Showcase:</b></td><td><input type=checkbox name=r_case id=r_case";
if (strpos($rank_show,"case"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table><br>";

// Pilot detail section
$html .= "<i>Show rank score on Pilot Details page.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Show Base Rank Points:</b></td><td><input type=checkbox name=rkd_base id=rkd_base";
if (strpos($rank_show,"base"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td><b>Show Bonus Rank Points:</b></td><td><input type=checkbox name=rkd_bonus id=rkd_bonus";
if (strpos($rank_show,"bonus"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "<tr><td width=300><b>Show Total Rank Points:</b></td><td><input type=checkbox name=rkd_total id=rkd_total";
if (strpos($rank_show,"total"))
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table><br>";
// END general options

// BEGIN rank options
$html .= "<div class=block-header2>Rank Options</div>";
$html .= "<i>Select images and names for the rank list.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Rank Title Set:</b></td><td>";
$html.='<select name="r_titleset">';
foreach($s_rank_titleset as $corp)
{
	$html .='<option value="'.$corp.'"';
	if($corp == $rank_titleset)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$corp.'</option>';
}
$html .="</select></td></tr>";
$html .= "<tr><td width=300><b>Rank Insignia Set:</b></td><td>";
$html.='<select name="r_imageset">';
foreach($s_rank_imageset as $corp)
{
	$html .='<option value="'.$corp.'"';
	if($corp == $rank_imageset)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$corp.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Rank Group:</b></td><td width=60>";
$html.='<select name="r_group">';
foreach($s_rank_type as $rtype)
{
	$html .='<option value="'.$rtype.'"';
	if($rtype == $rank_type)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$rtype.'</option>';
}
$html .="</select></td></tr></table>";
// END rank options

// BEGIN rank parameters
$html .= "<div class=block-header2>Rank Parameters</div>";
$html .= "<i>Parametes of the exp rank table.</i><br>";
$html .= "<i>Required Rank Points = (Rank Position * Base Rank Points) + (Previous Rank Position Required Rank Points * Exponential Factor)</i><br>";
$html .= "<i>Pilot Base Rank Points = Kill Points / Conversion Factor</i><br>";
$html .= "<i>Total Pilot Rank Points = Pilot Base Rank Points + Ribbons Bonus Rank Points + Medals Bonus Rank Points</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=300><b>Exponential Factor:</b></td><td>";
$html.='<select name="r_exp">';
foreach($s_e_factor as $exp)
{
	$html .='<option value="'.$exp.'"';
	if($exp == $rank_ef)
		{
			$html .= ' selected="selected"';
		}
	$html .='>'.$exp.'</option>';
}
$html .="</select></td></tr>";

$html .= "<tr><td width=300><b>Base Rank Points:</b></td><td>";
$html.="<input type=text name=r_base size=4 maxlength=4 class=password value=\"" . $rank_base . "\"></td></tr>";

$html .= "<tr><td width=300><b>Conversion Factor - Kill Points for 1 Rank Point:</b></td><td>";
$html.="<input type=text name=r_conv size=4 maxlength=4 class=password value=\"" . $rank_conv . "\"></td></tr>";

$html .= "</table>";
// END rank parameters

//BEGIN ribbon table
$html .= "<div class=block-header2>Ship Ribbon Settings Table</div>";
$html .= "<table class=kb-table width=\"750\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr class=kb-table-header><td colspan=2><b>Class</b><br><i>Subclass</i></td>";
$html .= "<td align=center><b>Expert<br>Requirement</b></td><td align=center><b>Expert<br>Bonus</b></td>";
$html .= "<td align=center><b>Veteran<br>Requirement</b></td><td align=center><b>Veteran<br>Bonus</b></td>";
$html .= "<td align=center><b>Elite<br>Requirement</b></td><td align=center><b>Elite<br>Bonus</b></td></tr>";
$row_class='odd';
$k=0;
foreach ($ribbon_parent as $i => $class) {
  if ($row_class=='odd') {$row_class='even';} else {$row_class='odd';}
  $html .= "<tr class=kb-table-row-".$row_class."><td colspan=2>".$class."</td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_expert size=4 maxlength=4 class=password value=\"" . $rank_badges[$i][2] . "\"></td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_bexpert size=4 maxlength=4 class=password value=\"" . $rank_bonus[$i][2] . "\"></td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_veteran size=4 maxlength=4 class=password value=\"" . $rank_badges[$i][1] . "\"></td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_bveteran size=4 maxlength=4 class=password value=\"" . $rank_bonus[$i][1] . "\"></td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_elite size=4 maxlength=4 class=password value=\"" . $rank_badges[$i][0] . "\"></td>";
  $html .= "<td align=center><input type=text name=rsc_".$i."_belite size=4 maxlength=4 class=password value=\"" . $rank_bonus[$i][0] . "\"></td>";
  $html .= "</tr>";
  foreach ($ribbon_child as $j => $subclass) {
    if ($subclass['parent'] == $i) {
    if ($row_class=='odd') {$row_class='even';} else {$row_class='odd';}
    $html .= "<tr class=kb-table-row-".$row_class."><td><input type=checkbox name=enable_".$k." id=enable_".$k;
    if (strpos($rank_subenabled, $subclass['class']))
    {
      $html .= " checked=\"checked\"";
    }
    $html .= "</td><td><i>".$subclass['class']."</i></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_expert size=4 maxlength=4 class=password value=\"" . $rank_sub_badges[$k][2] . "\"></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_bexpert size=4 maxlength=4 class=password value=\"" . $rank_sub_bonus[$k][2] . "\"></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_veteran size=4 maxlength=4 class=password value=\"" . $rank_sub_badges[$k][1] . "\"></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_bveteran size=4 maxlength=4 class=password value=\"" . $rank_sub_bonus[$k][1] . "\"></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_elite size=4 maxlength=4 class=password value=\"" . $rank_sub_badges[$k][0] . "\"></td>";
    $html .= "<td align=center><input type=text name=rsbc_".$k."_belite size=4 maxlength=4 class=password value=\"" . $rank_sub_bonus[$k][0] . "\"></td>";
    $html .= "</tr>";
    $k++;	
    }
  }
}
$html .= "</table><br>";
//END ribbon table

// WEAPON RIBBON table

$html .= "<div class=block-header2>Weapon Ribbon Settings Table</div>";
$html .= "<table class=kb-table width=\"750\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr class=kb-table-header><td align=center>Class</td>";
$html .= "<td align=center><b>Expert<br>Requirement</b></td><td align=center><b>Expert<br>Bonus</b></td>";
$html .= "<td align=center><b>Veteran<br>Requirement</b></td><td align=center><b>Veteran<br>Bonus</b></td>";
$html .= "<td align=center><b>Elite<br>Requirement</b></td><td align=center><b>Elite<br>Bonus</b></td></tr>";
$row_class='odd';
foreach ($weapon_ribbons_array as $i => $class) {
  if ($row_class=='odd') {$row_class='even';} else {$row_class='odd';}
  $html .= "<tr class=kb-table-row-".$row_class."><td>".$class."</td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_expert size=4 maxlength=4 class=password value=\"" . $rank_weap_badges[$i][2] . "\"></td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_bexpert size=4 maxlength=4 class=password value=\"" . $rank_weap_bonus[$i][2] . "\"></td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_veteran size=4 maxlength=4 class=password value=\"" . $rank_weap_badges[$i][1] . "\"></td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_bveteran size=4 maxlength=4 class=password value=\"" . $rank_weap_bonus[$i][1] . "\"></td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_elite size=4 maxlength=4 class=password value=\"" . $rank_weap_badges[$i][0] . "\"></td>";
  $html .= "<td align=center><input type=text name=rwr_".$i."_belite size=4 maxlength=4 class=password value=\"" . $rank_weap_bonus[$i][0] . "\"></td>";
  $html .= "</tr>";
}
$html .= "</table><br>";

// END WEAPON RIBBON TABLE

// BEGIN medal table
$html .= "<div class=block-header2>Medal Settings Table</div>";
$html .= "<table class=kb-table width=\"50%\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr class=kb-table-header><td width=\"50%\">Class</td>";
$html .= "<td align=center>Value</td></tr>";
$row_class='odd';
foreach ($medals_array as $i => $med) {
  if ($row_class=='odd') {$row_class='even';} else {$row_class='odd';}
  $html .= "<tr class=kb-table-row-".$row_class."><td>".$med."</td>";
  $html .= "<td align=center><input type=text name=rm_".$i." size=4 maxlength=4 class=password value=\"" . $rank_medvalues[$i] . "\"></td></tr>";
}
$html .= "</table><br>";
// END medal table

// Top loser negative
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Count Top Loser as a negative value:</b></td><td><input type=checkbox name=pm_neg id=pm_neg";
if ($rank_purplemalus)
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr>";
$html .= "</table><br>";
// END top loser

// Show ranks thingy
if ($keep_title) { $words = 'Custom Rank Set'; } else { $words = $rank_titleset." Rank Set"; }
$html .= "<div class=block-header2>Rank Evolution Table - ".$words." with ".$rank_imageset." Insignia Set - ".$rank_type."</div>";
$html .= "<i>Prevents to rename custom titles and abbreviations.</i><br>";
$html .= "<i>Use it only if you modify only settings and keep lenght of the table intact.</i><br><br>";
$html .= "<table class=kb-table width=\"360\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td><b>Enable Custom Titles:</b></td><td><input type=checkbox name=k_title id=k_title";
if ($keep_title)
{
    $html .= " checked=\"checked\"";
}
$html .= "></td></tr></table><br>";
$html .= "<table class=kb-table width=\"750\" border=\"0\" cellspacing=\"1\">";
$html .= "<tr><td width=34><b>Icon</b></td><td width=266><b>Rank Name</b></td><td width=150><b>Abbreviation</b></td><td width=150><b>Req. Rank Points</b></td><td width=150><b>Req. Kill Points</b></td></tr>";
foreach($rank_ttl as $i => $level) {
  $html .= "<tr height=36><td class=\"item-icon\" valign=\"top\" width=\"34\" height=\"36\">".$level['img']."</td>";	
  $html .= "<td><input type=text name=title_".$i." size=50 maxlength=50 class=password value=\"" . $level['title'] . "\"></td>";
  $html .= "<td><input type=text name=abbr_".$i." size=8 maxlength=8 class=password value=\"" . $level['abbr'] . "\"></td>";
  $html .= "<td align=right>".$level['reqrp']."</td>";
  $html .= "<td align=right>".$level['reqkp']."</td></tr>";
}
$html .= "</table><br>";
// SAVE thingy
$html .= "<table class=kb-subtable><tr><td width=120></td><td colspan=3 ><input type=submit name=submit value=\"Save\"></td></tr>";
$html .= "</table>";
$html .= "</form>";

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>