<?php
$page = new Page('Search');

$html .= "<form id=search action=\"?a=search\" method=post>";
$html .= "<table class=kb-subtable><tr>";
$html .= "<td>Type:</td><td>Text: (3 letters minimum)</td>";
$html .= "</tr><tr>";
$html .= "<td><select id=searchtype name=searchtype><option value=pilot>Pilot</option><option value=corp>Corporation</option><option value=alliance>Alliance</option><option value=system>System</option><option value=item>Items</option></select></td>";
$html .= "<td><input id=searchphrase name=searchphrase type=text size=30/></td>";
$html .= "<td><input type=submit name=submit value=Search></td>";
$html .= "</tr></table>";
$html .= "</form>";
$html .= "<div>Searches for all names beginning with the search phrase. To search for the phrase anywhere in the name use *yourquery.</div>";

$searchphrase = slashfix($_REQUEST['searchphrase']);
$searchphrase = preg_replace('/\*/', '%', $searchphrase);
$searchphrase = trim($searchphrase);
if ($searchphrase != "" && strlen($searchphrase) >= 3)
{
    switch ($_REQUEST['searchtype'])
    {
        case "pilot":
            $sql = "select plt.plt_id, plt.plt_name, crp.crp_name
                  from kb3_pilots plt, kb3_corps crp
                 where plt.plt_name  like '".$searchphrase."%'
                   and plt.plt_crp_id = crp.crp_id
                 order by plt.plt_name";
            $header = "<td>Pilot</td><td>Corporation</td>";
            break;
        case "corp":
            $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                  from kb3_corps crp, kb3_alliances ali
                 where lower( crp.crp_name ) like lower( '".$searchphrase."%' )
                   and crp.crp_all_id = ali.all_id
                 order by crp.crp_name";
            $header = "<td>Corporation</td><td>Alliance</td>";
            break;
        case "alliance":
            $sql = "select ali.all_id, ali.all_name
                  from kb3_alliances ali
                 where lower( ali.all_name ) like lower( '%".$searchphrase."%' )
                 order by ali.all_name";
            $header = "<td>Alliance</td><td></td>";
            break;
        case "system":
            $sql = "select sys.sys_id, sys.sys_name
                  from kb3_systems sys
                 where lower( sys.sys_name ) like lower( '%".$searchphrase."%' )
                 order by sys.sys_name";
            $header = "<td>System</td><td></td>";
            break;
        case "item":
            $sql = "select typeID, typeName from kb3_invtypes where typeName like ('%".$searchphrase."%')";
            break;
    }
    $qry = new DBQuery();
    if (!$qry->execute($sql))
    {
        die ($qry->getErrorMsg());
    }

    $html .= "<div class=block-header>Search results</div>";

    if ($qry->recordCount() > 0)
    {
        $html .= "<table class=kb-table width=450 cellspacing=1>";
        $html .= "<tr class=kb-table-header>".$header."</tr>";
    }
    else
    {
        $html .= "No results.";
    }

    while ($row = $qry->getRow())
    {
        $html .= "<tr class=kb-table-row-even>";
        switch ($_REQUEST['searchtype'])
        {
            case "pilot":
                $link = "?a=pilot_detail&plt_id=".$row['plt_id'];
                $html .= "<td><a href=\"$link\">".$row['plt_name']."</a></td><td>".$row['crp_name']."</td>";
                break;
            case "corp":
                $link = "?a=corp_detail&crp_id=".$row['crp_id'];
                $html .= "<td><a href=\"$link\">".$row['crp_name']."</a></td><td>".$row['all_name']."</td>";
                break;
            case "alliance":
                $link = "?a=alliance_detail&all_id=".$row['all_id'];
                $html .= "<td><a href=\"$link\">".$row['all_name']."</a></td><td></td>";
                break;
            case "system":
                $link = "?a=system_detail&sys_id=".$row['sys_id'];
                $html .= "<td><a href=\"$link\">".$row['sys_name']."</a></td><td></td>";
                break;
            case 'item':
                $link =  "?a=invtype&id=".$row['typeID'];
                $html .= "<td><a href=\"$link\">".$row['typeName']."</a></td><td></td>";
                break;
        }
        $html .= "</tr>";
        if ($qry->recordCount() == 1)
        {
            // if there is only one entry we redirect the user directly
            header("Location: $link");
        }
    }
    if ($qry->recordCount() > 0)
    {
        $html .= "</table>";
    }
}

$page->setContent($html);
$page->generate();
?>