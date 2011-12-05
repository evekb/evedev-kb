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
class pSelf extends pageAssembly
{
    /**
     * Construct the Alliance Details object.
     * Set up the basic variables of the class and add the functions to the
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
				if ($alliance->getExternalID()) {
					$url = edkURI::page('alliance_detail', $alliance->getExternalID(),
							'all_ext_id');
				} else {
					$url = edkURI::page('alliance_detail', $alliance->getID(),
							'all_id');
				}
				$alls[] = array('id' => $alliance->getID(),
					'extid' => $alliance->getExternalID(),
					'name' => $alliance->getName(),
					'portrait' => $alliance->getPortraitURL(128),
					'url' => $url);
			}
		}
		if(config::get('cfg_corpid'))
		{
			$corps = array();
			foreach(config::get('cfg_corpid') as $entity)
			{
				$corp = new Corporation($entity);
				if ($corp->getExternalID()) {
					$url = edkURI::page('corp_detail', $corp->getExternalID(),
							'crp_ext_id');
				} else {
					$url = edkURI::page('corp_detail', $corp->getID(),
							'crp_id');
				}
				$corps[] = array('id' => $corp->getID(),
					'extid' => $corp->getExternalID(),
					'name' => $corp->getName(),
					'portrait' => $corp->getPortraitURL(128),
					'url' => $url);
			}
		}
		if(config::get('cfg_pilotid'))
		{
			$pilots = array();
			foreach(config::get('cfg_pilotid') as $entity)
			{
				$pilot = new Pilot($entity);
				if ($pilot->getExternalID()) {
					$url = edkURI::page('pilot_detail', $pilot->getExternalID(),
							'plt_ext_id');
				} else {
					$url = edkURI::page('pilot_detail', $pilot->getID(),
							'plt_id');
				}
				$pilots[] = array('id' => $pilot->getID(),
					'extid' => $pilot->getExternalID(),
					'name' => $pilot->getName(),
					'portrait' => $pilot->getPortraitURL(128),
					'url' => $url);
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
	/* @var $alliance Alliance */
	$alliance = Cacheable::factory('Alliance', $alls[0]);
	if ($alliance->getExternalID()) {
		$url = edkURI::page('alliance_detail', $alliance->getExternalID(), 'all_ext_id');
	} else {
		$url = edkURI::page('alliance_detail', $alls[0], 'all_id');
	}
	header("Location: ".htmlspecialchars_decode($url));
	die;
}
elseif(config::get('cfg_corpid'))
{
	$corps = config::get('cfg_corpid');
	/* @var $corp Corporation */
	$corp = Cacheable::factory('Corporation', $corps[0]);
	if ($corp->getExternalID()) {
		$url = edkURI::page('corp_detail', $corp->getExternalID(), 'crp_ext_id');
	} else {
		$url = edkURI::page('corp_detail', $corps[0], 'crp_id');
	}
	header("Location: ".htmlspecialchars_decode($url));
	die;
}
elseif(config::get('cfg_pilotid'))
{
	$pilots = config::get('cfg_pilotid');
	/* @var $pilot Pilot */
	$pilot = Cacheable::factory('Pilot', $pilots[0]);
	if ($pilot->getExternalID()) {
		$url = edkURI::page('pilot_detail', $pilot->getExternalID(),
				'plt_ext_id');
	} else {
		$url = edkURI::page('pilot_detail', $pilots[0],
				'plt_id');
	}
	header("Location: ".htmlspecialchars_decode($url));
	die;
}
else
{
	header("Location: ".htmlspecialchars_decode(edkURI::page('about')));
	die;
}

