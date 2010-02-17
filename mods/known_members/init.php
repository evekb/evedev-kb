<?php
event::register('corpDetail_assembling', 'known_members::addView');
event::register('corpDetail_context_assembling', 'known_members::addMenu');

class known_members
{
	function addView($home)
	{
		$home->addView('known_members', "known_members::view");
	}
	function addMenu($home)
	{
		$home->addMenuItem("link","Known Members", "?a=corp_detail&amp;crp_id=" .
			$home->corp->getID() . "&amp;view=known_members");
	}
	function view($home)
	{
		if(config::get('mod_knownmembers_own'))
		{
			if (ALLIANCE_ID && $home->alliance->getID() == ALLIANCE_ID)
			{
				$can_view = 1;
			}
			elseif (CORP_ID && $home->crp_id == CORP_ID)
			{
				$can_view = 1;
			}

		}

		if($can_view == 1)
		{
			$html .= "Cannot View this corps Member List";
		}
		else
		{
			$query = "SELECT * FROM `kb3_pilots`  WHERE plt_crp_id =".$home->crp_id." ORDER BY `plt_name` ASC";
			$qry = new DBQuery();
			$qry->execute($query);
			$cnt = $qry->recordCount();

			$html .= "<div class=block-header2>Known Pilots (".$cnt.")</div>";
			$html .= "<table class=kb-table align=center>";
			$html .= '<tr class=kb-table-header>';
			if (config::get('mod_knownmembers_img'))
			{
				$html .= '<td class=kb-table-header align="center"></td>';
			}
			$html .= '<td class=kb-table-header align="center">Pilot</td>';
			if (config::get('mod_knownmembers_kllpnts'))
			{
				$html .= '<td class=kb-table-header align="center">Kill Points</td>';
			}
			if (config::get('mod_knownmembers_dmgdn'))
			{
				$html .= '<td class=kb-table-header align="center">Dmg Done (isk)</td>';
			}
			if (config::get('mod_knownmembers_dmgrcv'))
			{
				$html .= '<td class=kb-table-header align="center">Dmg Received (isk)</td>';
			}
			if (config::get('mod_knownmembers_eff'))
			{
				$html .= '<td class=kb-table-header align="center">Efficiency</td>';
			}
			if ($home->page->isAdmin())
			{
				$html .= '<td class=kb-table-header align="center">Admin - Move</td>';
			}
			$html .= '</tr>';
			while ($data = $qry->getRow())
			{
				$pilot = new Pilot( $data['plt_id'] );
				$plist = new KillList();
				$plist->addInvolvedPilot($pilot);
				$plist->getAllKills();
				$points = $plist->getPoints();

				$pllist = new KillList();
				$pllist->addVictimPilot($pilot);
				$pllist->getAllKills();

				$plistisk = $plist->getISK();
				$pllistisk = $pllist->getISK();
				if ($plistisk == 0)
				{ $plistisk = 1; } //Remove divide by 0
				if ($pllistisk == 0)
				{ $pllistisk = 1; } //Remove divide by 0
				$efficiency = round($plistisk / ($plistisk + $pllistisk) * 100, 2);

				if (!$odd)
				{
					$odd = true;
					$class = 'kb-table-row-odd';
				}
				else
				{
					$odd = false;
					$class = 'kb-table-row-even';
				}

				$html .= "<tr class=".$class." style=\"height: 32px;\">";
				if (config::get('mod_knownmembers_img'))
				{
					$html .= '<td width="64" align="center"><img src='.$pilot->getPortraitURL( 32 ).'></td>';
				}
				$html .= '<td align="center"><a href=?a=pilot_detail&plt_id='.$pilot->getID().'>'.$pilot->getName().'</a></td>';
				if (config::get('mod_knownmembers_kllpnts'))
				{
					$html .= '<td align="center">'.$points.'</td>';
				}
				if (config::get('mod_knownmembers_dmgdn'))
				{
					$html .= '<td align="center">'.(round($plist->getISK(),2)/1000000).'M</td>';
				}
				if (config::get('mod_knownmembers_dmgrcv'))
				{
					$html .= '<td align="center">'.(round($pllist->getISK(),2)/1000000).'M</td>';
				}
				if (config::get('mod_knownmembers_eff'))
				{
					$html .= '<td align="center">'.$efficiency.'%</td>';
				}
				if ($home->page->isAdmin())
				{
					$html .= "<td align=center><a href=\"javascript:openWindow('?a=admin_move_pilot&plt_id=".$data['plt_id']."', null, 500, 500, '' )\">Move</a></td>";
				}
				$html .= '</tr>';
			}
			
			$html .='</table>';
		}
		return $html;
	}
}
