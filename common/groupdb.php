<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */
/*
 * @package EDK
 */
class pInvtype extends pageAssembly
{
	/** @var Page */
	public $page = null;
	/** @var integer */
	public $groupID = 0;

	function __construct()
	{
		parent::__construct();

		$this->queue("start");
		$this->queue("details");
	}

	function start()
	{
		$this->groupID = (int)edkURI::getArg('id', 1);
		$this->page = new Page('Item Database');

	}

	function details()
	{
		global $smarty;
		if (!$this->groupID)
		{
			$this->page->setTitle('Error');
			return 'This ID is not a valid group ID.';
		}
		$sql = 'SELECT * FROM kb3_item_types d'.
				' WHERE d.itt_id = '.$this->groupID;
		$qry = DBFactory::getDBQuery();;
		$qry->execute($sql);
		$row = $qry->getRow();

		$this->page->setTitle('Item Database - '.$row['itt_name'].' Index');

		$sql = 'SELECT * FROM kb3_invtypes d'.
				' WHERE d.groupID = '.$this->groupID.
				' ORDER BY d.typeName ASC';
		$qry = DBFactory::getDBQuery();;
		$qry->execute($sql);
		$rows= array();
		while($row = $qry->getRow()) {
			$rows[] = array('typeID'=>$row['typeID'], 'typeName'=>$row['typeName']);
		}
		$smarty->assign('rows', $rows);
		return $smarty->fetch(get_tpl('groupdb'));
	}
}


$invtype = new pInvtype();
event::call("invtype_assembling", $invtype);
$html = $invtype->assemble();
$invtype->page->setContent($html);

$invtype->page->generate();
