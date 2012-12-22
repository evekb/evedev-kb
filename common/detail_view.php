<?php
$sec_color = array( "10" => '#4cfeff', 
					"9" => '#12f38d', 
					"8" => '#29ee46', 
					"7" => '#5cda2e', 
					"6" => '#5ad717', 
					"5" => '#fffb23',
					"4" => '#d9670f', 
					"3" => '#ea5511', 
					"2" => '#ec3a16', 
					"1" => '#d1230b', 
					"0" => '#FF0000');

$page = new Page('TEST');
$page->setTitle('Region detail view');

if (isset($_GET['big_view'])) {
	$html='<table width="700" border="0" cellspacing="1" cellpadding="1" class="kb-table">
			<tr>
				<td align="center"><a href="?a=detail_view&region_id='.intval($_GET['region_id']).'">[Back]</a></td>
			</tr>
			<tr>
				<td align="center">
					<img src="?a=map&region_id='.intval($_GET['region_id']).'&size=700&mode='.$_GET['mode'].'" width="700" height="700">
				</td>
			</tr>
		</table>';
} elseif (isset($_GET['search'])) {
	$html.='<form id="form1" name="form1" method="post" action="?a=detail_view&search">
				<input type="text" name="search_string" />
				<select name="selector">
					<option value="reg"'; if($_POST['selector']=='reg') { $html.=" selected"; }  $html.='>Region</option>
					<option value="con"'; if($_POST['selector']=='con') { $html.=" selected"; }  $html.='>Constellation</option>
					<option value="sys"'; if($_POST['selector']=='sys') { $html.=" selected"; }  $html.='>System</option>
				</select><br />
				<br />
				<input name="" type="submit" value="Search"/>
			</form>';

	if (isset($_POST['search_string']) && $_POST['search_string'] != "") {
		$html.='<br /><br />';
			
		switch ($_POST['selector']) {
			case "reg":
				$sql="	SELECT reg_id, reg_name
						FROM `kb3_regions`
						WHERE `reg_name` LIKE '%".$_POST['search_string']."%'";
				break;
			case "con":
				$sql="	SELECT con.con_name, reg.reg_id, reg.reg_name
						FROM kb3_constellations con, kb3_regions reg
						WHERE reg.reg_id = con.con_reg_id
						AND con.con_name LIKE '%".$_POST['search_string']."%'";
				break;
			case "sys":
				$sql="	SELECT sys.sys_name, reg.reg_id, reg.reg_name
						FROM kb3_systems sys, kb3_constellations con, kb3_regions reg
						WHERE con.con_id = sys.sys_con_id
						AND reg.reg_id = con.con_reg_id
						AND sys.sys_name LIKE '%".$_POST['search_string']."%'";
				break;
			default: 
				exit;
		}

		$qry = new DBQuery();
		$qry->execute($sql) or die($qry->getErrorMsg());

		if($qry->recordCount()) {
			$html .='<table width="250" border="0" cellspacing="1" cellpadding="1">';

			while ($row = $qry->getRow()) {
				$html .='<tr>';
				switch ($_POST['selector']) {
					case "con":
						$html .='<td width=125>'.$row['con_name'].'</td>';
						break;
					case "sys":
						$html .='<td width=125>'.$row['sys_name'].'</td>';
						break;
					default:
						$html .='<td></td>';
				}

				$html .='<td><a href="?a=detail_view&region_id='.$row['reg_id'].'">'.$row['reg_name'].'</a></td></tr>';
			}

			$html .='</table>';
				
		} else {
			$html .='No match found<br />';
		}
	} else {
		$html .='<br /><br />Empty search string<br />';
	}
} else {
	$sql="SELECT sys.sys_sec, sys.sys_id, sys.sys_name, sys.sys_id, con.con_id, con.con_name, reg.reg_id, reg.reg_name
			FROM kb3_systems sys, kb3_constellations con, kb3_regions reg
			WHERE con.con_id = sys.sys_con_id
			AND reg.reg_id = con.con_reg_id
			AND reg.reg_id =".intval($_GET['region_id'])."
			ORDER BY con.con_name, `sys`.`sys_name` ASC";

	$const="";
	$const_i ="0";
	$i=0;

	$qry = new DBQuery();
	$qry->execute($sql) or die($qry->getErrorMsg());
	while ($row = $qry->getRow()) {
		$region=$row['reg_name'];
			
		if($row['con_name'] != $const) {
			$const =$row['con_name'];
			$constellation[$const_i++] = $row['con_name'];
		}

		$systems[$i]['name'] = $row['sys_name'];
		$systems[$i]['id'] = $row['sys_id'];
			
		if ($row['sys_sec'] <= 0) {
			$sysec ="0.0";
		} else {
			$sysec = round($row['sys_sec'], 1);
		}
			
		if($sysec == 1) { //fix the 1
			$sysec ="1.0";
		}
			
		$systems[$i++]['sec'] = $sysec;
	}

	sort($systems);

	$html .='<table width="98%" border="0" cellspacing="1" cellpadding="1">
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td width="400" align="center" valign="top">
			<table width="300" border="0" cellspacing="1" cellpadding="1">

		  <tr>
			<td align="center"  class="kb-table">Player ship kills in the last hour<br />
				<a href="?a=detail_view&big_view&region_id='.intval($_GET['region_id']).'&mode=ship"><img src="?a=map&region_id='.$_GET['region_id'].'&size=300&mode=ship" border="0" /></a></td>
		  </tr>
		  <tr>
				<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td align="center" class="kb-table">NPC ship kills of the last hour <br />
				<a href="?a=detail_view&big_view&region_id='.intval($_GET['region_id']).'&mode=faction"><img src="?a=map&region_id='.$_GET['region_id'].'&size=300&mode=faction" width="300" height="300" border="0" /></a></td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>';
		  
	if (isset($_GET['sys'])) {	  
	$html .='	  <tr>
			<td align="center" class="kb-table">System location<br />
				<img src="?a=map&mode=sys&sys_id='.intval($_GET['sys']).'&size=300" width="300" height="300" border="0" /><br /><a href="?a=detail_view&region_id='.intval($_GET['region_id']).'">Clear Filter</a></td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>';
	}	 

	$html .='</table></td>
		<td align="center" valign="top">
			<table width="98%" border="0" cellspacing="1" cellpadding="1">
			 <tr>
			<th colspan="2">Details</th>
			</tr>
		  <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td width="50%">Region:</td>
			<td>'.$region.' <a href="?a=detail_view&search">[Search]</a></td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top">Constellations:</td>
			<td>'; 
	foreach($constellation as $const) {
		$html.=$const.'<br>';
	}		

	$html.='</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top">Systems: </td>
			<td>'; 
 
	foreach($systems as $sys) {
		$html.='<a href="?a=detail_view&region_id='.intval($_GET['region_id']).'&sys='.intval($sys['id']).'">'.$sys['name'].'</a> ( <span style="color:'.$sec_color[$sys['sec']*10].'"> '.$sys['sec'].'</span> )<br>';
	}		

	$html.='</td>
		  </tr>
		</table></td>
	  </tr>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	</table>';
}

$page->setContent($html);
$page->generate();
?>