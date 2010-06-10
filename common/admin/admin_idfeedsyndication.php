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
$html .= "<script language=\"JavaScript\" type='text/javascript'>function checkAll(checkname, exby) {for (i = 0; i < checkname.length; i++)checkname[i].checked = exby.checked? true:false}</script>";

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
		if($val['apikills']) $feedfetch->setAllKills(0);
		else $feedfetch->setAllKills(1);
		$feedfetch->setTrust($val['trust']);
		if(!$val['lastkill']) $feedfetch->setStartDate(time() - 60*60*24*7);
		else if($val['apikills']) $feedfetch->setStartKill($val['lastkill']);
		else $feedfetch->setStartKill($val['lastkill'], true);

		$feedfetch->read($val['url']);

		if($val['apikills'] && intval($feedfetch->getLastReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastReturned());
		else if(!$val['apikills'] && intval($feedfetch->getLastInternalReturned()) > $val['lastkill'])
			$val['lastkill'] = intval($feedfetch->getLastInternalReturned());
		$html .= "Feed: ".$val['url']."<br />\n";
		$html .= count($feedfetch->getPosted())." kills were posted and ".
			count($feedfetch->getSkipped())." were skipped.<br />\n";
		config::set("fetch_idfeeds", $feeds);
	}
}
// generating the html
$html .= '<form id="options" name="options" method="post" action="?a=admin_idfeedsyndication">';

$html .= "<div class='block-header2'>Feeds</div><table>";
$html .= "<tr style='text-align: left;'><th>Feed URL</th><th>Last Kill</th><th>Trusted</th><th>API only</th><th>Fetch</th><th>Delete</th></tr>\n";
foreach($feeds as $key => &$val)
{
	$key = md5($val['url']);
    $html .= "<tr><td><input type='text' name='" . $key . "' size='50' class='password' value=\"";
    $html .= $val['url'];
    $html .= "\" /></td>";

    $html .= "<td><input type='text' name='lastkill$key' class='lastkill' size='10' value='" . $val['lastkill'];
//    $html .= "' readonly='readonly' /></td>";
    $html .= "' /></td>";

    $html .= "<td><input type='checkbox' name='trusted[]' class='trusted' value='" . $key."'";
    if ($val['trusted'])
        $html .= " checked=\"checked\"";
    $html .= " /></td>";

	$html .= "<td><input type='checkbox' name='apikills[]' class='apikills' value='" . $key."'";
    if ($val['apikills']) $html .= " checked=\"checked\"";
    $html .= " /></td>";

    $html .= "<td><input type='checkbox' name='fetch_feed[]' class='fetch' value='" . $key."'";
    if (!isset($_POST['fetch_feed'][$key]) || $_POST['fetch_feed'][$key]) $html .= " checked=\"checked\"";
    $html .= " /></td>";

    $html .= "<td><input type='checkbox' name='delete[]' class='delete' value='" . $key."'";
    $html .= " />";
    $html .= "</td></tr>";
}
$html .= "<tr><td colspan='2'><i>Example: http://killboard.domain.com/?a=idfeed</i></td><td>";
$html .= "</td><td></td><td><input type='checkbox' name='all' onclick='checkAll(this.form.fetch,this)' /><i>all</i>";
$html .= "</td><td></td></tr></table><br /><br /><br />";

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
$html .= "<input type='submit' id='submitFetch' name='fetch' value=\"Fetch!\" /><br /><br />";

$html .= "<div class='block-header2'>Options</div><table>";
//$html .= "<tr><td height='30px' width='150px'><b>Number of feeds:</b></td>";
//$html .= "<td><input type='text' name='fetch_feed_count' size='2' maxlength='2' class='password' value='" . $feedcount . "'></td></tr>";
$html .= "<tr><td height='50' width='150'><b>Comment for automatically parsed killmails?</b></td>";
$html .= "<td><input type='text' size='50' class='password' name='fetch_comment' id='fetch_comment' value=\"";
if (config::get('fetch_comment'))
    $html .= config::get('fetch_comment');
$html .= "\" /><br /><i> (leave blank for none)</i><br /></td></tr>";
$html .= "</table><br /><br />";
$html .= "<input type='submit' id='submitOptions' name='submit' value=\"Save\" />";
$html .= "</form>";

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();