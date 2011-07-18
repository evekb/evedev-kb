<?php
/**
 * $Date: 2010-05-30 03:57:50 +1000 (Sun, 30 May 2010) $
 * $Revision: 711 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.contract.php $
 * @package EDK
 */

/**
 * Generate a list of campaigns.
 * @package EDK
 */
class ContractList
{
	/** @var string */
	private $active = 'both';
	/** @var integer */
	private $contractcounter = 1;
	/** @var boolean */
	private $executed = false;
	/** @var DBBaseQuery */
	private $qry;
	/** @var integer */
	private $offset;
	/** @var integer */
	private $limit;

	function ContractList()
	{
	}

	private function execQuery()
	{
		if ($this->executed) {
			return;
		}
		$sql = "select ctr.ctr_id, ctr.ctr_started, ctr.ctr_ended, ctr.ctr_name
                from kb3_contracts ctr
               where ctr.ctr_site = '".KB_SITE."'";
		if ($this->active_ == "yes") {
			$sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
		} else if ($this->active_ == "no") {
			$sql .= " and ( now() >= ctr_ended )";
		}
		$sql .= " order by ctr_ended, ctr_started desc";
		$this->qry = DBFactory::getDBQuery();
		$this->qry->execute($sql);
		$this->executed = true;
	}

	/**
	 * Set whether to list active, inactive, or both types of campaigns.
	 * @param string $active 'yes', 'no', 'both'
	 */
	function setActive($active)
	{
		$this->active_ = $active;
	}

	/**
	 * Legacy stub
	 * @deprecated
	 */
	function setCampaigns()
	{
	}

	/**
	 * Set a limit of campaigns to show on each page.
	 * @param integer $limit
	 */
	function setLimit($limit)
	{
		$this->limit = $limit;
	}

	/**
	 * Which page to show in a list.
	 * @param integer $page Which page to show in a list
	 */
	function setPage($page)
	{
		$this->page_ = $page;
		$this->offset = ($page * $this->limit) - $this->limit;
	}

	/**
	 * Get the next Contract found.
	 * @return Contract|null
	 */
	function getContract()
	{
		$this->execQuery();
		if ($this->offset && $this->contractcounter < $this->offset) {
			for ($i = 0; $i < $this->offset; $i++) {
				$row = $this->qry->getRow();
				$this->contractcounter++;
			}
		}
		if ($this->limit 
				&& ($this->contractcounter - $this->offset) > $this->limit) {
			return null;
		}

		$row = $this->qry->getRow();
		if ($row) {
			$this->contractcounter++;
			return new Contract($row['ctr_id']);
		} else {
			return null;
		}
	}

	/**
	 * Return how many campaigns were found.
	 * @return integer
	 */
	function getCount()
	{
		$this->execQuery();
		return $this->qry->recordCount();
	}

	/**
	 * Whether active campaigns are shown
	 * @return boolean
	 */
	function getActive()
	{
		return $this->active;
	}

	/**
	 * Rewind to the first contract in the list.
	 */
	public function rewind()
	{
		$this->contractcounter = 1;
		$this->qry->rewind();
	}
}
