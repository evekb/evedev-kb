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
class ContractListTable
{
	/** @var ContractList */
	private $contractlist = null;
	/** @var integer */
	private $paginate = null;

	/**
	 * @param ContractList $contractlist
	 */
	function ContractListTable(ContractList $contractlist)
	{
		$this->contractlist = $contractlist;
	}

	/**
	 * @param integer $paginate
	 * @param integer $page
	 */
	function paginate($paginate, $page = 1)
	{
		if (!$page or $page < 0) {
			$page = 1;
		}
		$this->paginate = $paginate;
		$this->contractlist->setLimit($paginate);
		$this->contractlist->setPage($page);
	}

	/**
	 * Get the statistics for this table.
	 * @return array Array of table statistics.
	 */
	function getTableStats()
	{
		$qry = DBFactory::getDBQuery();
		while ($contract = $this->contractlist->getContract())
		{
			// Losses

			// Outer query adds up the ships and cost.
			$sql = 'SELECT COUNT(kll_id) AS ships, SUM(kll_isk_loss) AS isk FROM (';
			$invcount = count($contract->getAlliances()) + count($contract->getCorps());

			// Inner query does the hard work of picking which kills to count.

			// Only use DISTINCT if we have to.
			if($invcount > 1) {
				$sql .= 'SELECT DISTINCT kll_id, kll_isk_loss FROM kb3_kills kll ';
			} else {
				$sql .= 'SELECT kll_id, kll_isk_loss FROM kb3_kills kll ';
			}

			if ($contract->getRegions()) {
				$sql .= ' INNER JOIN kb3_systems sys ON ( sys.sys_id = kll.kll_system_id )
						INNER JOIN kb3_constellations con ON ( con.con_id = sys.sys_con_id)';
			}
			if ($contract->getCorps() ) {
				$sql .= ' INNER JOIN kb3_inv_crp inc ON ( kll.kll_id = inc.inc_kll_id ) ';
			}
			if ($contract->getAlliances() ) {
				$sql .= ' INNER JOIN kb3_inv_all ina ON ( kll.kll_id = ina.ina_kll_id ) ';
			}

			$andargs = array();
			if($contract->getStartDate()) {
				$andargs[] = "kll.kll_timestamp >= '".$contract->getStartDate()."' ";
				if ($contract->getCorps()) {
					$andargs[] = "inc.inc_timestamp >= '".$contract->getStartDate()."' ";
				}
				if ($contract->getAlliances()) {
					$andargs[] = "ina.ina_timestamp >= '".$contract->getStartDate()."' ";
				}
			}

			if($contract->getEndDate()){
				$andargs[] = "kll.kll_timestamp < '".$contract->getEndDate()."' ";
				if ($contract->getCorps()) {
					$andargs[] = "inc.inc_timestamp < '".$contract->getEndDate()."' ";
				}
				if ($contract->getAlliances()) {
					$andargs[] = "ina.ina_timestamp < '".$contract->getEndDate()."' ";
				}
			}

			// Who are we shooting?
			$orargs = array();
			if ($contract->getCorps()) {
				$orargs[] = 'inc.inc_crp_id in ( '.join(',', $contract->getCorps()).')';
			}
			if ($contract->getAlliances()) {
				$orargs[] = 'ina.ina_all_id in ( '.join(',', $contract->getAlliances()).')';
			}
			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			// Who are we?
			$orargs = array();
			if(count(config::get('cfg_allianceid'))) {
				$orargs[] = 'kll.kll_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
			}
			if(count(config::get('cfg_corpid'))) {
				$orargs[] = 'kll.kll_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
			}
			if(count(config::get('cfg_pilotid'))) {
				$orargs[] = 'kll.kll_victim_id IN ('.implode(",", config::get('cfg_pilotid')).") ";
			}

			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			$orargs = array();
			if ($contract->getSystems()) {
				$orargs[] = 'kll.kll_system_id in ( '.join(',', $contract->getSystems()).')';
			}
			if ($contract->getRegions()) {
				$orargs[] = 'con.con_reg_id in ( '.join(',', $contract->getRegions()).' )';
			}
			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			if (count($andargs)) {
				$sql .= 'WHERE '.join(' AND ', $andargs);
			}
			$sql .= ') as kb3_shadow';
			$sql .= " /* contract: getTableStats '$invcount:losses */";
			$result = $qry->execute($sql);
			$row = $qry->getRow($result);

			$ldata = array('losses' => $row['ships'], 'lossisk' => $row['isk'] / 1000 );


			// Kills
			$sql = 'SELECT COUNT(kll_id) AS ships, sum(kll_isk_loss) AS isk FROM (';
			$invcount = count(config::get('cfg_pilotid'))
					+ count(config::get('cfg_corpid'))
					+ count(config::get('cfg_allianceid'));

			if($invcount > 1) {
				$sql .= 'SELECT DISTINCT kll_id, kll_isk_loss FROM kb3_kills kll ';
			} else {
				$sql .= 'SELECT kll_id, kll_isk_loss FROM kb3_kills kll ';
			}

			if ($contract->getRegions()) {
				$sql .= ' INNER JOIN kb3_systems sys ON ( sys.sys_id = kll.kll_system_id )
						INNER JOIN kb3_constellations con ON ( con.con_id = sys.sys_con_id)';
			}

			if(count(config::get('cfg_pilotid'))) {
				$sql .= ' INNER JOIN kb3_inv_detail ind ON ( kll.kll_id = ind.ind_kll_id ) ';
			}
			if(count(config::get('cfg_corpid'))) {
				$sql .= ' INNER JOIN kb3_inv_crp inc ON ( kll.kll_id = inc.inc_kll_id ) ';
			}
			if(count(config::get('cfg_allianceid'))) {
				$sql .=' INNER JOIN kb3_inv_all ina ON ( kll.kll_id = ina.ina_kll_id ) ';
			}

			$andargs = array();
			if($contract->getStartDate()) {
				$andargs[] = "kll.kll_timestamp >= '".$contract->getStartDate()."' ";
				if (count(config::get('cfg_corpid'))) {
					$andargs[] = "inc.inc_timestamp >= '".$contract->getStartDate()."' ";
				}
				if (count(config::get('cfg_allianceid'))) {
					$andargs[] = "ina.ina_timestamp >= '".$contract->getStartDate()."' ";
				}
			}

			if($contract->getEndDate()) {
				$andargs[] = "kll.kll_timestamp < '".$contract->getEndDate()."' ";
				if (count(config::get('cfg_corpid'))) {
					$andargs[] = "inc.inc_timestamp < '".$contract->getEndDate()."' ";
				}
				if (count(config::get('cfg_allianceid'))) {
					$andargs[] = "ina.ina_timestamp < '".$contract->getEndDate()."' ";
				}
			}

			$orargs = array();
			if ($contract->getCorps()) {
				$orargs[] = 'kll.kll_crp_id in ( '.join(',', $contract->getCorps()).' )';
			}
			if ($contract->getAlliances()) {
				$orargs[] = 'kll.kll_all_id in ( '.join(',', $contract->getAlliances()).' )';
			}
			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			$orargs = array();
			if(count(config::get('cfg_allianceid'))) {
				$orargs[] = '  ina.ina_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
			}
			if(count(config::get('cfg_corpid'))) {
				$orargs[] = ' inc.inc_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
			}
			if(count(config::get('cfg_pilotid'))) {
				$orargs[] = ' ind.ind_plt_id IN ('.implode(",", config::get('cfg_pilotid')).") ";
			}
			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			$orargs = array();
			if ($contract->getSystems()) {
				$orargs[] = 'kll.kll_system_id in ( '.join(',', $contract->getSystems()).')';
			}
			if ($contract->getRegions()) {
				$orargs[] = 'con.con_reg_id in ( '.join(',', $contract->getRegions()).' )';
			}
			if (count($orargs)) {
				$andargs[] = '('.join(' OR ', $orargs).')';
			}

			if (count($andargs)) {
				$sql .= 'WHERE '.join(' AND ', $andargs);
			}
			$sql .= ') as kb3_shadow';
			$sql .= " /* contract: getTableStats '$invcount':kills' */";
			$result = $qry->execute($sql);
			$row = $qry->getRow($result);

			$kdata = array('kills' => $row['ships'], 'killisk' => $row['isk'] / 1000 );

			if ($kdata['killisk']) {
				$efficiency = round($kdata['killisk'] / ($kdata['killisk']+$ldata['lossisk']) *100, 2);
			} else {
				$efficiency = 0;
			}
			$bar = new BarGraph($efficiency, 100);

			$tbldata[] = array_merge(array('name' => $contract->getName(),
				'startdate' => $contract->getStartDate(),
				'bar' => $bar->generate(),
				'enddate' => $contract->getEndDate(),
				'efficiency' => $efficiency,
				'id' => $contract->getID(),
				'url' => edkURI::page('cc_detail', $contract->getID(), 'ctr_id')),
				$kdata, $ldata);
		}
		$this->contractlist->rewind();
		return $tbldata;
	}

	/**
	 * Generates a table for this list of contracts.
	 * @return string Valid HTML for this list of contracts.
	 */
	function generate()
	{
		if ($table = $this->getTableStats()) {
			global $smarty;

			$smarty->assign('contract_getactive', $this->contractlist->getActive());
			$smarty->assignByRef('contracts', $table);
			$pagesplitter = new PageSplitter($this->contractlist->getCount(), 10);

			return $smarty->fetch(get_tpl('contractlisttable')).$pagesplitter->generate();
		} else {
			return "";
		}
	}
}
