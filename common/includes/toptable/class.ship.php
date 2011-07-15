<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Ship
{
	function TopTable_Ship($toplist)
	{
		$this->toplist = $toplist;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		$arg = array('a', 'invtype', true);
		while ($row = $this->toplist->getRow())
		{
			$ship = new Ship($row['shp_id']);
			$shipclass = $ship->getClass();
			$shipclass->getName();

			$rows[] = array(
				'rank' => false,
				'name' => $ship->getName(),
				'subname' => $shipclass->getName(),
				'uri' => edkURI::build($arg, array('id', $ship->getExternalID(), true)),
				'portrait' => $ship->getImage(32),
				'count' => $row['cnt']);
		}

		$smarty->assign('tl_name', Language::get('ship'));
		$smarty->assign('tl_type', Language::get('kills'));
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

