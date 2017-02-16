<?php
/**
 * GetCharactersCharacterIdContacts200Ok
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
 * GetCharactersCharacterIdContacts200Ok Class Doc Comment
 *
 * @category    Class */
 // @description 200 ok object
/** 
 * @package     Swagger\Client
 * @author      http://github.com/swagger-api/swagger-codegen
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class GetCharactersCharacterIdContacts200Ok implements ArrayAccess
{
    /**
      * The original name of the model.
      * @var string
      */
    protected static $swaggerModelName = 'get_characters_character_id_contacts_200_ok';

    /**
      * Array of property to type mappings. Used for (de)serialization
      * @var string[]
      */
    protected static $swaggerTypes = array(
        'contact_id' => 'int',
        'contact_type' => 'string',
        'is_blocked' => 'bool',
        'is_watched' => 'bool',
        'label_id' => 'int',
        'standing' => 'float'
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
        'contact_id' => 'contact_id',
        'contact_type' => 'contact_type',
        'is_blocked' => 'is_blocked',
        'is_watched' => 'is_watched',
        'label_id' => 'label_id',
        'standing' => 'standing'
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
        'contact_id' => 'setContactId',
        'contact_type' => 'setContactType',
        'is_blocked' => 'setIsBlocked',
        'is_watched' => 'setIsWatched',
        'label_id' => 'setLabelId',
        'standing' => 'setStanding'
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
        'contact_id' => 'getContactId',
        'contact_type' => 'getContactType',
        'is_blocked' => 'getIsBlocked',
        'is_watched' => 'getIsWatched',
        'label_id' => 'getLabelId',
        'standing' => 'getStanding'
    );

    public static function getters()
    {
        return self::$getters;
    }

    const CONTACT_TYPE_CHARACTER = 'character';
    const CONTACT_TYPE_CORPORATION = 'corporation';
    const CONTACT_TYPE_ALLIANCE = 'alliance';
    const CONTACT_TYPE_FACTION = 'faction';
    

    
    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public function getContactTypeAllowableValues()
    {
        return [
            self::CONTACT_TYPE_CHARACTER,
            self::CONTACT_TYPE_CORPORATION,
            self::CONTACT_TYPE_ALLIANCE,
            self::CONTACT_TYPE_FACTION,
        ];
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
        $this->container['contact_id'] = isset($data['contact_id']) ? $data['contact_id'] : null;
        $this->container['contact_type'] = isset($data['contact_type']) ? $data['contact_type'] : null;
        $this->container['is_blocked'] = isset($data['is_blocked']) ? $data['is_blocked'] : null;
        $this->container['is_watched'] = isset($data['is_watched']) ? $data['is_watched'] : null;
        $this->container['label_id'] = isset($data['label_id']) ? $data['label_id'] : null;
        $this->container['standing'] = isset($data['standing']) ? $data['standing'] : null;
    }

    /**
     * show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalid_properties = array();
        if ($this->container['contact_id'] === null) {
            $invalid_properties[] = "'contact_id' can't be null";
        }
        if ($this->container['contact_type'] === null) {
            $invalid_properties[] = "'contact_type' can't be null";
        }
        $allowed_values = array("character", "corporation", "alliance", "faction");
        if (!in_array($this->container['contact_type'], $allowed_values)) {
            $invalid_properties[] = "invalid value for 'contact_type', must be one of #{allowed_values}.";
        }

        if ($this->container['standing'] === null) {
            $invalid_properties[] = "'standing' can't be null";
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
        if ($this->container['contact_id'] === null) {
            return false;
        }
        if ($this->container['contact_type'] === null) {
            return false;
        }
        $allowed_values = array("character", "corporation", "alliance", "faction");
        if (!in_array($this->container['contact_type'], $allowed_values)) {
            return false;
        }
        if ($this->container['standing'] === null) {
            return false;
        }
        return true;
    }


    /**
     * Gets contact_id
     * @return int
     */
    public function getContactId()
    {
        return $this->container['contact_id'];
    }

    /**
     * Sets contact_id
     * @param int $contact_id contact_id integer
     * @return $this
     */
    public function setContactId($contact_id)
    {
        $this->container['contact_id'] = $contact_id;

        return $this;
    }

    /**
     * Gets contact_type
     * @return string
     */
    public function getContactType()
    {
        return $this->container['contact_type'];
    }

    /**
     * Sets contact_type
     * @param string $contact_type contact_type string
     * @return $this
     */
    public function setContactType($contact_type)
    {
        $allowed_values = array('character', 'corporation', 'alliance', 'faction');
        if (!in_array($contact_type, $allowed_values)) {
            throw new \InvalidArgumentException("Invalid value for 'contact_type', must be one of 'character', 'corporation', 'alliance', 'faction'");
        }
        $this->container['contact_type'] = $contact_type;

        return $this;
    }

    /**
     * Gets is_blocked
     * @return bool
     */
    public function getIsBlocked()
    {
        return $this->container['is_blocked'];
    }

    /**
     * Sets is_blocked
     * @param bool $is_blocked Whether this contact is in the blocked list. Note a missing value denotes unknown, not true or false
     * @return $this
     */
    public function setIsBlocked($is_blocked)
    {
        $this->container['is_blocked'] = $is_blocked;

        return $this;
    }

    /**
     * Gets is_watched
     * @return bool
     */
    public function getIsWatched()
    {
        return $this->container['is_watched'];
    }

    /**
     * Sets is_watched
     * @param bool $is_watched Whether this contact is being watched
     * @return $this
     */
    public function setIsWatched($is_watched)
    {
        $this->container['is_watched'] = $is_watched;

        return $this;
    }

    /**
     * Gets label_id
     * @return int
     */
    public function getLabelId()
    {
        return $this->container['label_id'];
    }

    /**
     * Sets label_id
     * @param int $label_id Custom label of the contact
     * @return $this
     */
    public function setLabelId($label_id)
    {
        $this->container['label_id'] = $label_id;

        return $this;
    }

    /**
     * Gets standing
     * @return float
     */
    public function getStanding()
    {
        return $this->container['standing'];
    }

    /**
     * Sets standing
     * @param float $standing Standing of the contact
     * @return $this
     */
    public function setStanding($standing)
    {
        $this->container['standing'] = $standing;

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


