<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');
require_once('common/includes/eve_central_sync.php');
/**
*	Item Value Editor version 0.2
*	Author: Duncan Hill <evedev@cricalix.net>
 *      Updater: FriedRoadKill
*
*	Licence: Do what you like with it, credit me as the original author
*		 Not warrantied for anything, might eat your cat.  Your responsibility.
*/
$eve_central_exists = true;


$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Item Values');

// On a POST, we're probably updating a value, as the search funtion uses GET
if ('POST' == $_SERVER['REQUEST_METHOD'] AND isset($_POST['update_value'])) {
	$item = $_POST['itm_id'];
	$value = $_POST['value'];
	$query = "UPDATE kb3_item_price SET price='$value' WHERE typeID=$item";
	$qry = DBFactory::getDBQuery();;
	$qry->execute($query);
	$smarty->assign('success', 'Manual update of item price was successful.');
}

// On a get, we might be doing an EVE Central update
// The $eve_central_exists test is redundant, but acts as a safety-net.
if ('GET' == $_SERVER['REQUEST_METHOD'] AND isset($_GET['d']) AND 'eve_central' == $_GET['d'] AND $eve_central_exists) {
	if (ec_update_value($_GET['itm_id'])) {
		$smarty->assign('success', 'EVE Central synchronise was successful.');
	} else {
		$smarty->assign('success', 'EVE Central synchronise was not successful.  This could be because you do not have cURL enabled, or EVE Central returned invalid data for an item value.');
	}
}

// Scan the items table for the internal ID, name and value.
$sql = "SELECT itm.typeID, itm.typeName, val.price FROM kb3_invtypes as itm LEFT JOIN kb3_item_price AS val ON itm.typeID = val.typeID WHERE ";
// Filter it if there's a search phrase
if (isset($_REQUEST['searchphrase']) && $_REQUEST['searchphrase'] != "" && strlen($_REQUEST['searchphrase']) >= 3) {
    	$smarty->assign('search', true);
	$where[] = "itm.typeName like '%" . slashfix($_REQUEST['searchphrase']) ."%'";
}
// If a particular type was requested, filter on that type
(isset($_REQUEST['item_type'])) ? $type = $_REQUEST['item_type'] : $type = 25; // Default to frigates
$where[] = "itm.groupID = $type";
$where = join (' AND ', $where);
// And make it alphabetical
$sql .= $where . " ORDER BY itm.typeName";

$qry = DBFactory::getDBQuery();;
$qry->execute($sql);

while ($row = $qry->getRow())
{
    $results[] = array('id' => $row['typeID'], 'name' => $row['typeName'], 'value' => $row['price']);
}
$smarty->assignByRef('results', $results);

// Stuff we don't want to display.
// There's a lot more than this, but the item DB has quite a few items and I haven't filtered it all out yet.
$bad_ids = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,19,23,24,32,94,95,186,190,226,227,332);
$bad_ids = join(',', $bad_ids);
$bad_likes = array('itt_name not like "asteroid%"',
		'itt_name not like "mission%"',
		'itt_name not like "deadspace%"',
		'itt_name not like "concord%"',
		'itt_name not like "corvet%"',
		);
$bad_likes = join(' AND ', $bad_likes);

// Query for the item types to fill in the top dropdown
$query_types = "SELECT itt_id, itt_name FROM kb3_item_types WHERE itt_id not in ($bad_ids) AND $bad_likes ORDER BY itt_name";

$qry->execute($query_types);

while ($row = $qry->getRow())
{
	$types[$row['itt_id']] = $row['itt_name'];
}

// Chuck it all at smarty
$smarty->assignByRef('item_types', $types);
$smarty->assign('mod', 'value_editor');
$smarty->assign('eve_central_exists', $eve_central_exists);
$smarty->assign('type', $type);

$page->addContext($menubox->generate());
// override the smarty path, get the mod template, set it back.
$page->setContent($smarty->fetch(get_tpl('value_editor')));
$page->generate();

