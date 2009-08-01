<?php
// Update ccp tables using install files.

die("You didn't expect this to be finished, did you?");

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
include('../common/includes/php_compat.php');
include('../common/includes/class.xml.php');
require('../config.php');
include('../common/includes/class.db.php');

echo 'Reading packages...';
$xml = new sxml();
$kb = $xml->parse(file_get_contents('../packages/database/contents.xml'));

$struct = $opt = $data = array();
$tables = array();
foreach($kb['kb3']['table'] as $idx => $tbl)
{
    $table = $tbl['name'];
    $files = array();
    $dir = opendir('../packages/database/'.$table);

    $xml = new sxml();
    $st = $xml->parse(file_get_contents('../packages/database/'.$table.'/table.xml'));
    $struct[$table] = $st['kb3']['structure'];
    $kb['kb3']['table'][$idx]['rows'] = $st['kb3']['rows'];
    $structc++;

    while ($file = readdir($dir))
    {
        if ($file == '.' || $file == '..' || $file == '.svn')
        {
            continue;
        }
        if (strpos($file, '_opt_'))
        {
            $dcnt++;
            $optcnt++;
            $opt[$table][] = '../packages/database/'.$table.'/'.$file;
            asort($opt[$table]);
        }
        elseif (!strpos($file, 'xml'))
        {
            $dcnt++;
            $datacnt++;
            $data[$table][] = '../packages/database/'.$table.'/'.$file;
            asort($data[$table]);
        }
    }
}

$db = mysql_connect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
mysql_select_db($_SESSION['sql']['db']);

if ($_REQUEST['action'] == 'drop')
{
    echo 'Running database checks... ';
    include('install_step40_tblchk.php');
    echo 'done<br/>';

    echo 'Dropping some tables...<br/>';
    $dropdata = explode(',', 'kb3_ships,kb3_ship_classes,kb3_item_types,kb3_regions,kb3_systems,kb3_system_jumps,kb3_item_locations,kb3_constellations,kb3_races');
    foreach ($dropdata as $table)
    {
        echo $table.' ';
        mysql_query("drop table ".$table);
    }
    echo 'done<br/>';
}

$result = mysql_query('show tables');
while ($row = mysql_fetch_row($result))
{
    $table = $row[0];
    unset($struct[$table]);
}

if ($_REQUEST['sub'] == 'struct')
{
    foreach ($struct as $table => $structure)
    {
        echo 'Creating table '.$table.'...';
        $query = $struct[$table];
        #echo $query;
        $id = mysql_query($query);
        if ($id)
        {
            echo 'done<br/>';
        }
        else
        {
            echo 'error: '.mysql_error().'<br/>';
        }
        unset($struct[$table]);
    }
}
if ($_REQUEST['do'] == 'reset')
{
    unset($_SESSION['sqlinsert']);
    unset($_SESSION['doopt']);
}

if ($_REQUEST['sub'] == 'data')
{
    if (!isset($_SESSION['sqlinsert']))
    {
        $_SESSION['sqlinsert'] = 1;
        if (isset($_POST['opt']))
        {
            $_SESSION['useopt'] = array();
            foreach ($_POST['opt'] as $table => $value)
            {
                $_SESSION['useopt'][] = $table;
            }
        }
    }

    $i = 0;
    $did = false;
    $errors = false;
    if (!isset($_SESSION['doopt']))
    {
        foreach ($data as $table => $files)
        {
            foreach ($files as $file)
            {
                $i++;
                if ($_SESSION['sqlinsert'] > $i)
                {
                    continue;
                }
                echo 'Inserting data ('.$i.'/'.$datacnt.') into '.$table.'<br/> using file '.$file.'...<br/>';

                $error = '';
                $fp = gzopen($file, 'r');
                $lines = 0;
                $errors = 0;
                while ($query = gzgets($fp, 4000))
                {
                    $text .= $query;
                    if (substr($text, -3, 1) != ';')
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
                        $query_count++;
                        $id = mysql_query($query);
                        #echo $query;
                        if (!$id)
                        {
                            $error .= 'error: '.mysql_error().'<br/>';
                            $errors++;
                        }
                    }
                }
                echo '<br/>File '.$file.' had '.$lines.' lines with '.$query_count.' querys.<br/> '.$errors.' Querys failed.<br/>';
                if (!$error)
                {
                    echo '<br/>Finished importing of this file.<br/>';
                    echo '<meta http-equiv="refresh" content="1; URL=?step=40&sub=data" />';
                    echo 'Automatic reload in 1s for next chunk. <a href="?step=40&sub=data">Manual Link</a><br/>';
                }
                else
                {
                    echo $error;
                    echo '<meta http-equiv="refresh" content="20; URL=?step=40&sub=data" />';
                    echo 'Automatic reload in 20s for next chunk because an error occured. <a href="?step=4&sub=data">Manual Link</a><br/>';
                }
                $_SESSION['sqlinsert']++;

                $did = true;
                break 2;
            }
        }
    }

    if (isset($_SESSION['useopt']) && !$did)
    {
        $i = 0;
        if (!isset($_SESSION['doopt']))
        {
            $_SESSION['doopt'] = true;
            $_SESSION['sqlinsert'] = 1;
        }
        foreach ($opt as $table => $files)
        {
            if (!in_array($table, $_SESSION['useopt']))
            {
                continue;
            }
            foreach ($files as $file)
            {
                $optsel++;
            }
        }
        foreach ($opt as $table => $files)
        {
            if (!in_array($table, $_SESSION['useopt']))
            {
                continue;
            }
            foreach ($files as $file)
            {
                $i++;
                if ($_SESSION['sqlinsert'] > $i)
                {
                    continue;
                }
                echo '<br/>Inserting optional data ('.$i.'/'.$optsel.') into '.$table.'<br/> using file '.$file.'...';
                $fp = gzopen($file, 'r');
                while ($query = gzgets($fp, 4000))
                {
                    $text .= $query;
                    if (substr($text, -3, 1) != ';')
                    {
                        continue;
                    }
                    $query = $text;
                    $text = '';
                    $query = trim($query);
                    if ($query)
                    {
                        if (substr($query, -1, 1) == ';')
                        {
                            $query = substr($query, 0, -1);
                        }
                        $id = mysql_query($query);
                        #echo $query;
                    }
                }
                if ($id)
                {
                    echo 'done<br/>';
                }
                else
                {
                    echo 'error: '.mysql_error().'<br/>';
                }
                $_SESSION['sqlinsert']++;
                echo '<meta http-equiv="refresh" content="1; URL=?step=40&sub=data" />';
                echo 'Automatic reload in 1s for next chunk. <a href="?step=40&sub=data">Manual Reload</a><br/>';
                $did = true;
                break 2;
            }
        }
    }
    if (!$did)
    {
        $stoppage = false;
        echo 'All tables imported. Checking tables for correct data...<br/>';
        foreach ($kb['kb3']['table'] as $line)
        {
            $table = $line['name'];
            $count = $line['rows'];
            echo 'Checking table '.$table.': ';
            $result = mysql_query('SELECT count(*) as cnt FROM '.$table);
            $test = mysql_fetch_array($result);
            $failed = 0;
            if ($test['cnt'] != $count && $count != 0)
            {
                echo $test['cnt'].'/'.$count.' - <font color="red"><b>FAILED</b></font>';
                $failed++;
            }
            else
            {
                echo $test['cnt'].'/'.$count.' - <font color="green"><b>PASSED</b></font>';
            }
            echo '<br/>';
        }
        if ($stoppage)
        {
            echo 'There has been an error with one of the tables, please <a href="?step=40&do=reset">Reset</a> and try again.<br/>';
        }
        else
        {
            echo '<br/>All tables passed.<br/>';
            echo 'You can now create or search your corporation/alliance: <a href="?step=41">Next Step</a><br/>';
        }
    }
    echo '<br/>Use <a href="?step=40&sub=datasel&do=reset">Reset</a> to step back to the sql-opt select.<br/>';
}