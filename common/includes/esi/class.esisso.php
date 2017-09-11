<?php

namespace EDK\ESI;

use Config;
use EDKError;

class EdkSsoException extends \Exception {}

/**
 * Class for handling ESI SSO tokens.
 * SSO Code based on example by FuzzySteve
 * https://github.com/fuzzysteve/eve-sso-auth/
 * 
 * @author Snitch Ashor
 * @author Salvoxia <Salvoxia@blindfish.info>
 */
class ESISSO
{
    // scopes for reading killmails
    const SSO_SCOPE_CHARACTER_READ_KILLMAILS = 'esi-killmails.read_killmails.v1';
    const SSO_SCOPE_CORPORATION_READ_KILLMAILS = 'esi-killmails.read_corporation_killmails.v1';
    
    // key types
    const KEY_TYPE_PILOT = 'pilot';
    const KEY_TYPE_CORPORATION = 'corp';
      
    /** string the user agent to use when talking to the Eve Online login server */
    protected static $USER_AGENT = EDK_USER_AGENT;
    protected $code = null;
    protected $accessToken = null;
    protected $refreshToken = null;
    protected $keyType = null;
    protected $scopes = array();
    protected $ownerHash = null;
    /** int the character ID owner for this SSO */
    protected $characterID = 0;
    protected $error = false;
    protected $message = null;
    protected $failCount = 0;
    /** boolean flag indicating whether this SSO is enabled */
    protected $isEnabled = true;
    protected $id = null;

    function __construct($id = null, $characterID = 0, $keyType = null, $refreshToken = null)
    {
        $fetchParams = new \DBPreparedQuery();
        // existing SSO entry, fetch from database by ID
        if($id != null) 
        {
            $this->id = $id;
            // prepare query
            $fetchParams->prepare('SELECT id, characterID, keyType, refreshToken, ownerHash, failCount, isEnabled, lastKillID FROM kb3_esisso WHERE id = ?');
            // bind results
            $arr = array(
                &$this->id, 
                &$this->characterID, 
                &$this->keyType,
                &$this->refreshToken,
                &$this->ownerHash,
                &$this->failCount,
                &$this->isEnabled,
                &$this->lastKillID
            );
            $fetchParams->bind_results($arr);
            // bind parameters
            $types = 'i';
            $arr2 = array(&$types, &$this->id);
            $fetchParams->bind_params($arr2);
            $fetchParams->execute();
            // entry found
            if($fetchParams->recordCount() > 0) 
            {
                $fetchParams->fetch();
                // get a new access token, no need to verify it
                $this->refreshAccessToken(false);
            }
        } 
        
        // existing sso token, fetch from datbaase by keyType and characterID
        elseif ($keyType != null && $characterID != 0) 
        {
            $this->characterID = $characterID;
            $this->keyType = $keyType;
           
            // prepare query
            $fetchParams->prepare('SELECT id, characterID, keyType, refreshToken, ownerhash, failCount, enabled, lastKillId FROM kb3_esisso WHERE (characterID = ? AND keytype = ?)');
            // bind results
            $arr = array(
                &$this->id, 
                &$this->characterID, 
                &$this->keyType,
                &$this->refreshToken,
                &$this->ownerHash,
                &$this->failCount,
                &$this->isEnabled,
                &$this->lastKillID
            );
            $fetchParams->bind_results($arr);
            // bind parameters
            $types = 'is';
            $arr2 = array(&$types, &$this->characterID, &$this->keyType);
            $fetchParams->bind_params($arr2);
            
            $fetchParams->execute();
            // entry found
            if($fetchParams->recordCount() > 0) 
            {
                $fetchParams->fetch();
                // get a new access token, no need to verify it
                $this->refresh(false);
            }
        } 
        
        // refresh using given refresh token
        elseif (isset($refreshToken)) 
        {
            $this->refreshToken = $refreshToken;
            $this->refresh();
        }
    }

    /**
     * Sets the code and fetches a refresh and access token
     * 
     * @param type $code
     * @throws EdkSsoException on error (while querying the OAuth server or verifying the access token)
     */
    public function fetchToken($code) 
    {
        $this->code = $code;

        $url = OAUTH_BASE_URL . '/token';
        $header = 'Authorization: Basic '.base64_encode(Config::get('cfg_sso_client_id').':'.Config::get('cfg_sso_secret'));
        
        // build POST parameters
        $postFieldString = '';
        $postFields = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
        );
        
        foreach ($postFields as $key => $value) 
        {
            $postFieldString .= $key.'='.$value.'&';
        }
        rtrim($postFieldString, '&');
        
        // prepare cURL instance
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, self::$USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($curl, CURLOPT_POST, count($postFields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFieldString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        // make sure we can verify the peer's certificate
        curl_setopt($curl, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . '/cert/cacert.pem');
        
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($result === false) 
        {
            $errorText = curl_error($curl);
            $errorMessage = "Error querying OAUTH SSO server while setting SSO Code: ".$errorText. "(HTTP Code: ".$httpCode.")";
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage, $httpCode);
        }
        
        $response = json_decode($result);
        
        if(isset($response->error))
        {
            $errorText = $response->error_description;
            $errorMessage = "Error querying OAUTH SSO server while setting SSO Code: ".$errorText;
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage);
        }
        
        $this->accessToken = $response->access_token;
        $this->refreshToken = $response->refresh_token;
        
        // verify the access token
        $this->verify();
    }

    /**
     * Verifies the access token against the OAuth server
     * 
     * @throws EdkSsoException if verification fails
     */
    public function verify() 
    {
        if (!isset($this->accessToken)) 
        {
            throw new EdkSsoException("No Acess Token to verify.");
        } 
           
        $verify_url = OAUTH_BASE_URL . '/verify';
        
        $curl = curl_init();
        $header = 'Authorization: Bearer '.$this->accessToken;
        curl_setopt($curl, CURLOPT_URL, $verify_url);
        curl_setopt($curl, CURLOPT_USERAGENT, self::$USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        // make sure we can verify the peer's certificate
        curl_setopt($curl, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . '/cert/cacert.pem');
        
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($result === false) 
        {
            $errorText = curl_error($curl);
            $errorMessage = "Error querying OAUTH SSO server while verifying OAUTH SSO access token: ".$errorText. "(HTTP Code: ".$httpCode.")";
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage, $httpCode);
        }

        $response = json_decode($result);
        // check for error in response
        if (isset($response->error)) 
        {
            $errorText = "Error verifying OAUTH SSO access token: ".$response->error_description;
            
            EDKError::log($errorText);
            throw new EdkSsoException($errorText);
        }
        
        // check for character ID in response
        if (!isset($response->CharacterID)) 
        {
            $errorText = "Failed to get character ID.";
            EDKError::log($errorText);
            throw new EdkSsoException($errorText);
        }
        
        $this->characterID = $response->CharacterID;
        $this->scopes = explode(' ', $response->Scopes);
        
        if ($this->scopes == null || $this->scopes == '') 
        {
            $errorText = "SSO Authentication returned no scopes!";
            EDKError::log($errorText);
            throw new EdkSsoException($errorText);
        }
        
        
        // determine key type by checking the scope
        if (in_array(self::SSO_SCOPE_CHARACTER_READ_KILLMAILS, $this->scopes)) 
        {
            $this->keyType = self::KEY_TYPE_PILOT;
        } 
        
        elseif (in_array(self::SSO_SCOPE_CORPORATION_READ_KILLMAILS, $this->scopes))
        {
            $this->keyType = self::KEY_TYPE_CORPORATION;
        }
        
        else 
        {
            $errorText = "SSO Authentication returned no scopes with killmail access!";
            EDKError::log($errorText);
            throw new EdkSsoException($errorText);
        }
        
        $this->ownerHash = $response->CharacterOwnerHash;
        $Pilot = new \Pilot(0, $this->characterID);
        $isOwner = false;
        
        // check whether the Pilot, their corp or alliances is a killboard owner
        if (count(Config::get('cfg_pilotid')) > 0)
        {
            if (in_array($Pilot->getID(), Config::get('cfg_pilotid')))
            {
                $isOwner = true;
            }
        }
        if (count(Config::get('cfg_corpid')) > 0)
        {
            if (in_array($Pilot->getCorp()->getID(), Config::get('cfg_corpid')))
            {
                $isOwner = true;
            }
        }
        if (count(Config::get('cfg_allianceid')) > 0)
        {
            if (in_array($Pilot->getCorp()->getAlliance()->getID(), Config::get('cfg_allianceid')))
            {
                $isOwner = true;
            }
        }
        
        if (!$isOwner && Config::get('cfg_ssoForOwnersOnly')) 
        {
            $errorText = "SSO Authentication returned no scopes with killmail access!";
            throw new EdkSsoException($errorText);
        }

    }

    /**
     * Persist the object in the database (table kb3_esisso).
     * 
     * @throws EsiSsoException on error
     */
    public function add() 
    {
        $refreshToken = $this->refreshToken;
        $keyType = $this->keyType;
        $ownerHash = $this->ownerHash;
        $characterID = $this->characterID;
        $failCount = 0;
        $isEnabled = true;
        $lastKillID = 0;
        
        // check whether we already know this SSO config
        $ssoParams = new \DBPreparedQuery();
        $id = null;
        // prepare query
        $ssoParams->prepare('SELECT id FROM kb3_esisso WHERE (characterID = ? AND keyType = ?)');
        // bind results
        $arr = array(
            &$id
        );
        $ssoParams->bind_results($arr);
        // bind parameters
        $types = 'is';
        $arr2 = array(&$types, &$characterID, &$keyType);
        $ssoParams->bind_params($arr2);
        $ssoParams->execute();
        // entry not found, add
        if($ssoParams->recordCount() == 0) 
        {
            $addSso = new \DBPreparedQuery();
            $addSso->prepare('INSERT into kb3_esisso (characterID, keyType, refreshToken, ownerHash, failCount, isEnabled, lastKillID)' 
                                          .' VALUES  (?, ?, ?, ?, ?, ?, ?)');
            $types = 'isssiii';
            $arr = array(&$types, &$characterID, &$keyType, &$refreshToken, &$ownerHash, &$failCount, &$isEnabled, &$lastKillID);
            $addSso->bind_params($arr);
            
            // error on insert
            if(!$addSso->execute())
            {
                $errorMessage = "Error while adding ESI SSO configuration: ".$addSso->getErrorMsg();
                EDKError::log($errorMessage);
                throw new EdkSsoException($errorMessage);
            }
        } 
        // entry existing, update it
        else 
        {
            $ssoParams->fetch();
            
            $updateSsoParams = new \DBPreparedQuery();
            $updateSsoParams->prepare('UPDATE kb3_esisso SET characterID = ?, keyType = ?, refreshToken = ?, ownerHash = ?, failCount = ?, isEnabled = ? WHERE id = ?');
            $types = 'isssiis';
            $arr = array(&$types, &$characterID, &$keyType, &$refreshToken, &$ownerHash, &$failCount, &$isEnabled, &$id);
            
            $updateSsoParams->bind_params($arr);
            // error on update
            if(!$updateSsoParams->execute())
            {
                $errorMessage = "Error while updateing ESI SSO configuration (ID: $id}: ".$addSso->getErrorMsg();
                EDKError::log($errorMessage);
                throw new EdkSsoException($errorMessage);
            }

        }
    }
    
    /**
     * Fetches a new access token using the refresh token.
     * 
     * @param boolean $verify flag indicating whether to verify the new access token
     * @throws EsiSsoException on error or if the access token could not be verified
     */
    public function refreshAccessToken( $verify = true ) 
    {
        if (!isset($this->refreshToken)) 
        {
            $errorMessage = "Could not update access token, no refresh token available!";
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage);
        }

        $postFieldsString = '';
        $postFields =  array(
            'grant_type' => 'refresh_token', 
            'refresh_token' => $this->refreshToken
        );

        $url = OAUTH_BASE_URL . '/token';
        $header = 'Authorization: Basic '.base64_encode(Config::get('cfg_sso_client_id').':'.Config::get('cfg_sso_secret'));

        foreach ($postFields  as $arrKey => $value) 
        {
            $postFieldsString .= $arrKey.'='.$value.'&';
        }
        $postFieldsString = rtrim($postFieldsString, '&');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, self::$USER_AGENT);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($curl, CURLOPT_POST, count($postFields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFieldsString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        // make sure we can verify the peer's certificate
        curl_setopt($curl, CURLOPT_CAINFO, getcwd() . DIRECTORY_SEPARATOR . KB_CACHEDIR . '/cert/cacert.pem');

        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($result === false) 
        {
            $errorText = curl_error($curl);
            $errorMessage = "Error querying OAUTH SSO server while fetching refreshed OAUTH SSO access access token: ".$errorText. "(HTTP Code: ".$httpCode.")";
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage, $httpCode);
        }


        $response = json_decode($result, true);
        
        if(isset($response->error))
        {
            $errorText = $response->error_description;
            $errorMessage = "Error querying OAUTH SSO server while fetching refreshed OAUTH SSO access access token: ".$errorText;
            EDKError::log($errorMessage);
            throw new EdkSsoException($errorMessage);
        }
        
        $this->accessToken = $response['access_token'];
        if ($verify) 
        {
            $this->verify();
        }
    }

    public function getError() 
    {
        return $this->error;
    }

    public function getMessage() 
    {
        return $this->message;
    }

    public function getAccessToken() 
    {
        return $this->accessToken;
    }

    public function getRefreshToken() 
    {
        return $this->refreshToken;
    }

    public function getKeyType() 
    { 
        return $this->keyType;
    }

    public function getOwnerHash() 
    { 
        return $this->ownerHash;
    }

    public function getCharacterID() 
    { 
        return $this->characterID;
    }

    public function getFailcount() 
    {
        return $this->failCount;
    }

    public function isEnabled() 
    {
        return $this->isEnabled;
    }
}
