<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

/*! Core ajax functions are included in this page. Registered functions are
 * called once all mods are initialised.
 */

require_once('common/xajax/xajax.php');

$xajax->register(XAJAX_FUNCTION, "doAjaxSearch");

edk_xajax::xajax();

//! Search function for the search.php page.
function doAjaxSearch($searchphrase='', $type='pilot')
{
	require_once('class.dbprepared.php');
	$qry = new DBPreparedQuery();
	switch($type)
	{
		case "pilot":
			$sql = "select plt.plt_name as name1, crp.crp_name as name2, plt.plt_id as id
				  from kb3_pilots plt, kb3_corps crp
				 where plt.plt_name  like ?
				   and plt.plt_crp_id = crp.crp_id
				 order by plt.plt_name LIMIT 10";
			break;
		case "corp":
			$sql = "select crp.crp_name as name1, ali.all_name as name2, crp.crp_id as id
				  from kb3_corps crp, kb3_alliances ali
				 where crp.crp_name like  ?
				   and crp.crp_all_id = ali.all_id
				 order by crp.crp_name LIMIT 10";
			break;
		case "alliance":
			$sql = "select ali.all_name as name1, '' as name2, ali.all_id as id
				  from kb3_alliances ali
				 where ali.all_name like  ?
				 order by ali.all_name LIMIT 10";
			break;
		case "system":
			$sql = "select sys.sys_name as name1, reg.reg_name as name2, sys.sys_id as id
				  from kb3_systems sys, kb3_constellations con, kb3_regions reg
				 where sys.sys_name like  ?
					and con.con_id = sys.sys_con_id and reg.reg_id = con.con_reg_id
				 order by sys.sys_name LIMIT 10";
			break;
		case "item":
			$sql = "select typeName as name1, '' as name2, typeID as id
				from kb3_invtypes where typeName like ? LIMIT 10";
			break;
		default:
			$objResponse = new xajaxResponse();
			$objResponse->assign('searchresults', "innerHTML", 'Invalid type');
			return $objResponse;

	}
	$name1 = 'No result';
	$name2 = '';
	$id = 0;
	$qry->prepare($sql);
	$searchphrase2 = $searchphrase.'%';
	$qry->bind_param('s', $searchphrase2);
	$qry->bind_result($name1, $name2, $id);
	$result = '';

	if(!$qry->execute() )
	{
		$result = $qry->getErrorMsg();
	}

	else
	{
		if(!$qry->recordCount()) $result = "No results";
		else
		{
			$result = "<table class='kb-table' width='450'><tr class='kb-table-header'>";
				switch($type)
				{
					case "pilot":
						$result .= "<td>Pilot</td><td>Corporation</td></tr>";
						break;
					case "corp":
						$result .= "<td>Corporation</td><td>Alliance</td></tr>";
						break;
					case "alliance":
						$result .= "<td>Alliance</td><td></td></tr>";
						break;
					case "system":
						$result .= "<td>System</td><td>Region</td></tr>";
						break;
					case "item":
						$result .= "<td>Item</td><td></td></tr>";
						break;
				}
			while($qry->fetch())
			{
				$result .= "<tr class='kb-table-row-even'><td><a href='".KB_HOST;
				switch($type)
				{
					case "pilot":
						$result .= "/?a=pilot_detail&amp;plt_id=$id'>";
						break;
					case "corp":
						$result .= "/?a=corp_detail&amp;crp_id=$id'>";
						break;
					case "alliance":
						$result .= "/?a=alliance_detail&amp;all_id=$id'>";
						break;
					case "system":
						$result .= "/?a=system_detail&amp;sys_id=$id'>";
						break;
					case "item":
						$result .= "/?a=invtype&amp;id=$id'>";
						break;
				}
				$result .= $name1."</a></td><td>".$name2."</td></tr>";
			}
		}
	}
	$objResponse = new xajaxResponse();
	$objResponse->assign('searchresults', "innerHTML", $result);
	return $objResponse;
}

