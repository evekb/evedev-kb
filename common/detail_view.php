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
	$imgSrc = edkURI::build(array(array('a', 'map', true),
		array('region_id', intval($_GET['region_id']), true),
		array('size', '700', true),
		array('mode', urlencode($_GET['mode']), true)));
	$html='<table width="700" border="0" cellspacing="1" cellpadding="1" class="kb-table">
			<tr>
				<td align="center"><a href="'.edkURI::build(array('region_id', intval($_GET['region_id']), true)).'">[Back]</a></td>
			</tr>
			<tr>
				<td align="center">
					<img src="'.$imgSrc.'" width="700" height="700">
				</td>
			</tr>
		</table>';
} elseif (isset($_GET['search'])) {
	$html.='<form id="form1" name="form1" method="post" action="'.edkURI::build(array('search', true, true)).'">
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
		
		$pqry = new DBPreparedQuery();
		switch ($_POST['selector']) {
			case "reg":
				$sql="	SELECT reg_id, reg_name
						FROM `kb3_regions`
						WHERE `reg_name` LIKE '%?%'";
				break;
			case "con":
				$sql="	SELECT con.con_name, reg.reg_id, reg.reg_name
						FROM kb3_constellations con, kb3_regions reg
						WHERE reg.reg_id = con.con_reg_id
						AND con.con_name LIKE '%?%'";
				break;
			case "sys":
				$sql="	SELECT sys.sys_name, reg.reg_id, reg.reg_name
						FROM kb3_systems sys, kb3_constellations con, kb3_regions reg
						WHERE con.con_id = sys.sys_con_id
						AND reg.reg_id = con.con_reg_id
						AND sys.sys_name LIKE '%?%'";
				break;
			default: 
				exit;
		}

		$pqry->prepare($sql);
		$pqry->bind_param('s', $_POST['search_string']);
		$pqry->execute();

		if($pqry->recordCount()) {
			$html .='<table width="250" border="0" cellspacing="1" cellpadding="1">';

			while ($row = $pqry->fetch()) {
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

				$html .='<td><a href="'.edkURI::build(array('region_id', $row['reg_id'], true)).'">'.$row['reg_name'].'</a></td></tr>';
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

	$playerKillsLink = edkURI::build(array(array('big_view', true, true),
			array('region_id', intval($_GET['region_id']), true),
			array('mode', 'ship', true)));
	$playerKillsImg = edkURI::build(array(array('a', 'map', true),
			array('region_id', intval($_GET['region_id']), true),
			array('size', '300', true),
			array('mode', 'ship', true)));
	$npcKillsLink = edkURI::build(array(array('big_view', true, true),
			array('region_id', intval($_GET['region_id']), true),
			array('mode', 'faction', true)));
	$npcKillsImg = edkURI::build(array(array('a', 'map', true),
		array('region_id', intval($_GET['region_id']), true),
		array('size', '300', true),
		array('mode', 'faction', true)));
	$html .='<table width="98%" border="0" cellspacing="1" cellpadding="1">
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
		<td width="400" align="center" valign="top">
			<table width="300" border="0" cellspacing="1" cellpadding="1">

		  <tr>
			<td align="center"  class="kb-table">Player ship kills in the last hour<br />
				<a href="'.$playerKillsLink.'"><img src="'.$playerKillsImg.'" border="0" /></a></td>
		  </tr>
		  <tr>
				<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td align="center" class="kb-table">NPC ship kills of the last hour <br />
				<a href="'.$npcKillsLink.'"><img src="'.$npcKillsImg.'" width="300" height="300" border="0" /></a></td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>';
		  
	if (isset($_GET['sys'])) {
		$systemImg = edkURI::build(array(array('a', 'map', true),
				array('mode', 'sys', true),
				array('sys_id', intval($_GET['sys']), true),
				array('size', '300', true)));
		$systemLink = edkURI::build(array('region_id', intval($_GET['region_id']), true));
		$html .='	  <tr>
			<td align="center" class="kb-table">System location<br />
				<img src="'.$systemImg.'" width="300" height="300" border="0" /><br /><a href="'.$systemLink.'">Clear Filter</a></td>
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
			<td>'.$region.' <a href="'.edkURI::build(array('search', true, true)).'">[Search]</a></td>
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
		$systemLink = edkURI::build(array(array('region_id', intval($_GET['region_id']), true),
			array('sys', intval($sys['id']), true)));
		$html.='<a href="'.$systemLink.'">'.$sys['name'].'</a> ( <span style="color:'.$sec_color[$sys['sec']*10].'"> '.$sys['sec'].'</span> )<br>';
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