<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;

extract($_SESSION['sql']);
$dbhost = $host;
extract($_SESSION['sett']);
$adminpw = crypt($adminpw);

$config = preg_replace("/\{([^\}]+)\}/e", "\\1", join('', file('config.tpl')));
$fp = fopen('../kbconfig.php', 'w');
fwrite($fp, trim($config));
fclose($fp);
chmod('../kbconfig.php', 0440);
?>
<p>I wrote the config to ../kbconfig.php and chmodded it to 440.<br/>
</p>
<?php
echo'<div class="config">';
highlight_string($config);
echo'</div>';
?>
<?php
if (!file_exists('../kbconfig.php'))
{
    ?>
<p>Something went wrong. The file ../kbconfig.php is missing!</p>
<?php
    return;
}
// config is there, use it to create all config vars which arent there
// to prevent that ppl with running installs get new values
require_once('../kbconfig.php');

$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

function insertConfig($key, $value)
{
    global $db;

    $result = mysql_query('select * from kb3_config where cfg_site=\''.KB_SITE.'\' and cfg_key=\''.$key.'\'');
    if (!$row = mysql_fetch_row($result))
    {
        $sql = "insert into kb3_config values ('".KB_SITE."','".$key."','".$value."')";
        mysql_query($sql);
    }
}

// move stuff from the config to the database
insertConfig('cfg_allianceid', $aid);
insertConfig('cfg_corpid', $cid);

insertConfig('cfg_common', $common);
insertConfig('cfg_img', $img);
insertConfig('cfg_kbhost', $host);
insertConfig('cfg_style', $style);
insertConfig('cfg_kbtitle', $title);

insertConfig('cfg_profile', 0);
insertConfig('cfg_qcache', 1);
insertConfig('cfg_sqlhalt', 0);

insertConfig('cfg_mainsite', '');

$confs = file('config.data');
foreach ($confs as $line)
{
	$valuepair = explode(chr(9), trim($line));
	if(!isset($valuepair[0])) continue;
	if(!isset($valuepair[1])) $valuepair[1] = '';
    insertConfig($valuepair[0], $valuepair[1]);
}
?>
<br/><br/><font size=+1>Found the config file in the right place. Please continue...</font><br/>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>