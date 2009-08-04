<?php
// Add alliance and corp summary tables.
function update011()
{
	global $url, $header, $footer;
	//Checking if this Update already done
	if (CURRENT_DB_UPDATE < "011" )
	{
		$qry = new DBQuery();
		$sql = "ALTER TABLE `kb3_ships` CHANGE `shp_baseprice` `shp_baseprice` BIGINT( 12 ) NOT NULL DEFAULT '0'";
		$qry->execute($sql);

		config::set("DBUpdate", "011");
		echo $header;
		echo "Update 011 completed.";
		echo $footer;
		die();
	}
}

