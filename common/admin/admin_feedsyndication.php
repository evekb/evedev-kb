<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/*
 * EDK Feed Syndication v1.7
 * based on liq's feed syndication mod v1.5
 *
 */

// set this to 1 if you are running a master killboard and want
// to even fetch mails not related to your corp / alliance
define('MASTER', 0);

@set_time_limit(0);
require_once('common/admin/admin_menu.php');

$page = new Page("Administration - Legacy Feed Syndication " . $feedversion);
$page->setCachable(false);
$page->setAdmin();
$validurl = "/^(http|https):\/\/([A-Za-z0-9_]+(:[A-Za-z0-9_]+)?@)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*((:[0-9]{1,5})?\/.*)?$/i";
$html .= "<script language=\"JavaScript\">function checkAll(checkname, exby) {for (i = 0; i < checkname.length; i++)checkname[i].checked = exby.checked? true:false}</script>";
$html .= "<div class='block-header2'>The new feed syndication is more reliable and faster. Support for the old feeds will be removed in EDK 4.1<br /><a href='".edkURI::page("admin_idfeedsyndication")."'>Feed Syndication</a></div>";
$html .= "<table class='kb-subtable'>";

if (config::get('fetch_feed_count'))
    $feedcount = config::get('fetch_feed_count');
else
    $feedcount = 3;
// saving urls and options
if ($_POST['submit'] || $_POST['fetch'])
{
    if (ctype_digit($_POST['fetch_feed_count']) && $_POST['fetch_feed_count'] > 0)
    {
        $feedcount = $_POST['fetch_feed_count'];
        for ($i = config::get('fetch_feed_count'); $i > $feedcount; $i--)
        {
            config::del('fetch_url_' . $i);
        }
        config::set('fetch_feed_count', $feedcount);
    }
        config::del('fetch_verbose');
        config::del('fetch_compress');

    if ($_POST['fetch_comment'])
        config::set('fetch_comment', $_POST['fetch_comment']);
    else
        config::set('fetch_comment', '');
	
    for ($i = 1; $i <= $feedcount; $i++)
    {
        $url = "fetch_url_" . $i;
        if (preg_match($validurl , $_POST[$url]))
        {
            if ($_POST['trusted'] && in_array ($i, $_POST['trusted']))
			$trusted = "1";
            else $trusted = "0";
            if ($_POST['apikills'] && in_array ($i, $_POST['apikills']))
                $apikills = "on";
            else $apikills = "";
            $fstr = config::get('fetch_url_' . $i);
            $ftmp = explode(':::', $fstr);
            // reset the feed lastkill details if the URL or api status has changed
            if($_POST[$url] != $ftmp[0] )
                config::set($url, $_POST[$url] . ':::' . 0 . ':::' . 0 . ':::' . $apikills . ':::' . $trusted);
            elseif($trusted != $ftmp[4] || $apikills != $ftmp[3] )
                config::set($url, $_POST[$url] . ':::' . $ftmp[1] . ':::' . 0 . ':::' . $apikills . ':::' . $trusted);
        }
        else
            config::set($url, '');
        $feed[$i] = '';
    }
}
$feed = array();
$feedlast = array();
$trusted = array();
$apikills = array();
for ($i = 1; $i <= $feedcount; $i++)
{
    $str = config::get('fetch_url_' . $i);
    $tmp = explode(':::', $str);
    $feed[$i] = $tmp[0];
    $feedlast[$i] = $tmp[1];
    if ($tmp[2] == "on")
        $friend[$i] = $tmp[2];
	if ($tmp[3] == "on")
        $apikills[$i] = $tmp[3];
	if (isset($tmp[4])&& $tmp[4])
        $trusted[$i] = 1;
	else $trusted[$i] = 0;
}
// building the request query and fetching of the feeds
if ($_POST['fetch'])
{
	$myid = array();
	if(!MASTER)
	{
		foreach (config::get('cfg_pilotid') as $entity)
		{
			$pilot = new Pilot($entity);
			$myid[] = '&pilot=' . urlencode($pilot->getName());
		}

		foreach (config::get('cfg_corpid') as $entity)
		{
			$corp = new Corporation($entity);
			$myid[] = '&corp=' . urlencode($corp->getName());
		}
		foreach (config::get('cfg_allianceid') as $entity)
		{
			$alli = new Alliance($entity);
			$myid[] = '&alli=' . urlencode($alli->getName());
		}
	}
	else $myid[] = '';
    for ($i = 1; $i <= $feedcount; $i++)
    {
        $feedfetch = new Fetcher();
        $cfg = "fetch_url_" . $i;
        if (preg_match($validurl , $feed[$i]) && $_POST['fetch_feed'] && in_array ($i, $_POST['fetch_feed']))
        {
            $str = '';
/* Fetch all kills when using the admin panel.
             if ($feedlast[$i])
             $str .= '&lastkllid='.$feedlast[$i];
 */
            if ($apikills[$i])
                $str .= '&APIkills=1';
            if ($_POST['range1'] && $_POST['range2'])
            {
                if ($_POST['range1'] > $_POST['range2'])
                {
                    $range1 = $_POST['range2'];
                    $range2 = $_POST['range1'];
                }
                else
                {
                    $range1 = $_POST['range1'];
                    $range2 = $_POST['range2'];
                }
                for ($l = $range1; $l <= $range2; $l++)
                {
					$lastkill = 0;
					// Fetch for current and previous weeks, both kills and losses
					foreach($myid as $id)
					{
	                    $html .= "<b>Week: " . $l . " losses</b><br>";
						$html .= $feedfetch->grab($feed[$i] . "&year=" . $_POST['year'] . "&week=" . $l, $id . $str, $trusted[$i], $cfg). "\n";
						if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill) $lastkill = intval($feedfetch->lastkllid_);
						$html .= "<b>Week: " . $l . " kills</b><br>";
						$html .= $feedfetch->grab($feed[$i] . "&year=" . $_POST['year'] . "&week=" . $l, $id . $str . "&losses=1", $trusted[$i], $cfg) . "\n";
						if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill) $lastkill = intval($feedfetch->lastkllid_);
					}
					if($lastkill > $feedlast[$i]) $feedlast[$i] = $lastkill;
					// Store oldest 'most recent kill id' fetched from all entities.
					// (since kills may be posted during multiple fetches)
					if($feedlast[$i]) config::set("fetch_url_" . $i, $feed[$i] . ':::' . $feedlast[$i] . ':::' . 0 . ':::' . $apikills[$i] . ':::' . $trusted[$i]);
                }
            }
            else
			{
				$lastkill = 0;
				foreach($myid as $id)
				{
					$html .= $feedfetch->grab($feed[$i], $id . $str);
					if(intval($feedfetch->lastkllid_) < $lastkill || !$lastkill) $lastkill = intval($feedfetch->lastkllid_);

					$html .= $feedfetch->grab($feed[$i], $id . $str .  "&losses=1");
					if(intval($feedfetch->lastkllid_) > $lastkill || !$lastkill) $lastkill = intval($feedfetch->lastkllid_);
				}
				if($lastkill > $feedlast[$i]) $feedlast[$i] = $lastkill;
				// If kills are fetched then change the last kill id for the feed
				if($feedlast[$i])
				{
						config::set($cfg, $feed[$i] . ':::' . $feedlast[$i] . ':::' . 0 . ':::' . $apikills[$i] . ':::' . $trusted[$i]);
				}
			}
        }
    }
}
// generating the html
$html .= '<form id="options" name="options" method="post" action="'.KB_HOST.'/?a=admin_feedsyndication">';
$html .= "</table>";

$html .= "<div class='block-header2'>Feeds</div><table>";
for ($i = 1; $i <= $feedcount; $i++)
{
    $html .= "<tr><td width='85px'><b>Feed url #" . $i . "</b></td><td><input type='text' name=fetch_url_" . $i . " size=50 class=password value=\"";
    if ($feed[$i])
        $html .= $feed[$i];
    $html .= "\"></td>";

    $html .= "<td><input type='checkbox' name=trusted[] id=trusted value=" . $i;
    if ($trusted[$i])
        $html .= " checked=\"checked\"";
    $html .= "><b>Trusted?</b></td>";
    $html .= "<td><input type='checkbox' name=apikills[] id=apikills value=" . $i;
    if ($apikills[$i])
        $html .= " checked=\"checked\"";
    $html .= "><b>API verified only?</b><br>";

    $html .= "<td><input type='checkbox' name=fetch_feed[] id=fetch value=" . $i;
    if ($feed[$i])
        $html .= " checked=\"checked\"";
    $html .= "><b>Fetch?</b><br>";

    $html .= "<input type='hidden' name=fetch_time_" . $i . " value=\"";
    if($feedlast[$i]) $html .= $feedlast[$i];
    $html .= "\"></td>";
    $html .= "</td></tr>";
}
$html .= "<tr><td colspan='2'><i>Example: http://killboard.eve-d2.com/?a=feed</i></td><td>";
$html .= "</td><td></td><td><input type='checkbox' name='all' onclick='checkAll(this.form.fetch,this)'><i>all/none</i>";
$html .= "</td></tr><br></table><br><br><br>";

$html .= "<table><tr><td height='20px' width='150px'><b>First week:</b></td>";
$html .= '<td><select name="range1">';
$now = gmdate("W");
for ($i = 1; $i <= 53; $i++)
{
    if ($now == $i)
        $html .= '<option selected="selected "value="' . $i . '">' . $i . '</option>';
    else
        $html .= '<option value="' . $i . '">' . $i . '</option>';
}
$html .= '</select>';
$html .= "<i></i></td></tr>";
$html .= "<tr><td height='20px' width='150px'><b>Last week:</b></td>";
$html .= '<td><select name="range2">';
for ($i = 1; $i <= 53; $i++)
{
    if ($now == $i)
        $html .= '<option selected="selected "value="' . $i . '">' . $i . '</option>';
    else
        $html .= '<option value="' . $i . '">' . $i . '</option>';
}
$html .= '</select>';
$html .= "<i></i></td></tr>";

$html .= "<tr><td height='20px' width='150px'><b>Year:</b></td>";
$html .= '<td><select name="year">';
for($dateit = 2005; $dateit <= gmdate('Y'); $dateit++)
{
        $html .='<option ';
        if($dateit == gmdate('o')) $html .= 'selected="selected"';
        $html .=' value="'.$dateit.'">'.$dateit.'</option> ';
}
$html .= '</select>';
$html .= "</td></tr>";
$html .= "</table><br><br>";
$html .= "<input type='submit' id='submit' name='fetch' value=\"Fetch!\"><br><br>";

$html .= "<div class='block-header2'>Options</div><table>";
$html .= "<tr><td height='30px' width='150px'><b>Number of feeds:</b></td>";
$html .= "<td><input type='text' name='fetch_feed_count' size='2' maxlength='2' class='password' value='" . $feedcount . "'></td></tr>";
$html .= "<tr><td height='50px' width='150px'><b>Comment for automatically parsed killmails?</b></td>";
$html .= "<td><input type='text' size='50' class='password' name='fetch_comment' id='fetch_comment' value=\"";
if (config::get('fetch_comment'))
    $html .= config::get('fetch_comment');
$html .= "\"><br><i> (leave blank for none)</i><br></td></tr>";
$html .= "</table><br><br>";
$html .= "<input type='submit' id='submit' name='submit' value=\"Save\">";
$html .= "</form>";

$page->addContext($menubox->generate());
$page->setContent($html);
$page->generate();