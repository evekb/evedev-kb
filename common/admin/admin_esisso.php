<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page('Administration - ESI SSO');
$page->setAdmin();
$confirm = "";

if(isset($_POST['submit-key']))
{
    config::set('cfg_sso_client_id',$_POST['client_id']);
    config::set('cfg_sso_secret',$_POST['secret']);
    config::set('cfg_max_proc_time_per_sso_key', (int)$_POST['cfg_max_proc_time_per_sso_key']);

    if (isset($_POST['post_no_npc_only']) && $_POST['post_no_npc_only']) 
    {
      config::set('post_no_npc_only', '1');
    } 
    
    else 
    {
      config::set('post_no_npc_only', '0');
    }

    if (isset($_POST['sso_allow_owners_only']) && $_POST['sso_allow_owners_only']) 
    {
      config::set('sso_allow_owners_only', '1');
    } 
    
    else 
    {
      config::set('sso_allow_owners_only', '0');
    }

    $confirm = "<span style='color:green'>Settings Saved</span><br/>";
}

$qry = DBFactory::getDBQuery();

if(isset($_POST['submit-apis']))
{
  foreach($_POST['apis'] as $id => $api) {
     if (isset($api['delete'])) {
         $qry->execute("DELETE FROM kb3_esisso WHERE id=".$id);
     } elseif (isset($api['isEnabled'])) {
         $qry->execute("UPDATE kb3_esisso SET isEnabled = 1 WHERE id=".$id);
     } else {
         $qry->execute("UPDATE kb3_esisso SET isEnabled = 0 WHERE id=".$id);
     }
  }
}

$html = "<form id='ssokey' name='ssokey' method='post' action='".KB_HOST."/?a=admin_esisso'>";
$html .= $confirm;

$html .='<form action="" method="post">';
$html .= "<table class=kb-subtable>";
$html .= "<tr><td colspan=\"4\"><div class=block-header2>SSO options</div></td></tr>";
$html .= "<tr><td><b>EVE SSO Client id:</b></td><td><input type='text' size='45' name='client_id' value='".config::get('cfg_sso_client_id')."'/></td></tr>";
$html .= "<tr><td><b>EVE SSO Client secret:</b></td><td><input type='text' size='45' name='secret' value='".config::get('cfg_sso_secret')."'/></td></tr>";
$html .= "<tr><td colspan=\"4\">Your EVE SSO callback URL should be set to ".edkURI::page('ssoregistration')."</td></tr>";
$html .= "<tr><td colspan=\"4\"><br/>Register and application here: <a href=https://developers.eveonline.com/ target=\"_blank\">https://developers.eveonline.com</a> (valid subscription needed)<br/>In the Permissions section, select the following scopes: esi-killmails.read_killmails.v1, esi-killmails.read_corporation_killmails.v1</td></tr>";
$html .= "<tr><td><b>Maximum processing time per ESI SSO key [s]:</b></td><td><input type='text' size='10' name='cfg_max_proc_time_per_sso_key' value='".config::get('cfg_max_proc_time_per_sso_key')."'/></td></tr>";

// option: ignore NPC only kills
$html .= "<tr><td><b>Ignore NPC only deaths?</b></td>";
$html .= "<td><input type='checkbox' name='post_no_npc_only' id='post_no_npc_only'";
if (config::get('post_no_npc_only')) $html .= " checked=\"checked\"";
$html .= " /></td></tr>";


// option: only allow board owners to register for ESI fetch
$html .= "<tr><td><b>Only allow board owners to register?</b></td>";
$html .= "<td><input type='checkbox' name='sso_allow_owners_only' id='sso_allow_owners_only'";
if (config::get('sso_allow_owners_only')) $html .= " checked=\"checked\"";
$html .= " /></td></tr>";

$html .= "<tr></tr><tr><td></td><td colspan=\"4\" ><input type=\"submit\" name=\"submit-key\" value=\"Save\"></td></tr>";
$html .= "<tr><td colspan=\"4\">&nbsp;</td></tr>";
$html .= "</table>";
$html .= "</form><br/>";

$qry->execute("SELECT * FROM kb3_esisso ORDER by id");
$apis = array();
while ($row = $qry->getRow()) {
    array_push($apis, $row);
}

$html .='<form action="" method="post">';
$html .= "<table class='kb-table' width='100%'>";
$html .= "<tr class='kb-table-header'><th>CharacterID</th><th>Keytype</th><th>Last Kill</th><th>Failcount</th><th>Last fetched</th><th>Enabled</th><th>Delete</th></tr>";
foreach ($apis as $api) {
    $html .= "<input type='hidden' name='apis[".$api['id']."][id]' value=".$api['id'].">";
    $plt = new Pilot(0, $api['characterID']);
    $html .= "<tr class=''><td>".$plt->getName()." (".$plt->getCorp()->getName().")</td>";
    $html .= "<td>".$api['keyType']."</td>";
    $html .= "<td>".$api['lastKillID']."</td>";
    $html .= "<td>".$api['failCount']."</td>";
    $html .= "<td>".$api['lastKillFetchTimestamp']."</td>";
    $html .= "<td><input type='checkbox' name='apis[".$api['id']."][isEnabled]'";
    if ($api['isEnabled']) $html.= " checked=\"checked\"";
    $html.="/></td>";
    $html .= "<td><input type='checkbox' name='apis[".$api['id']."][delete]'/></td></tr>";
}
$html .= "</table><br/>";
$html .= "<input type=submit name=submit-apis value=\"Submit\">";
$html .= "</form><br/>";

$html .= '<form action="" method="post">';
$html .= "<input type=submit name=fetch-kills value=\"Fetch Kills\">";
$html .= "</form><br/>";

$html .= "<style>#loader{zoom:1;display:block;width:16px;height:16px;margin:20px auto;animation:wait .8s steps(1,start) infinite;background:linear-gradient(0deg,#f4f5fa 1px,transparent 0,transparent 8px,#f4f5fa 8px),linear-gradient(90deg,#f4f5fa 1px,#f6f9fb 0,#f6f9fb 3px,#f4f5fa 3px),linear-gradient(0deg,#ececf5 1px,transparent 0,transparent 8px,#ececf5 8px),linear-gradient(90deg,#ececf5 1px,#f2f3f9 0,#f2f3f9 3px,#ececf5 3px),linear-gradient(0deg,#e7eaf4 1px,transparent 0,transparent 8px,#e7eaf4 8px),linear-gradient(90deg,#e7eaf4 1px,#eef1f8 0,#eef1f8 3px,#e7eaf4 3px),linear-gradient(0deg,#b9bedd 1px,transparent 0,transparent 10px,#b9bedd 10px),linear-gradient(90deg,#b9bedd 1px,#d0d5e8 0,#d0d5e8 3px,#b9bedd 3px),linear-gradient(0deg,#9fa6d2 1px,transparent 0,transparent 15px,#9fa6d2 15px),linear-gradient(90deg,#9fa6d2 1px,#c0c5e1 0,#c0c5e1 3px,#9fa6d2 3px),linear-gradient(0deg,#8490c6 1px,transparent 0,transparent 15px,#8490c6 15px),linear-gradient(90deg,#8490c6 1px,#aeb5da 0,#aeb5da 3px,#8490c6 3px);background-repeat:no-repeat;background-size:4px 9px,4px 9px,4px 9px,4px 9px,4px 9px,4px 9px,4px 11px,4px 11px,4px 16px,4px 16px,4px 16px,4px 16px;background-position:-4px 3px,-4px 3px,-4px 3px,-4px 3px,-4px 3px,-4px 3px,-4px 2px,-4px 2px,-4px 0,-4px 0,-4px 0,-4px 0}@keyframes wait{12.5%{background-position:-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,0,0}25%{background-position:-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,0,0,6px,6px}37.5%{background-position:-4px,-4px,-4px,-4px,-4px,-4px,0,0,6px,6px,12px,12px}50%{background-position:-4px,-4px,-4px,-4px,0,0,6px,6px,12px,12px,-4px,-4px}62.5%{background-position:-4px,-4px,0,0,6px,6px,12px,12px,-4px,-4px,-4px,-4px}75%{background-position:0,0,6px,6px,12px,12px,-4px,-4px,-4px,-4px,-4px,-4px}87.5%{background-position:6px,6px,12px,12px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px}100%{background-position:12px,12px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px,-4px}}body{font-family:Helvetica}</style>";

if(isset($_SESSION['esiapis'])) {
  if(count($_SESSION['esiapis'])) {
    $api = $_SESSION['esiapis'][0];
    $api->setIgnoreNpcOnlyKills((boolean)(config::get('post_no_npc_only')));
    $api->setMaximumProcessingTime((int)config::get('cfg_max_proc_time_per_sso_key'));
    array_shift($_SESSION['esiapis']);
    try
    {
        $_SESSION['esimsg'] .= $api->processAPI();
    }
    
    catch(Exception $e)
    {
        EDKError::log($e->getMessage());
        $_SESSION['esimsg'] .= $e->getMessage();
    }
    
    // display header for next key to fetch
    if(count($_SESSION['esiapis'])) 
    {
      $api = $_SESSION['esiapis'][0];
      $plt = new Pilot(0, $api->getCharacterID());
      $_SESSION['esimsg'] .= '<div class="block-header2">Fetching for Killlog for '.$plt->getName()." (".$api->getKeyType()."):</div>";
      $page->addHeader('<meta http-equiv="refresh" content="2">');
    }
    $html .= $_SESSION['esimsg'];
    if(count($_SESSION['esiapis'])) $html .='<div id="loader"></div>';
  }
}

else if(isset($_SESSION['esimsg']))
{
    $html .= $_SESSION['esimsg'];
    unset($_SESSION['esimsg']);
}

if(isset($_POST['fetch-kills']))
{
  $apis = \EDK\ESI\ESIFetch::getAll();
  $_SESSION['esiapis'] = $apis;
  $_SESSION['esimsg'] = '';
  $plt = new Pilot(0, $apis[0]->getCharacterID());
  $_SESSION['esimsg'] .= '<div class="block-header2">Fetching for Killlog for '.$plt->getName()." (".$apis[0]->getKeyType()."):</div>";
  $html .= $_SESSION['esimsg'].'<div id="loader"></div>';
  $page->addHeader('<meta http-equiv="refresh" content="2">');
  /*foreach ($apis as $api) {
    $plt = new Pilot(0, $api->getCharacterID());
    $html .= '<div class="block-header2">Fetching for Killlog for '.$plt->getName()." (".$api->getKeytype()."):</div>";
    $html .= $api->processAPI();
  }*/
}

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();

?>
