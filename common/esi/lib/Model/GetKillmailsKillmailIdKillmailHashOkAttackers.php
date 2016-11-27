<?php
/**
 * GetKillmailsKillmailIdKillmailHashOkAttackers
 *
 * PHP version 5
 *
 * @category Class
 * @package  Swagger\Client
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * EVE Swagger Interface
 *
 * An OpenAPI for EVE Online
 *
 * OpenAPI spec version: 0.2.6.dev1
 * 
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace Swagger\Client\Model;

use \ArrayAccess;

/**
 * GetKillmailsKillmailIdKillmailHashOkAttackers Class Doc Comment
 *
 * @category    Class */
 // @description attacker object
/** 
 * @package     Swagger\Client
 * @author      http://github.com/swagger-api/swagger-codegen
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class GetKillmailsKillmailIdKillmailHashOkAttackers implements ArrayAccess
{
    /**
      * The original name of the model.
      * @var string
      */
    protected static $swaggerModelName = 'get_killmails_killmail_id_killmail_hash_ok_attackers';

    /**
      * Array of property to type mappings. Used for (de)serialization
      * @var string[]
      */
    protected static $swaggerTypes = array(
        'alliance_id' => 'int',
        'character_id' => 'int',
        'corporation_id' => 'int',
        'damage_done' => 'int',
        'faction_id' => 'int',
        'final_blow' => 'bool',
        'security_status' => 'float',
        'ship_type_id' => 'int',
        'weapon_type_id' => 'int'
    );

    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of attributes where the key is the local name, and the value is the original name
     * @var string[]
     */
    protected static $attributeMap = array(
        'alliance_id' => 'alliance_id',
        'character_id' => 'character_id',
        'corporation_id' => 'corporation_id',
        'damage_done' => 'damage_done',
        'faction_id' => 'faction_id',
        'final_blow' => 'final_blow',
        'security_status' => 'security_status',
        'ship_type_id' => 'ship_type_id',
        'weapon_type_id' => 'weapon_type_id'
    );

    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     * @var string[]
     */
    protected static $setters = array(
        'alliance_id' => 'setAllianceId',
        'character_id' => 'setCharacterId',
        'corporation_id' => 'setCorporationId',
        'damage_done' => 'setDamageDone',
        'faction_id' => 'setFactionId',
        'final_blow' => 'setFinalBlow',
        'security_status' => 'setSecurityStatus',
        'ship_type_id' => 'setShipTypeId',
        'weapon_type_id' => 'setWeaponTypeId'
    );

    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     * @var string[]
     */
    protected static $getters = array(
        'alliance_id' => 'getAllianceId',
        'character_id' => 'getCharacterId',
        'corporation_id' => 'getCorporationId',
        'damage_done' => 'getDamageDone',
        'faction_id' => 'getFactionId',
        'final_blow' => 'getFinalBlow',
        'security_status' => 'getSecurityStatus',
        'ship_type_id' => 'getShipTypeId',
        'weapon_type_id' => 'getWeaponTypeId'
    );

    public static function getters()
    {
        return self::$getters;
    }

    

    

    /**
     * Associative array for storing property values
     * @var mixed[]
     */
    protected $container = array();

    /**
     * Constructor
     * @param mixed[] $data Associated array of property value initalizing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['alliance_id'] = isset($data['alliance_id']) ? $data['alliance_id'] : null;
        $this->container['character_id'] = isset($data['character_id']) ? $data['character_id'] : null;
        $this->container['corporation_id'] = isset($data['corporation_id']) ? $data['corporation_id'] : null;
        $this->container['damage_done'] = isset($data['damage_done']) ? $data['damage_done'] : null;
        $this->container['faction_id'] = isset($data['faction_id']) ? $data['faction_id'] : null;
        $this->container['final_blow'] = isset($data['final_blow']) ? $data['final_blow'] : null;
        $this->container['security_status'] = isset($data['security_status']) ? $data['security_status'] : null;
        $this->container['ship_type_id'] = isset($data['ship_type_id']) ? $data['ship_type_id'] : null;
        $this->container['weapon_type_id'] = isset($data['weapon_type_id']) ? $data['weapon_type_id'] : null;
    }

    /**
     * show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalid_properties = array();
        if ($this->container['damage_done'] === null) {
            $invalid_properties[] = "'damage_done' can't be null";
        }
        if ($this->container['final_blow'] === null) {
            $invalid_properties[] = "'final_blow' can't be null";
        }
        if ($this->container['security_status'] === null) {
            $invalid_properties[] = "'security_status' can't be null";
        }
        return $invalid_properties;
    }

    /**
     * validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properteis are valid
     */
    public function valid()
    {
        if ($this->container['damage_done'] === null) {
            return false;
        }
        if ($this->container['final_blow'] === null) {
            return false;
        }
        if ($this->container['security_status'] === null) {
            return false;
        }
        return true;
    }


    /**
     * Gets alliance_id
     * @return int
     */
    public function getAllianceId()
    {
        return $this->container['alliance_id'];
    }

    /**
     * Sets alliance_id
     * @param int $alliance_id alliance_id integer
     * @return $this
     */
    public function setAllianceId($alliance_id)
    {
        $this->container['alliance_id'] = $alliance_id;

        return $this;
    }

    /**
     * Gets character_id
     * @return int
     */
    public function getCharacterId()
    {
        return $this->container['character_id'];
    }

    /**
     * Sets character_id
     * @param int $character_id character_id integer
     * @return $this
     */
    public function setCharacterId($character_id)
    {
        $this->container['character_id'] = $character_id;

        return $this;
    }

    /**
     * Gets corporation_id
     * @return int
     */
    public function getCorporationId()
    {
        return $this->container['corporation_id'];
    }

    /**
     * Sets corporation_id
     * @param int $corporation_id corporation_id integer
     * @return $this
     */
    public function setCorporationId($corporation_id)
    {
        $this->container['corporation_id'] = $corporation_id;

        return $this;
    }

    /**
     * Gets damage_done
     * @return int
     */
    public function getDamageDone()
    {
        return $this->container['damage_done'];
    }

    /**
     * Sets damage_done
     * @param int $damage_done damage_done integer
     * @return $this
     */
    public function setDamageDone($damage_done)
    {
        $this->container['damage_done'] = $damage_done;

        return $this;
    }

    /**
     * Gets faction_id
     * @return int
     */
    public function getFactionId()
    {
        return $this->container['faction_id'];
    }

    /**
     * Sets faction_id
     * @param int $faction_id faction_id integer
     * @return $this
     */
    public function setFactionId($faction_id)
    {
        $this->container['faction_id'] = $faction_id;

        return $this;
    }

    /**
     * Gets final_blow
     * @return bool
     */
    public function getFinalBlow()
    {
        return $this->container['final_blow'];
    }

    /**
     * Sets final_blow
     * @param bool $final_blow Was the attacker the one to achieve the final blow
     * @return $this
     */
    public function setFinalBlow($final_blow)
    {
        $this->container['final_blow'] = $final_blow;

        return $this;
    }

    /**
     * Gets security_status
     * @return float
     */
    public function getSecurityStatus()
    {
        return $this->container['security_status'];
    }

    /**
     * Sets security_status
     * @param float $security_status Security status for the attacker
     * @return $this
     */
    public function setSecurityStatus($security_status)
    {
        $this->container['security_status'] = $security_status;

        return $this;
    }

    /**
     * Gets ship_type_id
     * @return int
     */
    public function getShipTypeId()
    {
        return $this->container['ship_type_id'];
    }

    /**
     * Sets ship_type_id
     * @param int $ship_type_id What ship was the attacker flying
     * @return $this
     */
    public function setShipTypeId($ship_type_id)
    {
        $this->container['ship_type_id'] = $ship_type_id;

        return $this;
    }

    /**
     * Gets weapon_type_id
     * @return int
     */
    public function getWeaponTypeId()
    {
        return $this->container['weapon_type_id'];
    }

    /**
     * Sets weapon_type_id
     * @param int $weapon_type_id What weapon was used by the attacker for the kill
     * @return $this
     */
    public function setWeaponTypeId($weapon_type_id)
    {
        $this->container['weapon_type_id'] = $weapon_type_id;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     * @param  integer $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     * @param  integer $offset Offset
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     * @param  integer $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(\Swagger\Client\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        }

        return json_encode(\Swagger\Client\ObjectSerializer::sanitizeForSerialization($this));
    }
}


