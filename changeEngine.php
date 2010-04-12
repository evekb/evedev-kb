<?php
if(!isset($_GET['type']))
{
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
<head><title>Change DB engine</title></head>
<body>';
	echo "<a href='changeEngine.php?type=InnoDB'>Switch to InnoDB</a> <br />";
	echo "<a href='changeEngine.php?type=MyISAM'>Switch to MyISAM</a> <br />";
	echo '</body></html>';
	die;
}
@define('DB_USE_QCACHE', 0);
require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');

$qry = new DBQuery(true);
$qry2 = new DBQuery(true);
$qry->execute("SHOW TABLE STATUS");
while($row = $qry->getRow())
{
	if(strpos($row['Name'], 'kb3_') === false) continue;
	if(strtolower($_GET['type']) == strtolower($row['Engine'])) continue;

	if(isset($_GET['type']) && strtolower($_GET['type']) == "myisam")
	{
		$qry2->execute("ALTER TABLE `".$row['Name']."`  ENGINE = MyISAM");
		echo "Altered table ".$row['Name']." to MyISAM<br/>\n";
	}
	else
	{
		$qry2->execute("ALTER TABLE `".$row['Name']."`  ENGINE = InnoDB");
	echo "Altered table ".$row['Name']." to InnoDB<br/>\n";
	}
	$qry2->execute("ANALYZE TABLE ".$row['Name']);
}
