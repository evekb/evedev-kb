<?php
/**
 * @package EDK
 */


$index= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

$page = new Page("Item Database - '".$id."' Group Index");

$sql = 'SELECT * FROM kb3_item_types d
        WHERE d.itt_name LIKE CONVERT( _utf8 "'.$id.'%" USING latin1 )
	COLLATE latin1_swedish_ci
	ORDER BY d.itt_name ASC';
$html .= "<table class=kb-table-header cellspacing=0><tr><td width=400 colspan=27 align=center>INDEX</td></tr><tr class=kb-table-row-odd>";
foreach ($index as $il) {
	$html .= '<td><a href="'.edkuri::build(array(array('a', 'itemdb', true), array('id', $il, true))).'">'.$il.'</a></td>';
}	 
$html .= "</tr></table><br><br>";
$qry = DBFactory::getDBQuery();
$qry->execute($sql);
$html .= "<table class=kb-table cellspacing=1>";
$html .= "<tr class=kb-table-header><td width=400>Group Name</td></tr>";
while ($row = $qry->getRow()) {
	$html .= '<tr class=kb-table-row-odd><td><a href="'.edkuri::build(array(array('a', 'groupdb', true), array('id', $row['itt_id'], true))).'">'.$row['itt_name'].'</a></td></tr>';
}
$html .= "</table>";

$page->setContent($html);
$page->generate();
?>
