<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


$modInfo['forum_post']['name'] = "Forum Post";
$modInfo['forum_post']['abstract'] = "Adds a link to contracts and kill_related that gives a summary of the results.";
$modInfo['forum_post']['about'] = "Core distribution mod.";

event::register("contractDetail_context_assembling", "forumPost::addContractMenu");
event::register("killRelated_assembling", "forumPost::addRelatedMenu");

/**
 * @package EDK
 */
class forumPost
{
	public static function addContractMenu($object)
	{
		$object->addMenuItem("link", "Forum Summary",
			"javascript:sndReq('index.php?a=forum_post&amp;ctr_id=".$object->ctr_id.
			"');ReverseContentDisplay('popup')");
	}
	public static function addRelatedMenu($object)
	{
		$object->addMenuItem("link", "Forum Summary",
			"javascript:sndReq('index.php?a=forum_post&amp;kll_id=".$object->kll_id.
			"');ReverseContentDisplay('popup')");

	}
}
