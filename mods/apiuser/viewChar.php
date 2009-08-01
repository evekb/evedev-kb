<?php
/*
print '<pre>';
print_r();
print '</pre>';
exit;
*/
require_once( "common/admin/admin_menu.php" );
require_once('common/includes/class.ship.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once( "common/includes/class.eveapi.php" );



$allAPI= new APIChar();
$charID=intval($_GET['charID']);
$qry = new DBQuery();
$qry->execute('select userID,apiKey,charID,charName  from kb3_api_user where charID='.$charID);
$row = $qry->getRow();
if (count($row)<2)
{
	$html.='Unknow Char.. Exit';
	$page = new Page('EVE Char - Error' );
	$page->setCachable(false);
	$page->setAdmin();
	$page->setContent($html);
}
else
{
$qry->execute('select charID,charName from kb3_api_user where userID='.$row['userID'].' and charID<>'.$row['charID']);
 while ($row2 = $qry->getRow())
	{
	 $listAlt[]=$row2;
	}
$apistring = 'userID=' . $row['userID'] . '&apiKey=' . $row['apiKey'].'&characterID='.$row['charID'];
if (!is_dir('./cache/xmlchar/')) mkdir ('./cache/xmlchar/');
$cache=true;

$storedXmlFile='./cache/xmlchar/'.$charID.'.xml';
if (is_file($storedXmlFile))
	$xml = new SimpleXMLElement(file_get_contents($storedXmlFile));
	if ((time()-strtotime($xml->currentTime))<3600*24)
		{
		$cache=false;
		$html.='(-)';
	}
if ($cache)
	{
	$fl=file_get_contents('http://api.eve-online.com/char/CharacterSheet.xml.aspx?'.$apistring);
	file_put_contents($storedXmlFile,$fl);
	$xml = new SimpleXMLElement($fl);
	$html.= '(+)';
	}

//$xmlstr=file_get_contents('./mods/apiuser/char.xml');


$page = new Page('EVE Char - '.$row['charName'] );
$page->setCachable(false);
$page->setAdmin();
$kb = new Killboard(KB_SITE);
//$xml = new SimpleXMLElement($xmlstr);
$listID='(';
foreach ($xml->result->rowset->row as $name)
{
	$t=$name->attributes();
	$listID.=$t['typeID'].',';
}
$listID=substr($listID,0,($listID-1)).')';
$qry->execute('select typeID,typeName,groupID from kb3_invtypes where typeID in '.$listID.' order  by groupID,typeName');
 while ($row = $qry->getRow())
	{
	$corrID[$row['typeID']]=$row;
	}

foreach ($xml->result->rowset->row as $name)
{
	$t=$name->attributes();
	$corrID[intval($t['typeID'])]['level']=intval($t['level']);
	$corrID[intval($t['typeID'])]['skillpoints']=intval($t['skillpoints']);
	$total_sp+=intval($t['skillpoints']);
	$total_skill+=1;
//	$name->addAttribute('typeName',$corrID[intval($t['typeID'])]['typeName']);
//	$name->addAttribute('groupID',$corrID[intval($t['typeID'])]['groupID']);
}

	//Preparation des attributs
//print $xml->result->attributes->intelligence;

$smarty->assign('total_sp', $total_sp);
$smarty->assign('total_skill', $total_skill);
$smarty->assign('basexml', $xml);
$smarty->assign('listalt', $listAlt);
$smarty->assign('xml', $corrID);
//$smarty->assign('xml', $xml);

$page->setContent($smarty->fetch('../mods/apiuser/templates/viewChar.tpl'));
}
$page->generate();

?>