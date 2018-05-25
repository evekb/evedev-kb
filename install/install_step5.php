<?php
/**
 * @package EDK
 */
require_once("../common/esi/autoload.php");
require_once("../common/includes/class.edkloader.php");
edkloader::setRoot("..");
spl_autoload_register('edkloader::load');


use Swagger\Client\ApiClient;
use Swagger\Client\Configuration;
use EsiClient\SearchApi;
use EsiClient\AllianceApi;
use EsiClient\CharacterApi;
use EsiClient\CorporationApi;
use Swagger\Client\ApiException;
use Swagger\Client\Model\GetSearchOk;

if(!$installrunning)
{
    header('Location: index.php');
    die();
}
$stoppage = true;
global $smarty;

$db = new mysqli($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass'], $_SESSION['sql']['db']);

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'create')
{
    $Api = new Api();
    if (!empty($_REQUEST['a']))
    {
        $externalId = $_REQUEST['a'];
        $_REQUEST['a'] = $Api->createAlliance($externalId);
    }
    else if(!empty($_REQUEST['c']))
    {
        $externalId = $_REQUEST['c'];
        $_REQUEST['c'] = $Api->createCorporation($externalId);
    }
    else
    {
        $externalId = $_REQUEST['p'];
        $_REQUEST['p'] = $Api->createCharacter($externalId);
    }
    $_SESSION['sett']['aid'] = $_REQUEST['a'];
    $_SESSION['sett']['cid'] = $_REQUEST['c'];
    $_SESSION['sett']['pid'] = $_REQUEST['p'];
    $stoppage = false;
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'select')
{
    $_SESSION['sett']['aid'] = $_REQUEST['a'];
    $_SESSION['sett']['cid'] = $_REQUEST['c'];
    $_SESSION['sett']['pid'] = $_REQUEST['p'];
    $stoppage = false;
}
if ($stoppage)
{

    if (!empty($_REQUEST['searchphrase']) && strlen($_REQUEST['searchphrase']) >= 3)
    {
        switch ($_REQUEST['searchtype'])
        {
            case "pilot":
                $query = "SELECT plt.plt_id, plt.plt_name, crp.crp_name
            FROM kb3_pilots plt, kb3_corps crp
            WHERE plt.plt_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
            AND plt.plt_crp_id = crp.crp_id
            ORDER BY plt.plt_name";
                break;
            case "corp":
                $query = "SELECT crp.crp_id, crp.crp_name, ali.all_name
            FROM kb3_corps crp, kb3_alliances ali
            WHERE crp.crp_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
            AND crp.crp_all_id = ali.all_id
            ORDER BY crp.crp_name";
                break;
            case "alliance":
                $query = "SELECT ali.all_id, ali.all_name
            FROM kb3_alliances ali
            WHERE ali.all_name LIKE '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%'
            ORDER BY ali.all_name";
                break;
        }

        $result = $db->query($query);

        $unsharp = true;
        $results = array();
        while ($row = $result->fetch_assoc())
        {
            switch ($_REQUEST['searchtype'])
            {
                case 'pilot':
                    $link = "?step=5&amp;do=select&amp;a=0&amp;c=0&amp;p=".$row['plt_id'].'">Select';
                    $descr = 'Pilot '.$row['plt_name'].', member of '.$row['crp_name'];
                    if ($row['plt_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
                    {
                        $unsharp = false;
                    }
                    break;
                case 'corp':
                    $link = "?step=5&amp;do=select&amp;a=0&amp;p=0&amp;c=".$row['crp_id'].'">Select';
                    $descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
                    if ($row['crp_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
                    {
                        $unsharp = false;
                    }
                    break;
                case 'alliance':
                    $link = '?step=5&amp;do=select&amp;c=0&amp;p=0&amp;a='.$row['all_id'].'">Select';
                    $descr = 'Alliance '.$row['all_name'];
                    if (strtolower($row['all_name']) == strtolower(stripslashes($_REQUEST['searchphrase'])))
                    {
                        $unsharp = false;
                    }
                    break;
            }
            $results[] = array('descr' => $descr, 'link' => $link);
        }
        if (!count($results) || $unsharp)
        {
            $name = stripslashes($_REQUEST['searchphrase']);
            $api = new Api();

            if ($_REQUEST['searchtype'] == 'corp')
            {
                $id = $api->getCorporationId($name);
                // check for result
                if(!$id)
                {
                    $link = '?step=5&amp;do=create&amp;c='.stripslashes($_REQUEST['searchphrase']).'&amp;a=0">Create';
                    $descr = 'Corporation not found. Check spelling.';
                }
                else
                {
                    $link = '?step=5&amp;do=create&amp;c='.$id.'&amp;a=0">Create';
                    $descr = 'Corporation: '.$name;
                }
            }
            else if($_REQUEST['searchtype'] == 'alliance')
            {
               $id = $api->getAllianceId($name);
                // check for result
                if(!$id)
                {
                    $link = '?step=5&amp;do=create&amp;a='.stripslashes($_REQUEST['searchphrase']).'&amp;c=0&amp;p=0">Create';
                    $descr = 'Alliance not found. Check spelling.';
                }
                else
                {
                    $link = '?step=5&amp;do=create&amp;a='.$id.'&amp;c=0&amp;p=0">Create';
                    $descr = 'Alliance: '.$name;
                }
            }
            else
            {
                $id = $api->getCharacterId($name);
                // check for result
                if(!$id)
                {
                    $link = '?step=5&amp;do=create&amp;p='.stripslashes($_REQUEST['searchphrase']).'&amp;c=0&amp;a=0">Create';
                    $descr = 'Pilot not found. Check spelling.';
                }
                else
                {
                    $link = '?step=5&amp;do=create&amp;p='.$id.'&amp;c=0&amp;a=0">Create';
                    $descr = 'Pilot: '.$name;
                }
            }
            $results[] = array('descr' => $descr, 'link' => $link);
        }
        $smarty->assign('res_check', count($results) > 0);
        $smarty->assign('results', $results);
    }
}
$smarty->assign('stoppage', $stoppage);
$smarty->assign('conflict', empty($_SESSION['sett']['aid']) && empty($_SESSION['sett']['cid']) && empty($_SESSION['sett']['pid']));
$smarty->assign('nextstep', $_SESSION['state']+1);
$smarty->display('install_step5.tpl');

class Api
{
    private $esiClient;
    private $db;
    
    function __construct()
    {
        $ApiConfiguration = Configuration::getDefaultConfiguration();
        // if the root CA bundle does not contain the signee of CCP's certificate,
        // we wouldn't be able to talk to the API. Disable SSL verification for now.
        $ApiConfiguration->setSSLVerification(false);
        $ApiConfiguration->setDebug(true);
        $ApiConfiguration->setDebugFile("debug.log");
        $this->esiClient = new ApiClient($ApiConfiguration);
        $this->db = new mysqli($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass'], $_SESSION['sql']['db']);
    }
    

    /**
     * Accepts the name and type of an entity to find using ESI's
     * search endpoint. This will perform an exact search for the given
     * entity type and name and only return an ID, if an exact match was found.
     * 
     * @param string $entityName the name of the entity
     * @param string $entityType the type of the entity, allowed values: agent, 
     *                           alliance, character, constellation, corporation, 
     *                           faction, inventorytype, region, solarsystem, station, 
     *                           wormhole
     * @return int the external ID for the given entity or <code>null</code> if not found
     * @throws ApiException on ESI communication error
     */
    public function getExternalIdForEntity($entityName, $entityType)
    {
        // search for the corp in order to get the external ID
        $SearchApi = new SearchApi($this->esiClient);

        $entitiesMatching = $SearchApi->getSearch(array($entityType), $entityName, null, null, null, null, true);

        $getter = GetSearchOk::getters()[$entityType];

        if(!is_null($entitiesMatching->$getter()) && count($entitiesMatching->$getter()) == 1)
        {
            $method = $entitiesMatching->$getter();
            return reset($method);
        }
        
        return null;
    }
    
    /**
     * 
     * @param type $name
     * @return type
     * @throws ApiException on ESI communication error
     */
    function getCharacterId($name)
    {
        return $this->getExternalIdForentity($name, 'character');
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws ApiException on ESI communication error
     */
    function getCorporationId($name)
    {
        return $this->getExternalIdForentity($name, 'corporation');
    }
    /**
     * 
     * @param type $id
     * @return type
     * @throws ApiException on ESI communication error
     */
    function getAllianceId($name)
    {
        return $this->getExternalIdForentity($name, 'alliance');
    }
    
    /**
     * Fetch character details and create the character with the given external ID 
     * in the database. The character's corporation is created as well.
     * 
     * @param int $id the external character ID
     * @throws ApiException on ESI communication error
     */
    function createCharacter($id)
    {
        $CharacterApi = new CharacterApi($this->esiClient);
        $EsiCharacter = $CharacterApi->getCharactersCharacterId($id);
        
        // check for and create alliance
        $corporationExternalId = $EsiCharacter->getCorporationId();
        $corporationId = $this->createCorporation($corporationExternalId);


        $query = "INSERT INTO kb3_pilots (plt_name, plt_crp_id, plt_externalid) VALUES ('".$this->db->escape_string($EsiCharacter->getName())."', ".$corporationId.", ".$id.")";
        $this->db->query($query);
        return $this->db->insert_id;
    }
    
    /**
     * Fetch corporation details and create the corporation with the given external ID 
     * in the database. If the corporation is part of an alliance, the alliance
     * is created as well.
     * 
     * @param int $id the external corporation ID
     * @throws ApiException on ESI communication error
     */
    function createCorporation($id)
    {
        $CorporationApi = new CorporationApi($this->esiClient);
        $EsiCorporation = $CorporationApi->getCorporationsCorporationId($id);
        
        // check for and create alliance
        $allianceExternalId = $EsiCorporation->getAllianceId();
        $allianceId = null;
        if($allianceExternalId != null)
        {
            $allianceId = $this->createAlliance($allianceExternalId);
        }
        
        else
        {
            $result = $this->db->query('SELECT all_id FROM kb3_alliances WHERE all_name like \'%None%\'');
            if ($row = @$result->fetch_assoc())
            {
                $allianceId = $row['all_id'];
            }
            else
            {
                $query = 'INSERT INTO kb3_alliances (all_name) VALUES (\'None\')';
                $this->db->query($query);
                $allianceId = $this->db->insert_id;
            }
        }

        $query = "INSERT INTO kb3_corps (crp_name, crp_all_id, crp_external_id) VALUES ('".$this->db->escape_string($EsiCorporation->getName())."', ".$allianceId.", ".$id.")";
        $this->db->query($query);
        return $this->db->insert_id;
    }
    
    /**
     * Fetch alliance details and create the alliance with the given external ID 
     * in the database.
     * 
     * @param int $id the external alliance ID
     * @return the internal alliance ID the was created
     * @throws ApiException on ESI communication error
     */
    function createAlliance($id)
    {
        $AllianceApi = new AllianceApi($this->esiClient);
        $EsiAlliance = $AllianceApi->getAlliancesAllianceId($id);
        
        $query = "INSERT INTO kb3_alliances (all_name, all_external_id) VALUES ('".$this->db->escape_string($EsiAlliance->getName())."', ".$id.")";
        $this->db->query($query);
        return $this->db->insert_id;
    }
}