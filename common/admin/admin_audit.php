<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page('Administration - Audit log');
$page->setAdmin();

$sql = 'SELECT log_kll_id, log_ip_address, log_timestamp, kll_timestamp, plt_name
			FROM kb3_log log, kb3_kills kll, kb3_pilots plt
			WHERE log.log_kll_id = kll.kll_id
			AND plt.plt_id = kll_fb_plt_id
			ORDER BY log_timestamp DESC limit 250';

$qry = DBFactory::getDBQuery();;
$qry->execute($sql) or die($qry->getErrorMsg());

$html .= '<table class="kb-table">';
$html .= "<tr class='kb-table-header'><td align='center' width='60'>ID</td><td width='150'>Killmail date</td><td width='150'>Posted</td><td width='120'>IP Address</td><td width='150'>Final Blow</td></tr>";
$odd = false;
while ($row = $qry->getRow())
{
    if ($odd)
    {
        $class = "kb-table-row-even";
        $odd = false;
    }
    else
    {
        $class = "kb-table-row-odd";
        $odd = true;
    }
    $html .= "<tr class='" . $class . "' onmouseover=\"this.className='kb-table-row-hover';\" onmouseout=\"this.className='" . $class . "';\" onclick=\"window.location.href='".KB_HOST."/?a=kill_detail&amp;kll_id=" . $row['log_kll_id'] . "';\">";
    $html .= "<td align='center'><b>" . $row['log_kll_id'] . "</b></td>";
    $html .= "<td>" . $row['kll_timestamp'] . "</td>";
    $html .= "<td>" . $row['log_timestamp'] . "</td>";
    $html .= "<td>" . $row['log_ip_address'] . "</td>";
    $html .= "<td>" . $row['plt_name'] . "</td>";
    $html .= "</tr>";
}
$html .= "</table>";

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();

?>
