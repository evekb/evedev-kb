<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

class TopTable_Corp
{
	function TopTable_Corp($toplist, $entity)
	{
		$this->toplist = $toplist;
		$this->entity_ = $entity;
	}

	function generate()
	{
		global $smarty;
		$this->toplist->generate();

		$i = 1;
		while ($row = $this->toplist->getRow())
		{
			/* @var $corp Corporation */
			$corp = Cacheable::factory('Corporation', $row['crp_id']);
			if($corp->getExternalID()) {
				$uri = KB_HOST."/?a=corp_detail&amp;crp_ext_id=".$corp->getExternalID();
			} else {
				$uri = KB_HOST."/?a=corp_detail&amp;crp_id=".$row['crp_id'];
			}
			$rows[] = array(
				'rank' => $i,
				'name' => $corp->getName(),
				'uri' => $uri,
				'portrait' => imageURL::getURL('Corporation', $corp->getExternalID(false), 32),
				'count' => $row['cnt']);
			$i++;
		}

		$smarty->assign('tl_name', 'Corporation');
		$smarty->assign('tl_type', $this->entity_);
		$smarty->assignByRef('tl_rows', $rows);

		return $smarty->fetch(get_tpl('toplisttable'));
	}
}

