<?php
/**
 * $Date: 2010-06-04 23:26:29 +1000 (Fri, 04 Jun 2010) $
 * $Revision: 774 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/admin/admin_feedsyndication.php $
 * @package EDK
 */

/*
 * EDK IDFeed Syndication Admin Page
 */
require_once('common/admin/admin_menu.php');

$page = new Page("Administration - Feed Syndication " . IDFeed::version);
$page->setCachable(false);
$page->setAdmin();

$qry = new DBQuery();

// Delete any old feeds first
if ($_POST['submit']) {
	if ($_POST['delete']) {
		foreach( $_POST['delete'] as $id ) {
			$id = intval($id);
			$qry->execute("DELETE FROM kb3_feeds WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
			unset( $_POST["feed"][$id] );
		}
	}
}

$feeds = array();

// Retrieve feeds from Database
$qry->execute("SELECT * FROM kb3_feeds WHERE feed_kbsite = '".KB_SITE."'");
while ($row = $qry->getRow()) {
	$active = (bool)($row["feed_flags"] & FEED_ACTIVE);

	$feeds[$row["feed_id"]] = array('id'=>$row["feed_id"], 'updated'=>$row["feed_updated"],'active'=>$active, 'uri'=>$row["feed_url"], 'lastkill'=>$row["feed_lastkill"]);
}

// updating/saving urls and options
if ($_POST['submit'])
{
    foreach($_POST["feed"] as $key => $val) {
		if ($key == "new" ) {
			// new
			$uri = $val["url"];
			if( $uri === "") {
				continue;
			}

			$active = (isset($val["active"]) ? 1 : 0);
			$lastkill = intval($val["lastkill"]);

			// check feed doesn't already exist
			foreach( $feeds as $fid => $fval ) {
				if ( $fval['uri'] == $uri ) {
					$html .= "<br />Not Adding Duplicate Feed with URL: " . $uri;
					continue 2;
				}
			}

			$feed_flags = 0;
			if($active)
				$feed_flags |= FEED_ACTIVE;

			$sql = "INSERT INTO kb3_feeds( feed_url, feed_lastkill, feed_kbsite, feed_flags) VALUES ( '" . $qry->escape($uri) . "', $lastkill, '" . KB_SITE . "', '$feed_flags' )";
			$qry->execute($sql);

			$qry->execute("SELECT * FROM kb3_feeds WHERE feed_kbsite = '".KB_SITE."' AND feed_url='" . $qry->escape($uri) . "'");
			while ($row = $qry->getRow()) {
				$active = (bool)($row["feed_flags"] & FEED_ACTIVE);
				$feeds[$row["feed_id"]] = array('id'=>$row["feed_id"], 'updated'=>$row["feed_updated"], 'active'=>$active, 'uri'=>$row["feed_url"], 'lastkill'=>$row["feed_lastkill"]);
			}

		} else {
			// update
			$id = intval($key);
			$uri = $val["url"];
			$active = (isset($val["active"]) ? 1 : 0);
			$lastkill = intval($val["lastkill"]);
			if( $feeds[$id]['active'] != $active) {
				// flags have changed
				$feed_flags = 0;
				if($active)
					$feed_flags |= FEED_ACTIVE;

				$qry->execute("UPDATE kb3_feeds SET feed_flags=$feed_flags WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
				$feeds[$id]['active'] = (bool)($feed_flags & FEED_ACTIVE);
			}

			if ( $feeds[$id]['lastkill'] != $lastkill || $feeds[$id]['uri'] != $uri ) {
				$qry->execute("UPDATE kb3_feeds SET feed_lastkill=$lastkill, feed_url='" . $qry->escape($uri) . "' WHERE feed_kbsite = '".KB_SITE."' AND feed_id = $id");
				$feeds[$id]['lastkill'] = $lastkill;
				$feeds[$id]['uri'] = $uri;
			}
		}
    }
}

// Add an empty feed to the list, or create with one empty feed.
$feeds[] = array('id'=>'new', 'updated'=>'', 'active'=>'', 'uri'=>"", 'lastkill'=>0);

$smarty->assignByRef('rows', $feeds);

$smarty->assign('results', $html);
$smarty->assign('url', edkURI::page("admin_idfeedsyndication"));

$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_idfeed')));
$page->generate();
