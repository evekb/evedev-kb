<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

class pSelf extends pageAssembly
{
    //! Construct the Alliance Details object.

    /** Set up the basic variables of the class and add the functions to the
     *  build queue.
     */
    function __construct()
    {
        parent::__construct();

        $this->queue("start");
        //$this->queue("summaryTable");
        $this->queue("display");
    }
    function start()
    {
        $this->page = new Page('Board Owners');
    }

	function summaryTable()
	{
		$summarytable = new KillSummaryTable();
		involved::load($summarytable,'kill');
		return $summarytable->generate();
	}

    function display()
    {
        global $smarty;
		$alls = $corps = $pilots = false;
		if(config::get('cfg_allianceid'))
		{
			$alls = array();
			foreach(config::get('cfg_allianceid') as $entity)
			{
				$alliance = new Alliance($entity);
				$alls[] = array('id' => $alliance->getID(),
					'extid' => $alliance->getExternalID(),
					'name' => $alliance->getName(),
					'portrait' => $alliance->getPortraitURL(128));
			}
		}
		if(config::get('cfg_corpid'))
		{
			$corps = array();
			foreach(config::get('cfg_corpid') as $entity)
			{
				$corp = new Corporation($entity);
				$corps[] = array('id' => $corp->getID(),
					'extid' => $corp->getExternalID(),
					'name' => $corp->getName(),
					'portrait' => $corp->getPortraitURL(128));
			}
		}
		if(config::get('cfg_pilotid'))
		{
			$pilots = array();
			foreach(config::get('cfg_pilotid') as $entity)
			{
				$pilot = new Pilot($entity);
				$pilots[] = array('id' => $pilot->getID(),
					'extid' => $pilot->getExternalID(),
					'name' => $pilot->getName(),
					'portrait' => $pilot->getPortraitURL(128));
			}
		}

		$smarty->assignByRef('alliances', $alls);
		$smarty->assignByRef('corps', $corps);
		$smarty->assignByRef('pilots', $pilots);

        return $smarty->fetch(get_tpl('self'));
    }
}

if(count(config::get('cfg_allianceid'))
	+ count(config::get('cfg_corpid'))
	+ count(config::get('cfg_pilotid')) > 1)
{

	$selfDetail = new pSelf();
	event::call("self_assembling", $selfDetail);
	$html = $selfDetail->assemble();
	$selfDetail->page->setContent($html);

	$selfDetail->page->generate();
}
else if(config::get('cfg_allianceid'))
{
	$alls = config::get('cfg_allianceid');
	$_GET['all_id'] = $alls[0];
	unset($alls);
	include('alliance_detail.php');
}
elseif(config::get('cfg_corpid'))
{
	$corps = config::get('cfg_corpid');
	$_GET['crp_id'] = $corps[0];
	unset($corps);
	include('corp_detail.php');
}
elseif(config::get('cfg_pilotid'))
{
	$pilots = config::get('cfg_pilotid');
	$_GET['plt_id'] = $pilots[0];
	unset($pilots);
	include('pilot_detail.php');
}
else
{
	include("about.php");
}

