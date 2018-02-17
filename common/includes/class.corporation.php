<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

use EDK\ESI\ESI;
use EsiClient\CorporationApi;
use Swagger\Client\ApiException;
use Swagger\Client\Model\GetCorporationsCorporationIdOk;

/**
 * Creates a new Corporation or fetches an existing one from the database.
 * @package EDK
 */
class Corporation extends Entity
{
    /** @var integer */
    protected $id = null;
    /** @var integer */
    protected $externalid = null;
    /** @var string */
    protected $name = null;
    /** @var Alliance */
    private $alliance = null;
    /** @var integer */
    private $updated = null;

    /**
     * Create a new Corporation object from the given $id.
     *
     * @param integer $id The corporation ID.
     * @param boolean $externalIDFlag true if the id is the external id.
    */
    function __construct($id = 0, $externalIDFlag = false)
    {
        if($externalIDFlag) $this->externalid=intval($id);
        else $this->id = intval($id);
    }
    /**
     * Return true if this corporation is an NPC corporation.
     *
     * @return boolean True if this corporation is an NPC corporation.
     */
    function isNPCCorp()
    {
        if($this->externalid > 1000001 && $this->externalid < 1000183)
            return true;
        // These are NPC alliances but they may show up as corps on mails.
        else if($this->externalid > 500000 && $this->externalid < 500021)
            return true;
        else return false;
    }

    /**
     * Return a URL for the icon of this corporation.
     *
     * If a cached image exists then return the direct url. Otherwise return
     * a link to the thumbnail page.
     *
     * @param integer $size The size in pixels of the image needed.
     * @return string The URL for this corporation's logo.
     */
    function getPortraitURL($size = 64)
    {
        if(!$this->externalid) $this->getExternalID();

        // NPC alliances can be recorded as corps on killmails.
        if($this->externalid > 500000 && $this->externalid < 500021)
            return imageURL::getURL('Alliance', $this->externalid, $size);

        return imageURL::getURL('Corporation', $this->externalid, $size);
    }

    /**
     * Return a URL for the details page of this Corporation.
     *
     * @return string The URL for this Corporation's details page.
     */
    function getDetailsURL()
    {
        if ($this->getExternalID()) {
            return edkURI::page('corp_detail', $this->externalid, 'crp_ext_id');
        } else {
            return edkURI::page('corp_detail', $this->id, 'crp_id');
        }
    }

    /**
     * Return the corporation CCP ID.
     * When populateList is true, the lookup will return 0 in favour of getting
     * the external ID from CCP. This helps the kill_detail page load times.
     *
     * @param boolean $populateList
     * @return integer
     */
    function getExternalID($populateList = false)
    {
        // sanity check: no factions!
        if(is_numeric($this->externalid) && $this->externalid < 1000000)
        {
            return 0;
        }
        if($this->externalid) return $this->externalid;
        $this->execQuery();
        if(!$populateList)
        {
            if($this->externalid && is_numeric($this->externalid) && $this->externalid > 1000000) return $this->externalid;

            // If we still don't have an external ID then try to fetch it from CCP.
            try
            {
                $this->setExternalID(ESI_Helpers::getExternalIdForEntity($this->getName(), 'corporation'));
                return $this->externalid;
            }

            catch (ApiException $e) 
            {
                EDKError::log(ESI::getApiExceptionReason($e) . PHP_EOL . $e->getTraceAsString());
            }
        }
        
        return 0;
    }

    /**
     * Return an Alliance object for the alliance this corporation belongs to.
     *
     * @return Alliance
     */
    function getAlliance()
    {
        if(!$this->alliance) $this->execQuery();
        return new Alliance($this->alliance);
    }
    /**
     * Lookup a corporation name and set this object to use the details found.
     *
     * @param string $name The corporation name to look up.
    */
    static function lookup($name)
    {
        $qry = DBFactory::getDBQuery();
        $qry->execute("select crp_id from kb3_corps where crp_name = '"
                .slashfix($name)."'");
        if($qry->recordCount()) {
            $row = $qry->getRow();
            return Cacheable::factory('Corporation', (int)$row['crp_id']);
        } else {
            return false;
        }
    }
    /**
     * Search the database for the corporation details for this object.
     *
     * If no record is found but we have an external ID then the result
     * will be fetched from CCP.
    */
    function execQuery()
    {
        // TODO: Should we double the size and record by external id as well?
        // We can't rely on having an external id but if it was used more
        // extensively in EDK then we could cache by external id if we have it
        // and internal id only when we do not.
        if( $this->id && $this->isCached() ) {
            $cache = $this->getCache();
            $this->id = $cache->id;
            $this->externalid = $cache->externalid;
            $this->name = $cache->name;
            $this->alliance = $cache->alliance;
        } else {
            $qry = DBFactory::getDBQuery();
            $sql = "select * from kb3_corps where ";
            if($this->externalid) $sql .= "crp_external_id = ".$this->externalid;
            else $sql .= "crp_id = ".$this->id;
            $qry->execute($sql);
            // If we have an external ID but no local record then fetch from CCP.
            if($this->externalid && !$qry->recordCount())
            {
                // check for success to prevent endless recursive calls
                try
                {
                    if($this->fetchCorp())
                    {
                        $this->putCache();
                    }
                }
                catch (ApiException $e) 
                {
                    EDKError::log(ESI::getApiExceptionReason($e) . PHP_EOL . $e->getTraceAsString());
                }
            } 
            else if($qry->recordCount())
            {
                $row = $qry->getRow();
                $this->id = intval($row['crp_id']);
                $this->name = $row['crp_name'];
                $this->externalid = intval($row['crp_external_id']);
                $this->alliance = $row['crp_all_id'];
                $this->putCache();
            }
        }
    }
    /**
     * Add a new corporation to the database or update the details of an existing one.
     *
     * @param string $name The name of the new corporation.
     * @param Alliance $alliance The alliance this corporation belongs to.
     * @param string $timestamp The timestamp the corporation's details were updated.
     * @param integer $externalID The external CCP ID for the corporation.
     * @param boolean $loadExternals Whether to fetch unknown information from the API.
     * @return Corporation
     */
    static function add($name, $alliance, $timestamp, $externalID = 0, $loadExternals = true)
    {
        if (!$name && !$externalID) 
            {
            trigger_error("Attempt to add a corporation with no name. Aborting.", E_USER_ERROR);
            // If things are going this wrong, it's safer to die and prevent more harm
            die;
        } else if (!$alliance->getID()) {
            trigger_error("Attempt to add a corporation with no alliance. Aborting.", E_USER_ERROR);
            // If things are going this wrong, it's safer to die and prevent more harm
            die;
        }
        $name = stripslashes($name);
        $externalID = (int) $externalID;
        $mysqlTimestamp = toMysqlDateTime($timestamp);
        
        // we don't have an external ID, but we do have a name
        if(!$externalID && $name)
        {
            // check whether we have that corp name in the database, for
            $qry = DBFactory::getDBQuery(true);
            $qry->execute("select * from kb3_corps
                           where crp_name = '".$qry->escape($name)."'");
            // If the corp name is not present or wie should load externals
            if (!$qry->recordCount() || $loadExternals) 
            {
                $externalID = ESI_Helpers::getExternalIdForEntity($name, 'corporation');
            }
            
            // we already know this corp
            else
            {
                $row = $qry->getRow();
                $crp = Corporation::getByID((int)$row['crp_id']);
                $crp->name = $row['crp_name'];
                $crp->externalid = (int) $row['crp_external_id'];
                $crp->alliance = $row['crp_all_id'];
                if (!is_null($row['crp_updated'])) {
                    $crp->updated = strtotime($row['crp_updated']." UTC");
                } else {
                    $crp->updated = null;
                }
                if ($row['crp_all_id'] != $alliance->getID()
                                && $crp->isUpdatable($timestamp)) {
                    $sql = 'update kb3_corps set crp_all_id = '.$alliance->getID().', ';
                    $sql .= "crp_updated = '".$mysqlTimestamp."' ".
                                    "where crp_id = ".$crp->id;
                    $qry->execute($sql);
                    $crp->alliance = $alliance->getID();
                }
                if (!$crp->externalid && $externalID) {
                    $crp->setExternalID((int)$externalID);
                }
                return $crp;
            }
        }
        
        // first check whether this corporation already exists by external ID
        if($externalID > 0)
        {
            $Corp = self::getByExternalID($externalID);
            if(!is_null($Corp))
            {
                // check if we can update this corp
                if($Corp->isUpdatable($timestamp))
                {
                    $updateCorp = new DBPreparedQuery();
                    $updateCorp->prepare('UPDATE kb3_corps SET crp_name = ?, crp_all_id = ?, crp_updated = ? WHERE crp_external_id = ?');
                    $types = 'sisi';
                    $allianceID = $alliance->getID();
                    $arr = array(&$types, &$name, &$allianceID, &$mysqlTimestamp, &$externalID);
                    $updateCorp->bind_params($arr);
                    $updateCorp->execute();
                    
                    Cacheable::delCache($Corp);
                    return new Corporation($externalID, true);
                }
                
                return $Corp;
            }
            
            // we need to fetch this Corp from the API
            else if($loadExternals)
            {
                $Corp = new Corporation($externalID, true);
                $Corp->fetchCorp();
                $Corp->putCache();
                return $Corp;                
            }
            
            // add this corp with the given data
            $qry = DBFactory::getDBQuery(true);
            $qry->execute("insert into kb3_corps ".
                                "(crp_name, crp_all_id, crp_external_id, crp_updated) ".
                                "values ('".$qry->escape($name)."',".$alliance->getID().
                                ", ".$externalID.", '".$mysqlTimestamp."') on duplicate key update crp_external_id = ".$externalID.", crp_updated = '".$mysqlTimestamp."'");
            
            return new Corporation($externalID, true);
        }
        
        else
        {
            // Neither corp name or external id was found so add this corp as new
            $qry = DBFactory::getDBQuery(true);
            $qry->execute("insert into kb3_corps ".
                                "(crp_name, crp_all_id, crp_updated) ".
                                "values ('".$qry->escape($name)."',".$alliance->getID().
                                ", '".$mysqlTimestamp."')");
            
            return new Corporation($qry->getInsertID(), false);
        }
        
        return false;
    }
    /**
     * Return whether this corporation was updated before the given timestamp.
     *
     * @param string $timestamp A timestamp to compare this corporation's details with.
    */
    function isUpdatable($timestamp)
    {
        $timestamp = toMysqlDateTime($timestamp);
        if(isset($this->updated))
            if(is_null($this->updated) || strtotime($timestamp." UTC") > $this->updated) return true;
            else return false;
        $qry = DBFactory::getDBQuery();
        $qry->execute("select crp_id from kb3_corps
                       where crp_id = ".$this->id."
                       and ( crp_updated < '".$timestamp."' 
                       or crp_updated is null )");
        return $qry->recordCount() == 1;
    }

    /**
     * Set the CCP external ID for this corporation.
     *
     * If the same externalid already exists then that corp name is changed to
     * the new one.
     *
     * @param integer $externalid The new external id to set for this corp.
     * @return boolean
     */
    function setExternalID($externalid)
    {
        $externalid = intval($externalid);
        if($externalid && $this->id)
        {
            $this->execQuery();
            $qry = DBFactory::getDBQuery(true);
            $qry->execute("SELECT crp_id FROM kb3_corps WHERE crp_external_id = ".$externalid." AND crp_id <> ".$this->id);
            if($qry->recordCount())
            {
                $result = $qry->getRow();
                $old_id = $result['crp_id'];
                $qry->autocommit(false);
                $qry->execute("UPDATE kb3_pilots SET plt_crp_id = ".$old_id." WHERE plt_crp_id = ".$this->id);
                $qry->execute("UPDATE kb3_kills SET kll_crp_id = ".$old_id." WHERE kll_crp_id = ".$this->id);
                $qry->execute("UPDATE kb3_inv_detail SET ind_crp_id = ".$old_id." WHERE ind_crp_id = ".$this->id);
                $qry->execute("UPDATE kb3_inv_crp SET inc_crp_id = ".$old_id." WHERE inc_crp_id = ".$this->id);
                $qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$this->id);
                $qry->execute("DELETE FROM kb3_sum_corp WHERE csm_crp_id = ".$old_id);
                $qry->execute("DELETE FROM kb3_corps WHERE crp_id = ".$this->id);
                $qry->execute("UPDATE kb3_corps SET crp_name = '".$qry->escape($this->name)."' where crp_id = ".$old_id);
                $qry->autocommit(true);
                $this->id = $old_id;
                $this->putCache();
                return true;
            }
                        
                        // update the database with this ID, but don't return it!
            if($qry->execute("UPDATE kb3_corps SET crp_external_id = ".$externalid." where crp_id = ".$this->id) && $externalid > 1000000)
            {
                $this->externalid = $externalid;
                $this->putCache();
                return true;
            }
        }
        return false;
    }
    
    /**
     * Return the corporation ID.
     *
     * @return integer
     */
    function getID()
    {
        if ($this->id) 
        {
            return $this->id;
        } 
        
        elseif ($this->externalid) 
        {
            $this->execQuery();
            return $this->id;
        }        
        return 0;
    }

    /**
     * Returns an array of pilots we know to be in this corp.
     *
     * @return Pilot
     */
    function getMemberList()
    {
        $qry = DBFactory::getDBQuery();
        $qry->execute("SELECT plt_id FROM kb3_pilots
                       WHERE plt_crp_id = " . $this->id);

        if ($qry->recordCount() < 1)
            return null;
        else
        {
            $list = array();
            while ($row = $qry->getRow())
            {
                $pilot = new Pilot($row['plt_id']);
                $list[] = $pilot;
            }
        }
        return $list;
    }

    /**
     * Fetch corporation details and alliance from CCP using the external ID.
     * The corporation and alliance will be added to the database, or an existing entry will be updated.
     * <p>
     * This always executes an ESI call!
     *
     * @return GetCorporationsCorporationIdOk the ESI corporation object
     * @throws ApiException
     */
    public function fetchCorp()
    {
        if(!$this->externalid) 
        {
            $this->execQuery();
        }
        
        if(!$this->externalid) 
        {
            return false;
        }
        
        // create EDK ESI client
        $EdkEsi = new ESI();
        $CorporationApi = new CorporationApi($EdkEsi);

        // only get the ESI corp representation and the headers, we don't need the status code
        list($EsiCorp, , $headers) = $CorporationApi->getCorporationsCorporationIdWithHttpInfo($this->externalid);

        $allianceId = $EsiCorp->getAllianceId();
        if($allianceId)
        {
            $Alliance = new Alliance($allianceId, true);
        }

        else
        {
            $Alliance = Alliance::add("None");
        }

        $crp = Corporation::add(slashfix($EsiCorp->getName()), $Alliance, ESI_Helpers::formatRFC7231Timestamp($headers['Last-Modified']), (int) $this->externalid, false);

        $this->name = $crp->getName();
        $this->alliance = $crp->getAlliance()->getID();
        $this->updated = ESI_Helpers::formatRFC7231Timestamp($headers['Last-Modified']);
        $this->id = $crp->getID();

        return $EsiCorp;
    }

    /**
     * Return a new object by ID. Will fetch from cache if enabled.
     *
     * @param mixed $id ID to fetch
     * @return Corporation
     */
    static function getByID($id)
    {
        return Cacheable::factory(get_class(), $id);
    }
    
    /**
     * Gets a corp by its external ID. Will fetch from cache if enabled.
     *
     * @param mixed $externalId ID to fetch $id
     * @return \Corporation the corp, if found
     */
    static function getByExternalID($externalId)
    {
        $getIdByExternalId = new DBPreparedQuery();
        $getIdByExternalId->prepare('SELECT crp_id FROM kb3_corps WHERE crp_external_id = ?');
        $corpId = NULL;
        $arr = array(&$corpId);
        $getIdByExternalId->bind_results($arr);
        $types = 'i';
        $arr2 = array(&$types, &$externalId);
        $getIdByExternalId->bind_params($arr2);

        $getIdByExternalId->execute();
        if($getIdByExternalId->recordCount() > 0)
        {
            $getIdByExternalId->fetch();
            return Cacheable::factory(get_class(), $corpId);
        }

        return NULL;
    }
}
