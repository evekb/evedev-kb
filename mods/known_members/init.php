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
		if(config::get('known_members_own'))
		{
			$home->alliance->getID();
			if (ALLIANCE_ID && $home->alliance->getID() == ALLIANCE_ID)
			{
				$can_view = 1;
			}
			elseif (CORP_ID && $corp->getID() == CORP_ID)
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
			$clmn = config::get('known_members_clmn');

			$html .= "<div class=block-header2>Known Pilots (".$cnt.")</div>";
			$html .= "<table class=kb-table align=center>";
			$html .= '<tr class=kb-table-header>';
			if (strpos($clmn,"img"))
			{
				$html .= '<td class=kb-table-header align="center"></td>';
			}
			$html .= '<td class=kb-table-header align="center">Pilot</td>';
			if (strpos($clmn,"kll_pnts"))
			{
				$html .= '<td class=kb-table-header align="center">Kill Points</td>';
			}
			if (strpos($clmn,"dmg_dn"))
			{
				$html .= '<td class=kb-table-header align="center">Dmg Done (isk)</td>';
			}
			if (strpos($clmn,"dmg_rcd"))
			{
				$html .= '<td class=kb-table-header align="center">Dmg Recived (isk)</td>';
			}
			if (strpos($clmn,"eff"))
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
				if (strpos($clmn,"img"))
				{
					$html .= '<td width="64" align="center"><img src='.$pilot->getPortraitURL( 32 ).'></td>';
				}
				$html .= '<td align="center"><a href=?a=pilot_detail&plt_id='.$pilot->getID().'>'.$pilot->getName().'</a></td>';
				if (strpos($clmn,"kll_pnts"))
				{
					$html .= '<td align="center">'.$points.'</td>';
				}
				if (strpos($clmn,"dmg_dn"))
				{
					$html .= '<td align="center">'.(round($plist->getISK(),2)/1000000).'M</td>';
				}
				if (strpos($clmn,"dmg_rcd"))
				{
					$html .= '<td align="center">'.(round($pllist->getISK(),2)/1000000).'M</td>';
				}
				if (strpos($clmn,"eff"))
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
