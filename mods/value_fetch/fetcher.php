<?php
Class Fetcher
{
	var $faction;
	var $xml_parser;
	var $xml_file;
	var $insideitem;
	var $avg_sell;
	var $avg_buy;
	var $typeid;
	var $count;
	var $timestamp;
	var $tooLow;
	var $sell_median;
	var $buy_median;
	var $factionPrice;
//	var $compfile = "http://svn.nsbit.dk/itemfetch/items.xml.gzphp.php";
//	var $uncompfile = "http://svn.nsbit.dk/itemfetch/items.xml.php";
	var $uncompfile = "http://eve.no-ip.de/prices/30d/prices-all.xml";

	function updateShips()
	{
		$qry = new DBQuery();
		$qryins = new DBQuery();
		$str = "SELECT ship.shp_id as id, item.price as price FROM kb3_ships ship JOIN kb3_item_price item ON item.typeID = ship.shp_externalid WHERE item.price > 0";
		$i = 0;
		$qry->execute($str);
		while ($row = $qry->getRow())
		{
			if($i) $querytext .=",";
			else $querytext="INSERT INTO kb3_ships_values (shp_id, shp_value) VALUES ";
			$querytext .= "('".$row['id']."','".$row['price']."')";
			$i++;
		}
		$querytext .= " ON DUPLICATE KEY UPDATE shp_value = VALUES(shp_value);";
		$qry->execute($querytext);
		return $i;
	}

	// Some of this work is based of the value_editor from Eve-dev killboard
	function fetch_values_php5($factionin) 
	{
		$this->faction = $factionin;
		// Fetch the gzip file.
		// Switch fopen to cURL if it exists
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->uncompfile);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			$content = curl_exec($ch);
			curl_close($ch);
			if(strlen($content)==0) return 0;
		}
		else
		{
			$file = fopen($this->uncompfile , "r");

				if (! $file) {
							return 0;
					}
					$content = stream_get_contents($file);
				fclose($file);
		}
        //$content = gzinflate($content);
		// Old style, direct, no gzip!
		// $sxe = simplexml_load_file("http://svn.nsbit.dk/itemfetch/items.xml");
		$sxe = new SimpleXMLElement($content);
		// prepare counter
		$i = 0;
		// New query
		$qry = new DBQuery();
		//$sxe2 = $sxe->result[0]->rowset[0];
		if(!count($sxe->result[0]->rowset[0])) return 0;
		foreach($sxe->result[0]->rowset[0]->row as $stat)
		{
			// If there is almost nothing for sale, AT ALL, don't include!
			if ($stat['vol'] < 5) continue;
			// Same average as used in value_editor (eve_central_sync)
			//$weighted_average = round(((1.6 * $stat->avg_buy_price + 0.8 * $stat->avg_sell_price) / 2),0);
			// Use global sell prices
			//$weighted_average = round($stat->avg_sell_price,0);
			// Use sell median
			$weighted_average = round($stat['median'],0);

			//if (($this->faction == true) && ($stat->factionPrice > 0))
			//	$weighted_average = round($stat->factionPrice,0);
			if (!$weighted_average) continue;
			// Insert new values into the database and update the old
			// For the first item start the query. For later items add ','
			if($i) $querytext .=",";
			else $querytext="INSERT INTO kb3_item_price (typeID, price) VALUES ";
			$querytext .= "(".$stat['typeID'].",".number_format($weighted_average, 0, '', '').")";
			$i++;
		}
		// Finish query with a check for duplicates. If so, just update
		$querytext .= " ON DUPLICATE KEY UPDATE price = VALUES(price);";
		$qry->execute($querytext);
		//return "Count: ".$i." <br><br>Cached on: ".date('H:i:s - j/m/Y',(int)($sxe->timestamp));
		return "Count: ".$i;
	}

	function fetch_values_php4($factionin)
	{
		// PHP4 section still needs rewriting for the new feed.
		return 0;
		$this->faction = $factionin;
		$this->sell_median = null;
		$this->buy_median = null;
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser, $this);
		// use case-folding so we are sure to find the tag in $map_array
		//xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, true);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($this->xml_parser, "characterData");
		
		if (!($fp = fopen($this->uncompfile, "r"))) {
			die("could not open XML input");
		}    

		while ($data = fread($fp, 4096)) 
		{
			if (!xml_parse($this->xml_parser, $data, feof($fp))) 
			{
				die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->xml_parser)), xml_get_current_line_number($this->xml_parser)));
			}
		}
		return "Count: ".$this->count." <br><br>Cached on: ".date('H:i:s - j/m/Y',$this->timestamp);
	}


	function characterData($parser, $data)
	{
		switch($this->tag)
		{
			case "timestamp":
				$this->timestamp .= $data;
				break;
			case "total_sell_volume":
				$this->tooLow .= $data;
				break;
			case "typeid":
				$this->typeid .= $data;
				break;
			case "avg_buy_price":
				$this->avg_buy .= $data;
				break;
			case "avg_sell_price":
				$this->avg_sell .= $data;
				break;
			case "buy_median":
				$this->buy_median .= $data;
				break;
			case "sell_median":
				$this->sell_median .= $data;
				break;
			case "factionprice":
				$this->factionPrice .= $data;
				break;
		}
	}


	function startElement($parser, $name, $attrs)
	{
		if ($this->insideitem)
		{
			$this->tag = strtolower($name);
		}
		elseif (strtolower($name) == "timestamp")
		{
			$this->tag = strtolower($name);
		}
		elseif (strtolower($name) == "market_stat")
		{
			$this->insideitem = true;
		}
	}

	function endElement($parser, $name)
	{
		if ($this->insideitem && (strtolower($name) == "market_stat"))
		{ 
			if (true)//($this->tooLow > 4)
			{ 
				$qry = new DBQuery();
				// Old average calculation
				//$weighted_average = round(((1.6 * $this->avg_buy + 0.8 * $this->avg_sell) / 2),0);
				// New average using sell only
				//$weighted_average = round($this->avg_sell_price,0);
				// Median prices, if exists
				if (($this->sell_median != null) && ($this->sell_median != 0))
					$weighted_average = round($this->sell_median,0);
				else
					$weighted_average = round($this->avg_sell,0);
				if (($this->faction == true) && ($this->factionPrice != null))
				{
					$weighted_average = round($this->factionPrice,0);
				}
				$qry->execute("REPLACE INTO kb3_item_price (typeID, price) VALUES ('".$this->typeid."','".number_format($weighted_average, 0, '', '')."')");
				$this->count++;
			}
			$this->insideitem = false;
			$this->typeid = null;
			$this->avg_buy = null;
			$this->avg_sell = null;
			$this->tooLow = null;
			$this->buy_median = null;
			$this->sell_median = null;
			$this->factionPrice = null;
		}
	}

	function destroy() 
	{
		xml_parser_free($this->xml_parser);
	}
}
?>
