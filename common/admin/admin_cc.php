<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();

if ($_GET['op'] == 'view')
{
	$type = $_GET['type'];
	if ($type == 'campaign')
	{
		$page->setTitle('Administration - Campaigns');
		$campaign = 1;
	}
	$list = new ContractList();
	if ($type == 'campaign') $list->setCampaigns(true);
	$html = "[<a href=\"?a=admin_cc&amp;op=add&amp;type=".$type."\">Add ".$type."</a>]<br />";
	if ($list->getCount() > 0)
	{
		$html .= '<table class="kb-table" cellspacing="1">';
		$html .= "<tr class='kb-table-header'><td class='kb-table-cell' width='160'>Name</td><td class='kb-table-cell' width='80'>Startdate</td><td class='kb-table-cell' width='80'>Enddate</td><td class='kb-table-cell' width='140' colspan='2' align='center'>Action</td></tr>";
	}
	while ($contract = $list->getContract())
	{
		$html .= "<tr class='kb-table-row-odd'>";
		$html .= "<td class='kb-table-cell'>".$contract->getName()."</td>";
		$html .= "<td class='kb-table-cell'>".substr($contract->getStartDate(), 0, 10)."</td>";
		$html .= "<td class='kb-table-cell'>".substr($contract->getEndDate(), 0, 10)."</td>";
		$html .= "<td class='kb-table-cell' align='center' width='70'><a href=\"?a=admin_cc&amp;ctr_id=".$contract->getID()."&amp;op=edit&amp;type=".$type."\">Edit</a></td><td align='center'><a href=\"?a=admin_cc&amp;ctr_id=".$contract->getID()."&amp;op=del&amp;type=".$type."\">Delete</a></td>";
		$html .= "</tr>";
	}
	if ($list->getCount() > 0)
		$html .= "</table><br />";
	if ($list->getCount() > 10) $html .= "[<a href=\"?a=admin_cc&amp;op=add&amp;type=".$type."\">Add ".$type."</a>]";
}
// delete
if ($_GET['op'] == "del")
{
	if ($_GET['confirm'])
	{
		$contract = new Contract($_GET['ctr_id']);
		if (!$contract->validate()) exit;
		$contract->remove();

		Header("Location: ".KB_HOST."/?a=admin_cc&op=view&type=".$_GET['type']);
	}
	else
	{
		$page->setTitle("Administration - Delete ".$_GET['type']);
		$html .= "Confirm deletion:&nbsp;";
		$html .= "<button onclick=\"window.location.href='".KB_HOST."/?a=admin_cc&amp;ctr_id=".$_GET['ctr_id']."&amp;op=del&amp;type=".$_GET['type']."&amp;confirm=yes'\">Yes</button>&nbsp;&nbsp;&nbsp;";
		$html .= "<button onclick=\"window.history.back();\">No</button>";
	}
}
// edit
if ($_GET['op'] == "edit")
{
	$contract = new Contract($_GET['ctr_id']);
	if (!$contract->validate()) exit;
	if ($_POST['detail_submit'])
	{
		$contract->add($_POST['ctr_name'], $_GET['type'],
			$_POST['ctr_started'], $_POST['ctr_ended'], $_POST['ctr_comment']);

		header("Location: ".KB_HOST."/?a=admin_cc&op=view&type=".$_GET['type']);
	}

	if ($_GET['sop'])
	{
		$id = $_GET['id'];
		switch ($_GET['sop'])
		{
			case "del_corp":
				$crp_id = $id;
				break;
			case "del_alliance":
				$all_id = $id;
				break;
			case "del_region":
				$reg_id = $id;
				break;
			case "del_system":
				$sys_id = $id;
				break;
		}
		$contracttarget = new ContractTarget($contract, $crp_id, $all_id, $reg_id, $sys_id);
		$contracttarget->remove();

		header("Location: ".KB_HOST."/?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type']);
	}

	if ($_GET['add_id'])
	{
		$id = $_GET['add_id'];
		switch ($_GET['add_type'])
		{
			case 0:
				$crp_id = $id;
				break;
			case 1:
				$all_id = $id;
				break;
			case 2:
				$reg_id = $id;
				break;
			case 3:
				$sys_id = $id;
				break;
		}
		$contracttarget = new ContractTarget($contract, $crp_id, $all_id, $reg_id, $sys_id);
		$contracttarget->add();

		header("Location: ".KB_HOST."/?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type']);
	}
	if ($_POST['add_name'])
	{
		$page->setTitle("Add target");
		if (strlen($_POST['add_name']) < 3)
			$html .= "Please type atleast 3 letters.";
		else
		{
			switch ($_POST['add_type'])
			{
				case 0:
					$sql = "select crp.crp_id as id, crp.crp_name as name
                      from kb3_corps crp
                     where lower( crp.crp_name ) like '%".slashfix(strtolower($_POST['add_name']))."%'";
					break;
				case 1:
					$sql = "select ali.all_id as id, ali.all_name as name
                      from kb3_alliances ali
                     where lower( ali.all_name ) like '%".slashfix(strtolower($_POST['add_name']))."%'";
					break;
				case 2:
					$sql = "select reg_id as id, reg_name as name
                      from kb3_regions
                     where lower( reg_name ) like '%".slashfix(strtolower($_POST['add_name']))."%'";
					break;
				case 3:
					$sql = "select sys_id as id, sys_name as name
                      from kb3_systems
                     where lower( sys_name ) like '%".slashfix(strtolower($_POST['add_name']))."%'";
					break;
			}

			$qry = DBFactory::getDBQuery();;
			$qry->execute($sql) or die($qry->getErrorMsg());

			if ($qry->recordCount() > 0)
			{
				$html .= "<table class='kb-table' width='450'>";
				$html .= "<tr class='kb-table-header'><td width='340'>Name</td><td width='80' align='center'>Action</td></tr>";
			}
			else
				$html .= "No matches found for '".$_POST['add_name']."'.";

			while ($row = $qry->getRow())
			{
				$html .= "<tr class='kb-table-row-even'>";
				switch ($_POST['add_type'])
				{
					case 0:
						$html .= "<td><a href=\"?a=corp_detail&amp;crp_id=".$row['id']."\">".$row['name']."</a></td><td align='center'><button id='submit' name='submit' onclick=\"window.location.href='".KB_HOST."/?a=admin_cc&amp;ctr_id=".$_GET['ctr_id']."&amp;op=edit&amp;type=".$_GET['type']."&amp;add_type=".$_POST['add_type']."&amp;add_id=".$row['id']."'\">Select</button></td>";
						break;
					case 1:
						$html .= "<td><a href=\"?a=alliance_detail&amp;all_id=".$row['id']."\">".$row['name']."</a></td><td align='center'><button id='submit' name='submit' onclick=\"window.location.href='".KB_HOST."/?a=admin_cc&amp;ctr_id=".$_GET['ctr_id']."&amp;op=edit&amp;type=".$_GET['type']."&amp;add_type=".$_POST['add_type']."&amp;add_id=".$row['id']."'\">Select</button></td>";
						break;
					case 2:
						$html .= "<td>".$row['name']."</td><td align=center><button id=submit name=submit onClick=\"window.location.href='".KB_HOST."/?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type']."&add_type=".$_POST['add_type']."&add_id=".$row['id']."'\">Select</button></td>";
						break;
					case 3:
						$html .= "<td>".$row['name']."</td><td align=center><button id=submit name=submit onClick=\"window.location.href='".KB_HOST."/?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type']."&add_type=".$_POST['add_type']."&add_id=".$row['id']."'\">Select</button></td>";
						break;
				}
				$html .= "</tr>";
			}
			if ($qry->recordCount() > 0)
				$html .= "</table>";
		}
	}
	else
	{
		$page->setTitle("Administration - Edit ".$_GET['type']);

		$contract = new Contract($_GET['ctr_id']);

		$html .= "<div class=block-header2>Details</div>";

		$html .= "<form id=detail_edit name=detail_edit method=post action=?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type'].">";
		$html .= "<table class=kb-subtable width=98%>";

		$html .= "<tr><td width=80><b>Name:</b></td><td><input type=text name=ctr_name id=ctr_name size=38 maxlength=38 value=\"".htmlspecialchars($contract->getName(), ENT_QUOTES)."\"></td></tr>";
		$html .= "<tr><td width=80><b>Start date:</b></td><td><input type=text name=ctr_started id=ctr_started size=10 maxlength=10 value=\"".substr($contract->getStartDate(), 0, 10)."\"> (yyyy-mm-dd)</td></tr>";
		$html .= "<tr><td width-80><b>End date:</b></td><td><input type=text name=ctr_ended id=ctr_ended size=10 maxlength=10 value=\"".substr($contract->getEndDate(), 0, 10)."\"> (yyyy-mm-dd or blank)</td></tr>";
		$html .= "<tr><td><b>Comment:</b></td><td><input type='text' name='ctr_comment' value='".htmlspecialchars($contract->getComment(), ENT_QUOTES)."' size='100'/></td></tr>";
		$html .= "<tr><td></td></tr>";
		$html .= "<tr><td></td><td><input type=submit name=detail_submit value=\"Save\"></td></tr>";

		$html .= "</table>";
		$html .= "</form>";

		$html .= "<div class=block-header2>Targets</div>";

		$html .= "<table class=kb-table cellspacing=1>";
		$html .= "<tr class=kb-table-header><td class=kb-table-cell width=160>Target</td><td class=kb-table-cell width=80 align=center>Corporation</td><td class=kb-table-cell width=80 align=center>Alliance</td><td class=kb-table-cell width=80 align=center>Region</td><td class=kb-table-cell width=80 align=center>System</td><td class=kb-table-cell width=80 align=center>Action</td></tr>";

		$c = 0;
		while ($contracttarget = $contract->getContractTarget())
		{
			$c++;
			$type = $contracttarget->getType();
			if ($type == "corp")
			{
				$corp = new Corporation($contracttarget->getID());
				$name = $corp->getName();
			}
			if ($type == "alliance")
			{
				$alliance = new Alliance($contracttarget->getID());
				$name = $alliance->getName();
			}
			if ($type == "region")
			{
				$region = new Region($contracttarget->getID());
				$name = $region->getName();
			}
			if ($type == "system")
			{
				$system = new SolarSystem($contracttarget->getID());
				$name = $system->getName();
			}

			$html .= "<tr class=kb-table-row-odd><td class=kb-table-cell><b>".$name."</b></td><td class=kb-table-cell align=center>";
			if ($type == "corp")
				$html .= "x";
			$html .= "</td><td class=kb-table-cell align=center>";
			if ($type == "alliance")
				$html .= "x";
			$html .= "</td><td class=kb-table-cell align=center>";
			if ($type == "region")
				$html .= "x";
			$html .= "</td><td class=kb-table-cell align=center>";
			if ($type == "system")
				$html .= "x";
			$html .= "</td>";

			$html .= "<td align=center><a href=\"?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type']."&sop=del_".$type."&id=".$contracttarget->getID()."\">delete</a></td></tr>";
		}

		if ($c < 30)
		{
			$html .= "<form id=add_target name=add_target method=post action=?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=edit&type=".$_GET['type'].">";
			$html .= "<tr><td></td></tr>";
			$html .= "<tr><td><input type=text id=add_name name=add_name size=30 maxlength=30></td><td align=center><input type=radio name=add_type id=add_type value=0 checked></td><td align=center><input type=radio name=add_type id=add_type value=1></td><td align=center><input type=radio name=add_type id=add_type value=2></td><td align=center><input type=radio name=add_type id=add_type value=3></td><td align=center><input type=submit id=submit name=submit value=Add></td></tr>";
		}
		$html .= "</table>";
		$html .= "</form>";
	}
}
// add
if ($_GET['op'] == "add")
{
	if ($_POST['detail_submit'])
	{
		$contract = new Contract();
		$contract->add($_POST['ctr_name'], $_GET['type'], $_POST['ctr_started'], $_POST['ctr_ended'], $_POST['ctr_comment']);

		header("Location: ".KB_HOST."/?a=admin_cc&ctr_id=".$contract->getID()."&op=edit&type=".$_GET['type']);
	}

	$page->setTitle("Administration - Add ".$_GET['type']);

	$html .= "<div class=block-header2>Details</div>";

	$html .= "<form id=detail_edit name=detail_edit method=post action=?a=admin_cc&ctr_id=".$_GET['ctr_id']."&op=add&type=".$_GET['type'].">";
	$html .= "<table class=kb-table width=98%>";

	$html .= "<tr><td width=80><b>Name:</b></td><td><input type=text name=ctr_name id=ctr_name size=40 maxlength=40></td></tr>";
	$html .= "<tr><td width=80><b>Start date:</b></td><td><input type=text name=ctr_started id=ctr_started size=10 maxlength=10 value=\"".kbdate("Y-m-d")."\"> (yyyy-mm-dd)</td></tr>";
	$html .= "<tr><td width-80><b>End date:</b></td><td><input type=text name=ctr_ended id=ctr_ended size=10 maxlength=10> (yyyy-mm-dd or blank)</td></tr>";
	$html .= "<tr><td><b>Comment:</b></td><td><input type='text' name='ctr_comment' size='100'/></td></tr>";
	$html .= "<tr><td></td></tr>";
	$html .= "<tr><td></td><td><input type=submit name=detail_submit value=\"Save\"></td></tr>";

	$html .= "</table>";
	$html .= "</form>";
}

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();
?>
