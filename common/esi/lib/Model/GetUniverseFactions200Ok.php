<?php
/**
 * GetUniverseFactions200Ok
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
 * OpenAPI spec version: 0.3.10.dev19
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
 * GetUniverseFactions200Ok Class Doc Comment
 *
 * @category    Class */
 // @description 200 ok object
/** 
 * @package     Swagger\Client
 * @author      http://github.com/swagger-api/swagger-codegen
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class GetUniverseFactions200Ok implements ArrayAccess
{
    /**
      * The original name of the model.
      * @var string
      */
    protected static $swaggerModelName = 'get_universe_factions_200_ok';

    /**
      * Array of property to type mappings. Used for (de)serialization
      * @var string[]
      */
    protected static $swaggerTypes = array(
        'corporation_id' => 'int',
        'description' => 'string',
        'faction_id' => 'int',
        'is_unique' => 'bool',
        'militia_corporation_id' => 'int',
        'name' => 'string',
        'size_factor' => 'float',
        'solar_system_id' => 'int',
        'station_count' => 'int',
        'station_system_count' => 'int'
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
        'corporation_id' => 'corporation_id',
        'description' => 'description',
        'faction_id' => 'faction_id',
        'is_unique' => 'is_unique',
        'militia_corporation_id' => 'militia_corporation_id',
        'name' => 'name',
        'size_factor' => 'size_factor',
        'solar_system_id' => 'solar_system_id',
        'station_count' => 'station_count',
        'station_system_count' => 'station_system_count'
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
        'corporation_id' => 'setCorporationId',
        'description' => 'setDescription',
        'faction_id' => 'setFactionId',
        'is_unique' => 'setIsUnique',
        'militia_corporation_id' => 'setMilitiaCorporationId',
        'name' => 'setName',
        'size_factor' => 'setSizeFactor',
        'solar_system_id' => 'setSolarSystemId',
        'station_count' => 'setStationCount',
        'station_system_count' => 'setStationSystemCount'
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
        'corporation_id' => 'getCorporationId',
        'description' => 'getDescription',
        'faction_id' => 'getFactionId',
        'is_unique' => 'getIsUnique',
        'militia_corporation_id' => 'getMilitiaCorporationId',
        'name' => 'getName',
        'size_factor' => 'getSizeFactor',
        'solar_system_id' => 'getSolarSystemId',
        'station_count' => 'getStationCount',
        'station_system_count' => 'getStationSystemCount'
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
        $this->container['corporation_id'] = isset($data['corporation_id']) ? $data['corporation_id'] : null;
        $this->container['description'] = isset($data['description']) ? $data['description'] : null;
        $this->container['faction_id'] = isset($data['faction_id']) ? $data['faction_id'] : null;
        $this->container['is_unique'] = isset($data['is_unique']) ? $data['is_unique'] : null;
        $this->container['militia_corporation_id'] = isset($data['militia_corporation_id']) ? $data['militia_corporation_id'] : null;
        $this->container['name'] = isset($data['name']) ? $data['name'] : null;
        $this->container['size_factor'] = isset($data['size_factor']) ? $data['size_factor'] : null;
        $this->container['solar_system_id'] = isset($data['solar_system_id']) ? $data['solar_system_id'] : null;
        $this->container['station_count'] = isset($data['station_count']) ? $data['station_count'] : null;
        $this->container['station_system_count'] = isset($data['station_system_count']) ? $data['station_system_count'] : null;
    }

    /**
     * show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalid_properties = array();
        if ($this->container['corporation_id'] === null) {
            $invalid_properties[] = "'corporation_id' can't be null";
        }
        if ($this->container['description'] === null) {
            $invalid_properties[] = "'description' can't be null";
        }
        if ($this->container['faction_id'] === null) {
            $invalid_properties[] = "'faction_id' can't be null";
        }
        if ($this->container['is_unique'] === null) {
            $invalid_properties[] = "'is_unique' can't be null";
        }
        if ($this->container['name'] === null) {
            $invalid_properties[] = "'name' can't be null";
        }
        if ($this->container['size_factor'] === null) {
            $invalid_properties[] = "'size_factor' can't be null";
        }
        if ($this->container['solar_system_id'] === null) {
            $invalid_properties[] = "'solar_system_id' can't be null";
        }
        if ($this->container['station_count'] === null) {
            $invalid_properties[] = "'station_count' can't be null";
        }
        if ($this->container['station_system_count'] === null) {
            $invalid_properties[] = "'station_system_count' can't be null";
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
        if ($this->container['corporation_id'] === null) {
            return false;
        }
        if ($this->container['description'] === null) {
            return false;
        }
        if ($this->container['faction_id'] === null) {
            return false;
        }
        if ($this->container['is_unique'] === null) {
            return false;
        }
        if ($this->container['name'] === null) {
            return false;
        }
        if ($this->container['size_factor'] === null) {
            return false;
        }
        if ($this->container['solar_system_id'] === null) {
            return false;
        }
        if ($this->container['station_count'] === null) {
            return false;
        }
        if ($this->container['station_system_count'] === null) {
            return false;
        }
        return true;
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
     * Gets description
     * @return string
     */
    public function getDescription()
    {
        return $this->container['description'];
    }

    /**
     * Sets description
     * @param string $description description string
     * @return $this
     */
    public function setDescription($description)
    {
        $this->container['description'] = $description;

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
     * Gets is_unique
     * @return bool
     */
    public function getIsUnique()
    {
        return $this->container['is_unique'];
    }

    /**
     * Sets is_unique
     * @param bool $is_unique is_unique boolean
     * @return $this
     */
    public function setIsUnique($is_unique)
    {
        $this->container['is_unique'] = $is_unique;

        return $this;
    }

    /**
     * Gets militia_corporation_id
     * @return int
     */
    public function getMilitiaCorporationId()
    {
        return $this->container['militia_corporation_id'];
    }

    /**
     * Sets militia_corporation_id
     * @param int $militia_corporation_id militia_corporation_id integer
     * @return $this
     */
    public function setMilitiaCorporationId($militia_corporation_id)
    {
        $this->container['militia_corporation_id'] = $militia_corporation_id;

        return $this;
    }

    /**
     * Gets name
     * @return string
     */
    public function getName()
    {
        return $this->container['name'];
    }

    /**
     * Sets name
     * @param string $name name string
     * @return $this
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }

    /**
     * Gets size_factor
     * @return float
     */
    public function getSizeFactor()
    {
        return $this->container['size_factor'];
    }

    /**
     * Sets size_factor
     * @param float $size_factor size_factor number
     * @return $this
     */
    public function setSizeFactor($size_factor)
    {
        $this->container['size_factor'] = $size_factor;

        return $this;
    }

    /**
     * Gets solar_system_id
     * @return int
     */
    public function getSolarSystemId()
    {
        return $this->container['solar_system_id'];
    }

    /**
     * Sets solar_system_id
     * @param int $solar_system_id solar_system_id integer
     * @return $this
     */
    public function setSolarSystemId($solar_system_id)
    {
        $this->container['solar_system_id'] = $solar_system_id;

        return $this;
    }

    /**
     * Gets station_count
     * @return int
     */
    public function getStationCount()
    {
        return $this->container['station_count'];
    }

    /**
     * Sets station_count
     * @param int $station_count station_count integer
     * @return $this
     */
    public function setStationCount($station_count)
    {
        $this->container['station_count'] = $station_count;

        return $this;
    }

    /**
     * Gets station_system_count
     * @return int
     */
    public function getStationSystemCount()
    {
        return $this->container['station_system_count'];
    }

    /**
     * Sets station_system_count
     * @param int $station_system_count station_system_count integer
     * @return $this
     */
    public function setStationSystemCount($station_system_count)
    {
        $this->container['station_system_count'] = $station_system_count;

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


