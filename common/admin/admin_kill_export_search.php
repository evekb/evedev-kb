<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


/*
* Export killmails, uses the searchroutine to find them.
* Currently only supports users,
* but will be made to support corps and alliances
*/

require_once('admin_menu.php');

$page = new Page('Administration - Export searcher');
$page->setAdmin();

$html .= "<form id=search action=\"?a=admin_kill_export_search\" method=post>";
$html .= "<table class=kb-subtable><tr>";
$html .= "<td>Type:</td><td>Text: (3 letters minimum)</td>";
$html .= "</tr><tr>";
$html .= "<td><input id=searchphrase name=searchphrase type=text size=30/></td>";
$html .= "<td><input type=submit name=submit value=Search></td>";
$html .= "</tr></table>";
$html .= "</form>";

if ($_POST['searchphrase'] != "" && strlen($_POST['searchphrase']) >= 3)
{
    $sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                  from kb3_pilots plt, kb3_corps crp
                 where lower( plt.plt_name ) like lower( '%".slashfix($_POST['searchphrase'])."%' )
                   and plt.plt_crp_id = crp.crp_id
                 order by plt.plt_name";
    $header = "<td>Pilot</td><td>Corporation</td>";
    $qry = DBFactory::getDBQuery();;
    if (!$qry->execute($sql))
        die ($qry->getErrorMsg());

    $html .= "<div class=block-header>Search results</div>";

    if ($qry->recordCount() > 0)
    {
        $html .= "<table class=kb-table width=450 cellspacing=1>";
        $html .= "<tr class=kb-table-header>".$header."</tr>";
    }
    else
        $html .= "No results.";

    while ($row = $qry->getRow())
    {
        $html .= "<tr class=kb-table-row-even>";
        $html .= "<td><a href=\"?a=admin_kill_export_csv&plt_id=".$row['plt_id']."\">".$row['plt_name']."</a></td><td>".$row['crp_name']."</td>";
        $html .= "</tr>";
    }
    if ($qry->recordCount() > 0)
        $html .= "</table>";
}

$page->setContent($html);
$page->addContext($menubox->generate());
$page->generate();
?>