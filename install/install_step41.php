<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = false;

include_once('../config.php');
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$site = KB_SITE;
$adminpw = ADMIN_PASSWORD;
$dbhost = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

$config = preg_replace("/\{([^\}]+)\}/e", "\\1", join('', file('config.tpl')));
$fp = fopen('../kbconfig.php', 'w');
fwrite($fp, trim($config));
fclose($fp);
chmod('../kbconfig.php', 0440);

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
insertConfig('cfg_allianceid', ALLIANCE_ID);
insertConfig('cfg_corpid', CORP_ID);

insertConfig('cfg_common', COMMON_URL);
insertConfig('cfg_img', IMG_URL);
insertConfig('cfg_kbhost', KB_HOST);
insertConfig('cfg_style', STYLE_URL);
insertConfig('cfg_kbtitle', KB_TITLE);

insertConfig('cfg_profile', KB_PROFILE);
insertConfig('cfg_qcache', DB_USE_QCACHE);
insertConfig('cfg_sqlhalt', DB_HALTONERROR);

insertConfig('cfg_mainsite', MAIN_SITE);

echo 'Upgraded your Config and chmodded ../kbconfig.php 440. If there was a warning for chmod please change the permission manually.<br/>';

echo 'The next query checks for abandoned items, save this list for your reference.<br/>';

$query = "select itd_kll_id, itm_id, itm_name
from kb3_items_destroyed
left join kb3_items on itd_itm_id=itm_id
left join kb3_invtypes on itm_name=typeName
where invtypes.typeID is null";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_array($result))
{
    echo 'Killmail id '.$row['itd_kll_id'].' contains item named "'.$row['itm_name'].'" (id '.$row['itm_id'].') that will get orphaned.<br/>';
}
?>
<p>Warning!</p><br/>
Once you click for the next step the following querys will be run:<br/>
<pre>
update
kb3_items_destroyed
left join kb3_items on itd_itm_id=itm_id
left join kb3_invtypes on itm_name=typeName
set itd_itm_id=typeID

update
kb3_inv_detail
left join kb3_items on ind_wep_id=itm_id
left join kb3_invtypes on itm_name=typeName
set ind_wep_id=typeID

insert into kb3_item_price
select typeID, itm_value as price
from kb3_items
left join kb3_invtypes on itm_name=typeName
where typeID is not null and itm_value != 0 and itm_value!=basePrice
</pre>

Make sure you backed up those tables!<br/>
<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>