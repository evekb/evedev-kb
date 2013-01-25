<?php
$config = new Config(KB_SITE);
define('KB_TITLE', config::get('cfg_kbtitle'));

$page = new Page("Map Mod");
$page->setCachable(false);
$db = new DBQuery();

$week = intval($_GET['w']);
$year = intval($_GET['y']);

$region_set = intval($_GET['region_id']);
$region_nav="";

if ($week == '')
    $week = kbdate('W');

if ($year == '')
    $year = kbdate('Y');

$nyear = $year + 1;
$pyear = $year - 1;

// week 53 fix
$month = kbdate('n');

if ($month == 12 && $week == 01)
{
    $week = 53;
}


$region=0;

if ($region_set>0) {
	$using=" using filters for regions";
}
else {
	$using=" using most active region per month";
}

if( $year == kbdate('Y')) {
$html .= '<div id="page-title">Graphical view of this years activity'.$using.'</div><br />';
}
else {
$html .= '<div id="page-title">Graphical view of '.$year.' activity'.$using.'</div><br />';
}


$html .= '<table class=kb-table width="100%" border=0 cellspacing="1"> ';
	for( $i=1; $i <= 12; $i++){
	
	
if ($region_set==0) {
	
	$sql2 ="select reg.reg_id, count(distinct kll.kll_id) as kills 
				from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv, kb3_constellations con, kb3_regions reg
				where kll.kll_system_id = sys.sys_id 
				and inv.ind_kll_id = kll.kll_id";

			if(count(config::get('cfg_allianceid'))) {
				$orargs[] = 'inv.ind_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
			}
			if(count(config::get('cfg_corpid'))) {
				$orargs[] = 'inv.ind_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
			}
			if(count(config::get('cfg_pilotid'))) {
				$orargs[] = 'inv.ind_plt_id IN ('.implode(",", config::get('cfg_pilotid')).") ";
			}

			$sql2 .= " AND (".implode(" OR ", $orargs).")";	
		 
	$sql2 .="    and date_format( kll.kll_timestamp, \"%m\" ) = ".$i."
				and date_format( kll.kll_timestamp, \"%Y\" ) = ".$year."
				and con.con_id = sys.sys_con_id
                and reg.reg_id = con.con_reg_id
				group by reg.reg_id
				
				order by kills desc
				LIMIT 0,1;";

$qry2 = new DBQuery();
 $qry2->execute($sql2) or die($qry2->getErrorMsg());
   while ($row2 = $qry2->getRow())
            {
			$region=$row2['reg_id'];
			}
	
}
else 	{ 
	$region= $region_set; 
	$region_nav='&region_id='.$region_set;
 }
	
			if($i%2) {
			$html .= '<tr >';
			}
			
			
				if($region!=0){
				$html .= ' 	<td align="center"><img src="?a=map&mode=activity&size=350&region_id='.$region.'&month='.$i.'&year='.$year.'" /></td>';
				}
				else {$html .= ' 	<td align="center"><img src="?a=map&mode=na&size=250" width="350" height="350"></td>';}
			 
			 if($i%2) {
			
			}	
			else {$html .= '</tr >';}
			
			$region=0;
				}
$html .= '</table>	';


$page->setContent($html);

$menubox = new box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Navigation");
$menubox->addOption("link","Previous Year", edkURI::page('map_year')."&y=" . $pyear.$region_nav);
$menubox->addOption("link","Next Year", edkURI::page('map_year')."&y=" . $nyear.$region_nav);
$page->addContext($menubox->generate());

$page->generate();
?>