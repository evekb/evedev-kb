<?php
/**
 * @package EDK
 */
require_once( "common/admin/admin_menu.php" );
require_once( "helper_functions.php");

define ("ME_VER", "0.9.2");

$page = new Page( "Mail Editor v". ME_VER ." by FriedRoadKill");
$page->setAdmin();
$html = ""; //set's the string for easy abuse later

$html .= "<form id=options name=options method=post action=?a=settings_mail_editor>";
$kiddie = 0;

//top or bottom set of controls used?
$old_kid = $_POST['hidden_kid'];

if(isset($_POST['enter_0'])) {
    if(strlen($_POST['kid_0'] > 0))
        $kiddie = $_POST['kid_0'];
    $old_kid = $kiddie;
}
else if(isset($_POST['enter_1'])) {
    if(strlen($_POST['kid_1'] > 0))
        $kiddie = $_POST['kid_1'];
   $old_kid = $kiddie;
} else $kiddie = $old_kid;

if(isset($_POST['hidden_date_stamp'])) {
        if($_POST['hidden_date_stamp'] > $_POST['date_stamp'])
            $date = $_POST['hidden_date_stamp'];
        else $date = $_POST['date_stamp'];
}

//responses and their ilk - there will be boatloads
if(isset($_POST['kadoef'])) {
    $errors = "";
    $hidden = $_POST['hidden_kid']; //lookup hidden children before the ninjas find them!

    if($_POST['date_stamp'])
        $errors.= setDateTime($_POST['date_stamp'], $hidden);

    if(($_POST['vic_name'] != "") || ($_POST['vic_corp'] != "") || ($_POST['vic_all'] != "")) {
        $errors .= setVictimEnt($_POST['vic_name'], $_POST['vic_corp'], $_POST['vic_all'],
            $hidden, $_POST['old_vn'], $_POST['old_vc'], $_POST['old_va'], $date);
    }

    if($_POST['vic_shp']  != "")
        $errors .= setVictimShip($_POST['vic_shp'], $hidden);

    if($_POST['system']  != "")
        $errors .= setSolarSystem($_POST['system'], $hidden);

    if($_POST['vic_dmg']  != "")
        $errors .= setDamageTaken($_POST['vic_dmg'], $hidden);

    $invc = $_POST['hidden_invc']; //because we want the highest id for 'em
    if(isset($_POST['hidden_fb'])) {
        $slot = getFBSlot($_POST['hidden_fb'], $hidden);
    }
    else $slot = 0;
    $i = 0;

    while($i < $invc) {
        if($_POST['inv_p_'.$i]  != "" || $_POST['inv_c_'.$i]  != "" || $_POST['inv_a_'.$i]  != "") {
            $errors .= setInvEnt($_POST['inv_p_'.$i], $_POST['inv_c_'.$i], $_POST['inv_a_'.$i],
                $hidden, $i,$_POST['old_p_'.$i], $_POST['old_c_'.$i], $_POST['old_a_'.$i], $date, $slot);
        }

        if($_POST['inv_shp_'.$i]  != "")
            $errors .= setInvShip($_POST['inv_shp_'.$i], $hidden, $i);

        if($_POST['inv_sec_'.$i] != "")
            $errors .= setInvSec($_POST['inv_sec_'.$i], $hidden, $i);

        if($_POST['inv_w_'.$i] != "")
            $errors .= setInvWep($_POST['inv_w_'.$i], $hidden, $i);

        if($_POST['inv_d_'.$i] != "")
            $errors .= setInvDmg($_POST['inv_d_'.$i], $hidden, $i);
         
        $i++;
    }
    recalcInvData($hidden); //retally which corps and alliances are on a kill - very important

    //items next
    //destroyed
    $itmd = $_POST['hidden_destroyed_count'];
    $i = 0;

    while($i < $itmd) {
        if($_POST['itm_destroyed'.$i])
            $errors .= setItm($_POST['itm_destroyed'.$i], $hidden, $i, 'destroyed', $_POST['hidden_itm_destroyed'.$i]);
            
        $i++;
    }

    $itmd = $_POST['hidden_dropped_count'];
    $i = 0;
    //dropped items now
    while($i < $itmd) {
        if($_POST['itm_dropped'.$i])
            $errors .= setItm($_POST['itm_dropped'.$i], $hidden, $i, 'dropped', $_POST['hidden_itm_dropped'.$i]);

        $i++;
    }
}
//help thing
if(isset($_POST['help'])) {
    $html .= "<table class=\"kb-table\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
    $html .= "<tr class=\"kb-table-header\"><td valign=\"left\" width=\"100%\"><b>Help</b></td></tr>";
    $html .= "<tr class=\"kb-table-row-even\"><td>";
    $html .= getHelp();
    $html .= "</td></tr></table><br/><br/>";
}

//error drawing thing
if(strlen($errors) > 0) {
    $html .= "<table class=\"kb-table\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
    $html .= "<tr class=\"kb-table-header\"><td valign=\"left\" width=\"100%\"><b>Errors</b></td></tr>";
    $html .= "<tr class=\"kb-table-row-even\"><td>";
    $html .= $errors;
    $html .= "</td></tr></table><br/><br/>";
}
//form related drawing stuff
if (is_numeric($kiddie) > 0 || (is_numeric($old_kid) > 0)) {
    if ($old_kid > 0)
        $kill = new Kill($old_kid);
        else $kill = new Kill($kiddie);
    if (!$kill->exists())
    {
        $html .= "That kill doesn't exist.<br/><br/>";
    }
    else {
        //pre header var declaration
        $ship = $kill->getVictimShip();
        $system = $kill->getSystem();

        $html .= selectorThinger($kiddie, 0)."<br/><br/>";
        //header
        $html .= "<input type=hidden name=hidden_kid value=\"".$kiddie."\">"; //hidden children - hope the ninjas don't find them first!
        $html .= "<input type=hidden name=hidden_invc value=\"".count($kill->involvedparties_)."\">";
        $html .= "<input type=hidden name=hidden_fb value=\"".$kill->getFBPilotID()."\">";
        $html .= "<table class=\"kb-table\" cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $html .= "<tr class=\"kb-table-header\"><td valign=\"left\" width=\"90%\"><b>Mail - Kill #".$kiddie."</b></td><td><b>New Value</b></td></tr>";
        $html .= "<tr class=\"kb-table-row-even\"><td>".$kill->getTimeStamp()
            ."</td><td><input type=text name=date_stamp></td><input type=hidden name=hidden_date_stamp value=\""
            .$kill->getTimeStamp()."\"></td></tr>";
        $html .= "<tr height=\"18px\"><td></td><td></td></tr>";
        $html .= "<tr class=\"kb-table-row-even\"><td>Victim: ".$kill->getVictimName()."</td><td><input type=text name=vic_name><input type=hidden name=old_vn value=\"".$kill->getVictimID()."\"></td></tr>";
        $html .= "<tr class=\"kb-table-row-odd\"><td>Corp: ".$kill->getVictimCorpName()."</td><td><input type=text name=vic_corp><input type=hidden name=old_vc value=\"".$kill->getVictimCorpID()."\"></td></tr>";
        $html .= "<tr class=\"kb-table-row-even\"><td>Alliance: ".$kill->getVictimAllianceName()."</td><td><input type=text name=vic_all><input type=hidden name=old_va value=\"".$kill->getVictimAllianceID()."\"></td></tr>";
        $html .= "<tr class=\"kb-table-row-odd\" height=\"18px\"><td>Faction: NONE</td><td></tr>";
        $html .= "<tr class=\"kb-table-row-even\"><td>Destroyed: ".$ship->getName()."</td><td><input type=text name=vic_shp></td></tr>";
        $html .= "<tr class=\"kb-table-row-odd\"><td>System: ".$system->getName()."</td><td><input type=text name=system></td></tr>";
        $html .= "<tr class=\"kb-table-row-even\" height=\"18px\"><td>Security: ".$system->getSecurity(true)."</td><td></td></tr>";
        $html .= "<tr class=\"kb-table-row-odd\"><td>Damage Taken: ".$kill->VictimDamageTaken."</td><td><input type=text name=vic_dmg></td></tr>";
        $html .= "<tr height=\"18px\"><td></td><td align=right><input type=submit name=kadoef value=\"Change!\"></td></tr>";
        $html .= "<tr class=\"kb-table-row-even\" height=\"18px\"><td><b>Involved parties:</b></td><td></td></tr>";

        //involved parties
        $i = 0;
        foreach($kill->involvedparties_ as $inv) {
            //stuff some pre-loaded bras, err, values
            $pilot = new Pilot($inv->getPilotID());
            $corp = new Corporation($inv->getCorpID());
            $all = new Alliance($inv->getAllianceID());
            $ship = $inv->getShip();
            $weapon = $inv->getWeapon();
            $final = $kill->getFBPilotName();
            $pname = $pilot->getName();
            $number = $i +1;

            if($pname == $final)
                $html .= "<tr class=\"kb-table-row-odd\"><td>Name #".$number.": ".$pilot->getName()." (Final Blow)"."</td><td><input type=text name=inv_p_".$i."><input type=hidden name=old_p_".$i." value=\"".$pilot->getID()."\"></td></tr>";
            else $html .= "<tr class=\"kb-table-row-odd\"><td>Name #".$number.": ".$pilot->getName()."</td><td><input type=text name=inv_p_".$i."><input type=hidden name=old_p_".$i." value=\"".$pilot->getID()."\"></td></tr>";
            $html .= "<tr class=\"kb-table-row-even\"><td>Security: ".$inv->getSecStatus()."</td><td><input type=text name=inv_sec_".$i."></td></tr>";
            $html .= "<tr class=\"kb-table-row-odd\"><td>Corp: ".$corp->getName()."</td><td><input type=text name=inv_c_".$i."><input type=hidden name=old_c_".$i." value=\"".$corp->getID()."\"></td></tr>";
            $html .= "<tr class=\"kb-table-row-even\"><td>Alliance: ".$all->getName()."</td><td><input type=text name=inv_a_".$i."><input type=hidden name=old_a_".$i." value=\"".$all->getID()."\"></td></tr>";
            $html .= "<tr class=\"kb-table-row-odd\" height=\"18px\"><td>Faction: NONE</td><td></td></tr>";
            $html .= "<tr class=\"kb-table-row-even\"><td>Ship: ".$ship->getName()."</td><td><input type=text name=inv_shp_".$i."></td></tr>";
            $html .= "<tr class=\"kb-table-row-odd\"><td>Weapon: ".$weapon->getName()."</td><td><input type=text name=inv_w_".$i."></td></tr>";
            $html .= "<tr class=\"kb-table-row-even\"><td>Damage done: ".$inv->dmgdone_."</td><td><input type=text name=inv_d_".$i."></td></tr>";
            $html .= "<tr height=\"18px\"><td></td><td align=right><input type=submit name=kadoef value=\"Change!\"></td></tr>";
            $i++;
        }
        $html .= itemThinger("destroyed", $kiddie);
        $html .= itemThinger("dropped", $kiddie);
        $html .= "</table><br/><br/>"; 
    }
}
else {
    $html .= "You haven't enterend in any values yet.<br/><br/>"; //that means you...
}

$html .= selectorThinger($kiddie, 1);
$html .= "</form>";
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();
?>