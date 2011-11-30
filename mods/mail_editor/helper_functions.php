<?php
/**
 *  This file contains all the gunky SQL statements necessary to update kill details
 * by bypassing the Items class and updating the SQL manually. Urgh. Anyway, these
 * functions could have been part of the settings file, but this makes both files
 * look a bit neater. - FriedRoadKill
 * @package
 */

require_once( "common/admin/admin_menu.php" );

function itemThinger($type, $kid) {//just fetches the items of a mail
    $html_f = "";
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT * FROM `kb3_items_".$type."` WHERE `itd_kll_id` = ".$kid.";";
    $qry->execute($sql);
    $count = $qry->recordCount();
    if($count > 0)
    {
        //sub-heading
        $odd_even = 0;
        $html_f .= "<input type=hidden name=hidden_".$type."_count value=\"".$count."\">";
        $html_f .= "<tr class=\"kb-table-row-even\" height=\"18px\"><td><b>Items "
            .$type.":</b></td><td></td></tr>";
        $i = 0;
        while ($row = $qry->getRow())
        {
            if($odd_even == 0) {
                $odd = "kb-table-row-odd";
            }
            else $odd = "kb-table-row-even";

            $item = new Item($row['itd_itm_id']);
            $qty = $row['itd_quantity'];
            $loc = $row['itd_itl_id'];
            $happy_pack = $item->getName();

            if($qty > 1)
                $happy_pack .= ", Qty: ".$qty;
            switch($loc) {
                case 1: $happy_pack .= " (High)"; break;
                case 2: $happy_pack .= " (Med)"; break;
                case 3: $happy_pack .= " (Low)"; break;
                case 4: $happy_pack .= " (Cargo)"; break;
                case 5: $happy_pack .= " (Rig)"; break;
                case 6: $happy_pack .= " (Drone Bay)"; break;
            }

            $html_f .= "<tr class=\"".$odd."\"><td>".$happy_pack."</td><td>
                <input type=text name=itm_".$type.$i."><input type=hidden name=hidden_itm_"
                .$type.$i." value=\"".$item->getID()."\"></td></tr>";

            $odd_even++; //to retain the nice and cute alternation of the background colour
            if($odd_even >= 2)
                $odd_even = 0;
            $i++;
        }
        $html_f .= "<tr height=\"18px\"><td></td><td align=right><input type=submit name=kadoef value=\"Change!\"></td></tr>";
    }
    return $html_f;
}

function selectorThinger($id, $loc) {
    $html_f = "";
    $html_f .= "Type in the ID of the kill you want to edit:<br/><br/>";
    $html_f .= "<input type=text name=kid_".$loc." value=\"".$id."\">\t";
    $html_f .= "<input type=submit name=enter_".$loc." value=\"Go!\">\t";
    $html_f .= "<input type=submit name=help value=\"Help\">";
    
    return $html_f;
}

function setDateTime($timeString, $kill_id) {
    $now = time();
    $timeString = str_replace('.', '-', $timeString); //otherwise the time is in the wrong format
    $time = strtotime($timeString);

    if($time == -1 || strlen($timeString) < 16 || strlen($timeString) > 19) //make it a tad picky
        return "Date: Date format not correct.<br/>";

    if($time > $now)
        return "Date: Can't set timestamp to a future date.<br/>";

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_kills` SET `kll_timestamp` = '".$timeString."' WHERE `kll_id` = '".$kill_id."';";
    $qry->execute($sql);
    return;    
}

function setVictimEnt($name, $corp, $all, $kill_id, $ov_n, $ov_c, $ov_a, $time) {
    //boring grubby bit where we check if we actuall got input
    $msg = "";
    $name = trim($name);
    $corp = trim($corp);
    $all = trim($all);

     if(strlen($name) == "" || strlen($corp) == "" || strlen($all) == "") {
        $msg .= "Victim: Enter in something other than whitespace.<br/>";
        return $msg;
    }


    if(strlen($all) > 0) { //determine all we know about the pilot, and change if needed
        $al = Alliance::add($all);
    }
    else $al = new Alliance($ov_a);

    if(strlen($corp) > 0) { //same again for corp
        $crp = Corporation::add($corp, $al, $time);
    }
    else {
        $crp = new Corporation($ov_c);
        $co = $crp->getName();
        $crp = Corporation::add($co, $al, $time);
    }

    if(strlen($name) > 0) {
        $plt = Pilot::add($name, $crp, $time);
    }
    else {
        $plt = new Pilot($ov_n); //get the name from the id, and add again
        $na = $plt->getName();
        $plt = Pilot::add($na, $crp, $time);
    }

    if(strlen($name) > 0) {
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_kills` SET `kll_victim_id` = '".$plt->getID()."' WHERE `kll_id` = '".$kill_id."'";
        $qry->execute($sql);
    }

    if(strlen($corp) > 0) {
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_kills` SET `kll_crp_id` = '".$crp->getID()."' WHERE `kll_id` = '".$kill_id."'";
        $qry->execute($sql);
    }

    if(strlen($all) > 0) {
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_kills` SET `kll_all_id` = '".$al->getID()."' WHERE `kll_id` = '".$kill_id."'";
        $qry->execute($sql);
    }
    return;
}

function setVictimShip($name, $kill_id) {
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `shp_id` FROM `kb3_ships` WHERE `shp_name` = '".$name."';";
    $qry->execute($sql);
    if($qry->recordCount() < 1)
        return "Ship '".$name."' doesn't exist in the database.<br/>";
    $row = $qry->getRow();

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_kills` SET `kll_ship_id` = '".$row['shp_id']
        ."' WHERE `kll_id` = '".$kill_id."'";
    $qry->execute($sql);
    return;
}

function setSolarSystem($name, $kill_id) {
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `sys_id` FROM `kb3_systems` WHERE `sys_name` = '".$name."';";
    $qry->execute($sql);
    if($qry->recordCount() < 1)
        return "Solar system: '".$name."' doesn't exist in the database.<br/>";
    $row = $qry->getRow();

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_kills` SET `kll_system_id` = '".$row['sys_id']
        ."' WHERE `kll_id` = '".$kill_id."'";
    $qry->execute($sql);

    return;
}

function setDamageTaken($name, $kill_id) {

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_kills` SET `kll_dmgtaken` = '".$name
        ."' WHERE `kll_id` = '".$kill_id."'";
    $qry->execute($sql);

    return;
}

function getFBSlot($fb_id, $kill_id) {

    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `ind_order` FROM `kb3_inv_detail` WHERE `ind_kll_id` = '"
        .$kill_id."' AND `ind_plt_id` = '".$fb_id."'";
    $qry->execute($sql);
    $row = $qry->getRow();
    return $row['ind_order'];
}

function setInvEnt($name, $corp, $all, $kill_id, $i, $old_n, $old_c, $old_a, $time, $fb) {
    //this may look very familiar...
    $msg = "";
    $name = trim($name);
    $corp = trim($corp);
    $all = trim($all);

    if(strlen($name) == "" || strlen($corp) == "" || strlen($all) == "") {
        $number = $i +1;
        $msg .= "Involved Party #".$number.": Enter in something other than whitespace.<br/>";
        return $msg;
    }

    if(strlen($all) > 0) {
        $al = Alliance::add($all);
    }
    else $al = new Alliance($old_a);

    if(strlen($corp) > 0) {
        $crp = Corporation::add($corp, $al, $time);
    }
    else {
        $crp = new Corporation($old_c);
        $corp = $crp->getName();
        $crp = Corporation::add($corp, $al, $time);
    }

    if(strlen($name) > 0) {
        $plt = Pilot::add($name, $crp, $time);
    }
    else {
        $plt = new Pilot($old_p); //get the name from the id, and add again
        $name = $plt->getName();
        $plt = Pilot::add($name, $crp, $time);
    }

    if(strlen($name) > 0) {
        $hold_row = $plt->getID();
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_inv_detail` SET `ind_plt_id` = '".$hold_row
            ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."'";
        $qry->execute($sql);

        $qry = DBFactory::getDBQuery();
        $sql = "DELETE FROM `kb3_inv_plt` WHERE `inp_kll_id` ='".$kill_id
            ."' AND `inp_plt_id` = '".$old_n."';";
        $qry->execute($sql);

        $qry = DBFactory::getDBQuery();
        $sql = "INSERT INTO `kb3_inv_plt` (`inp_kll_id`, `inp_plt_id`) VALUES("
            .$kill_id.",".$hold_row.");";
        $qry->execute($sql);

        if($i == $fb) {
            $qry = DBFactory::getDBQuery();
            $sql = "UPDATE `kb3_kills` SET `kll_fb_plt_id` = '".$hold_row
                ."' WHERE `kll_id` = '".$kill_id."'";
            $qry->execute($sql);
        }
    }

    if(strlen($corp) > 0) {
        $hold_row = $crp->getID();
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_inv_detail` SET `ind_crp_id` = '".$hold_row
            ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."'";
        $qry->execute($sql);

        if($i == $fb) {
            $qry = DBFactory::getDBQuery();
            $sql = "UPDATE `kb3_kills` SET `kll_fb_crp_id` = '".$hold_row
                ."' WHERE `kll_id` = '".$kill_id."'";
            $qry->execute($sql);
        }
    }

    if(strlen($all) > 0) {
        $hold_row = $al->getID();

        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_inv_detail` SET `ind_all_id` = '".$hold_row
            ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."'";
        $qry->execute($sql);

        if($i == $fb) {
            $qry = DBFactory::getDBQuery();
            $sql = "UPDATE `kb3_kills` SET `kll_fb_all_id` = '".$hold_row
                ."' WHERE `kll_id` = '".$kill_id."'";
            $qry->execute($sql);
        }
    }
    return $msg;
}

function setInvShip($name, $kill_id, $i) {
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `shp_id` FROM `kb3_ships` WHERE `shp_name` = '".$name."';";
    $qry->execute($sql);
    if($qry->recordCount() < 1)
        return "Ship'".$name."' doesn't exist in the database.<br/>";
    $row = $qry->getRow();

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_inv_detail` SET `ind_shp_id` = '".$row['shp_id']
        ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."';";
    $qry->execute($sql);
    return;
}

function setInvWep($name, $kill_id, $i) {
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `typeID` FROM `kb3_invtypes` WHERE `typeName` = '".$name."';";
    $qry->execute($sql);

    if($qry->recordCount() < 1)
        return "Weapon '".$name."' doesn't exist in the database.<br/>";
    $row = $qry->getRow();

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_inv_detail` SET `ind_wep_id` = '".$row['typeID']
        ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."';";
    $qry->execute($sql);
    return;
}

function setInvSec($name, $kill_id, $i) {
    if(is_numeric($name)) {
        if($name > 10 || $name < -10) {
            return "Involved pilot sec values need to be between -10 & 10, you wrote, '".$name."'.";
        }
    }
    else return "Involved pilot sec value is not a number. You wrote, '".$name."'.";

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_inv_detail` SET `ind_sec_status` = '".$name
        ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."';";
    $qry->execute($sql);
    return;
}

function setInvDmg($name, $kill_id, $i) {
    if(!is_numeric($name)) {
        return "Involved pilot damage done is not a number. You wrote, '".$name."'.";
    }

    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_inv_detail` SET `ind_dmgdone` = '".$name
        ."' WHERE `ind_kll_id` = '".$kill_id."' AND `ind_order` = '".$i."';";
    $qry->execute($sql);
    return;
}

function setItm($name, $kill_id, $i, $type, $old) {
    //some string manipulation to find quantity and location
    //that's a lot of ifs, mister!
    $itm = strpos($name, ",");
    $q_pos = strpos(strtolower($name), "qty:");
    $l_pos = strpos($name, "(");
    $l_pos_end = strpos($name, ")");
    $location = 0;

    if($itm == 0) { //did we find a comma?
        $justName = trim($name);
        if($l_pos > 0) //bracket instead? (for if no comma is set)
            $justName = trim(substr($name, 0, $l_pos));
    }
    else $justName = trim(substr($name, 0, $itm));

    if($l_pos == 0) //did we find a bracket?
        $location = 0;
    else {
       if($l_pos_end == 0) //and the accompanying bracket?
           return $justName.": Open bracket must be accompanied by a closed bracket.<br/>";
       $loc_text = substr($name, $l_pos+1, $l_pos_end - ($l_pos+1));

       //the 6 locations
       switch (strtolower($loc_text)) {
           case "high": $location = 1; break;
           case "med": $location = 2; break;
           case "low": $location = 3; break;
           case "cargo": $location = 4; break;
           case "rig": $location = 5; break;
           case "drone bay": $location = 6; break;
           default: return $justName.": location text not recognised.</br>"; break;
       }
    }

    if($q_pos == 0) //did the quantity value get changed?
        $quantity = 0;
    else {
        if($l_pos == 0)
            $end = strlen($name) -1;
        else $end = $l_pos -1;

        $quantity = trim(substr($name, $q_pos +4, $end - $l_pos+4));
        if(!is_numeric($quantity))
            return $justName.": Quantity must be a number. You entered in '".$quantity."'<br/>";
    }

    //usual DB stuff
    $qry = DBFactory::getDBQuery();
    $sql = "SELECT `typeID` FROM `kb3_invtypes` WHERE `typeName` = '".$justName."';";
    $qry->execute($sql);
    if($qry->recordCount() < 1)
        return $name." doesn't exist in the database.<br/>";
    $row = $qry->getRow();
    $iid = $row['typeID'];

    if($quantity > 0) { //update quantity
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_items_".$type."` SET `itd_quantity` = '".$quantity
            ."' WHERE `itd_kll_id` = '".$kill_id."' AND `itd_itm_id` = '".$old."'";
        $qry->execute($sql);
    }

    if($location > 0) { //update location
        $qry = DBFactory::getDBQuery();
        $sql = "UPDATE `kb3_items_".$type."` SET `itd_itl_id` = '".$location
            ."' WHERE `itd_kll_id` = '".$kill_id."' AND `itd_itm_id` = '".$old."'";
        $qry->execute($sql);
    }

    //set the id last, so the other properties can be set first with copy paste code.
    $qry = DBFactory::getDBQuery();
    $sql = "UPDATE `kb3_items_".$type."` SET `itd_itm_id` = '".$iid
        ."' WHERE `itd_kll_id` = '".$kill_id."' AND `itd_itm_id` = '".$old."'";
    $qry->execute($sql);

    return;
}

function recalcInvData($kill_id) {
    //this table will have to be rebuilt, updating will be problematic.
    //each corp reference must be unique, so only select one of each corp.
    $qry = DBFactory::getDBQuery();
    $sql = "DELETE FROM `kb3_inv_crp` WHERE `inc_kll_id` ='".$kill_id."';";
    $qry->execute($sql);

    $qry = DBFactory::getDBQuery();
    $sql = "SELECT DISTINCT `ind_crp_id` FROM `kb3_inv_detail`
        WHERE `ind_kll_id` = '".$kill_id."';";
    $qry->execute($sql);

    while($row = $qry->getRow()) {
        $qry2 = DBFactory::getDBQuery();
        $sql2 = "INSERT INTO `kb3_inv_crp` (`inc_kll_id`, `inc_crp_id`)
            VALUES (".$kill_id.",".$row['ind_crp_id'].");";
        $qry2->execute($sql2);
    }
    //same again, but now with more alliance flavour
    $qry = DBFactory::getDBQuery();
    $sql = "DELETE FROM `kb3_inv_all` WHERE `ina_kll_id` ='".$kill_id."';";
    $qry->execute($sql);

    $qry = DBFactory::getDBQuery();
    $sql = "SELECT DISTINCT `ind_all_id` FROM `kb3_inv_detail` WHERE `ind_kll_id` = '"
        .$kill_id."' AND `ind_all_id` != '14';"; //filter out 'None' (we don't love None like the other children)
    $qry->execute($sql);

    while($row = $qry->getRow()) {
        $qry2 = DBFactory::getDBQuery();
        $sql2 = "INSERT INTO `kb3_inv_all` (`ina_kll_id`, `ina_all_id`)
            VALUES (".$kill_id.",".$row['ind_all_id'].");";
        $qry2->execute($sql2);
    }
}

function getHelp() { // help information document type stuff.
    $text = "<p>This mod allows the admin to edit a kill mail once it has been posted
        into the killboard's database. The editor is quite liberal, allowing
        you to change just about everything in each kill mail.<br/><br/>
        <b>Basics</b><br/>You start by selecting a mail to edit, by typing
        in its ID and whacking 'Go!'. You're then presented with the relevant
        mail, and input boxes for all editable fields. To change something
        type in the name of what you want into the corresponding box and
        whacking 'Change!' at the bottom of the table. (You can edit as many
        fields at a time as you like.) As a safety feature, if you are trying
        to change a field to something that doesn't exist in your database, the
        mail editor will not allow the change to be made - with the exception of
        pilot, corp, and alliance names - these may be anything your heart desires.
        <br/><br/>
        <b>More Advanced Editing</b><br/>
        The date - to change the date your input must match the usual eve-mail
        format (YYYY.MM.DD HH:MM:SS)<br/>
        Items - string entry comes in 3 parts: the item name, the quantity,
        and the location of the item:<br/>Item, Qty: xx (Place)<br/>
        So, here's the input and the types of input by way of example:<br/>
        \"Damage Control II\" - will change the name of the item only.<br/>
        \"Damage Control II, Qty: 5\" - will change the name of the item and
        set the quantity.<br/>
        \"Damage Control II, Qty: 5 (Cargo)\" - will change the name of the
        item, the quantity and the location that the item should now reside.<br/>
        \"Damage Control II (Cargo)\" will also work (indicating an automatic
        quantity of one.)<br/>
        Allowed location values are: High, Med, Low, Cargo, Drone Bay, and Rig.
        <u>Beware!</u> You can change the location of any item to any location!
        (Like drones in high slots, and salvagers in the rig slots, for example.)
        <br/>
        For the quantity and location parts of the string, the case is insensitive.
        ((Cargo), (CaRgO), and (cargo) are all equivalent.)<br/><br/>
        <b>Limitations</b><br/>
        As we're still at v".ME_VER." There's still some stuff to be added
        depending on community response. The limitations are:<br/>
        The text a user provides <u>has</u> be an exact match to the text in the
        database. (Very case-sensitive & no partial text searches yet)<br/>
        You can't add or delete items / involved parties.<br/>
        Might be slow with killmails with lots of involved parties / items.<br/>
        No integration into the kill list for the admin. (Too lazy atm - it conflicts
		with oh so many other mods)<br/>
        After editing a pilot, his new corp might not reflect properly until the next
		kill featuring said is posted. This is down to a limitation in the pilot class
		of the core. This applies to corps and the alliances they belong to as well.
		<br/><br/>
        <b>Disclaimer</b><br/>
        As you can potentially mess up kills in your database, I'm slapping
        this with the usual \"If you break something, dont blame me\" disclaimer.
        If you're calm and careful, there's no reason it won't work. If something
        does happen, please report it, and I will repair it as fast as possible so that
        it doesn't happen in the future!<br/><br/>FriedRoadKill</p>";

    return $text;
}
?>
