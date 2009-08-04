<?php
function update004(){
	// new trinity ships are wrong saved as T1 shipes
	if (CURRENT_DB_UPDATE < "004" )
	{
		$qry = new DBQuery();

		$query = "UPDATE kb3_ships
					INNER JOIN kb3_ship_classes ON scl_id = shp_class
					SET shp_techlevel = 2
					WHERE scl_class IN ('Electronic Attack Ship','Heavy Interdictor','Black Ops','Marauder','Heavy Interdictor','Jump Freighter')
					AND shp_techlevel = 1;";
		$qry->execute($query);
		config::set("DBUpdate","004");
	}
}

