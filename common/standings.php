<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

$page = new Page();
$page->setTitle('Standings');

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

$permt = array();
while ($row = $qry->getRow()) {
	$typ = $row['sta_to_type'];
	$val = sprintf("%01.1f", $row['sta_value']);
	$id = (int) $row['sta_to'];

	if ($row['sta_value'] > 5) {
		$icon = 'high';
	} else if ($row['sta_value'] > 0) {
		$icon = 'good';
	} else if ($row['sta_value'] >= -5) {
		$icon = 'bad';
	} else {
		$icon = 'horrible';
	}

	if ($typ == 'a') {
		$alliance = Alliance::getByID($id);
		$text = $alliance->getName();
		$pid = $alliance->getUnique();
		$link = edkURI::page('admin_standings', $typ . $row['sta_to'], 'del');
		$permt[$typ][] = array(
			'text' => $text,
			'link' => $link,
			'all_url' => $alliance->getDetailsURL(),
			'all_img' => $alliance->getPortraitURL(32),
			'value' => $val,
			'comment' => $row['sta_comment'],
			'id' => $id,
			'pid' => $pid,
			'typ' => $row['sta_to'],
			'icon' => $icon);
	} else if ($typ == 'c') {
		$corp = Corporation::getByID((int) $row['sta_to']);
		$text = $corp->getName();
		$link = edkURI::page('admin_standings', $typ . $row['sta_to'], 'del');
		$permt[$typ][] = array(
			'text' => $text,
			'link' => $link,
			'crp_url' => $corp->getDetailsURL(),
			'crp_img' => $corp->getPortraitURL(32),
			'value' => $val,
			'comment' => $row['sta_comment'],
			'id' => $id,
			'typ' => $typ,
			'icon' => $icon);
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

$page->setContent($smarty->fetch(get_tpl('standings')));
$page->generate();
