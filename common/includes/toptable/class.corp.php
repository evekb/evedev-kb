<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Corp
{
	function TopTable_Corp($TopList, $entity)
	{
		$this->TopList = $TopList;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->TopList->generate();

		$i = 1;
		while ($row = $this->TopList->getRow())
		{
			$corp = new Corporation($row['crp_id']);
			if($corp->getExternalID()) $uri = "?a=corp_detail&amp;crp_ext_id=".$corp->getExternalID();
			else $uri = "?a=corp_detail&amp;crp_id=".$row['crp_id'];
			$rows[] = array(
				'rank' => $i,
				'name' => $corp->getName(),
				'uri' => $uri,
				'portrait' => $corp->getPortraitURL(32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Corporation');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

