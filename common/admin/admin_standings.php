<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Standings');

if ($_REQUEST['searchphrase'] != "" && strlen($_REQUEST['searchphrase']) >= 3)
{
    switch ($_REQUEST['searchtype'])
    {
        case 'corp':
            $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                    from kb3_corps crp, kb3_alliances ali
                    where lower( crp.crp_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                    and crp.crp_all_id = ali.all_id
                    order by crp.crp_name";
            break;
        case 'alliance':
            $sql = "select ali.all_id, ali.all_name
                    from kb3_alliances ali
                    where lower( ali.all_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                    order by ali.all_name";
            break;
    }

    $qry = new DBQuery();
    $qry->execute($sql);

    while ($row = $qry->getRow())
    {
        switch ($_REQUEST['searchtype'])
        {
            case 'corp':
                $typ = 'Corporation';
                $link = 'c'.$row['crp_id'];
                $descr = $row['crp_name'].', member of '.$row['all_name'];
                break;
            case 'alliance':
                $typ = 'Alliance';
                $link = 'a'.$row['all_id'];
                $descr = $row['all_name'];
                break;
        }
        $results[] = array('descr' => $descr, 'link' => $link, 'typ' => $typ);
    }
    $smarty->assign_by_ref('results', $results);
    $smarty->assign('search', true);
}
if ($val = $_REQUEST['standing'])
{
    $fields = array();
    if (CORP_ID)
    {
        $fromtyp = 'c';
        $fields[] = CORP_ID;
    }
    else
    {
        $fromtyp = 'a';
        $fields[] = ALLIANCE_ID;
    }
    $fields[] = intval(substr($_REQUEST['sta_id'], 1));
    $fields[] = $fromtyp;
    $fields[] = substr($_REQUEST['sta_id'], 0, 1);
    $fields[] = str_replace(',', '.', $val);
    $fields[] = slashfix($_REQUEST['comment']);

    $qry = new DBQuery();
    $qry->execute('INSERT INTO kb3_standings VALUES (\''.join("','", $fields).'\')');
}
if ($_REQUEST['del'])
{
    if (CORP_ID)
    {
        $fromtyp = 'c';
        $fromid = CORP_ID;
    }
    else
    {
        $fromtyp = 'a';
        $fromid = ALLIANCE_ID;
    }
    $totyp = substr($_REQUEST['del'], 0, 1);
    $toid = intval(substr($_REQUEST['del'], 1));

    $qry = new DBQuery();
    $qry->execute('DELETE FROM kb3_standings WHERE sta_from='.$fromid.' AND sta_from_type=\''.$fromtyp.'\'
                                             AND sta_to='.$toid.' AND sta_to_type=\''.$totyp.'\' LIMIT 1');
}

$qry = new DBQuery();
if (CORP_ID)
{
    $qry->execute('SELECT * FROM kb3_standings WHERE sta_from='.CORP_ID.' AND sta_from_type=\'c\' ORDER BY sta_value DESC');
}
else
{
    $qry->execute('SELECT * FROM kb3_standings WHERE sta_from='.ALLIANCE_ID.' AND sta_from_type=\'a\' ORDER BY sta_value DESC');
}

$permt = array();
while ($row = $qry->getRow())
{
    $typ = $row['sta_to_type'];
    $val = sprintf("%01.1f", $row['sta_value']);
    $id = $typ.$row['sta_to'];
    if ($typ == 'a')
    {
        $alliance = new Alliance($row['sta_to']);
        $text = $alliance->getName();
        $link = '?a=admin_standings&del='.$typ.$row['sta_to'];
        $permt[$typ][] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'], 'id' => $id);
    }
    if ($typ == 'c')
    {
        $corp = new Corporation($row['sta_to']);
        $text = $corp->getName();
        $link = '?a=admin_standings&del='.$typ.$row['sta_to'];
        $permt[$typ][] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'], 'id' => $id);
    }
}
$perm = array();
if ($permt['a'])
{
    $perm[] = array('name' => 'Alliances', 'list' => $permt['a']);
}
if ($permt['c'])
{
    $perm[] = array('name' => 'Corporations', 'list' => $permt['c']);
}

$smarty->assign_by_ref('standings', $perm);

$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_standings')));
$page->generate();
?>