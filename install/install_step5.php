<?php
if(!$installrunning) {header('Location: index.php');die();}
$stoppage = true;
$db = mysql_pconnect($_SESSION['sql']['host'], $_SESSION['sql']['user'], $_SESSION['sql']['pass']);
mysql_select_db($_SESSION['sql']['db']);

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'create')
{
    if (!empty($_REQUEST['a']))
    {
        $result = mysql_query('select all_id from kb3_alliances where all_name like \'%'.addslashes(stripslashes($_REQUEST['a'])).'%\'');
        if ($row = @mysql_fetch_row($result))
        {
            $id = $row[0];
        }
        else
        {
            $query = 'insert into kb3_alliances (all_name) VALUES (\''.addslashes(stripslashes($_REQUEST['a'])).'\')';
            mysql_query($query);
            $id = mysql_insert_id();
        }
        $_REQUEST['a'] = $id;
    }
    else
    {
        $result = mysql_query('select all_id from kb3_alliances where all_name like \'%Unknown%\'');
        if ($row = @mysql_fetch_row($result))
        {
            $id = $row[0];
        }
        else
        {
            $query = 'insert into kb3_alliances (all_name) VALUES (\'Unknown\')';
            mysql_query($query);
            $id = mysql_insert_id();
        }
        $query = 'select crp_id from kb3_corps where crp_name like \'%'.addslashes(stripslashes($_REQUEST['c'])).'%\'';
        $result = mysql_query($query);
        if ($row = @mysql_fetch_row($result))
        {
            $id = $row[0];
        }
        else
        {
            $query = 'insert into kb3_corps (crp_name, crp_all_id) VALUES (\''.addslashes(stripslashes($_REQUEST['c'])).'\','.$id.')';
            mysql_query($query);
            $id = mysql_insert_id();
        }
        $_REQUEST['c'] = $id;
    }
    $_SESSION['sett']['aid'] = $_REQUEST['a'];
    $_SESSION['sett']['cid'] = $_REQUEST['c'];
    $stoppage = false;
}
if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'select')
{
    $_SESSION['sett']['aid'] = $_REQUEST['a'];
    $_SESSION['sett']['cid'] = $_REQUEST['c'];
    $stoppage = false;
}
?>
<p>You can now search for your corporation/alliance.<br/><br/>
If you haven't imported that data or your corporation/alliance is missing I will offer to create it for you.<br/>
<b>Note:</b> Make sure you spell your corporation/alliance <b>correctly</b> (including capitalisation), else you cannot post any mails!<br/>
</p>
<?php
if ($stoppage)
{
?>
<form id="options" name="options" method="post" action="?step=5">
<input type="hidden" name="step" value="5">
<div class="block-header2">Search</div>
<table class="kb-subtable">
<tr><td width="120">
<select id="searchtype" name="searchtype"><option value="corp">Corporation</option><option value="alliance">Alliance</option></select>
</td><td><input id="searchphrase" name="searchphrase" type="text" size="30"/>
</td><td><input type="submit" name="submit" value="Search"/></td></tr>
</table>
<?php
if (!empty($_REQUEST['searchphrase']) && strlen($_REQUEST['searchphrase']) >= 3)
{
    switch ($_REQUEST['searchtype'])
    {
        case "corp":
            $query = "select crp.crp_id, crp.crp_name, ali.all_name
                    from kb3_corps crp, kb3_alliances ali
                    where lower( crp.crp_name ) like lower( '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%' )
                    and crp.crp_all_id = ali.all_id
                    order by crp.crp_name";
            break;
        case "alliance":
            $query = "select ali.all_id, ali.all_name
                    from kb3_alliances ali
                    where lower( ali.all_name ) like lower( '%".addslashes(stripslashes($_REQUEST['searchphrase']))."%' )
                    order by ali.all_name";
            break;
    }

    $result = mysql_query($query);

    $unsharp = true;
    $results = array();
    while ($row = mysql_fetch_assoc($result))
    {
        switch ($_REQUEST['searchtype'])
        {
            case 'corp':
                $link = "?step=5&do=select&a=0&c=".$row['crp_id'].'">Select';
                $descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
                if ($row['crp_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
                {
                    $unsharp = false;
                }
                break;
            case 'alliance':
                $link = '?step=5&do=select&c=0&a='.$row['all_id'].'">Select';
                $descr = 'Alliance '.$row['all_name'];
                if ($row['all_name'] == addslashes(stripslashes($_REQUEST['searchphrase'])))
                {
                    $unsharp = false;
                }
                break;
        }
        $results[] = array('descr' => $descr, 'link' => $link);
    }
    if (!count($results) || $unsharp)
    {
        if ($_REQUEST['searchtype'] == 'corp')
        {
            $link = '?step=5&do=create&c='.stripslashes($_REQUEST['searchphrase']).'&a=0">Create';
            $descr = 'Corporation: '.stripslashes($_REQUEST['searchphrase']);
        }
        else
        {
            $link = '?step=5&do=create&a='.stripslashes($_REQUEST['searchphrase']).'&c=0">Create';
            $descr = 'Alliance: '.stripslashes($_REQUEST['searchphrase']);
        }
        $results[] = array('descr' => $descr, 'link' => $link);
    }
    ?>
<br/>
<table class="kb-table">
<tr class="kb-table-header">
<td colspan="2">Results</td></tr>
<?php
foreach ($results as $result)
{
?>
<tr><td><?php echo $result['descr']; ?></td><td><a href="<?php echo $result['link']; ?></a></td></tr>
<?php
}
?>
</table>
<?php
}
}
?>

<?php if ($stoppage)
{
    return;
}
if ($_SESSION['sett']['aid'] == 0 && $_SESSION['sett']['cid'] == 0)
{
    echo '<b>Warning:</b> It seems like I received no alliance or corp id. You can continue but you might have to edit it into the config yourself.<br/>';
}
?>
<p><a href="?step=<?php echo ($_SESSION['state']+1); ?>">Next Step</a></p>