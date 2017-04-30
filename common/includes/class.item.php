<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

use EDK\ESI\ESI;
use Swagger\Client\ApiException;
use EsiClient\UniverseApi;

/**
 * Contains the details about an Item.
 * @package EDK
 */
class Item extends Cacheable
{       
    /** @var int attribute ID holding the maximum size of a fighter squadron
     */
    public static $ATTRIBUTE_ID_FIGHTER_SQUADRON_MAX_SIZE = 2215;
    
	private $executed = false;
	private $id = 0;
	private $row_ = null;
    private $slotId = 0;

    /** @var int category ID indicating this item is a drone */
    public static $CATEGORY_ID_DRONE = 18;
        
    /**
     * Construct a new Item.
     *
     * If $row is set then it will be used as an array of item attributes for
     * this Item. Otherwise the attributes will be fetched from the db using
     * the given ID.
     *
     * @param integer $id Item ID
     * @param array $row Array of attributes.
     */
    function __construct($id = 0, $row = null)
    {
        $this->id = (int) $id;
        if (isset($row)) {
            $this->row_ = $row;
            $this->executed = true;
                        // evaluate slot
                        // do we have an effectID indicating the slot?
                        $slotIndicator = $this->row_['slotIndicator'];
                        if($slotIndicator && array_key_exists($slotIndicator, InventoryFlag::$EFFECT_ID_SLOT_MAPPING))
                        {
                                $this->slotId = InventoryFlag::$EFFECT_ID_SLOT_MAPPING[$slotIndicator];
                        }

                        else if($this->row_['itt_cat'] == self::$CATEGORY_ID_DRONE)
                        {
                                $this->slotId = InventoryFlag::$DRONE_BAY;
                        }

                        else
                        {
                                $this->slotId = InventoryFlag::$OTHER;
                        }
        } else if ($this->isCached()) {
            $cache = $this->getCache();
            $this->row_ = $cache->row_;
            $this->slotId = $cache->slotId;
            $this->executed = true;
        }
    }

    public function getID()
    {
        return $this->id;
    }

    public function getExternalID()
    {
        return $this->getID();
    }

    public function getName()
    {
        if (!$this->row_['typeName']) {
            $this->execQuery();
        }
        return $this->row_['typeName'];
    }

    /**
     * Return the URL for an icon for this item.
     * @param integer $size
     * @param boolean $full Whether to return a full image tag.
     * @return string
     */
    public function getIcon($size, $full = true)
    {
        $this->execQuery();
        global $smarty;
        $img = imageURL::getURL('InventoryType', $this->id, $size);

        if (!$full) {
            return $img;
        }
        if ($size == 24) {
            $show_style = '_'.config::get('fp_ammostyle');
            $t_s = config::get('fp_ttag');
            $f_s = config::get('fp_ftag');
            $d_s = 0;
            $o_s = 0;
        } elseif ($size == 48 || $size = 32) {
            $show_style = '_'.config::get('fp_highstyle');
            $t_s = config::get('fp_ttag');
            $f_s = config::get('fp_ftag');
            $d_s = config::get('fp_dtag');
            $o_s = config::get('fp_otag');
        } else {
            $show_style = "";
            $t_s = 1;
            $f_s = config::get('kd_ftag');
            $d_s = config::get('kd_dtag');
            $o_s = config::get('kd_otag');
        }
        if ($show_style == "_none" || $show_style == "_") {
            $show_style = "";
        }

        if ($show_style == "_tag" || $show_style == "") {
            return "<img src='$img' title=\"".$this->getName()."\" alt=\"".$this->getName()."\" style='width:{$size}px; height:{$size}px; border:0px' />";
        }

        $it_name = $this->getName();
        if (($this->row_['itm_techlevel'] == 5) && $show_style) { // is a T2?
            $icon .= config::get('cfg_img').'/items/'.$size.'_'.$size.'/t2'.$show_style.'.png';
        } elseif (($this->row_['itm_techlevel'] > 5) && ($this->row_['itm_techlevel'] < 10) && $show_style) { // is a faction item?
            $icon .= config::get('cfg_img').'/items/'.$size.'_'.$size.'/f'.$show_style.'.png';
        } elseif (($this->row_['itm_techlevel'] > 10) && strstr($it_name, "Modified") && $show_style) { // or it's an officer?
            $icon .= config::get('cfg_img').'/items/'.$size.'_'.$size.'/o'.$show_style.'.png';
        } elseif (($this->row_['itm_techlevel'] > 10) && (strstr($it_name, "-Type")) && $show_style) { // or it's just a deadspace item.
            $icon .= config::get('cfg_img').'/items/'.$size.'_'.$size.'/d'.$show_style.'.png';
        } elseif (
                (strstr($it_name, "Blood ")
                || strstr($it_name, "Sansha")
                || strstr($it_name, "Arch")
                || strstr($it_name, "Domination")
                || strstr($it_name, "Republic")
                || strstr($it_name, "Navy")
                || strstr($it_name, "Guardian")
                || strstr($it_name, "Guristas")
                || strstr($it_name, "Shadow")
                ) && $show_style
        ) { // finally if it's a faction it should have its prefix
            $icon = config::get('cfg_img').'/items/'.$size.'_'.$size.'/f'.$show_style.'.png';
        } else { // but maybe it was only a T1 item :P
            return "<img src='$img' title=\"".$this->getName()."\" alt=\"".$this->getName()."\" style='width:{$size}px; height:{$size}px; border:0px' />";
        }

        if (($size == 32 || $size == 48 || true) && ($show_style == '_backglowing')) {
            $temp = $img;
            $img = $icon;
            $icon = $temp;
        }

        $smarty->assign('img', $img);
        $smarty->assign('icon', $icon);
        $smarty->assign('name', $it_name);
        return $smarty->fetch(get_tpl('icon'.$size));
    }

    public function getSlot()
    {
        $this->execQuery();
        return $this->slotId;
    }

    private function execQuery()
    {
        if (!$this->executed) {
            if (!$this->id) {
                return false;
            }
            if ($this->id && $this->isCached()) {
                $this->row_ = $this->getCache()->row_;
                                $this->slotId = $this->getCache()->slotId;
                $this->executed = true;
                return;
            }

            $qry = DBFactory::getDBQuery();

            $sql = "select inv.*, kb3_item_types.*, dga.value as techlevel,
                   itp.price, dc.value as usedcharge, dl.value as usedlauncher, te.effectID as slotIndicator
                   from kb3_invtypes inv
                   left join kb3_dgmtypeattributes dga on dga.typeID=inv.typeID and dga.attributeID=633
                   left join kb3_item_price itp on itp.typeID=inv.typeID
                   left join kb3_item_types on groupID=itt_id
                   left join kb3_dgmtypeattributes dc on dc.typeID = inv.typeID AND dc.attributeID IN (128)
                   left join kb3_dgmtypeattributes dl on dl.typeID = inv.typeID AND dl.attributeID IN (137,602,603)
                                   left join kb3_dgmtypeeffects te on te.typeID = inv.typeID AND te.effectID IN ("
                                        .implode(", ", array_keys(InventoryFlag::$EFFECT_ID_SLOT_MAPPING))
                                   .")
                   where inv.typeID = '".$this->id."'";
            if ($qry->execute($sql)) {
                $this->row_ = $qry->getRow();
                $this->executed = true;
                                
                                // evaluate slot
                                // do we have an effectID indicating the slot?
                                $slotIndicator = $this->row_['slotIndicator'];
                                if($slotIndicator && array_key_exists($slotIndicator, InventoryFlag::$EFFECT_ID_SLOT_MAPPING))
                                {
                                        $this->slotId = InventoryFlag::$EFFECT_ID_SLOT_MAPPING[$slotIndicator];
                                }
                                
                                else if($this->row_['itt_cat'] == self::$CATEGORY_ID_DRONE)
                                {
                                        $this->slotId = InventoryFlag::$DRONE_BAY;
                                }
                                
                                else
                                {
                                        $this->slotId = InventoryFlag::$OTHER;
                                }

                $this->putCache();
            }
        }
    }

    /**
     * Lookup an Item by name
     * @param string $name
     * @return Item|boolean
     */
    public static function lookup($name)
    {
        static $cache_name;
        if (isset($cache_name[$name])) {
            return $cache_name[$name];
        }
        $name = trim(stripslashes($name));
        $qry = DBFactory::getDBQuery();
        $query = "select typeID as itm_id from kb3_invtypes itm
                  where typeName = '".$qry->escape($name)."'";
        $qry->execute($query);
        if (!$qry->recordCount()) {
            $cache_name[$name] = false;
        } else {
            $row = $qry->getRow();
            $cache_name[$name] = Item::getByID((int)$row['itm_id']);
        }
        return $cache_name[$name];
    }

    /**
     * Return typeID by name.
     * @param string $name
     * @return integer return typeID by name, dont change $this->id
     */
    public static function get_item_id($name)
    {
        $qry = DBFactory::getDBQuery();
        $query = "select typeID as itm_id
                  from kb3_invtypes
                  where typeName = '".$qry->escape($name)."'";
        $qry->execute($query);

        $row = $qry->getRow();
        if ($row['itm_id']) {
            return $row['itm_id'];
        }
    }

    public function get_used_launcher_group($name = null)
    {
        if (is_null($name) && $this->executed) {
            return $this->row_['usedlauncher'];
        }
        $qry = DBFactory::getDBQuery();
        // I dont think CCP will change this attribute in near future ;-)
        $query = "SELECT value
                     FROM kb3_dgmtypeattributes d
                     INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
                     WHERE i.typeName = '".$qry->escape($name)."' AND d.attributeID IN (137,602);";
        $qry->execute($query);
        $row = $qry->getRow();
        return $row['value'];
    }

    public function get_used_charge_size($name = null)
    {
        if (is_null($name) && $this->executed) {
            return $this->row_['usedcharge'];
        }
        $qry = DBFactory::getDBQuery();
        // I dont think CCP will change this attribute in near future ;-)
        if (is_null($name)) {
            $query = "SELECT value
                     FROM kb3_dgmtypeattributes d
                     INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
             WHERE i.typeID = ".$this->row_['typeID']." AND d.attributeID IN (128);";
        } else {
            $query = "SELECT value
             FROM kb3_dgmtypeattributes d
             INNER JOIN kb3_invtypes i ON i.typeID = d.typeID
                     WHERE i.typeName = '".$qry->escape($name)."' AND d.attributeID IN (128);";
        }
        $qry->execute($query);
        $row = $qry->getRow();
        return $row['value'];
    }

    public static function get_ammo_size($name)
    {
        $temp = substr($name, strlen($name) - 2, 2);
        if (strstr($name, 'Mining')) {
            $a_size = 1;
        } elseif ($temp == 'XL') {
            $a_size = 4;
        } elseif ($temp == ' L') {
            $a_size = 3;
        } elseif ($temp == ' M') {
            $a_size = 2;
        } elseif ($temp == ' S') {
            $a_size = 1;
        } else {
            $a_size = 0;
        }
        return $a_size;
    }

    /**
     * Return the group ID for this item, or a given typeName
     * @param string $name
     * @return string
     */
    public function get_group_id($name = null)
    {
        if (is_null($name) && $this->executed) {
            return $this->row_['groupID'];
        }
        $qry = DBFactory::getDBQuery();
        if (is_null($name)) {
            $query = "select groupID
                        from kb3_invtypes
                        where typeName = ".$this->row_['typeID'];
        } else {
            $query = "select groupID
                        from kb3_invtypes
                       where typeName = '".$qry->escape($name)."'";
        }
        $qry->execute($query);

        $row = $qry->getRow();
        if ($row['groupID']) {
            return $row['groupID'];
        }
    }

    /**
     * Return an attribute of this item.
     *
     * @param string $key
     * @return string
     */
    public function getAttribute($key)
    {
        if (!$this->executed) {
            $this->execQuery();
        }
        return $this->row_[$key];
    }

    /**
     * Return a new object by ID. Will fetch from cache if enabled.
     *
     * @param mixed $id ID to fetch
     * @return Item
     */
    static function getByID($id)
    {
        $Item = Cacheable::factory(get_class(), $id);
                
        // unknown item?
        if(is_null($Item->getName()))
        {
            // try fetching it from the API
            $typeName = ESI_Helpers::getTypeNameById($id, TRUE);
            if(!is_null($typeName))
            {
                // remove the item with no info from the cache
                self::delCache($Item);
                return self::lookup($typeName);
            }
        }
        return $Item;
    }
        
    /**
     * Fetches the type with the given ID from ESI, adds it to the database
     * along with dogma attributes and effects
     * @param int $typeId
     * @return \Item
     */
    static function fetchItem($typeId)
    {
        // create EDK ESI client
        $EdkEsi = new ESI();
        $UniverseApi = new UniverseApi($EdkEsi);

        try 
        {
            $typeInfo = $UniverseApi->getUniverseTypesTypeId($typeId, $EdkEsi->getDataSource());
        } 
        catch (ApiException $e) 
        {
            // fallback: Use generic item name
            // this database entry will be corrected with the next database update
            // store the item in the database
            $typeName = "Unknown Type ".$typeId;

            $query = new DBPreparedQuery();
            $query->prepare('INSERT INTO kb3_invtypes (`typeID`, `typeName`) VALUES (?, ?)');
            $types = 'is';
            $arr2 = array(&$types, &$typeId, &$typeName);
            $query->bind_params($arr2);
            $query->execute();

            return Item::lookup($typeName);
        }

        $typeName = $typeInfo->getName();
        if($typeName == NULL)
        {
            $typeName = "Unknown Item ".$typeId;
        }

        $description = $typeInfo->getDescription();
        $iconId = $typeInfo->getGraphicId();
        $mass = $typeInfo->getMass();
        $volume = $typeInfo->getVolume();
        $capacity = $typeInfo->getCapacity();
        $portionSize = $typeInfo->getPortionSize();
        $groupId = $typeInfo->getGroupId();

        // this is no yet available via ESI
        $marketGroupId = null;

        // store the item in the database
        $query = new DBPreparedQuery();
        $query->prepare('INSERT INTO kb3_invtypes (`typeID`, `groupID`, `typeName`, `icon`, `description`, `mass`, `volume`, `capacity`, `portionSize`, `marketGroupID` ) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $types = 'iisssdddii';
        $arr2 = array(&$types, &$typeId, &$groupId, &$typeName, &$iconId, &$description, &$mass, &$volume, &$capacity, &$portionSize, &$marketGroupId);
        $query->bind_params($arr2);
        $query->execute();

        // add dogma attributes (if any)
        $dogmaAttributes = $typeInfo->getDogmaAttributes();
        if($dogmaAttributes)
        {
            $query = DBFactory::getDBQuery();

            // store attributes in database  
            $attributeInserts = array();
            foreach($dogmaAttributes AS $dogmaAttribute)
            {
                $attributeInserts[] = '('.$typeId.', '.$query->escape($dogmaAttribute->getAttributeId()).',  '.$query->escape($dogmaAttribute->getValue()).')';
            }

            if(count($attributeInserts) > 0) 
            {
                $sql = 'REPLACE INTO kb3_dgmtypeattributes (`typeID`, `attributeID`, `value`) VALUES '. implode(", ", $attributeInserts);
                $query->execute($sql);
            }

        }

        // add dogma effects (if any)
        $dogmaEffects = $typeInfo->getDogmaEffects();
        if($dogmaEffects)
        {
            $query = DBFactory::getDBQuery();
            $effectInserts = array();
            foreach($dogmaEffects AS $dogmaEffect)
            {
                $effectInserts[] = "(".$typeId.", ".$query->escape($dogmaEffect->getEffectId()).",  ".(int) $dogmaEffect->getIsDefault().")";
            }

            if(count($effectInserts) > 0) 
            {
                $sql = 'REPLACE INTO kb3_dgmtypeeffects (`typeID`, `effectID`, `isDefault`) VALUES '. implode(", ", $effectInserts);
                $query->execute($sql);
            }

        }

        return self::lookup($typeName);
    }
}
