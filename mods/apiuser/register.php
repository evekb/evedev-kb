<?php
require_once('common/includes/class.ship.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once( "common/includes/class.eveapi.php" );



$page = new Page('User - Registration');

if (config::get('user_regdisabled'))
{
    $page->error('Registration has been disabled.');
    return;
}
$qry = new DBQuery();

	$qry->execute('select count(*) as nb from kb3_all_corp ');
	$row = $qry->getRow();
if ($row['nb']<1)
	{
	$html.='<span class=losscount>You need to update the allaince list via the setting panel of this mod to be able to register account</span>';
	$page->setContent($html);
	$page->generate();
	exit;
	}

$html .= '<div class=block-header2>New user</div>';
// Please leave the information on the next line as is so that other people can easily find the EVE-Dev website.
// Remember to share any modifications to the EVE-Dev Killboard.

$html.='Hello, welcome to the register account page.<br>';

if (config::get('apiuser_comment'))
	$html.='<ul> &raquo; You need to have an account created here to be able to post comment.</ul>';
if (config::get('apiuser_killmail'))
	$html.='<ul> &raquo; You need to have an account created here to be able to add killmail.</ul>';
if (config::get('apiuser_registerphpbb'))
	$html.='<ul> &raquo; You need to be logged on the forum BEFORE registering an account here .</ul>';

$html.='Go <a target=_new href="http://myeve.eve-online.com/api/default.asp">Here</a> for you\'re getting you\'r <b>Limited Access API Key</b> ';
$html .= '<form id="options" name="options" method="post" action="?a=register">';
if (config::get('apiuser_registerphpbb'))
{
//debut Mod PHPBB
	if (!is_file($phpbb_root_path . 'common.' . $phpEx) && is_file($phpbb_root_path . 'includes/functions_display.' . $phpEx))
		$html.='<h1>ERROR PHPBB PATH in APIuser mod</h1>';
}
//fin Mod PHPBB

if (!isset($_POST['step']))
	{
	$html .= '<div class=block-header2>Step 1 : Enter Api Key</div><table>';
	$html .= '<tr><td>user ID (6 numer): </td><td><input type=text name=userID ></td></tr>';
	$html .= '<tr><td>API Key (64 char) : </td><td><input size=64 type=text name=apiKey ></td></tr>';
	$html .= '</table><input type=hidden name=step value=2 />';
	}
if (isset($_POST['step']) && $_POST['step']==2)
{
$html .= '<div class=block-header2>Step 2 : Choose a char</div>';
$qry->execute('select count(*) as nb  from kb3_api_user where userID='.intval($_POST['userID']).' and ban=1');
$row = $qry->getRow();
if ($row['nb']>0)
	{
	$html.='<span class="losscount">Sorry, you have been banned to use this killboard</span>';
	$page->setContent($html);
	$page->generate();
	exit;
	}

$userID=intval($_POST['userID']);
$apiKey=$_POST['apiKey'];

$apistring = 'userID=' . $userID . '&apiKey=' . $apiKey;
$myCharSelect = new APIChar();

$charL= $myCharSelect->fetchChars($apistring);
if (!is_array($charL))
	{
	$html.='<span class="losscount">Error API KEY</span>';
		$page->setContent($html);
	$page->generate();
	exit;
	}

for ($i=0;$i<3;$i++)
{
	$qry->execute('select all_name from kb3_all_corp where corp_id='.intval($charL[$i]['corpID']));
	$row = $qry->getRow();
	$charL[$i]['allianceName']=$row['all_name'];
$qry->execute('delete from kb3_api_user where userID='.intval($userID).' and charID='.intval($charL[$i]['charID']));

$query='insert into kb3_api_user values('.$userID.",'".slashfix($apiKey)."','".intval($charL[$i]['charID'])."','".slashfix($charL[$i]['Name'])."','".slashfix($charL[$i]['corpName'])."','".slashfix($charL[$i]['allianceName'])."','',0)";

	$qry->execute($query);
	}
foreach($charL as $key=>$char)
	{
		if (CORP_ID)
		{
			$corp = new Corporation(CORP_ID);
			if (strtolower($corp->getName())==strtolower($char['corpName']))
				$char['selectable']=1;
			 $nn=$corp->getName();
		}
			if (ALLIANCE_ID)
		{
			$alli = new Alliance(ALLIANCE_ID);
			 if (strtolower($alli->getName())==strtolower($char['allianceName']))
					$char['selectable']=1;
			 $nn=$alli->getName();
		}
		if (isset($char['selectable']))
			$listChar[]=$char;
	}
	$html .= '<input type=hidden name=userid value='.$userID.' />';
	$html.=' <br>Only Char in <b>'.$nn.'</b> can be selected.<br><br>';
		if (!is_array($listChar))
		$html.='<span class=losscount>Error no valid char found</span><br>';
		else
	{
	$html.='<select name=characterID>';

	foreach ($listChar as $key=>$char)
	{
		$html.='<option value="'.$char['charID'].'">['.$char['allianceName'].'] '.$char['corpName'].' / '.$char['Name'].'</option>';
	}
	$html.='</select>';
	$html .= '<br><br> password :<input type=password name=password />';
	$html .= '<input type=hidden name=step value=3 />';
	}
}

if (isset($_POST['step']) && $_POST['step']==3)
{

if (!config::get('apiuser_storechar'))
	$qry->execute("delete from kb3_api_user where password='' and userID=".intval($_POST['userid']));

$qry->execute("delete from kb3_user where usr_login='".$usr['charName']."'");
$html .= '<div class=block-header2>Step 3 : Final Step</div>';

	$qry->execute('select charName,apiKey from kb3_api_user where  charID='.intval($_POST['characterID']));
	$usr= $qry->getRow();
	$qry->execute('select plt_id from kb3_pilots where plt_externalid='.intval($_POST['characterID']));
	$char= $qry->getRow();
	$id = intval($_POST['characterID']);
    $pilot = $char['plt_id'];
     user::register(slashfix($usr['charName']), slashfix($_POST['password']), $pilot, $id);
	$qry->execute("select usr_id from kb3_user where usr_login='".slashfix($usr['charName'])."'");
	$tmp= $qry->getRow();	
	$qry->execute('update kb3_api_user set usr_id ='.$tmp['usr_id'].' where charID='.intval($_POST['characterID']));
	$page->setContent('Account registered.');
    $page->generate();
     return;

}

if ($_POST['step']<3)
	{
$html .= "<br><br><input type=submit id=submit name=go value=\"Next Step\"><br><br>";
$html.='</form>';
	}

$page->setContent($html);
$page->generate();
?>
