<?php
require_once('common/includes/class.ship.php');

$page = new Page('About');

// i store the names here for an easy edit
$developer = array('exi (Lead Developer)',
					'Beansman (Developer)',
					'Ralle030583 (Developer)',
					'Hon Kovell (Developer)');

$contributor = array('FriedRoadKill',
						'JaredC01',
						'liquidism',
						'Mitchman',
						'Coni',
						'bunjiboys',
						'Karbowiak',
						'EDG');
sort($contributor);

$html .= '<div class=block-header2>The Killboard</div>';

// Please leave the information on the next line as is so that other people can easily find the EVE-Dev website.
// Remember to share any modifications to the EVE-Dev Killboard.
$html .= "This is the EVE Development Network Killboard running version ".KB_VERSION." ".KB_RELEASE." rev ".SVN_REV.", created for <a href=\"http://www.eve-online.com/\">EVE Online</a> corporations and alliances. Based on the EVE-Killboard created by rig0r, it is now developed and maintained by the <a href=\"http://www.eve-dev.net/\">EVE-Dev</a> group.<br/>"
        ."All EVE graphics and data used are property of <a href=\"http://www.ccpgames.com/\">CCP</a>.<br/><br/>";
$html .= '<a href="http://www.eve-dev.net/" target="_blank"><img src="http://www.eve-dev.net/logo.png" border="0"/></a><br/><br/>';

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

// $html .= "<div class=block-header2>Killboard stats</div>";
$html .= "This killboard currently contains: <b>" . number_format($kills, 0, ',', '.') . "</b> killmails, <b>" . number_format($items, 0, ',', '.') . "</b> destroyed items, <b>" . number_format($pilots, 0, ',', '.') . "</b> pilots, <b>" . number_format($corps, 0, ',', '.') . "</b> corporations and <b>" . number_format($alliances, 0, ',', '.') . "</b> alliances.<br><br>";
$filename  = "./mods/history/history.xml";
$history_xml = simplexml_load_file($filename);

if (!isset($_GET['showAll']))
{
    $html .= "<div class=block-header2>Revision History (last 5)</div>";
}
else
{
    $html .= "<div class=block-header2>Revision History (all)</div>";
}
$html .= "<table class=kb-table cellspacing=1 width=100%>";
$count = 1;
foreach ($history_xml as $set)
{	
if ($count > 5 && !isset($_GET['showAll']))
    break;
$html .= "<tr class=kb-table-row-odd><td width = 50><a href='https://svn.nsbit.dk/trac/edk/changeset/".$set->rev."' target='_blank'>REV".$set->rev."</a><td>".$set->author."</td><td width=100 align='right'>".$set->date."</tr>";
foreach ($set->comment as $comment)
{
    $html .= "<tr class=kb-table-row-even><th>".$comment->type."</th><td colspan = 3>".nl2br($comment->text)."</td></tr>";
}
$count ++;
}
if (!isset($_GET['showAll'])) 
{
    $html .= "<tr><td colspan=3>(<a href = '?a=about&showAll=true'>show all</a>)</td></tr>";
}
else
{
    $html .= "<tr><td colspan=3>(<a href = '?a=about'>show last 5</a>)</td></tr>";
}
    $html .= "</table>";

$html .= "<div class=block-header2>Portraits</div>";
$html .= "In order to make your charater portrait visable on the killboard, please take the time to visit the killboard using the ingame browser and choose the option: 'Update portrait'.<br><br>";
$html .= "When prompted to trust the site choose YES, at this point the killboard will obtain your character ID and record it within the database.<br><br>";

$html .= "<div class=block-header2>Kills & Real kills</div>";
$html .= "'Kills' -    The count of all kills by an entity. <br>'Real kills' - This is the count of recorded kills minus any pod, shuttle and noobship kills. <br><p> The 'Real kills' value is used throughout all award and statistic pages.<br><br>";

$html .= "<div class=block-header2>Kill points</div>";
$html .= "Administrator option.<br><br>";
$html .= "If enabled, every kill is assigned a point value. Based on the shiptype destroyed, and the number and types of ships involved in the kill, the number of points indicates the difficulty of the kill... As a result, a gank will get a lot less points awarded than a kill in a small engagement.<br><br>";

$html .= "<div class=block-header2>Efficiency</div>";
$html .= "Each shipclass has an ISK value assigned. These are based on the average amount of ISK that would have been lost if the ship was destroyed, taking current average market prices, insurance costs and insurance payouts into account. ";
$html .= "Any modules that may have been fitted, contained within the destroyed cargo or confiscated are not included within this value.<br><br>";
$html .= "Efficiency is calculated as the ratio of damage done in ISK versus the damage received in ISK. This comes down to <i>damagedone / (damagedone + damagereceived ) * 100</i>.<br><br>";

$html .= "<div class=block-header2>Ship values</div>";
$html .= "The shipclasses and average ISK value are as follows:<br><br>";
$sql = "select scl_id
            from kb3_ship_classes
	   where scl_class not in ( 'Drone', 'Unknown' )
	  order by scl_value";

$qry = new DBQuery();
$qry->execute($sql);
$html .= "<table class=kb-table cellspacing=1>";
$html .= "<tr class=kb-table-header><td width=160>Ship class</td><td>Value in ISK</td><td>Points</td><td align=center>Indicator</td></tr>";
while ($row = $qry->getRow())
{
    $shipclass = new ShipClass($row['scl_id']);
    $html .= "<tr class=kb-table-row-odd><td>".$shipclass->getName()."</td><td align=\"right\">".number_format($shipclass->getValue()*1000000,0,',','.')."</td><td align=\"right\">".number_format($shipclass->getPoints(),0,',','.')."</td><td align=center><img class=ship src=\"" . $shipclass->getValueIndicator() . "\" border=\"0\"></td></tr>";
}
$html .= "</table>";

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
    elseif ($value > 250)
        $color = "purple";

    return IMG_URL . "/ships/ship-" . $color . ".gif";
}

if (config::get('ship_values'))
{
    $sql = 'select kbs.shp_id as id, shp.shp_name, kbs.shp_value,
                 shp.shp_techlevel, scl.scl_class, scl.scl_points
                 from kb3_ships_values kbs
                 inner join kb3_ships shp on (kbs.shp_id = shp.shp_id)
                 inner join kb3_ship_classes scl on (shp.shp_class = scl.scl_id)
                 order by shp.shp_name asc';
    $html .= "<br/>Custom shipvalues which override the value from shipclasses:<br><br>";
    $qry = new DBQuery();
    $qry->execute($sql);
    $html .= "<table class=kb-table cellspacing=1>";
    $html .= "<tr class=kb-table-header><td width=160>Ship Name</td><td>Ship Class</td><td>Points</td><td align=\"right\">Value in ISK</td></tr>";
    while ($row = $qry->getRow())
    {
        if ($row['shp_techlevel'] == 2)
        {
            $row['shp_name'] = '<img src="'.IMG_URL.'/items/32_32/t2.gif">'.$row['shp_name'];
        }
        $html .= "<tr class=kb-table-row-odd><td>".$row['shp_name']."&nbsp;</td><td>".$row['scl_class']."&nbsp;</td><td align=\"right\">".number_format($row['scl_points'],0,',','.')."</td><td align=\"right\">&nbsp;".number_format($row['shp_value'],0,',','.')."&nbsp;<img src=\"".getVictimShipValueIndicator($row['shp_value']/1000000)."\"></td></tr>";
    }
    $html .= "</table>";
}

$page->setContent($html);
$page->generate();
?>
