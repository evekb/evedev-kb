<?php
$page = new Page('Search');

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
                 where plt.plt_name  like '%".$searchphrase."%'
                   and plt.plt_crp_id = crp.crp_id
                 order by plt.plt_name";
			$smarty->assign('result_header', 'Pilot');
			$smarty->assign('result_header_group', 'Corporation');
            break;
        case "corp":
            $sql = "select crp.crp_id, crp.crp_name, ali.all_name
                  from kb3_corps crp, kb3_alliances ali
                 where lower( crp.crp_name ) like lower( '%".$searchphrase."%' )
                   and crp.crp_all_id = ali.all_id
                 order by crp.crp_name";
			$smarty->assign('result_header', 'Corporation');
			$smarty->assign('result_header_group', 'Alliance');
            break;
        case "alliance":
            $sql = "select ali.all_id, ali.all_name
                  from kb3_alliances ali
                 where lower( ali.all_name ) like lower( '%".$searchphrase."%' )
                 order by ali.all_name";
			$smarty->assign('result_header', 'Alliance');
			$smarty->assign('result_header_group', '');
            break;
        case "system":
            $sql = "select sys.sys_id, sys.sys_name
                  from kb3_systems sys
                 where lower( sys.sys_name ) like lower( '%".$searchphrase."%' )
                 order by sys.sys_name";
			$smarty->assign('result_header', 'System');
			$smarty->assign('result_header_group', '');
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
	$smarty->assign('searched', 1);
	if ($qry->recordCount() == 0)
    {
        $smarty->assign('results', 0);
    }
    else
    {
		$results = array();
		while ($row = $qry->getRow())
		{
			$result = array();
			switch ($_REQUEST['searchtype'])
			{
				case "pilot":
					$result['link'] = "?a=pilot_detail&plt_id=".$row['plt_id'];
					$result['name'] = $row['plt_name'];
					$result['type'] = $row['crp_name'];
					$results[] = $result;
					break;
				case "corp":
					$result['link'] = "?a=corp_detail&crp_id=".$row['crp_id'];
					$result['name'] = $row['crp_name'];
					$result['type'] = $row['all_name'];
					$results[] = $result;
					break;
				case "alliance":
					$result['link'] = "?a=alliance_detail&all_id=".$row['all_id'];
					$result['name'] = $row['all_name'];
					$result['type'] = '';
					$results[] = $result;
					break;
				case "system":
					$result['link'] = "?a=system_detail&sys_id=".$row['sys_id'];
					$result['name'] = $row['sys_name'];
					$result['type'] = '';
					$results[] = $result;
					break;
				case 'item':
					$result['link'] = "?a=invtype&id=".$row['typeID'];
					$result['name'] = $row['typeName'];
					$result['type'] = '';
					$results[] = $result;
					break;
			}
			if ($qry->recordCount() == 1)
			{
				// if there is only one entry we redirect the user
				header("Location: ".KB_HOST.$result['link']);
				die;
			}
		}
		$smarty->assign_by_ref('results', $results);
	}
}
$page->setContent($smarty->fetch(get_tpl('search')));
$page->generate();
?>