<?php
require_once('common/includes/class.contract.php');
require_once('common/includes/class.http.php');
require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Post Permissions');

if ($_REQUEST['searchphrase'] != "" && strlen($_REQUEST['searchphrase']) >= 3)
{
    switch ($_REQUEST['searchtype'])
    {
        case "pilot":
            $sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                    from kb3_pilots plt, kb3_corps crp
                    where lower( plt.plt_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                    and plt.plt_crp_id = crp.crp_id
                    order by plt.plt_name";
            break;
        case "corp":
            $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                    from kb3_corps crp, kb3_alliances ali
                    where lower( crp.crp_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                    and crp.crp_all_id = ali.all_id
                    order by crp.crp_name";
            break;
        case "alliance":
            $sql = "select ali.all_id, ali.all_name
                    from kb3_alliances ali
                    where lower( ali.all_name ) like lower( '%".slashfix($_REQUEST['searchphrase'])."%' )
                    order by ali.all_name";
            break;
    }

    $qry = new DBQuery();
    if (!$qry->execute($sql))
    {
        die($qry->getErrorMsg());
    }

    while ($row = $qry->getRow())
    {
        switch ($_REQUEST['searchtype'])
        {
            case 'pilot':
                $link = '?a=admin_postperm&add=p'.$row['plt_id'];
                $descr = 'Pilot '.$row['plt_name'].' from '.$row['crp_name'];
                break;
            case 'corp':
                $link = "?a=admin_postperm&add=c".$row['crp_id'];
                $descr = 'Corp '.$row['crp_name'].', member of '.$row['all_name'];
                break;
            case 'alliance':
                $link = '?a=admin_postperm&add=a'.$row['all_id'];
                $descr = 'Alliance '.$row['all_name'];
                break;
        }
        $results[] = array('descr' => $descr, 'link' => $link);
    }
    $smarty->assign_by_ref('results', $results);
    $smarty->assign('search', true);
}

if (isset($_REQUEST['authall']))
{
    if ($_REQUEST['authall'])
    {
        config::set('post_permission', 'all');
    }
    else
    {
        config::set('post_permission', '');
    }
}
if (!$string = config::get('post_permission'))
{
    $string = '';
}
if ($string != 'all')
{
    $tmp = explode(',', $string);
    $permissions = array('a' => array(), 'c' => array(), 'p' => array());
    foreach ($tmp as $item)
    {
        if (!$item)
        {
            continue;
        }
        $typ = substr($item, 0, 1);
        $id = substr($item, 1);
        $permissions[$typ][$id] = $id;
    }

    if ($_REQUEST['add'])
    {
        $typ = substr($_REQUEST['add'], 0, 1);
        $id = intval(substr($_REQUEST['add'], 1));
        $permissions[$typ][$id] = $id;
        $configstr = '';
        foreach ($permissions as $typ => $id_array)
        {
            foreach ($id_array as $id)
            {
                $conf[] = $typ.$id;
            }
        }
        config::set('post_permission', implode(',', $conf));
    }

    if ($_REQUEST['del'])
    {
        $typ = substr($_REQUEST['del'], 0, 1);
        $id = intval(substr($_REQUEST['del'], 1));
        unset($permissions[$typ][$id]);
        $conf = array();
        foreach ($permissions as $typ => $id_array)
        {
            foreach ($id_array as $id)
            {
                $conf[] = $typ.$id;
            }
        }
        config::set('post_permission', implode(',', $conf));
    }

    asort($permissions['a']);
    asort($permissions['c']);
    asort($permissions['p']);

    $permt = array();
    foreach ($permissions as $typ => $ids)
    {
        foreach ($ids as $id)
        {
            if ($typ == 'a')
            {
                $alliance = new Alliance($id);
                $text = $alliance->getName();
                $link = '?a=admin_postperm&del='.$typ.$id;
                $permt[$typ][] = array('text' => $text, 'link' => $link);
            }
            if ($typ == 'p')
            {
                $pilot = new Pilot($id);
                $text = $pilot->getName();
                $link = '?a=admin_postperm&del='.$typ.$id;
                $permt[$typ][] = array('text' => $text, 'link' => $link);
            }
            if ($typ == 'c')
            {
                $corp = new Corporation($id);
                $text = $corp->getName();
                $link = '?a=admin_postperm&del='.$typ.$id;
                $permt[$typ][] = array('text' => $text, 'link' => $link);
            }
        }
    }
    $perm = array();
    if ($permt['a'])
    {
        $perm[] = array('name' => 'Alliances', 'list' => $permt['a']);
    }
    if ($permt['p'])
    {
        $perm[] = array('name' => 'Pilots', 'list' => $permt['p']);
    }
    if ($permt['c'])
    {
        $perm[] = array('name' => 'Corporations', 'list' => $permt['c']);
    }

    $smarty->assign_by_ref('permissions', $perm);
}
$html = $smarty->fetch(get_tpl('admin_postperm'));

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>