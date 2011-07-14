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
		$this->toplist = $TopList;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		$i = 1;
		while ($row = $this->toplist->getRow())
		{
			$corp = new Corporation($row['crp_id']);
			if($row['crp_external_id']) {
				$uri = KB_HOST."/?a=corp_detail&amp;crp_ext_id=".$row['crp_external_id'];
			} else {
				$uri = KB_HOST."/?a=corp_detail&amp;crp_id=".$row['crp_id'];
			}
			$rows[] = array(
				'rank' => $i,
				'name' => $row['crp_name'],
				'uri' => $uri,
				'portrait' => imageURL::getURL('Corporation', (int)$row['crp_external_id'], 32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Corporation');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

