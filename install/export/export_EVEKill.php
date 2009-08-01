<?php
$serverAddress = '';
$databaseUsername = '';
$databasePassword = '';
$databaseToUse = '';
$prefix = 'EVEkill_';

if (isset($_POST['Export']))
{
    if (!isset($_SESSION['export']))
    {
        session_start();
    }
    // connect to database
    $killboardDatabase = mysql_pconnect($serverAddress, $databaseUsername, $databasePassword);
    mysql_select_db($databaseToUse, $killboardDatabase);

    $count = 0;
    if (isset($_SESSION['export']))
    {
        $count = $_SESSION['export'];
    }

    $sql = "SELECT * FROM `" . $prefix . "battles` LIMIT " . $count . ", 200";
    echo "sql =" . $sql . "<br>";

    $result = mysql_query($sql);
    echo "results= " . mysql_num_rows($result) . "<br /><br />";
    $count_results = mysql_num_rows($result);
    if ($count_results == 0)
    {
        echo "no more results<br>";
    }

    for ($i = 1; $i <= 200; $i++)
    {
        $sql = "SELECT * FROM `" . $prefix . "battles` LIMIT " . $count . ", 200";
        $result = mysql_query($sql);

        while ($KillDetails = mysql_fetch_row($result))
        {
            $_SESSION['export'] = $count;
            # ###### Get the victim ########
            $sql2 = "SELECT * FROM `" . $prefix . "victims` WHERE (battle_id = '" . $KillDetails[0] . "')";
            $result = mysql_query($sql2);
            $Victim = mysql_fetch_array($result);
            // echo $Victim[1]."<br>";
            // $Victim[1] - name
            // $Victim[2] - corpID
            // $Victim[3] - allianceID
            // $Victim[4] - ship
            // $Victim[5] - system(text)
            // $Victim[6] - sys sec
            $sql2 = "SELECT * FROM `" . $prefix . "players` WHERE (playerID = '$Victim[1]')";
            $result2 = mysql_query($sql2);
            $VictimName = mysql_fetch_row($result2);
            // echo $VictimName[1]."<br>";
            $sql2 = "SELECT * FROM `" . $prefix . "alliance` WHERE (allianceID = '$Victim[3]')";
            $result2 = mysql_query($sql2);
            $LastKilledAlliance = mysql_fetch_row($result2);
            // echo $LastKilledAlliance[1]."<br>";
            $sql2 = "SELECT * FROM `" . $prefix . "corps` WHERE (corpID = '$Victim[2]')";
            $result2 = mysql_query($sql2);
            $LastKilledCorp = mysql_fetch_row($result2);
            // echo $LastKilledCorp[1]."<br>";
            $sql2 = "SELECT * FROM `" . $prefix . "ships` WHERE (ID = '$Victim[4]')";
            $result2 = mysql_query($sql2);
            $LastKilledShip = mysql_fetch_row($result2);
            // echo $LastKilledShip[1] ."<br>";
            $timeStamp = date("Y.m.d H:i" , $KillDetails[2]);

            $victim = sprintf("%s\r\n\r\nVictim: %s\r\nAlliance: %s\r\nCorp: %s\r\nDestroyed: %s\r\nSystem: %s\r\nSecurity: %s\r\n\r\n", $timeStamp, html_entity_decode($VictimName[1], ENT_QUOTES), html_entity_decode($LastKilledAlliance[1], ENT_QUOTES), html_entity_decode($LastKilledCorp[1], ENT_QUOTES), $LastKilledShip[1], $Victim[5], $Victim[6]);
            // echo $victim;
            # ###### Get the involved parties ########
            $sql3 = "SELECT * FROM `" . $prefix . "attackers` WHERE (battle_id = '$KillDetails[0]')";
            $result3 = mysql_query($sql3);
            unset($involved);
            while ($Involved = mysql_fetch_array($result3)) // 9 end
            {
                $sql4 = "SELECT * FROM `" . $prefix . "players` WHERE (playerID = '$Involved[1]')";
                $result4 = mysql_query($sql4);
                $KillersName = mysql_fetch_row($result4);
                $sql4 = "SELECT * FROM `" . $prefix . "alliance` WHERE (allianceID = '$Involved[3]')";
                $result4 = mysql_query($sql4);
                $KillersAlliance = mysql_fetch_row($result4);
                $sql4 = "SELECT * FROM `" . $prefix . "corps` WHERE (corpID = '$Involved[2]')";
                $result4 = mysql_query($sql4);
                $KillersCorp = mysql_fetch_row($result4);
                $sql4 = "SELECT * FROM `" . $prefix . "ships` WHERE (ID = '$Involved[4]')";
                $result4 = mysql_query($sql4);
                $KillersShip = mysql_fetch_row($result4);

                $killer = $KillersName[1];
                // Laid the final blow?
                if ($Involved[7] == 1)
                {
                    $killer .= " (laid the final blow)";
                }

                $involved .= sprintf("Name: %s\r\nSecurity: %s\r\nAlliance: %s\r\nCorp: %s\r\nShip: %s\r\nWeapon: %s\r\n\r\n", html_entity_decode($killer, ENT_QUOTES), $Involved[5], html_entity_decode($KillersAlliance[1], ENT_QUOTES), html_entity_decode($KillersCorp[1], ENT_QUOTES), $KillersShip[1], html_entity_decode($Involved[6], ENT_QUOTES));
            }
            // echo $involved;
            # ###### Get the Destroyed items ########
            $sql5 = "SELECT * FROM `" . $prefix . "items` WHERE (battle_id = '$KillDetails[0]')";
            $result5 = mysql_query($sql5);

            while ($destroyedItems = mysql_fetch_array($result5))
            {
                $destroyed = $destroyedItems[1];
                $destroyed = str_replace("", "", $destroyed);
                $destroyed = str_replace("<br />", "", $destroyed);
                $destroyed = str_replace("Type: ", "", $destroyed);
                $destroyed = str_replace("(Fitted - Medium slot)", "", $destroyed);
                $destroyed = str_replace("(Fitted - High slot)", "", $destroyed);
                $destroyed = str_replace("(Fitted - Low slot)", "", $destroyed);
                $destroyed = str_replace("(Cargo)", "", $destroyed);
                $destroyed = str_replace("(Drone Bay)", "", $destroyed);
                $destroyed = str_replace("\nQuantity:", ", Qty:", $destroyed);
                $destroyed = html_entity_decode($destroyed, ENT_QUOTES);
            }

            $exportKM = $victim . "Involved parties:\r\n\r\n" . $involved . "\r\nDestroyed items:\r\n\r\n" . $destroyed;
            // echo str_replace("\r\n","<br>",$exportKM);
            $openfile = "export/" . $count . ".txt";
            $handle = fopen($openfile, 'w');

            fwrite($handle, $exportKM);
            fclose($handle);
            ++$count;
        }

        echo "" . $_SESSION['export'] . "-";
    }

    ?>

<form id="export" name="export" method="post" action="">
   <label>continue
   <input type="submit" name="Export" value="Continue" />
   </label>
</form>
<?php }
else
{
    unset($_SESSION['export']);
    echo $_SESSION['export'];
    ?>
<form id="export" name="export" method="post" action="export.php">
   <label>Export
   <input type="submit" name="Export" value="submit" />
   </label>
</form>
<?php }
?>
