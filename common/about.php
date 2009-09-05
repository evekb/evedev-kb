<?php
require_once('common/includes/class.ship.php');

$page = new Page('About');

// i store the names here for an easy edit
$developer = array('exi (Developer)',
	'Beansman (Developer)',
	'Ralle030583 (Developer)',
	'Hon Kovell (Developer)');

$contributor = array('JaredC01',
	'liquidism',
	'Mitchman',
	'Coni',
	'FriedRoadKill',
	'bunjiboys',
	'Karbowiak',
	'EDG',
	'Duncan - Shailo Koljas',
	'mastergamer',
	'Captain Thunk');
sort($contributor);
$smarty->assign_by_ref('developer', $developer);
$smarty->assign('contributor', $contributor);

$smarty->assign('version', KB_VERSION." ".KB_RELEASE." rev ".SVN_REV);
$html .= '<b>Staff:</b><br/>';
$html .= join(', ', $developer);
$html .= '<br/><br/><b>Contributors:</b><br/>';
$html .= join(', ', $contributor);
$html .= '<br/><br/>';

$qry = new DBQuery();
$qry->execute("select count(*) as cnt from kb3_kills");
$row = $qry->getRow();
$kills = $row['cnt'];
$qry->execute("select sum(itd_quantity) as cnt from kb3_items_destroyed");
$row = $qry->getRow();
$items = $row['cnt'];
$qry->execute("select count(*) as cnt from kb3_pilots");
$row = $qry->getRow();
$pilots = $row['cnt'];
$qry->execute("select count(*) as cnt from kb3_corps");
$row = $qry->getRow();
$corps = $row['cnt'];
$qry->execute("select count(*) as cnt from kb3_alliances");
$row = $qry->getRow();
$alliances = $row['cnt'];

$smarty->assign('kills', $kills);
$smarty->assign('items', $items);
$smarty->assign('pilots', $pilots);
$smarty->assign('corps', $corps);
$smarty->assign('alliances', $alliances);

$sql = "select scl_id
            from kb3_ship_classes
	   where scl_class not in ( 'Drone', 'Unknown' )
	  order by scl_value";

$qry = new DBQuery();
$qry->execute($sql);

$shipcl = array();
while ($row = $qry->getRow())
{
	$shipclass = new ShipClass($row['scl_id']);
	$class = array();
	$class['name']=$shipclass->getName();
	$class['value']=number_format($shipclass->getValue() * 1000000,0,',','.');
	$class['points']=number_format($shipclass->getPoints(),0,',','.');
	$class['valind']=$shipclass->getValueIndicator();
	$shipcl[]=$class;
}
number_format($shipclass->getPoints(),0,',','.')."</td><td align='center'><img class='ship' alt='' src=\"" . $shipclass->getValueIndicator() . "\" border=\"0\" /></td></tr>";
$smarty->assign('shipclass', $shipcl);

function getVictimShipValueIndicator($value)
{
	if ($value >= 0 && $value <= 1)
		$color = "gray";
	elseif ($value > 1 && $value <= 15)
		$color = "blue";
	elseif ($value > 15 && $value <= 25)
		$color = "green";
	elseif ($value > 25 && $value <= 40)
		$color = "yellow";
	elseif ($value > 40 && $value <= 80)
		$color = "red";
	elseif ($value > 80 && $value <= 250)
		$color = "orange";
	elseif ($value > 250 && $value)
		$color = "purple";

	return IMG_URL . "/ships/ship-" . $color . ".gif";
}

$page->setContent($smarty->fetch(get_tpl('about')));
$page->generate();
?>
