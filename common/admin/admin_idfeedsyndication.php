<?php
/*
 * $Date: 2010-06-04 23:26:29 +1000 (Fri, 04 Jun 2010) $
 * $Revision: 774 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/admin/admin_feedsyndication.php $
 */

/*
 * EDK IDFeed Syndication v0.90
 *
 */

// set this to 1 if you are running a master killboard and want
// to even fetch mails not related to your corp / alliance
require_once('common/admin/admin_menu.php');

$page = new Page("Administration - IDFeed Syndication " . IDFeed::version);
$page->setCachable(false);
$page->setAdmin();

$feeds = config::get("fetch_idfeeds");
if(is_null($feeds))
{
	$feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);
	config::set("fetch_idfeeds", $feeds);
}
else $feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);

$feedcount = count($feeds);

// saving urls and options
if ($_POST['submit'] || $_POST['fetch'])
{
    if ($_POST['fetch_comment'])
        config::set('fetch_comment', $_POST['fetch_comment']);
    else
        config::set('fetch_comment', '');
	if(!is_null($feeds))
    foreach($feeds as $key => &$val)
    {
        $url = md5($val['url']);
        if ($_POST[$url])
        {
            if ($_POST['trusted'] && in_array ($url, $_POST['trusted']))
			$val['trusted'] = 1;
            else $val['trusted'] = 0;
            if ($_POST['apikills'] && in_array ($url, $_POST['apikills']))
			{
				if(!$val['apikills']) $val['lastkill'] = 0;
				$val['apikills'] = 1;
			}
            else
			{
				if($val['apikills']) $val['lastkill'] = 0;
				$val['apikills'] = 0;
			}
			if($_POST['lastkill'.$url] != $val['lastkill']) $val['lastkill'] = intval($_POST['lastkill'.$url]);
            // reset the feed lastkill details if the URL or api status has changed
            if($_POST[$url] != $val['url'] )
			{
				$val['url'] = $_POST[$url];
				$val['lastkill'] = 0;
			}
			if ($_POST['delete'] && in_array ($url, $_POST['delete']))
				unset($feeds[$key]);
        }
        else unset($feeds[$key]);
    }
	$newlist = array();
	foreach($feeds as $key => &$val) $newlist[$val['url']] = $val;
	$feeds = &$newlist;
	config::set("fetch_idfeeds", $feeds);
	$feeds[] = array('url'=>"", 'apikills'=>0, 'trusted'=>0, 'lastkill'=>0);
}

// building the request query and fetching of the feeds
if ($_POST['fetch'])
{
    foreach($feeds as $key => &$val)
    {
		if(!($_POST['fetch_feed'] && in_array (md5($val['url']), $_POST['fetch_feed']))
			|| empty($val['url'])) continue;
        $feedfetch = new IDFeed();
		$feedfetch->setID();
		// It's possible to post a kill without CCP ID, not fetch it, then add
		// an ID later, so only fetching API verified kills is problematic.
		// Disabling until a solution is worked out.
//		if($val['apikills']) $feedfetch->setAllKills(0);
//		else $feedfetch->setAllKills(1);
		$feedfetch->setAllKills(1);
		$feedfetch->setTrust($val['trusted']);
		if(!$val['lastkill']) $feedfetch->setStartDate(time() - 60*60*24*7);
		else if($val['apikills']) $feedfetch->setStartKill($val['lastkill'] + 1);
		else $feedfetch->setStartKill($val['lastkill'] + 1, true);

		$feedfetch->read($val['url']);

		if($val['apikills'] && intval($feedfetch->getLastReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastReturned());
		else if(!$val['apikills'] && intval($feedfetch->getLastInternalReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		$html .= "Feed: ".$val['url']."<br />\n";
		if(count($feedfetch->getPosted()) == 1) $html .= count($feedfetch->getPosted())." kill was posted and ";
		else $html .= count($feedfetch->getPosted())." kills were posted and ";
		if(count($feedfetch->getSkipped()) == 1) $html .= count($feedfetch->getSkipped())." was skipped.<br />\n";
		else $html .= count($feedfetch->getSkipped())." were skipped.<br />\n";
		config::set("fetch_idfeeds", $feeds);
	}
}
// generating the html
$rows = array();
foreach($feeds as $key => &$val)
{
	$key = md5($val['url']);
    if (!isset($_POST['fetch_feed'][$key]) || $_POST['fetch_feed'][$key]) $fetch=false;
	else $fetch = true;
	$rows[] = array('name'=>$key, 'uri'=>$val['url'], 'lastkill'=>$val['lastkill'], 'trusted'=>$val['trusted'], 'fetch'=>!$fetch);
}
$smarty->assignByRef('rows', $rows);

//$html .= "<table><tr><td height='20px' width='150px'><b>First week:</b></td>";
//$html .= '<td><select name="range1">';
//$now = gmdate("W");
//for ($i = 1; $i <= 53; $i++)
//{
//    if ($now == $i)
//        $html .= '<option selected="selected "value="' . $i . '">' . $i . '</option>';
//    else
//        $html .= '<option value="' . $i . '">' . $i . '</option>';
//}
//$html .= '</select>';
//$html .= "<i></i></td></tr>";
//$html .= "<tr><td height='20px' width='150px'><b>Last week:</b></td>";
//$html .= '<td><select name="range2">';
//for ($i = 1; $i <= 53; $i++)
//{
//    if ($now == $i)
//        $html .= '<option selected="selected "value="' . $i . '">' . $i . '</option>';
//    else
//        $html .= '<option value="' . $i . '">' . $i . '</option>';
//}
//$html .= '</select>';
//$html .= "<i></i></td></tr>";
//
//$html .= "<tr><td height='20px' width='150px'><b>Year:</b></td>";
//$html .= '<td><select name="year">';
//for($dateit = 2005; $dateit <= gmdate('Y'); $dateit++)
//{
//        $html .='<option ';
//        if($dateit == gmdate('o')) $html .= 'selected="selected"';
//        $html .=' value="'.$dateit.'">'.$dateit.'</option> ';
//}
//$html .= '</select>';
//$html .= "</td></tr>";
//$html .= "</table><br /><br />";
if (config::get('fetch_comment'))
    $smarty->assign('comment', config::get('fetch_comment'));
$smarty->assign('results', $html);
$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_idfeed')));
$page->generate();
