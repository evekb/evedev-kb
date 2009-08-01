<?php
require_once( "common/admin/admin_menu.php" );
require_once('common/includes/class.ship.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once( "common/includes/class.eveapi.php" );

$page = new Page( "Settings - API user management" );
$page->setCachable(false);
$page->setAdmin();
define (APIUSER_VERSION,'0.5');

$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpfile=config::get('apiuser_phpbbrelative').'/common.' . $phpEx;

$qry = new DBQuery();
	// SQL File installation

if (!config::get('apiuser_version') || floatval(config::get('apiuser_version'))<floatval(APIUSER_VERSION))
{
	define(A_INSTALLER,'1');
	include('instdb.php');
	$html.='Apiuser now in version '.APIUSER_VERSION.'<br>';
}
//--- Sauvegarde
if (isset($_POST['go']))
{
		//PHPbb 3 integration
   if ($_POST['registerphpbb'])
        config::set('apiuser_registerphpbb', '1');
   else
        config::set('apiuser_registerphpbb', '0');
   if ($_POST['forcephpbb'])
        config::set('apiuser_forcephpbb', '1');
   else
        config::set('apiuser_forcephpbb', '0');
   config::set('apiuser_allianceforum', intval($_POST['allianceforum']));
   config::set('apiuser_alliedforum', intval($_POST['alliedforum']));
   config::set('apiuser_phpbbrelative', ($_POST['phpbbrelative']));



   if ($_POST['roleAcces'])
        config::set('apiuser_roleAcces', '1');
   else
        config::set('apiuser_roleAcces', '0');

   if ($_POST['comment'])
        config::set('apiuser_comment', '1');
   else
        config::set('apiuser_comment', '0');

   if ($_POST['storechar'])
        config::set('apiuser_storechar', '1');
   else
        config::set('apiuser_storechar', '0');
   if ($_POST['killmail'])
        config::set('apiuser_killmail', '1');
   else
        config::set('apiuser_killmail', '0');

   if ($_POST['showchar'])
        config::set('apiuser_show3char', '1');
   else
        config::set('apiuser_show3char', '0');

   if ($_POST['showPoster'])
        config::set('apiuser_showPoster', '1');
   else
        config::set('apiuser_showPoster', '0');

   $html.='<i>Setting saved.</i><br />';
}
//---



if (isset($_GET['check_config']))
{
$html .= "<div class=block-header2>Configuration control</div>";
	//Check tt les fichiers / config
if (is_file("common/includes/class.eveapi.php"))
{
	require_once("common/includes/class.eveapi.php");
	if (floatval(str_replace('V','',APIVERSION))>2.4)
		$html.='&raquo; APIeve Found, version '.APIVERSION. ' <font color=green>Ok</font><br>';
	else
		$html.='&raquo; APIeve Found, but version '.APIVERSION. ' is not enough, need version > 2.4 <font color=red>Error</font><br>';
}
else
	$html.='&raquo; file : class APIEVE "common/includes/class.eveapi.php" not found, you may pick it from <a href="http://www.eve-dev.net/e107_plugins/forum/forum_viewtopic.php?8327.0">Here</a> <font color=red>Error</font><br>';

	//Check PhpBB Settings
if (config::get('apiuser_registerphpbb') ||config::get('apiuser_forcephpbb'))
{
if (!is_file($phpfile))
	$html.='&raquo; phpBB enable, but i can\'t find require phpBB file "'.$phpfile.'" <font color=red>Error</font><br>';
else
	$html.='&raquo; phpBB enable, phpBB file "'.$phpfile.'" found <font color=green>Ok</font><br>';

if (config::get('apiuser_forcephpbb'))
	{
	$f=file_get_contents('./common/index.php');
	if (strstr($f,"f (config::get('apiuser_forcephpbb')==1)")===FALSE)
		$html.='&raquo; file : phpBB session forced, but the file "./common/index.php" have NOT been edited <font color=red>Error</font><br>';
	else
		$html.='&raquo; phpBB session forced, file  "./common/index.php" has been edited <font color=green>Ok</font><br>';
	}
}
else
	$html.='&raquo; PHPBB setting disabled, phpBB setting not checked <font color=green>Ok</font><br>';

$qry->execute('select count(*) as nb from kb3_all_corp');
$row=$qry->getRow();
if (intval($row['nb'])>0)
	$html.='&raquo; table : kb3_kill_all exist with '.$row['nb'].' corporation known <font color=green>Ok</font><br>';
	else
	$html.='&raquo; table : kb3_kill_all : can\'t get number of corporation or no corporation, click <a href="index.php?a=settings_apiuser&getlistAlliance">here</a> to update the list <font color=red>error</font><br>';
	//Check kill_detail.php

	$mods_active = explode(',', config::get('mods_active'));
$modOverrides = false;
$lmod='';
foreach ($mods_active as $mod)
{

    if (file_exists('./mods/'.$mod.'/kill_detail.php'))
    {
	$f=file_get_contents('./mods/'.$mod.'/kill_detail.php');
	if (strstr($f,"kb3_api_user")===FALSE)
		$html.='&raquo; file : "'.'./mods/'.$mod.'/kill_detail.php'.'" isn\'t edited to show the Username of the killmail poster,read txt file  <font color=orange>warning</font><br>';
	else
		$html.='&raquo; file : "./mods/'.$mod.'/kill_detail.php have been edited for poster info <font color=green>Ok</font><br>';

        if ($modOverrides)
		{
		$html.='&raquo; Two or more of the mods you have activated are conflicting for page kill_detail.php :'.$lmod.$mod.' <font color=red>error</font><br>';
		}
        $modOverrides = true;
        $modOverride = $mod;
		$lmod.=$mod.',';
	$validmod=$mod;
    }

}

if (strlen($lmod)==0 && strlen($validmod)>0)
	$html.='&raquo; file : "./mods/'.$mod.'/kill_detail.php  will be used <font color=green>Ok</font><br>';
if (strlen($lmod)==0 && strlen($validmod)==0)
	{
	$f=file_get_contents('./common/kill_detail.php');
	if (strstr($f,"kb3_api_user")===FALSE)
		$html.='&raquo; file : "'.'./common/kill_detail.php'.'" isn\'t edited to show the Username of the killmail poster,read txt file  <font color=orange>warning</font><br>';
	else
		$html.='&raquo; file : "./common/kill_detail.php have been edited for poster info <font color=green>Ok</font><br>';
	}

}//fin de check control
else
	$html.='<form id="options" name="options" method="post" action="?a=settings_apiuser&check_config"><input type=submit value="Check Configuration" ></form>';


if (isset($_GET['getlistAlliance']))
{
	require_once( "common/includes/class.eveapi.php" );
	$allAPI= new AllianceAPI();
	$test = $allAPI->updatealliancetable();
	$qry->execute('select count(*) as nb from kb3_all_corp');
	$tb=$qry->getRow();
	if (config::get('API_AllianceXMLLastUpdate'))
	$html.='<span class=losscount>'.$tb['nb']. ' Corporation added</span><br>';
	else
		$html.='<span class=losscount>No know alliance table update';
}

if (isset($_GET['getlistAlliance2']))
{
	$content=file_get_contents('http://api.eve-online.com/eve/AllianceList.xml.aspx');
	file_put_contents('./cache/AllianceList.xml',$content);

	$dataxml= simplexml_load_file(config::get('api_user_allianceCache'));
	$j=0;$i=0;

	while (is_object($dataxml->result->rowset[0]->row[$i]))
	{
		$alliance=$dataxml->result->rowset[0]->row[$i]->attributes();

		$j=0;
		while (is_object($dataxml->result->rowset[0]->row[$i]->rowset[0]->row[$j]))
			{
				$corpo=$dataxml->result->rowset[0]->row[$i]->rowset[0]->row[$j]->attributes();

				$q.="(".$alliance['allianceID'].",".$corpo['corporationID'].",'".slashfix($alliance['name'])."'),";
				$j++;
			}
	$qry = new DBQuery();
	$qry->execute('delete from kb3_all_corp where all_ID='.$alliance['allianceID']);
	$qry->execute("INSERT INTO kb3_all_corp values ".substr($q,0,strlen($q)-1));
	$q="";
	$i++;
	}
	config::set('api_user_allianceCache','./cache/AllianceList.xml');
$html.='<span class=losscount>List Of alliance updated</span><br>';
}


$html .= '<form id="options" name="options" method="post" action="?a=settings_apiuser">';
$html .= "<div class=block-header2>General Settings</div>";
$html .= "<input type=checkbox name=showPoster id=fetch_losses";
if (config::get('apiuser_showPoster'))
    $html .= " checked=\"checked\"";
$html .= "> Show the Username of the killmail poster<br>";

$html .= "<div class=block-header2>Access Settings</div>";
$html .= "<input type=checkbox name=comment id=fetch_losses";
if (config::get('apiuser_comment'))
    $html .= " checked=\"checked\"";
$html.="> Need the role <b>'comment'</b> to post comment<br>";

$html .= "<input type=checkbox name=killmail id=fetch_losses";
if (config::get('apiuser_killmail'))
    $html .= " checked=\"checked\"";
$html.="> Need the role <b>'post killmaill'</b>  to post killmail<br>";

$html .= "<input type=checkbox name=roleAcces";
if (config::get('apiuser_roleAcces'))
    $html .= " checked=\"checked\"";
$html.="> <b>Set the killboard private (need 'access' role)</b><br>";

$html .= "<div class=block-header2>API Key Settings</div>";

$html .= "<input type=checkbox name=storechar id=fetch_losses";
if (config::get('apiuser_storechar'))
    $html .= " checked=\"checked\"";
$html .= "> Store the 3 char of the API key<br>";

$html .= "<input type=checkbox name=showchar id=fetch_losses";
if (config::get('apiuser_show3char'))
    $html .= " checked=\"checked\"";
$html .= "> Show the 3 char of the API key in the user management page<br>";

$html .= "<div class=block-header2>PHPBB3 security Integration</div>";


	//Check PhpBB Settings

if (config::get('apiuser_registerphpbb')  && !is_file($phpfile))
	{
	$html.='phpBB enable, but i can\'t find require phpBB file "'.$phpfile.'" <font color=red>Error</font><br>';
	config::set('apiuser_registerphpbb', '0');
	}


	if (config::get('apiuser_forcephpbb'))
		{
		$f=file_get_contents('./common/index.php');
		if (strstr($f,"f (config::get('apiuser_forcephpbb')==1)")===FALSE)
			{
			$html.='file : phpBB session forced, but the file "./common/index.php" have NOT been edited <font color=red>Error</font><br>';
	        config::set('apiuser_forcephpbb', '0');
			}

		}


$html.=' &nbsp;  I <b>STRONGLY</b> advice you to read this paragraph.<br>';
$html.=' &nbsp;  If you set the path of phpbb/id of forum wrong you will no longer be able to acces the killboard.<br>';
$html.=' &nbsp;  set apiuser_forcephpbb to 0 in the kb3_config table .<br>';

$html .= "<input type=checkbox name=forcephpbb id=fetch_losses";
if (config::get('apiuser_forcephpbb'))
    $html .= " checked=\"checked\"";
$html .= "> Force to have a phpBB session to access on the board<br>";


$html .= "<input type=checkbox name=registerphpbb id=fetch_losses";
if (config::get('apiuser_registerphpbb'))
    $html .= " checked=\"checked\"";
$html .= "> Force to have a phpBB session to register on the board<br>";
$html.='<input type=text name="allianceforum" size=2 value="'.config::get('apiuser_allianceforum').'"> ID of the alliance forum <br>';
$html.='<input type=text name="alliedforum" size=2 value="'.config::get('apiuser_alliedforum').'"> ID of the allied forum (set to 0 if you doesn\'t want to use that feature)<br>';
$html.='<input type=text name="phpbbrelative" size=15 value="'.config::get('apiuser_phpbbrelative').'"> Relative path of the phpBB forum (ex : ../forum)<br>';






$html .= "<br><input type=submit id=submit name=go value=\"Save\"><br><br>";
$html .= "</form /><br><br>";

$html.='<a href="index.php?a=settings_apiuser&getlistAlliance">Update list of corporation in alliances (via API CLass)</a><br>';
$html.='<a href="index.php?a=settings_apiuser&getlistAlliance2">Update list of corporation in alliances (for php5 user)</a>';


$html .= "<div class=block-header2>User Managment</div>";
$html.=' <a href="?a=user_management">Manage Users</a>';



$html.='<span class="killcount">APIUser Version '.config::get('apiuser_version').'</span>';
$page->setContent( $html );
$page->addContext( $menubox->generate() );
$page->generate();

?>