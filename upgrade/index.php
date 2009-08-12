<?php
// Upgrade an existing installation.

/*
Each upgrade is placed in a subfolder and subfolder/update.php is included then
function [subfoldername] is called. Official updates are numbered sequentially.
e.g. upgrade/012/
*/
if(function_exists("set_time_limit"))
	set_time_limit(0);

define('DB_HALTONERROR', true);
chdir("..");
require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');

$config = new Config(KB_SITE);
session::init();
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
if($_SERVER['QUERY_STRING'] != "") $url .= '?'.$_SERVER['QUERY_STRING'];

// Define style.
$style = '<style type="text/css">
body{margin: 0px;  color: #fff9ff;  padding: 0px;  height: 100%;  background-color: #0D2323;}
font,th,td,p,a,div{  font-family: Verdana, Bitstream Vera Sans, Arial, Helvetica;}
a{  color: #ffffff;  text-decoration: underline;}
#content{  margin-top: 10px;  padding: 10px;  background: #3B5353;  font-size: 11px;}
#page-title{  margin: 5px;  padding-top: 3px;  height: 25px;  color: #ffffff;  border-bottom: 1px solid #ffffff;  font-size: 16px;  font-weight: bold;}
</style>
';

$header1 = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>';
$header2 = '<meta http-equiv="content-type" content="text/html; charset=UTF8">
<title>EVE Development Network Killboard Upgrade Script</title>
'.$style.'
</head>
<body>
<table align="center" bgcolor="#111111" border="0" cellspacing="1" style="height: 100%">
<tr style="height: 100%">
<td valign="top" style="height: 100%">
<img src="../banner/revelations_gray.jpg" border="0">
<div id="page-title">Upgrade</div>
<table cellpadding="0" cellspacing="0" width="100%" border="0">
<tr><td valign="top"><div id="content">';

$header = $header1.'<meta http-equiv="refresh" content="5;url='.$url.'" >'.$header2;

$footer = '</div></td></tr></table>
<div class="counter"><font style="font-size: 9px;">&copy;2006-2009 <a href="http://www.eve-dev.net/" target="_blank">EVE Development Network</a></font></div>
</td></tr></table></body></html>';

if (!session::isAdmin())
{
	if (isset($_POST['usrpass']) && (crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD || $_POST['usrpass'] == ADMIN_PASSWORD))
	{
		session::create(true);

		header('Location: '.$url);
		die;
	}
	else
	{
		echo $header1.$header2; ?>
You must log in as admin to complete an upgrade.
<form method="post" action="<?php echo $url; ?>">
	<table>
		<tr>
			<td width="160"><b>Admin Password:</b></td>
			<td><input type="password" name="usrpass" maxlength="32"></td>
		</tr>
		<tr>
			<td width="160">&nbsp;</td>
			<td><input type="submit" name="submit" value="Login"></td>
		</tr>
	</table></form>
		<?php
		echo $footer;
		die;
	}
}
$qry=new DBQuery(true);
define('CURRENT_DB_UPDATE', config::get("DBUpdate"));
define('LASTEST_DB_UPDATE', "012");
if (CURRENT_DB_UPDATE >= LASTEST_DB_UPDATE )
{
	echo $header1.$header2;
	echo"Board is up to date.<br><a href='".config::get('cfg_kbhost')."'>Return to your board</a>";
	echo $footer;
	die();
}
updateDB();
@touch ('install/install.lock');
echo $header1.$header2;
echo "Upgrade complete.<br><a href='".config::get('cfg_kbhost')."'>Return to your board</a>";
echo $footer;
die();

function updateDB()
{
// if update nesseary run updates
	killCache();
	removeOld(0,'cache/templates_c', false);
	chdir('upgrade');
	$dir = opendir('.');
	$updatedirs = array();
	while ($file = readdir($dir))
    {
		
        if ($file[0] == '.' || !is_dir($file))
        {
            continue;
        }
		else $updatedirs[] = $file;
	}
	asort($updatedirs);
	foreach($updatedirs as $curdir)
	{
		if(!preg_match("/[0-9]+/",$curdir)) continue;
		if(CURRENT_DB_UPDATE >= $curdir) continue;
		require_once($curdir.'/update.php');
		$func = 'update'.$curdir;
		$func();
	}
}

/*
 * Too much has changed between update005 and current status for a clean
 * update006. Restarting from update007 in the hope that the differences
 * between 5 and 7 are worked out and an update006 implemented
 */

function update_slot_of_group($id,$oldSlot = 0 ,$newSlot)
{
	$qry  = new DBQuery();
	$query = "UPDATE kb3_item_types
				SET itt_slot = $newSlot WHERE itt_id = $id and itt_slot = $oldSlot;";
	$qry->execute($query);
	$query = "UPDATE kb3_items_destroyed
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);

	$query = "UPDATE kb3_items_dropped
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);
}

function move_item_to_group($id,$oldGroup ,$newGroup)
{
	$qry  = new DBQuery();
	$query = "UPDATE kb3_invtypes
				SET groupID = $newGroup
				WHERE typeID = $id AND groupID = $oldGroup;";
	$qry->execute($query);
}

function killCache()
{
	if(!is_dir(KB_CACHEDIR)) return;
	$dir = opendir(KB_CACHEDIR);
	while ($line = readdir($dir))
	{
		if (strstr($line, 'qcache_qry') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
		elseif (strstr($line, 'qcache_tbl') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
	}
}

function removeOld($hours, $dir, $recurse = false)
{
	if(!session::isAdmin()) return false;
	if(strpos($dir, '.') !== false) return false;
	//$dir = KB_CACHEDIR.'/'.$dir;
	if(!is_dir($dir)) return false;
	if(substr($dir,-1) != '/') $dir = $dir.'/';
	$seconds = $hours*60*60;
	$files = scandir($dir);

	foreach ($files as $num => $fname)
	{
		if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
		{
			$mod_time = filemtime("{$dir}{$fname}");
			if (unlink("{$dir}{$fname}")) $del = $del + 1;
		}
		if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
			 && substr($fname,0,1) != "." && $fname !== ".." )
		{
			$del = $del + admin_acache::remove_old($hours, $dir.$fname."/");
		}
	}
	return $del;
}
