<?php
/**
 * $Date: 2010-06-04 23:26:29 +1000 (Fri, 04 Jun 2010) $
 * $Revision: 774 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/admin/admin_feedsyndication.php $
 * @package EDK
 */

/*
 * EDK IDFeed Syndication v1.5
 *
 */

require_once('common/admin/admin_menu.php');

$page = new Page("Administration - IDFeed Syndication " . ID_FEED_VERSION);
$page->setCachable(false);
$page->setAdmin();

$feeds = config::get("fetch_idfeeds");
// Add an empty feed to the list, or create with one empty feed.
if(is_null($feeds)) {
    $feeds[] = array('url'=>"", 'lastkill'=>0);
    config::set("fetch_idfeeds", $feeds);
} else {
    $feeds[] = array('url'=>"", 'lastkill'=>0);
}

$feedcount = count($feeds);

// saving urls and options
if ($_POST['submit'] || $_POST['fetch'])
{
    if(is_null($feeds)) {
        $feeds = array();
    }
        foreach($feeds as $key => &$val) {
                    // Use the md5 of the url as a key for each feed.
            $url = md5($val['url']);

            if ($_POST[$url]) {
                    if($_POST['lastkill'.$url] != $val['lastkill']) {
                            $val['lastkill'] = intval($_POST['lastkill'.$url]);
                    }
                    // reset the feed lastkill details if the URL or api status has changed
                    if($_POST[$url] != $val['url'] ) {
                            $val['url'] = $_POST[$url];
                            $val['lastkill'] = 0;
                    }
                    if ($_POST['delete'] && in_array ($url, $_POST['delete'])) {
                            unset($feeds[$key]);
                    }
            } else {
                    unset($feeds[$key]);
            }
        }
    $newlist = array();
    foreach($feeds as $key => &$val) {
        if ($val['url']) {
            $newlist[$val['url']] = $val;
        }
    }
    $feeds = &$newlist;
    config::set("fetch_idfeeds", $feeds);
        
        if($_POST['post_no_npc_only_feed'])
        {
            config::set('post_no_npc_only_feed', 1);
        }
        
        else
        {
            config::set('post_no_npc_only_feed', 0);
        }
    $feeds[] = array('url'=>"", 'lastkill'=>0);
}

// building the request query and fetching of the feeds
if ($_POST['fetch'])
{
    foreach($feeds as $key => &$val)
    {
            if(!($_POST['fetch_feed'] && in_array (md5($val['url']), $_POST['fetch_feed']))
                    || empty($val['url'])) continue;

            $html .= getIDFeed($key, $val);
    }
}
// generating the html
$rows = array();
foreach($feeds as $key => &$val) {
    $key = md5($val['url']);
    if (!isset($_POST['fetch_feed'][$key])
            || $_POST['fetch_feed'][$key]) {
        $fetch=false;
    } else {
        $fetch = true;
    }
    $rows[] = array('name'=>$key, 'uri'=>$val['url'], 'lastkill'=>$val['lastkill'], 'fetch'=>!$fetch);
}
$smarty->assignByRef('rows', $rows);
$smarty->assign('post_no_npc_only_feed', config::get('post_no_npc_only_feed'));
$smarty->assign('results', $html);
$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_idfeed')));
$page->generate();

/**
 * Fetch the board owners.
 * @return array Array of id strings to add to URLS
 */
function getOwners()
{
    $myids = array();
    if(!defined('MASTER') || !MASTER) {
        foreach(config::get('cfg_pilotid') as $entity) {
            $pilot = new Pilot($entity);
            $myids[] = '&pilot=' . urlencode($pilot->getName());
        }

        foreach(config::get('cfg_corpid') as $entity) {
            $corp = new Corporation($entity);
            $myids[] = '&corp=' . urlencode($corp->getName());
        }
        foreach(config::get('cfg_allianceid') as $entity) {
            $alli = new Alliance($entity);
            $myids[] = '&alli=' . urlencode($alli->getName());
        }
    }
    return $myids;
}

function getIDFeed(&$key, &$val)
{
    $html = '';
    // Just in case, check for empty urls.
    if(empty($val['url'])) {
        return 'No URL given<br />';
    }
    $feedfetch = new IDFeed();
    $feedfetch->setID();
    $feedfetch->setAllKills(1);
    if(!$val['lastkill']) {
        $feedfetch->setStartDate(time() - 60*60*24*7);
    } else {
        $feedfetch->setStartKill($val['lastkill'] + 1, true);
    }

    if($feedfetch->read($val['url']) !== false) {
                if(intval($feedfetch->getLastInternalReturned()) > $val['lastkill']) 
                {
            $val['lastkill'] = intval($feedfetch->getLastInternalReturned());
        }
        $html .= "IDFeed: ".$val['url']."<br />\n";
        $html .= count($feedfetch->getPosted())." kills were posted and ".
                        count($feedfetch->getSkipped())." were skipped"
                                                . " (".$feedfetch->getNumberOfKillsFetched()." kills fetched)<br />\n";
        if ($feedfetch->getParseMessages()) {
            $html .= implode("<br />", $feedfetch->getParseMessages());
        }
    } else {
        $html .= "Error reading feed: ".$val['url'];
        if (!$val['lastkill']) {
            $html .= ", Start time = ".(time() - 60 * 60 * 24 * 7);
        }
        $html .= $feedfetch->errormsg();
    }
    return $html;
}