<?php
require_once("common/includes/class.corp.php");
require_once("common/includes/class.alliance.php");
require_once("common/includes/class.killlist.php");
require_once("common/includes/class.killlisttable.php");

function mktable($klist, $limit) 
{
	$odd = false;
	$klist->rewind();
	while ($kill = $klist->getKill()) {
		if ($limit && $c > $limit)
       	        	break;
       		else
			$c++;
		if (!$odd) {
        		$odd = true;
	        	$html .= "<tr bgcolor=#222222><td>";
      		} else {
        		$odd = false;
        		$html .= "<tr><td>";
		}
		$html .= "<img src=\"" .$kill->getVictimShipImage(32). "\">";
		$html .= " ";
		$html .= $kill->getVictimShipName();
		$html .= "(".$kill->getVictimShipClassName().") </td>";
		$html .= "<td>";
		$html .= $kill->getVictimName()."(".  shorten($kill->getVictimCorpName()).")"; 
		$html .= "</td><td>";
		$html .= $kill->getFBPilotName()."(".shorten($kill->getFBCorpName()) .")";
		$html .= "</td><td>";
		$html .= $kill->getTimeStamp();
		$html .= "</td><td>";
		$html .= $kill->getSolarSystemName() ."(".roundsec($kill->getSolarSystemSecurity()).")";
		$html .= "</td><td>";
		$html .= "<a href=\"".KB_HOST."?a=igb_kill_mail&kll_id=".$kill->getID()."\">Mail</a>";
		$html .= "</td></tr>";
	}
	return $html;
}


$html .= "<html><head><title>IGB Killboard</title></head><body>";
$html .= "<a href=\"".KB_HOST."?a=post_igb\">Post killmail</a> | <a href=\"".KB_HOST."?a=portrait_grab\">Update portrait</a> | <a href=\"".KB_HOST."?a=igb&mode=kills\">Kills</a> | <a href=\"".KB_HOST."?a=igb&mode=losses\">Losses</a><br>";
$html .= "<table width=\"100%\" border=1>";
$html .= "<tr><td>Ship</td><td>Victim</td><td>Final Blow</td><td>Date/Time</td><td>System</td><td>Raw Mail</td></tr>";
switch ($_GET[mode]) {
	case "losses":
		$klist = new KillList();
		$klist->setOrdered(true);
		$klist->setLimit(30);
		involved::load($klist,'loss');		
		$html .= mktable($klist,30);
		break;
	case "kills": 
		$klist = new KillList();
		$klist->setOrdered(true);
		$klist->setLimit(30);
		involved::load($klist,'kill');
		$html .= mktable($klist,30);
		break;
	default: 
		$klist = new KillList();
		$klist->setOrdered(true);
		$klist->setLimit(10);
		involved::load($klist,'kill');
		$html .= mktable($klist,10);
		break;
}


$html .= "</table>";
$html .= "</body></html>";
echo $html;
?>
