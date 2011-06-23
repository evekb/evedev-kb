<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 *	Verify that the EVE central tracking table exists.
 */
function verify_sync_table() {
	$query = DBFactory::getDBQuery();;

	$query->execute("SHOW TABLES LIKE 'kb3_eve_central'");
	if ($query->recordCount() == 0) {
		$query->execute("CREATE TABLE kb3_eve_central (item_id int unsigned not null, item_price varchar(20), last_updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, primary key (item_id)) engine=myisam comment='EVE central sync tracker'");
	}

        if (!file_exists(KB_CACHEDIR.'/evecentral'))
        {
            if (!mkdir(KB_CACHEDIR.'/evecentral', 0770))
                {
                        // creating folder failed - spam something about that
                        echo "Failed to create folder \'".KB_CACHEDIR."/evecentral/activity.log/activity.log\' you should create the folder yourself and set chmod 777";
                }
        }
}

/**
 *	Retrieve the item value from EVE Central
 */
function ec_get_value($item_id) {
    $query = DBFactory::getDBQuery();;

    $query->execute("SELECT item_price, unix_timestamp(last_updated) last_upd FROM kb3_eve_central WHERE item_id=$item_id");
    // If there's 1 record, then we have an archived value.
    if ($query->recordcount() == 1) {
        // Is it recent enough?
        $data = $query->getrow();
        if (48*3600 > (date('U') - $data['last_upd'])) {
            file_put_contents(KB_CACHEDIR . '/evecentral/activity.log', "Handling from cache.\n", FILE_APPEND);
            return $data['item_price'];
        } else {
            // Not recent enough, interrogate EVE Central
            return ask_eve_central($item_id);
        }
    } else {
        return ask_eve_central($item_id);
    }
}

/**
 *	Query EVE Central's XML feed.
 */
function ask_eve_central($item_id) {
	file_put_contents(KB_CACHEDIR.'/evecentral/activity.log', "Handling from live.\n", FILE_APPEND);
	$query = DBFactory::getDBQuery();;
	if (function_exists('curl_init'))
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://api.eve-central.com/api/marketstat?regionlimit=10000002&typeid=".$item_id."");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);
		curl_close($ch);
	}
	else
	{
		$file = fopen("http://api.eve-central.com/api/marketstat?regionlimit=10000002&typeid=".$item_id."" , "r");
		if (! $file)
		{
			return -99;
		}
		$content = stream_get_contents($file);
		fclose($file);
	}


	if(strpos($content, '</evec_api>') == false) {
		return -99;
	}
	else {
		$parse = new XMLParser();
		$parse->preparseXML();
		$parse->parseXML($content);

		$values = $parse->get_data();

		if (0 == $values['WEIGHTED']) {
	return -99;
		}
		$weighted_average = $values['WEIGHTED'];
	}

	file_put_contents(KB_CACHEDIR.'/evecentral/activity.log', "$content\n", FILE_APPEND);
	
	$query->execute("REPLACE INTO kb3_eve_central (item_id, item_price) VALUES ($item_id, '$weighted_average')");
	return $weighted_average;
}

/**
 *	Wrapper to do all to work.  Updates the items table based on the cached or live data.
 */
function ec_update_value($item_id) {
    $query = DBFactory::getDBQuery();;

    // Don't try if we can't open URLs with fopen.
	// Do to!
//    if (1 != function_exists('curl_init')) {
//            return;
//    }
    // Don't try if the item id isn't an integer.
    if (!is_numeric($item_id)) {
            return;
    }
    // Verify we have a sync table to use.
    verify_sync_table();
    // The destroyed items etc feed in the -internal- killboard item ID.
    // EVE Central needs the external ID if we have it.
    $query->execute("SELECT typeID FROM kb3_invtypes WHERE typeID=$item_id");
    $data = $query->getRow();
    $e_item_id = $data['typeID'];
    // Don't try if the item id isn't an integer or it's 0.
    if (!is_numeric($e_item_id) OR 0 == $e_item_id) {
            return;
    }
    file_put_contents(KB_CACHEDIR.'/evecentral/activity.log', "Request for $item_id -> $e_item_id\n", FILE_APPEND);

    $value = ec_get_value($e_item_id);
    if (-99 != $value) {
		$query->execute("update kb3_item_price set price='$value' WHERE typeID=$item_id");
		return true;
    } else {
            file_put_contents(KB_CACHEDIR.'/evecentral/activity.log', "Failed to find it.\n", FILE_APPEND);
	}
    return false;
}

/**
 * @package EDK
 */
class XMLParser {
    private $allKinds = 0;
    private $data = array();
    private $tagName = "";
    private $singular = "";
    private $parser = null;

    function preparseXML() {
        $this->parser = xml_parser_create();

        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");
    }

    function parseXML($rawXML) {
        return xml_parse($this->parser, $rawXML);
    }

    function tag_open($parser, $tag, $attributes) {
        switch($tag) {
            case "BUY": { $this->allKinds = 2; break; }
            case "SELL": { $this->allKinds = 3; break; }
        }
        $this->singular = null;
        $this->tagName = $tag;
    }

    /*The space between tags is interpreted here.*/
    function cdata($parser, $cdata) {
        $this->singular .= $cdata;
    }

    /*Runs through the closing XML tags  */
    function tag_close($parser, $tag) {
        switch($this->allKinds) {
            case 2: {
                if($tag == "MEDIAN") {
                    $this->data['BUY_MED'] = $this->singular;
                }
                break;
            }
            case 3: {
                if($tag == "MEDIAN") {
                    $this->data['SELL_MED'] = $this->singular;
                    $this->data['WEIGHTED'] = round(((($this->data['BUY_MED'] * 1.4) + ($this->data['SELL_MED'] * 0.6))) / 2, 2);
                }
                break;
            }
        }
    }

    function get_data() {
        return $this->data;
    }
}