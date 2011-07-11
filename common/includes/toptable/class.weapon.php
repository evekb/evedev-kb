<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
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

		$smarty->assign('tl_name', Language::get('weapon'));
		$smarty->assign('tl_type', Language::get('kills'));
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}
