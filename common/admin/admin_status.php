<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Troubleshooting');
$page->setAdmin();
$qry = DBFactory::getDBQuery(true);;
$qry->execute("SELECT cfg_key, cfg_value FROM kb3_config WHERE cfg_site = '".
	KB_SITE."' AND cfg_key NOT LIKE 'API_%' AND cfg_key NOT LIKE '%password%'");
$html = "<h2>Config Settings</h2>";
$html .= "<table>";
while($row = $qry->getRow())
{
	$html .= "<tr><td>".implode($row, '</td><td>')."</td></tr>";
}
$html .= "</table>";

$qry->execute('SHOW TABLES');
$qry2 = DBFactory::getDBQuery(true);;
//$html .= '<form><textarea class="indexing" name="indexing" cols="60" rows="30" readonly="readonly">';
$html .= "<h2>Index Settings</h2>";
	$html .= "<table>";
while($row = $qry->getRow())
{
	$qry2->execute('SHOW INDEXES FROM '.implode($row));
	while($row2 = $qry2->getRow())
	{
		$html .= "<tr><td>".implode($row2, '</td><td>')."</td></tr>";
	}
}
	$html .= "</table>";

$page->setContent($html);
$page->generate();
?>
