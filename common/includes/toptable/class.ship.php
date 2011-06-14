<?php
/*
 * $Date: $
 * $Revision: -1 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/TopList/class.TopList.php $
 */

class TopTable_Ship
{
	function TopTable_Ship($TopList)
	{
		$this->TopList = $TopList;
	}

	function generate()
	{
		global $smarty;
		$this->TopList->generate();

		while ($row = $this->TopList->getRow())
		{
			$ship = new Ship($row['shp_id']);
			$shipclass = $ship->getClass();
			$shipclass->getName();

			$rows[] = array(
				'rank' => false,
				'name' => $ship->getName(),
				'subname' => $shipclass->getName(),
				'uri' => "?a=invtype&amp;id=".$ship->getExternalID(),
				'portrait' => $ship->getImage(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', 'Ship');
		$smarty->assign('tl_type', 'Kills');
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('TopListtable'));
	}
}

