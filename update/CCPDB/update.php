<?php
/**
 * @package EDK
 */

/**
 * @package EDK
 */

require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');
require_once('common/includes/globals.php');
require_once('common/includes/class.xml.php');

// If we reset then we don't want to keep loading the reset
$url=preg_replace('/(\?|&)do=reset/','',$url);
$smarty->assign('url',$url);

$content ='Reading packages...';
$xml = new sxml();
$kb = $xml->parse(file_get_contents('update/CCPDB/contents.xml'));

$struct = $opt = $data = array();
$structc = $dcnt = $optcnt = $datacnt = 0;
$tables = array();
foreach($kb['kb3']['table'] as $idx => $tbl)
{
	$table = $tbl['name'];
	$files = array();
	$dir = opendir('packages/database/'.$table);

	$xml = new sxml();
	$st = $xml->parse(file_get_contents('packages/database/'.$table.'/table.xml'));
	$struct[$table] = $st['kb3']['structure'];
	$kb['kb3']['table'][$idx]['rows'] = $st['kb3']['rows'];
	$structc++;

	while ($file = readdir($dir))
	{
		if ($file == '.' || $file == '..' || $file == '.svn')
		{
			continue;
		}
		elseif (!strpos($file, 'xml') && !strpos($file, '_opt_'))
		{
			$dcnt++;
			$datacnt++;
			$data[$table][] = 'packages/database/'.$table.'/'.$file;
			asort($data[$table]);
		}
	}
}

if ((!empty($_GET['do']) && $_GET['do'] == 'reset')
	|| !isset($_SESSION['sqlinsert']))
	$_SESSION['sqlinsert'] = 1;

$i = 0;
$did = false;
$errors = false;
$qry = DBFactory::getDBQuery(true);
$lasttable='';
foreach ($data as $table => $files)
{
	foreach ($files as $fileIndex => $file)
	{
		$i++;
		if ($_SESSION['sqlinsert'] > $i)
		{
			$lasttable=$table;
			continue;
		}
      
		$error = '';
		$errors = 0;
		// we have a (new) table structure definition and this is the first chunk for this table -> re-create it first
		if($struct[$table] && $lasttable!=$table)  // Only drop/create the table before using 1st sql file
		{
			$first = false;
			// drop table
			//$content .= "<br/>Dropping table ".$table."<br/>";
			$result = $qry->execute("DROP TABLE IF EXISTS `".$table."`;");
			if (!$result)
			{
			$error .= 'error: '.$qry->getErrorMsg().'<br/>';
			$errors++;
			break;
			}

			// create table
			//$content .= "Creating table ".$table." .<br/>";
			$result = $qry->execute($struct[$table]);
			if (!$result)
			{
				$error .= 'error: '.$qry->getErrorMsg().'<br/>';
				$errors++;
				break;
			}
		}
		$content .= 'Inserting data ('.$i.'/'.$datacnt.') into '.$table.'<br/> using file '.$file.'...<br/>';

		$fp = gzopen($file, 'r');
		$lines = 0;
		$text = '';
		$query_count = 0;
		$qry->autocommit(false);
		while ($query = gzgets($fp, 65536))
		{
			$text .= $query;
			if (substr(trim($query), -1, 1) != ';')
			{
				continue;
			}
			$query = $text;
			$text = '';
			$lines++;
			if (trim($query))
			{
				$query = trim($query);
				if (substr($query, -1, 1) == ';')
				{
					$query = substr($query, 0, -1);
				}
				if (strpos($query, 'TRUNCATE') !== FALSE)
				{
					$qry->autocommit(true);
				}
				$query_count++;
				$id = $qry->execute($query);
				if (strpos($query, 'TRUNCATE') !== FALSE)
				{
					$qry->autocommit(false);
				}
				#echo $query;
				if (!$id)
				{
					$error .= 'error: '.$qry->getErrorMsg().'<br/>';
					$errors++;
				}
			}
		}
		$qry->autocommit(true);
		$content .=  '<br/>File '.$file.' had '.$lines.' lines with '.$query_count.' queries.<br/> '.$errors.' Queries failed.<br/>';
		if (!$error)
		{
			$content .=  '<br/>Finished importing of this file.<br/>';
			$smarty->assign('refresh',1);
//			$content .=  '<meta http-equiv="refresh" content="1; URL=?package=CCPDB" />';
			$content .=  'Automatic reload in 1s for next chunk. <a href="?package=CCPDB">Manual Link</a><br/>';
			$_SESSION['sqlinsert']++;
		}
		else
		{
			$content .=  $error;
			$smarty->assign('refresh',20);
//			$content .=  '<meta http-equiv="refresh" content="20; URL=?package=CCPDB" />';
			$content .=  'Automatic reload in 20s for next chunk because an error occurred. <a href="?package=CCPDB">Manual Link</a><br/>';
		}

		$did = true;
		break 2;
	}
}
	if (!$did)
	{
		$failed = 0;
		$content .=  'All tables imported. Checking tables for correct data...<br/>';
		foreach ($kb['kb3']['table'] as $line)
		{
			$table = $line['name'];
			$count = $line['rows'];
			$content .=  'Checking table '.$table.': ';
			$qry->execute('SELECT count(*) as cnt FROM '.$table);
			$test = $qry->getRow();
			if ($test['cnt'] != $count && $count != 0)
			{
				$content .=  $test['cnt'].'/'.$count.' - <font color="red"><b>FAILED</b></font>';
				$failed++;
			}
			else
			{
				$content .=  $test['cnt'].'/'.$count.' - <font color="green"><b>PASSED</b></font>';
			}
			$content .=  '<br/>';
		}
		if ($failed)
		{
			$content .=  'There has been an error with one of the tables, please <a href="?package=CCPDB&do=reset">Reset</a> and try again.<br/>';
		}
		else
		{
			// Update was successful, set the CCP_DB_VERSION coming with this KB version to config db
                        $qry = DBFactory::getDBQuery(true);
			$qry->execute("INSERT INTO kb3_config (cfg_site, cfg_key, cfg_value) SELECT cfg_site, 'CCPDbVersion', '".KB_CCP_DB_VERSION."' FROM kb3_config GROUP BY cfg_site ON DUPLICATE KEY UPDATE cfg_value = '".KB_CCP_DB_VERSION."'");
			$content .=  '<br/>All tables passed.<br/>';
			$content .=  '<br/><a href="'.config::get('cfg_kbhost').'/">Return to your board</a>';
		}
	}
				$smarty->assign('content', $content);
				$smarty->display('update.tpl');
