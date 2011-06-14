<?php
/*
 * $Date: 2011-04-22 17:06:57 +1000 (Fri, 22 Apr 2011) $
 * $Revision: 1274 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/branches/3.2/common/includes/class.toplist.php $
 */

// Create a box to display the top pilots at something. Subclasses of TopList
// define the something.

class TopList_ContractScore extends TopList_Score
{
	function generate()
	{
		parent::generate();
	}

	function setContract($contract)
	{
		$this->setStartDate($contract->getStartDate());
		if ($contract->getEndDate() != "")
			$this->setEndDate($contract->getEndDate());

		while ($target = $contract->getContractTarget())
		{
			switch ($target->getType())
			{
				case "corp":
					$this->addVictimCorp($target->getID());
					break;
				case "alliance":
					$this->addVictimAlliance($target->getID());
					break;
				case "region":
					$this->addRegion($target->getID());
					break;
				case "system":
					$this->addSystem($target->getID());
					break;
			}
		}
	}
}
