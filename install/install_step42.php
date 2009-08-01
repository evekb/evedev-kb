<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = false;

include_once('../kbconfig.php');
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME);

$query = "update kb3_items_destroyed
            inner join kb3_items on itd_itm_id=itm_id
            inner join kb3_invtypes on itm_name=typeName
            set itd_itm_id=typeID";
mysql_query($query);
echo mysql_error();

$query = "update kb3_inv_detail
            inner join kb3_items on ind_wep_id=itm_id
            inner join kb3_invtypes on itm_name=typeName
            set ind_wep_id=typeID";
mysql_query($query);
echo mysql_error();

$query = "insert into kb3_item_price
            select typeID, itm_value as price
            from kb3_items
            left join kb3_invtypes on itm_name=typeName
            where typeID is not null and itm_value != 0 and itm_value!=basePrice";
mysql_query($query);
echo mysql_error();

?>
<p>Queries executed, please proceed...</p>
<?php if ($stoppage)
{
    return;
}?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>