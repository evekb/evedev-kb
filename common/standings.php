<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');

$page = new Page();
$page->setTitle('Standings');

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
    $id = $row['sta_to'];

    if ($row['sta_value'] > 5)
    {
        $icon = 'high';
    }
    elseif ($row['sta_value'] > 0)
    {
        $icon = 'good';
    }
    elseif ($row['sta_value'] > -5)
    {
        $icon = 'bad';
    }
    else
    {
        $icon = 'horrible';
    }

    if ($typ == 'a')
    {
        $alliance = new Alliance($row['sta_to']);
        $text = $alliance->getName();
        $pid = $alliance->getUnique();
        $link = '?a=admin_standings&del='.$typ.$row['sta_to'];
        $permt[$typ][] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'],
                               'id' => $id, 'pid' => $pid, 'typ' => $row['sta_to'], 'icon' => $icon);
    }
    if ($typ == 'c')
    {
        $corp = new Corporation($row['sta_to']);
        $text = $corp->getName();
        $link = '?a=admin_standings&del='.$typ.$row['sta_to'];
        $permt[$typ][] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'],
                               'id' => $id, 'typ' => $typ, 'icon' => $icon);
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

$page->setContent($smarty->fetch(get_tpl('standings')));
$page->generate();
?>