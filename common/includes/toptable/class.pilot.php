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
		$this->TopList = $TopList;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->TopList->generate();

		$i = 1;
		$rows = array();
		while ($row = $this->TopList->getRow())
		{
			$pilot = new Pilot($row['plt_id']);
			if($pilot->getExternalID()) $uri = "?a=pilot_detail&amp;plt_ext_id=".$pilot->getExternalID();
			else $uri = "?a=pilot_detail&amp;plt_id=".$row['plt_id'];
			$rows[] = array(
				'rank' => $i,
				'name' => $pilot->getName(),
				'uri' => $uri,
				'portrait' => $pilot->getPortraitURL(32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Pilot');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('TopListtable'));
	}
}
