<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

require_once('common/admin/admin_menu.php');

$page = new Page();
$page->setAdmin();
$page->setTitle('Administration - Standings');

if ($_REQUEST['searchphrase'] != "" && strlen($_REQUEST['searchphrase']) >= 3) {
	switch ($_REQUEST['searchtype']) {
		case 'corp':
			$sql = "select crp.crp_id, crp.crp_name, ali.all_name
                    from kb3_corps crp, kb3_alliances ali
                    where lower( crp.crp_name ) like lower( '%" . slashfix($_REQUEST['searchphrase']) . "%' )
                    and crp.crp_all_id = ali.all_id
                    order by crp.crp_name";
			break;
		case 'alliance':
			$sql = "select ali.all_id, ali.all_name
                    from kb3_alliances ali
                    where lower( ali.all_name ) like lower( '%" . slashfix($_REQUEST['searchphrase']) . "%' )
                    order by ali.all_name";
			break;
	}

	$qry = DBFactory::getDBQuery();
	$qry->execute($sql);

	while ($row = $qry->getRow()) {
		switch ($_REQUEST['searchtype']) {
			case 'corp':
				$typ = 'Corporation';
				$link = 'c' . $row['crp_id'];
				$descr = $row['crp_name'] . ', member of ' . $row['all_name'];
				break;
			case 'alliance':
				$typ = 'Alliance';
				$link = 'a' . $row['all_id'];
				$descr = $row['all_name'];
				break;
		}
		$results[] = array('descr' => $descr, 'link' => $link, 'typ' => $typ);
	}
	$smarty->assignByRef('results', $results);
	$smarty->assign('search', true);
}
if ($val = $_REQUEST['standing']) {
	$qry = DBFactory::getDBQuery();
	foreach (config::get('cfg_corpid') as $id) {
		$fromtyp = 'c';
		$fields = array();
		$fields[] = $id;
		$fields[] = intval(substr($_REQUEST['sta_id'], 1));
		$fields[] = $fromtyp;
		$fields[] = substr($_REQUEST['sta_id'], 0, 1);
		$fields[] = str_replace(',', '.', $val);
		$fields[] = slashfix($_REQUEST['comment']);

		$qry->execute('INSERT INTO kb3_standings VALUES (\'' . join("','", $fields) . '\')');
	}
	foreach (config::get('cfg_allianceid') as $id) {
		$fromtyp = 'a';
		$fields = array();
		$fields[] = $id;
		$fields[] = intval(substr($_REQUEST['sta_id'], 1));
		$fields[] = $fromtyp;
		$fields[] = substr($_REQUEST['sta_id'], 0, 1);
		$fields[] = str_replace(',', '.', $val);
		$fields[] = slashfix($_REQUEST['comment']);

		$qry->execute('INSERT INTO kb3_standings VALUES (\'' . join("','", $fields) . '\')');
	}
}
if ($_REQUEST['del']) {
	$totyp = preg_replace('/[^ac]/', '', substr($_REQUEST['del'], 0, 1));
	$toid = intval(substr($_REQUEST['del'], 1));

	$qry = DBFactory::getDBQuery();
	if (config::get('cfg_corpid')) {
		$qry->execute('DELETE FROM kb3_standings WHERE sta_from IN ('
				. join(',', config::get('cfg_corpid'))
				. ') AND sta_from_type=\'c\' AND sta_to=' . $toid
				. ' AND sta_to_type=\'' . $totyp . '\'');
	}
	if (config::get('cfg_allianceid')) {
		$qry->execute('DELETE FROM kb3_standings WHERE sta_from IN ('
				. join(',', config::get('cfg_allianceid'))
				. ') AND sta_from_type=\'a\' AND sta_to=' . $toid
				. ' AND sta_to_type=\'' . $totyp . '\'');
	}
}

$permt = array();
if (config::get("cfg_corpid") || config::get("cfg_allianceid")) {
	$qry = DBFactory::getDBQuery();
	$ent = array();
	if (config::get("cfg_corpid")) {
		$ent[] = 'sta_from IN (' . join(',', config::get("cfg_corpid"))
				. ') AND sta_from_type=\'c\'';
	}
	if (config::get("cfg_allianceid")) {
		$ent[] = 'sta_from IN (' . join(',', config::get("cfg_allianceid"))
				. ') AND sta_from_type=\'a\'';
	}
	$qry->execute('SELECT * FROM kb3_standings WHERE ('
			. join(') OR (', $ent)
			. ') ORDER BY sta_value DESC');

	while ($row = $qry->getRow()) {
		$typ = $row['sta_to_type'];
		$val = sprintf("%01.1f", $row['sta_value']);
		$id = $typ . $row['sta_to'];
		if ($typ == 'a') {
			$alliance = Alliance::getByID($row['sta_to']);
			$text = $alliance->getName();
			$link = edkURI::page('admin_standings', $typ . $row['sta_to'], 'del');
			$permt[$typ][$row['sta_to']] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'], 'id' => $id);
		}
		if ($typ == 'c') {
			$corp = Corporation::getByID($row['sta_to']);
			$text = $corp->getName();
			$link = edkURI::page('admin_standings', $typ . $row['sta_to'], 'del');
			$permt[$typ][$row['sta_to']] = array('text' => $text, 'link' => $link, 'value' => $val, 'comment' => $row['sta_comment'], 'id' => $id);
		}
	}
}
$perm = array();
if ($permt['a']) {
	$perm[] = array('name' => 'Alliances', 'list' => $permt['a']);
}
if ($permt['c']) {
	$perm[] = array('name' => 'Corporations', 'list' => $permt['c']);
}

$smarty->assignByRef('standings', $perm);

$page->addContext($menubox->generate());
$page->setContent($smarty->fetch(get_tpl('admin_standings')));
$page->generate();
