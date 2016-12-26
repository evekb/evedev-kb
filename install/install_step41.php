<?php
/**
 * @package EDK
 */

if(!$installrunning) {header('Location: index.php');die();}
$stoppage = false;

include_once('./kbconfig.php');
$db = new mysqli($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass'], $_SESSION['sql']['db']);
$db->select_db(DB_NAME);

$site = KB_SITE;
$adminpw = ADMIN_PASSWORD;
$dbhost = DB_HOST;
$db = DB_NAME;
$user = DB_USER;
$pass = DB_PASS;

$config = preg_replace("/\{([^\}]+)\}/e", "\\1", join('', file('./templates/config.tpl')));
$fp = fopen('../kbconfig.php', 'w');
fwrite($fp, trim($config));
fclose($fp);
chmod('../kbconfig.php', 0440);

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

$query = "SELECT itd_kll_id, itm_id, itm_name
FROM kb3_items_destroyed
LEFT JOIN kb3_items ON itd_itm_id = itm_id
LEFT JOIN kb3_invtypes ON itm_name = typeName
WHERE invtypes.typeID IS NULL";
$result = $db->query($query);
$smarty->assign('sql_error');

$notice = '';
while ($row = $result->fetch_assoc())
{
    $notice .= 'Killmail id '.$row['itd_kll_id'].' contains an item named "'.$row['itm_name'].'" (id '.$row['itm_id'].') that will be orphaned.<br/>';
}
$smarty->assign('notice', $notice);
$smarty->assign('stoppage', $stoppage);
$smarty->display('install_step41.tpl');

function insertConfig($key, $value)
{
    $result = $db->query('SELECT * FROM kb3_config WHERE cfg_site=\''.KB_SITE.'\' AND cfg_key=\''.$key.'\'');
    if (!$row = $result->fetch_assoc())
    {
        $sql = "INSERT INTO kb3_config VALUES ('".KB_SITE."','".$key."','".$value."')";
        $db->query($sql);
    }
    $result = $db->query('SELECT * FROM kb3_config WHERE cfg_site=\'\' AND cfg_key=\''.$key.'\'');
    if (!$row = $result->fetch_assoc())
    {
        $sql = "INSERT INTO kb3_config VALUES ('','".$key."','".$value."')";
        $db->query($sql);
    }
}