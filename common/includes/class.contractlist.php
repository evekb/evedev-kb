<?php
/**
 * $Date: 2010-05-30 03:57:50 +1000 (Sun, 30 May 2010) $
 * $Revision: 711 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.contract.php $
 * @package EDK
 */

/**
 * @package EDK
 */
class ContractList
{
	public $qry_ = null;
	private $active_ = "both";
	private $contractcounter_ = 1;

	function ContractList()
	{
		$this->qry_ = DBFactory::getDBQuery();
	}

	function execQuery()
	{
		if ($this->qry_->executed())
			return;

		$sql = "select ctr.ctr_id, ctr.ctr_started, ctr.ctr_ended, ctr.ctr_name
                from kb3_contracts ctr
               where ctr.ctr_site = '".KB_SITE."'";
		if ($this->active_ == "yes")
			$sql .= " and ( ctr_ended is null or now() <= ctr_ended )";
		elseif ($this->active_ == "no")
			$sql .= " and ( now() >= ctr_ended )";
/*
		if ($this->campaigns_)
			$sql .= " and ctr.ctr_campaign = 1";
		else
			$sql .= " and ctr.ctr_campaign = 0";
*/
		$sql .= " order by ctr_ended, ctr_started desc";
		// if ( $this->limit_ )
		// $sql .= " limit ".( $this->page_ / $this->limit_ ).", ".$this->limit_;
		$this->qry_ = DBFactory::getDBQuery();
		$this->qry_->execute($sql) or die($this->qry_->getErrorMsg());
	}

	function setActive($active)
	{
		$this->active_ = $active;
	}

	function setCampaigns($campaigns)
	{
		$this->campaigns_ = $campaigns;
	}

	function setLimit($limit)
	{
		$this->limit_ = $limit;
	}

	function setPage($page)
	{
		$this->page_ = $page;
		$this->offset_ = ($page * $this->limit_) - $this->limit_;
	}

	function getContract()
	{
	// echo "off: ".$this->offset_."<br>";
	// echo "cnt: ".$this->contractcounter_."<br>";
	// echo "limit: ".$this->limit_."<br>";
		$this->execQuery();
		if ($this->offset_ && $this->contractcounter_ < $this->offset_)
		{
			for ($i = 0; $i < $this->offset_; $i++)
			{
				$row = $this->qry_->getRow();
				$this->contractcounter_++;
			}
		}
		if ($this->limit_ && ($this->contractcounter_ - $this->offset_) > $this->limit_)
			return null;

		$row = $this->qry_->getRow();
		if ($row)
		{
			$this->contractcounter_++;
			return new Contract($row['ctr_id']);
		}
		else
			return null;
	}

	function getCount()
	{
		$this->execQuery();
		return $this->qry_->recordCount();
	}

	function getActive()
	{
		return $this->active_;
	}

	public function rewind()
	{
		$this->contractcounter_ = 1;
		$this->qry_->rewind();
	}
}
