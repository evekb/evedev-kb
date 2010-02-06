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
require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');

$qry = new DBQuery(true);
$qry2 = new DBQuery(true);
$qry->execute("SHOW TABLES");
while($row = $qry->getRow())
{
	foreach($row as $col)
	{
		if(strpos($col, 'kb3_') === false) continue;
		if(isset($_GET['type']) && strtolower($_GET['type']) == "myisam")
		{
			$qry2->execute("ALTER TABLE `".$col."`  ENGINE = MyISAM");
			echo "Altered table ".$col." to MyISAM<br/>\n";
		}
		else
		{
			$qry2->execute("ALTER TABLE `".$col."`  ENGINE = InnoDB");
		echo "Altered table ".$col." to InnoDB<br/>\n";
		}
		$qry2->execute("ANALYZE TABLE ".$col);
	}
}
