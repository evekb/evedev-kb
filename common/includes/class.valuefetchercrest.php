<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class ValueFetcherCrestException extends Exception {}
/**
 * Fetches average item prices from CREST
 * 
 * @package EDK
 */
class ValueFetcherCrest
{
    
    /** CREST url pointing to average item prices */
    public static $CREST_PRICES_ENDPOINT = '/market/prices/';
    
    /** the url to fetch item prices from */
    protected $url;

    /**
     * @param string $url URL for item price xml
     * @param string $factionurl URL for faction price xml
     */
    public function __construct($url)
    {
        // Check the input
        if ($url != null && $url != "" && (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://')) 
        {
            $this->url = $url;
        }
        
        else
        {
            $this->url = CREST_PUBLIC_URL . self::$CREST_PRICES_ENDPOINT;
        }
    }

    /**
     * Fetch item values.
     * 
     * @return int The count of values fetched
     * @throws ValueFetcherCrestException
     */
    public function fetchValues()
    {
        // New query
        $qry = DBFactory::getDBQuery();

        // fetch and decode JSON
        $data = SimpleCrest::getReferenceByUrl($this->url);

        if(!isset($data->items) || count($data->items) < 1)
        {
            return 0;
        }

        $numberOfItemsUpdated = 0;
        $numberOfItemsSkipped = 0;
        foreach ($data->items as $item) 
        {
            // use averagePrice (alternative is adjustedPrice, but it's not public what it's adjusted to)
            $itemPrice = @(float)$item->averagePrice;
            $typeId = @(int)$item->type->id;
            
            // use adjustedPrice as fallback if averagePrice is not available
            if(!$itemPrice)
            {
                $itemPrice = @(float)$item->adjustedPrice;
            }
            

            // Make sure we still have data
            if (!$itemPrice || !$typeId) 
            {
                $numberOfItemsSkipped++;
                continue;
            }
            
            // handle item values not correctly represented by market/adjustedPrice, e.g. super capitals
            $itemPrice = self::handleSpecialItemValues($typeId, $itemPrice);


            // Insert new values into the database and update the old
            // For the first item start the query. For later items add ','
            if ($numberOfItemsUpdated > 0) 
            {
                $querytext .=",";
            } 

            else 
            {
                $querytext = "INSERT INTO kb3_item_price (typeID, price) VALUES ";
            }
            $querytext .= "($typeId,".number_format($itemPrice, 0, '', '').")";

            $numberOfItemsUpdated++;
        }

        // Finish query with a check for duplicates. If so, just update
        $querytext .= " ON DUPLICATE KEY UPDATE price = VALUES(price);";

        $qry->execute($querytext);
        config::set('lastfetch', time());
        return $numberOfItemsUpdated;
    }
    
    /**
     *  handle item values not correctly represented by market/adjustedPrice, e.g. super capitals
     * @param int $typeId the typeID for the item
     * @param double $itemPrice the item price returned by CREST
     */
    static function handleSpecialItemValues($typeId, $itemPrice)
    {
        // Supercarrier => 20b
        if(in_array($typeId, array(3628, 22852, 23913, 23917, 23919)))
        {
           return "20000000000"; 
        }
        
        // Revenant => 100b
        else if($typeId == 3514)
        {
            return "100000000000";
        }
        
        // Titans => 100b
        else if(in_array($typeId, array(671, 3764, 11567, 23773)))
        {
            return "100000000000";
        }

        // Source: http://www.reddit.com/r/Eve/comments/1znr1l/etana_still_keeps_its_value/cfvc9yu
        // Utu => 50b
        else if($typeId == 2834)
        {
            return "50000000000";
        }
        
        // Malice => 75b
        else if($typeId == 3516)
        {
            return "75000000000";
        }
        
        // Erinye => 75b
        else if($typeId == 11375)
        {
            return "75000000000";
        }
        
        // Chremoas => 120b
        else if($typeId == 33397)
        {
            return "120000000000";
        }
        
        // Cambion => 105b
        else if($typeId == 32788)
        {
            return "105000000000";
        }
        
        // Adrestia => 100b
        else if($typeId == 2836)
        {
            return "100000000000";
        }
        
        // Vangel => 60b
        else if($typeId == 3518)
        {
            return "60000000000";
        }
        
        // Etana => 70b
        else if($typeId == 32790)
        {
            return "70000000000";
        }
        
        // Moracha => 100b
        else if($typeId == 33395)
        {
            return "100000000000";
        }
        
        // Mimir => 80b
        else if($typeId == 32209)
        {
            return "80000000000";
        }
        
        // Chameleon => 120b
        else if($typeId == 33675)
        {
            return "120000000000";
        }
        
        // Whiptail => 100b
        else if($typeId == 33673)
        {
            return "100000000000";
        }
        
        // Freki => 70b
        else if($typeId == 32207)
        {
            return "70000000000";
        }
        
        // Polaris => 1t
        else if($typeId == 9860)
        {
            return "1000000000000";
        }
        
        // Cockroach => 1t
        else if($typeId == 11019)
        {
            return "1000000000000";
        }
        
        // Federate/State/Imperial/Tribal issue Battleships => 750b
        else if(in_array($typeId, array(13202, 26840, 11936, 11938, 26842)))
        {
            return "750000000000";
        }
        
        
        // default:
        return $itemPrice;       
    }
}
