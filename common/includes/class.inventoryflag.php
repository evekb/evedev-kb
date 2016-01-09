<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * represents an InventoryFlag used to describe the position of items in a kill
 *
 * @author Salvoxia
 */
class InventoryFlag extends Cacheable
{

    /** @var int the flag number*/
    protected $flagID;
    /** @var string the flag name */
    protected $flagName;
    /** @var string the flag text (description) */
    protected $flagText;
    /** @var string the flag's icon */
    protected $icon;
    
    /** @var boolean flag indicating whether data has already been fetched from the database */
    protected $executed = FALSE;
   
    
    private static $legacyFlagIDMapping = array(
        // High Slot => High power slot 1
        1 => 27,
        // Medium Slot => Medium power slot 1
        2 => 19,
        // Low Slot => Low Power slot 1
        3 => 11,
        // Cargo => Cargo
        4 => 5,
        // Rig Slot => Rig power slot 1
        5 => 92,
        // Drone Bay =>
        6 => 87,
        // Sub System => Sub system slot 0
        7 => 125,
        // Implant => Implant
        8 => 89, 
        // Copy => Cargo, Copy
        9 => -1
    );
    
    
    private static $legacyFlagNameMapping = array(
        // High Slot => High power slot 1
        "Fitted - High slot" => 27,
        // Medium Slot => Medium power slot 1
        "Fitted - Medium slot" => 19,
        // Low Slot => Low Power slot 1
        "Fitted - Low slot" => 11,
        // Cargo => Cargo
        "Cargo" => 5,
        // Rig Slot => Rig power slot 1
        "Rig slot" => 92,
        // Drone Bay =>
        "Drone Bay" => 87,
        // Sub System => Sub system slot 0
        "Subsystem slot" => 125,
        // Implant => Implant
        "Implant" => 89, 
        // Copy => Cargo, Copy
        "Copy" => -1,
        // Other
        "Other" => 0
    );
    
    
    // constans to use when addressing specific flag IDs
    public static $HIGH_SLOT_1 = 27;
    public static $HIGH_SLOT_8 = 34;
    
    public static $MED_SLOT_1 = 19;
    public static $MED_SLOT_8 = 26;
    
    public static $LOW_SLOT_1 = 11;
    public static $LOW_SLOT_8 = 18;
    
    public static $RIG_SLOT_1 = 92;
    public static $RIG_SLOT_8 = 99;
    
    public static $SUB_SYSTEM_SLOT_1 = 125;
    public static $SUB_SYSTEM_SLOT_8 = 132;
    
    public static $CARGO = 5;
    public static $DRONE_BAY = 87;
    public static $IMPLANT = 89;
    public static $OTHER = 0;
    /** @deprecated copies are now handled via the singleton flag! */
    public static $COPY = -1;
    
    public static $SINGLETON_COPY = 2;
    
    public static $UNKNOWN= -10;
    
    
    /**
     * instantiates a new InventoryFlag
     * @param int $flag the flag
     */
    function __construct($flag)
    {
        $this->flagID = $flag;
        
         if($this->isCached())
        {
           $cache = $this->getCache();
           $this->flagID = $cache->flagID;
           $this->flagName = $cache->flagName;
           $this->flagText = $cache->flagText;
           $this->icon = $cache->icon;
           
           $this->executed = TRUE;
        }
    }
    
    
    /**
     * populates the class attributes (either from cache or from the database)
     */
    public function execQuery()
    {
        if($this->executed)
        {
            return;
        }
        
        if($this->isCached())
        {
           $cache = $this->getCache();
           $this->flagID = $cache->flagID;
           $this->flagName = $cache->flagName;
           $this->flagText = $cache->flagText;
           $this->icon = $cache->icon;
           
           $this->executed = TRUE;
           return;
        }
        
        $flagDetails = new DBPreparedQuery();
        $flagDetails->prepare('SELECT itl_flagID, itl_flagName, itl_flagText, itl_icon FROM kb3_item_locations WHERE itl_flagID = ?');
        
        $resultArray = array(
            &$this->flagID,
            &$this->flagName,
            &$this->flagText,
            &$this->icon
        );
        // bind results
        $flagDetails->bind_results($resultArray);
        
        // bind parameter
        $params = array('i', &$this->flagID);
        $flagDetails->bind_params($params);
        $flagDetails->execute();
        if($flagDetails->recordCount())
        {
            $flagDetails->fetch();
            
            // default for icon
            if(is_null($this->icon))
            {
                // default icon
                $this->icon = "03_14";
            }
            $this->executed = TRUE;
            $this->putCache();
        }
    }

    /**
     * gets the flag ID
     * @return int the flag ID
     */
    function getID() 
    {
        return $this->flagID;
    }
    
    /**
     * get the flag name
     * @return string
     */
    function getName()
    {
        if(!$this->exeucted)
        {
            $this->execQuery();
        }
        
        return $this->flagNanme;
    }
    
    /**
     * gets the flag's text (description)
     * @return string
     */
    function getText()
    {  
        if(!$this->exeucted)
        {
            $this->execQuery();
        }
        
        return $this->flagText;
    }
    
    /**
     * gets the flag's icon
     * @return string
     */
    function getIcon()
    {
        if(!$this->exeucted)
        {
            $this->execQuery();
        }
        
        return $this->icon;
    }
    
    
    /**
     * gets a valid CCP flag from a legacy item location name
     * @return \InventoryFlag
     */
    public static function getConvertedByName($flagName)
    {
        $flagID = self::$legacyFlagNameMapping[$flagName];
        if(!is_null($flagID))
        {
            return new InventoryFlag($flagID);
        }
    }
    
    /**
     * gets a valid CCP flag from a legacy item location ID
     * @return \InventoryFlag
     */
    public static function getConvertedByID($legacyLocationID)
    {
        $flagID = self::$legacyFlagIDMapping[$legacyLocationID];
        if($flagID != null)   
        {
            return new InventoryFlag($flagID);
        }
    }
    
    /**
     * returns the legacy location name by CCP flag ID
     * @param int $flagID the CCP flag to translate
     * @return string the legacy location name
     */
    public static function getLegacyNameByID($flagID)
    {
        if(!is_numeric($flagID))
        {
            return NULL;
        }
        
        // High slots
        if($flagID >= 27 && $flagID <= 36)
        {
            return "Fitted - High slot";
        }
        
        // Med slots
        if($flagID >= 19 && $flagID <= 26)
        {
            return "Fitted - Medium slot";
        }
        
        // Low slots
        if($flagID >= 11 && $flagID <= 18)
        {
            return "Fitted - Low slot";
        }
        
        // Cargo
        if($flagID == 5)
        {
            return "Cargo";
        }
        
        // Rig slot
        if($flagID >= 92 && $flagID <= 99)
        {
            return "Rig slot";
        }
        
        // Drone bay
        if($flagID == 87)
        {
            return "Drone Bay";
        }
        
        // Subsystem
        if($flagID >= 125 && $flagID  <= 132)
        {
            return "Subsystem slot";
        }
        
        // Implant
        if($flagID == 89)
        {
            return "Implant";
        }
        
        // Copy
        if($flagID == -1)
        {
            return "Copy";
        }
        
        return NULL;
    }
    
    
    
    /**
     * consolidates flags to a single flagID for displaying
     * e.g high slots 1-8 will be consolidated to high slot 1
     * @param int $flagID the flag to consolidate
     * @return int the consolidated flag
     */
    public static function collapse($flagID)
    {
        /*
        * High Slots : flags 27 to 34
        * Medium Slots : flags 19 to 26
        * Low Slots : flags 11 to 18
        * Rig Slots : flags 92 to 99
        * Sub Systems : 125 to 132 (note current ships only have subsystems ranging from 125 to 129
        */
       if( $flagID >= 27 && $flagID <= 34 ) {
               $flagID = 27;
       }

       if( $flagID >= 19 && $flagID <= 26 ) {
               $flagID = 19;
       }

       if( $flagID >= 11 && $flagID <= 18 ) {
               $flagID = 11;
       }

       if( $flagID >= 92 && $flagID <= 99 ) {
               $flagID = 92;
       }

       if( $flagID >= 125 && $flagID <= 132 ) {
               $flagID = 125;
       }
       return $flagID;

    }
}
