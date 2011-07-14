<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Weapon
{
	function TopTable_Weapon(TopList_Base $toplist)
	{
		$this->toplist = $toplist;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		while ($row = $this->toplist->getRow())
		{
			$item = new Item($row['itm_id']);
			$rows[] = array(
				'rank' => false,
				'name' => $item->getName(),
				'uri' => edkURI::build(array('a', 'invtype', true),
						array('id', $item->getID(), true)),
				'icon' => $item->getIcon(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', Language::get('weapon'));
		$smarty->assign('tl_type', Language::get('kills'));
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}
