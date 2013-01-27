<?php
/**
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$ctrID = (int)edkURI::getArg('ctr_id');

if (edkURI::getArg('op') == 'view') {
	$page->setTitle('Administration - Campaigns');
	$list = new ContractList();
	$html = '[<a href="'.edkuri::build(array('op', 'add', false)).'">Add</a>]<br />';
	if ($list->getCount() > 0) {
		$html .= '<table class="kb-table" cellspacing="1">';
		$html .= "<tr class='kb-table-header'><td class='kb-table-cell' width='160'>Name</td><td class='kb-table-cell' width='80'>Startdate</td><td class='kb-table-cell' width='80'>Enddate</td><td class='kb-table-cell' width='140' colspan='2' align='center'>Action</td></tr>";
	}
	while ($contract = $list->getContract()) {
		$html .= "<tr class='kb-table-row-odd'>";
		$html .= "<td class='kb-table-cell'>".$contract->getName()."</td>";
		$html .= "<td class='kb-table-cell'>".substr($contract->getStartDate(), 0, 10)."</td>";
		$html .= "<td class='kb-table-cell'>".substr($contract->getEndDate(), 0, 10)."</td>";
		$html .= '<td class="kb-table-cell" align="center" width="70"><a href="'.edkuri::build(array(array('ctr_id', $contract->getID(), false), array('op', 'edit', false))).'">Edit</a></td><td align="center"><a href="'.edkuri::build(array(array('ctr_id', $contract->getID(), false), array('op', 'del', false))).'">Delete</a></td>';
		$html .= "</tr>";
	}
	if ($list->getCount() > 0)
		$html .= "</table><br />";
	if ($list->getCount() > 10)
		$html .= '[<a href="'.edkuri::build(array('op', 'add', false)).'">Add</a>]';
}
// delete
if (edkURI::getArg('op') == "del") {
	if ($_GET['confirm']) {
		$contract = new Contract($ctrID);
		if (!$contract->validate()) exit;
		$contract->remove();

		Header("Location: ".htmlspecialchars_decode(edkuri::build(array('op', 'view', false))));
	} else {
		$page->setTitle("Administration - Delete Campaign");
		$html .= "Confirm deletion:&nbsp;";
		$html .= '<button onclick="window.location.href="'.edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'del', false)), array('confirm', 'yes', false)).'">Yes</button>&nbsp;&nbsp;&nbsp;';
		$html .= "<button onclick=\"window.history.back();\">No</button>";
	}
}
// edit
if (edkURI::getArg('op') == "edit") {
	$contract = new Contract($ctrID);
	if (!$contract->validate()) exit;
	if ($_POST['detail_submit']) {
		$contract->add($_POST['ctr_name'], $_POST['ctr_started'], $_POST['ctr_ended'], $_POST['ctr_comment']);

		header("Location: ".htmlspecialchars_decode(edkuri::build(array('op', 'view', false))));
	}

	$editURL = edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'edit', false)));
	
	if (isset($_GET['sop'])) {
		$id = $_GET['id'];
		switch ($_GET['sop']) {
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

		header("Location: ".htmlspecialchars_decode($editURL));
	}

	if (isset($_GET['add_id']) && isset($_GET['add_type'])) {
		$id = $_GET['add_id'];
		switch ($_GET['add_type']) {
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

		header("Location: ".htmlspecialchars_decode($editURL));
	}
	if (isset($_POST['add_name'])) {
		$page->setTitle("Add target");
		if (strlen($_POST['add_name']) < 3)
			$html .= "Please type atleast 3 letters.";
		else {
			$qry = new DBQuery();
			switch ($_POST['add_type']) {
				case 0:
					$sql = "select crp.crp_id as id, crp.crp_name as name
                      from kb3_corps crp
                     where lower( crp.crp_name ) like '%".$qry->escape(strtolower($_POST['add_name']), true)."%'";
					break;
				case 1:
					$sql = "select ali.all_id as id, ali.all_name as name
                      from kb3_alliances ali
                     where lower( ali.all_name ) like '%".$qry->escape(strtolower($_POST['add_name']), true)."%'";
					break;
				case 2:
					$sql = "select reg_id as id, reg_name as name
                      from kb3_regions
                     where lower( reg_name ) like '%".$qry->escape(strtolower($_POST['add_name']), true)."%'";
					break;
				case 3:
					$sql = "select sys_id as id, sys_name as name
                      from kb3_systems
                     where lower( sys_name ) like '%".$qry->escape(strtolower($_POST['add_name']), true)."%'";
					break;
			}

			$qry->execute($sql) or die($qry->getErrorMsg());

			if ($qry->recordCount())
			{
				$html .= "<table class='kb-table' width='450'>";
				$html .= "<tr class='kb-table-header'><td width='340'>Name</td><td width='80' align='center'>Action</td></tr>";
			}
			else
				$html .= "No matches found for '".htmlentities($_POST['add_name'])."'.";

			while ($row = $qry->getRow()) {
				$html .= "<tr class='kb-table-row-even'>";
				$editURL = edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'edit', false),
					array('add_type', (int)$_POST['add_type'], false), array('add_id', $row['id'], false)));
				switch ($_POST['add_type']) {
					case 0:
						$html .= '<td><a href="'.edkURI::page('corp_detail', $row['id'], 'crp_id').'">'.$row['name']."</a></td><td align='center'><button id='submit' name='submit' onclick=\"window.location.href='".$editURL."'\">Select</button></td>";
						break;
					case 1:
						$html .= '<td><a href="'.edkURI::page('alliance_detail', $row['id'], 'all_id').'">'.$row['name']."</a></td><td align='center'><button id='submit' name='submit' onclick=\"window.location.href='".$editURL."'\">Select</button></td>";
						break;
					case 2:
						$html .= '<td><a href="'.edkURI::page('detail_view', $row['id'], 'region_id').'">'.$row['name']."</td><td align=center><button id=submit name=submit onClick=\"window.location.href='".$editURL."'\">Select</button></td>";
						break;
					case 3:
						$html .= '<td><a href="'.edkURI::page('system_detail', $row['id'], 'sys_id').'">'.$row['name']."</td><td align=center><button id=submit name=submit onClick=\"window.location.href='".$editURL."'\">Select</button></td>";
						break;
				}
				$html .= "</tr>";
			}
			if ($qry->recordCount())
				$html .= "</table>";
		}
	}
	else
	{
		$page->setTitle("Administration - Edit Campaign");

		$contract = new Contract($ctrID);

		$html .= "<div class=block-header2>Details</div>";

		$html .= '<form id=detail_edit name=detail_edit method=post action="'.edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'edit', false))).'">';
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
		while ($contracttarget = $contract->getContractTarget()) {
			$c++;
			$type = $contracttarget->getType();
			$typeURL = false;
			$ctrTypeID = $contracttarget->getID();
			switch($type) {
				case "corp":
					$corp = new Corporation($ctrTypeID);
					$name = $corp->getName();
					$typeURL = edkURI::page('corp_detail', $ctrTypeID, 'crp_id');
					break;
				case "alliance":
					$alliance = new Alliance($ctrTypeID);
					$name = $alliance->getName();
					$typeURL = edkURI::page('alliance_detail', $ctrTypeID, 'all_id');
					break;
				case "region":
					$region = new Region($ctrTypeID);
					$name = $region->getName();
					$typeURL = edkURI::page('detail_view', $ctrTypeID, 'region_id');
					break;
				case "system":
					$system = new SolarSystem($ctrTypeID);
					$name = $system->getName();
					$typeURL = edkURI::page('system_detail', $ctrTypeID, 'sys_id');
					break;
			}

			if ($typeURL)
				$html .= '<tr class=kb-table-row-odd><td class=kb-table-cell><b><a href="'.$typeURL.'">'.$name.'</b></td><td class=kb-table-cell align=center>';
			else
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

			$html .= '<td align=center><a href="'.edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'edit', false), array('sop', "del_$type", false), array('id', $contracttarget->getID(), false))).'">delete</a></td></tr>';
		}

		if ($c < 30) {
			$html .= '<form id=add_target name=add_target method=post action="'.edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'edit', false))).'">';
			$html .= "<tr><td></td></tr>";
			$html .= "<tr><td><input type=text id=add_name name=add_name size=30 maxlength=30></td><td align=center><input type=radio name=add_type id=add_type value=0 checked></td><td align=center><input type=radio name=add_type id=add_type value=1></td><td align=center><input type=radio name=add_type id=add_type value=2></td><td align=center><input type=radio name=add_type id=add_type value=3></td><td align=center><input type=submit id=submit name=submit value=Add></td></tr>";
		}
		$html .= "</table>";
		$html .= "</form>";
	}
}
// add
if (edkURI::getArg('op') == "add") {
	if ($_POST['detail_submit']) {
		$contract = new Contract();
		$contract->add($_POST['ctr_name'], $_POST['ctr_started'], $_POST['ctr_ended'], $_POST['ctr_comment']);

		header("Location: ".htmlspecialchars_decode(edkuri::build(array(array('ctr_id', $contract->getID(), false), array('op', 'edit', false)))));
	}

	$page->setTitle("Administration - Add Campaign");

	$html .= "<div class=block-header2>Details</div>";

	$html .= '<form id=detail_edit name=detail_edit method=post action="'.edkuri::build(array(array('ctr_id', $ctrID, false), array('op', 'add', false))).'">';
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
