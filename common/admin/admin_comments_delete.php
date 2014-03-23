<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

if(isset($_GET['c_id'])) $_GET['c_id'] = intval($_GET['c_id']);
else $_GET['c_id'] = 0;

$page = new Page("Administration - Deletion of Comment ID \"".$_GET['c_id']."\"");
$page->setAdmin();


if ($_POST['confirm'])
{
    $qry = DBFactory::getDBQuery();
    $qry->execute("DELETE FROM kb3_comments WHERE id='".$_GET['c_id']."'");
	$smarty->assign('deleted', true);
	$smarty->assign('id', $_GET['c_id']);
}
else
{
    $qry = DBFactory::getDBQuery();;
    $qry->execute("SELECT id, name, comment FROM kb3_comments WHERE `id`='".$_GET['c_id']."'");
    if ($qry->recordCount() == 0)
    {
        // no commment
		$smarty->assign('id', false);
    }
    else
    {
        if($data = $qry->getRow())
        {
			$smarty->assign('id', $data['id']);
			$smarty->assign('name', $data['name']);
			$smarty->assign('comment', $data['comment']);
        }
    }
}
$page->setContent($smarty->fetch(get_tpl('admin_comment_delete')));
$page->generate();
