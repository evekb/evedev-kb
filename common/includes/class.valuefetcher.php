<?php
/**
 * @package EDK
 */
class valueFetcher
{
	private $url;

	/**
	 * @param string $url URL for item price xml
	 */
	public function valueFetcher($url)
	{
		// Check the input
		if ($url == null || $url == "") {
			die("ERROR");
		}
		$this->url = $url;
	}

	private function fetchItemValues()
	{
		$content = "";
		// Fetch the file.
		// Switch fopen to cURL if it exists
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			$content = curl_exec($ch);
			curl_close($ch);
			// CHeck that data exists
			if (strlen($content) == 0) {
				return 0;
			}
		} else {
			$file = fopen($this->url, "r");
			// Check that the file really is open
			if (!$file) {
				return 0;
			}
			// Grab contents and close
			$content = stream_get_contents($file);
			fclose($file);
		}
		return $content;
	}

	/**
	 * Fetch item values.
	 * 
	 * @return int The count of values fetched
	 */
	public function fetch_values()
	{
		// New query
		$qry = DBFactory::getDBQuery();
		$items = array();


		// Fetch normal items
		$sxe = new SimpleXMLElement($this->fetchItemValues());
		// Check that file contains data
		if (!count($sxe->result[0]->rowset[0])) {
			return 0;
		}
		// Loop ALL std prices
		foreach ($sxe->result[0]->rowset[0]->row as $stat) {
			// If there is almost nothing for sale, AT ALL, don't include!
			if ($stat['vol'] < 5) {
				continue;
			}
			// Use sell median
			$weighted_average = round($stat['median'], 0);
			// Make sure we still have data
			if (!$weighted_average) continue;
			// Add to std array
			$items[(int) $stat['typeID']] = ''.$weighted_average;
		}

		// prepare counter
		$i = 0;
		foreach ($items as $key => $value) {
			// Insert new values into the database and update the old
			// For the first item start the query. For later items add ','
			if ($i) {
				$querytext .=",";
			} else {
				$querytext = "INSERT INTO kb3_item_price (typeID, price) VALUES ";
			}
			$querytext .= "('$key',".number_format($value, 0, '', '').")";
			$i++;
		}
		// Finish query with a check for duplicates. If so, just update
		$querytext .= " ON DUPLICATE KEY UPDATE price = VALUES(price);";
		$qry->execute($querytext);

		config::set('lastfetch', time());
		return $i;
	}
}
