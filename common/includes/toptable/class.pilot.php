<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Pilot
{
	function TopTable_Pilot($toplist, $entity)
	{
		$this->toplist = $toplist;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		$i = 1;
		$rows = array();
		while ($row = $this->toplist->getRow())
		{
			$pilot = Pilot::getByID($row['plt_id']);
			if($row['plt_externalid']) {
				$uri = edkURI::build(array('a', 'pilot_detail', true),
						array('plt_ext_id', $row['plt_externalid'], true));
				$img = imageURL::getURL('Pilot', $row['plt_externalid'], 32);
			} else {
				$uri = edkURI::build(array('a', 'pilot_detail', true),
						array('plt_id', $row['plt_id'], true));

				$img = $pilot->getPortraitURL(32);
			}
			$rows[] = array(
				'rank' => $i,
				'name' => $pilot->getName(),
				'uri' => $uri,
				'portrait' => $img,
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Pilot');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}
