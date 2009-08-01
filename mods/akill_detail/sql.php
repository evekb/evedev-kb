<?php
function createtable() {
    $query = "INSERT IGNORE INTO `kb3_item_locations` (`itl_id`, `itl_location`) VALUES(7, 'Subsystem Slot'); UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 954 LIMIT 1; UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 955 LIMIT 1; UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 956 LIMIT 1; UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 957 LIMIT 1; UPDATE `kb3_item_types` SET `itt_slot` = '7' WHERE `kb3_item_types`.`itt_id` = 958 LIMIT 1;";

$arr= explode( '; ', $query );
foreach( $arr as $command )
{
    $queryresult = mysql_query( $command ) or die ('Error with MySQL Query : ' .mysql_error());
}

    //$queryresult = mysql_query($query) or die ('Error with MySQL Query : ' .mysql_error());

    if ($queryresult) {
	return(1);
    } else {
	return(0);
    }
}
?>