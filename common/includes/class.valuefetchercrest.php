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
    public static $CREST_URL = 'https://public-crest.eveonline.com/market/prices/';
    
    /** the url to fetch item prices from */
    protected $url;

    /**
     * @param string $url URL for item price xml
     * @param string $factionurl URL for faction price xml
     */
    public function ValueFetcherCrest($url)
    {
        // Check the input
        if ($url != null && $url != "" && (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://')) 
        {
            $this->url = $url;
        }
        
        else
        {
            $this->url = $CREST_URL;
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

            // Make sure we still have data
            if (is_null($itemPrice) || is_null($typeId)) 
            {
                $numberOfItemsSkipped++;
                continue;
            }


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
}
