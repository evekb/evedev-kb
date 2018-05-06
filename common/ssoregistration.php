<?php

use EDK\ESI\ESISSO;
use EDK\ESI\EdkSsoException;

class pSsoRegistration extends pageAssembly
{
    /** @var \Page the page object*/
    public $page;
    /** @var string error message */
    protected $errorMessage = '';
    /** @var string info message */
    protected $infoMessage = '';    
    /**
     * Construct the SSO Register page object.
     * Add the functions to the build queue.
     */
    function __construct()
    {
        parent::__construct();
        $this->queue("start");
        $this->queue("handleBeginSsoRegistration");
        $this->queue("handleFinishSsoRegistration");
        $this->queue("generate");
        $this->queue("showRegistrationForm");
        $this->queue("bottom");
    }
    
    /**
     * Start constructing the page.
     *
     * Prepare all the shared variables such as dates and check alliance ID.
     */
    function start()
    {
        $this->page = new Page('Register for ESI kill fetching');
        $this->page->addHeader('<meta name="robots" content="index, nofollow" />');
        
        if (null === config::get('cfg_sso_client_id') || null === config::get('cfg_sso_client_id') || config::get('cfg_sso_client_id') == '' || config::get('cfg_sso_secret') == '' ) 
        {
            $this->errorMessage .= "The Board owner has to enter a developer app key in order to register via SSO.";
        }
    }

    protected static function generateRandomString($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) 
        {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
    
    /**
     * Redirects to the Eve Online SSO login page.
     * 
     * @global resource $smarty the Smarty instance
     */
    function handleBeginSsoRegistration()
    {
        if(strlen($this->errorMessage) > 0 )
        {
            return;
        }
        
        event::call("ssoRegistration_begin", $this);
        
        if (isset($_POST['submit'])) 
        {
            if ($_POST['keytype'] == 'pilot') 
            {
                $scopes = ESISSO::SSO_SCOPE_CHARACTER_READ_KILLMAILS;
            } 
        
            else 
            {
                 $scopes = ESISSO::SSO_SCOPE_CORPORATION_READ_KILLMAILS;
            }
            
            Session::create();
            $authUrl = OAUTH_BASE_URL . "/authorize/";
            $state = uniqid();
            $_SESSION['authstate'] = $state;
            $url = $authUrl."?response_type=code&redirect_uri=".edkURI::page('ssoregistration')."&client_id=".config::get('cfg_sso_client_id')."&scope=".$scopes."&state=".$state;
            header('Location: '.$url);
            exit;
        }
    }
    
    /**
     * If the page was redirected to from the SSO Auth page, 
     * the SSO config gets saved or updated.
     */
    function handleFinishSsoRegistration()
    {   
        global $smarty;
        if(strlen($this->errorMessage) > 0 )
        {
            return;
        }
        
        event::call("ssoRegistration_finish", $this);
        
        if (isset($_GET['code'])) 
        {
            $code = $_GET['code'];
            $state = $_GET['state'];
            
            // check the SSO state;
            // make sure, the authorization request was started with the same session
            if ($state != $_SESSION['authstate']) 
            {
                $this->errorMessage .= "Error: Invalid SSO state, aborting.";
                return;
            }
          
            $EsiSso = new ESISSO();
            try
            {
                $EsiSso->fetchToken($code);
                $Pilot = new \Pilot(0, $EsiSso->getCharacterID());
                
                // verify whether that pilot is allowed to register
                $this->verifyPilotForRegistration($Pilot);

                $EsiSso->add();
                
                
                if(ESISSO::KEY_TYPE_CORPORATION == $EsiSso->getKeyType())
                {
                    $Corporation = $Pilot->getCorp();
                    $smarty->assign('infoMessage', 'Successfully registered Corporation '.$Corporation->getName().' for ESI killmail fetching!');
                }
                
                else if(ESISSO::KEY_TYPE_PILOT == $EsiSso->getKeyType())
                {
                    $smarty->assign('infoMessage', 'Successfully registered Pilot '.$Pilot->getName().' for ESI killmail fetching!');
                }
            }
            
            catch(EdkSsoException $e)
            {
                $this->errorMessage .= $e->getMessage();
            }
        }
    }
    
    
    function showRegistrationForm()
    {
        if(strlen($this->errorMessage) > 0 )
        {
            return;
        }
        
        global $smarty;
        return $smarty->fetch(get_tpl('ssoregistrationform'));
    }
    
    function bottom()
    {
        global $smarty;
        return $smarty->fetch(get_tpl('ssoregistration_bottom'));
    }
            
    
    /**
     * Generate HTML using the template.
     * 
     * @global resource $smarty
     * @return string HTMl generated using the ssoregister template
     */
    function generate()
    {
        global $smarty;
        $smarty->assign('errorMessage', $this->errorMessage);
        return $smarty->fetch(get_tpl('ssoregistration'));
    }
    
    /**
     * Executes verifications to check whether that pilot is allowed
     * to register.
     * 
     * @param \Pilot $Pilot the Pilot trying to register
     * @throws EdkSsoException if the verifications fail
     */
    function verifyPilotForRegistration($Pilot)
    {
        // do we need to check for board owners?
        if(Config::get('sso_allow_owners_only'))
        {
            $isPilotAllowed = false;
            // make sure the pilot information is up-to-date
            $Pilot->fetchPilot();
            
            $Corporation = $Pilot->getCorp();
            $Corporation->fetchCorp();
            
            if (count(config::get('cfg_pilotid')) > 0)
            {
                if (in_array($Pilot->getID(), config::get('cfg_pilotid')))
                {
                    $isPilotAllowed = true;
                }
            }
            if ($Corporation && count(config::get('cfg_corpid')) > 0)
            {
                if (in_array($Corporation->getID(), config::get('cfg_corpid')))
                {
                    $isPilotAllowed = true;
                }
            }
            if ($Corporation && count(config::get('cfg_allianceid')) > 0)
            {
                $Alliance = $Corporation->getAlliance();
                $Alliance->fetchAlliance();
                if ($Alliance && in_array($Alliance->getID(), config::get('cfg_allianceid')))
                {
                    $isPilotAllowed = true;
                }
            }
            
            if(!$isPilotAllowed)
            {
                EDKError::log("Pilot ".$Pilot->getName()." is not allowed to register for ESI fetching! (Corp: ".$Pilot->getCorp()->getName().", Alliance: ".$Pilot->getCorp()->getAlliance()->getName().")");
                throw new EdkSsoException('Only killboard owners are allowed to register!');
            }
        }
    }
    
}

$ssoRegistration = new pSsoRegistration();
event::call("ssoRegistration_assembling", $ssoRegistration);
$html = $ssoRegistration->assemble();
$ssoRegistration->page->setContent($html);

$ssoRegistration->page->generate();
?>
