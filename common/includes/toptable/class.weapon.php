<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class TopTable_Weapon
{
	function TopTable_Weapon($TopList)
	{
		$this->TopList = $TopList;
	}

	function generate()
	{
		global $smarty;
		$this->TopList->generate();

		while ($row = $this->TopList->getRow())
		{
			$item = new Item($row['itm_id']);
			$rows[] = array(
				'rank' => false,
				'name' => $item->getName(),
				'uri' => "?a=invtype&amp;id=".$item->getID(),
				'icon' => $item->getIcon(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', 'Weapon');
		$smarty->assign('tl_type', 'Kills');
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('TopListtable'));
	}
}
