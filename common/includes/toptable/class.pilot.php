<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Pilot
{
	function TopTable_Pilot($TopList, $entity)
	{
		$this->toplist = $TopList;
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
			if($row['plt_externalid']) {
				$uri = edkURI::build(array('a', 'pilot_detail', true),
						array('plt_ext_id', $row['plt_externalid'], true));
				$img = imageURL::getURL('Pilot', $row['plt_externalid'], 32);
			} else {
				$uri = edkURI::build(array('a', 'pilot_detail', true),
						array('plt_id', $row['plt_id'], true));

				$pilot = new Pilot($row['plt_id']);
				$img = $pilot->getPortraitURL(32);
			}
			$rows[] = array(
				'rank' => $i,
				'name' => $row['plt_name'],
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
