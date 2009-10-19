<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;

if ($_REQUEST['submit'])
{
    foreach ($_POST['set'] as $name => $value)
    {
        $_SESSION['sett'][$name] = $value;
    }
}
$uri = 'http://'.$_SERVER['HTTP_HOST'].str_replace('/install/index.php','', $_SERVER['SCRIPT_NAME']);
if (!$_SESSION['sett']['host'])
{
    $_SESSION['sett']['host'] = $uri.'/';
}
if (!$_SESSION['sett']['style'])
{
    $_SESSION['sett']['style'] = $uri.'/style';
}
if (!$_SESSION['sett']['img'])
{
    $_SESSION['sett']['img'] = $uri.'/img';
}
if (!$_SESSION['sett']['common'])
{
    $_SESSION['sett']['common'] = $uri.'/common';
}

if ($_SESSION['sett']['adminpw'] && $_SESSION['sett']['site'])
{
    $stoppage = false;
}
if ($_SESSION['sett']['aid'] && $_SESSION['sett']['cid'])
{
    echo '<b>Error:</b> You have entered an alliance AND corp id, please fix this conflict.<br/>';
    $stoppage = true;
}
if (isset($_SESSION['sett']['site']) && strlen($_SESSION['sett']['site']) > 12)
{
    echo '<b>Error:</b> Your site identification string is way too long.<br/>';
    $stoppage = true;
}
?>
<p>You have to enter/edit some settings now. I will generate a config file based on this data for you.<br/>
To be able to continue you have to enter at least an admin password and a site identification key.<br/>
<br/>
<b>Tips:</b><br/>
Title is used as title attribute for every page so your corp/alliance name could be a good idea.<br/>
Site identification should be 1-8 chars, they will be used to reference your settings inside the database, something like 'GKB' will be sufficient.<br/>
The URLs are guessed on the location of this installscript, you might need to correct them for some installations.<br/>
</p>
<form id="options" name="options" method="post" action="?step=6">
<input type="hidden" name="step" value="6">
<div class="block-header2">Settings</div>
<table class="kb-subtable">
<?php
$settings = array();
$settings[] = array('descr' => 'Adminpassword', 'name' => 'adminpw');
$settings[] = array('descr' => 'Title', 'name' => 'title');
$settings[] = array('descr' => 'Site', 'name' => 'site');

$settings[] = array('descr' => 'Host', 'name' => 'host');
$settings[] = array('descr' => 'Style URL', 'name' => 'style');
$settings[] = array('descr' => 'IMG URL', 'name' => 'img');
$settings[] = array('descr' => 'Common URL', 'name' => 'common');

//$settings[] = array('descr' => 'CorpID', 'name' => 'cid');
//$settings[] = array('descr' => 'AllianceID', 'name' => 'aid');

foreach ($settings as $set)
{
?>
<tr><td width="120"><b><?php echo $set['descr']; ?></b></td><td><input type=<?php 
	if($set[name] == 'adminpw') echo "password"; else echo "text"
	?> name=set[<?php echo $set['name']; ?>] size=60 maxlength=80 value="<?php echo $_SESSION['sett'][$set['name']]; ?>"></td></tr>
<?php
}
?>
<tr><td width="120"></td><td><input type=submit name=submit value="Save"></td></tr>
</table>
<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>