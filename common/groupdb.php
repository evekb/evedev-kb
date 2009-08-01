<?php
if (!$id = intval($_GET['id']))
{
    $page = new Page('Error');
    $page->setContent('No valid ID specified.');
    $page->generate();
    exit;
}

$sql = 'SELECT * FROM kb3_item_types d
        WHERE d.itt_id = '.$id;
$qry = new DBQuery();
$qry->execute($sql);
$row = $qry->getRow();

$page = new Page('Item Database - '.$row['itt_name'].' Index');

$sql = 'SELECT * FROM kb3_invtypes d
        WHERE d.groupID = '.$id.'
	ORDER BY d.typeName ASC';
    $qry = new DBQuery();
    $qry->execute($sql);
    $html .= "<table class=kb-table cellspacing=1>";
    $html .= "<tr class=kb-table-header><td width=400>Item Name</td></tr>";
    while ($row = $qry->getRow())
    {
        $html .= '<tr class=kb-table-row-odd><td><a href="?a=invtype&id='.$row['typeID'].'">'.$row['typeName'].'</a></td></tr>';
    }
    $html .= "</table>";

$page->setContent($html);
$page->generate();
?>
