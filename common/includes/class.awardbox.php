<?php
/**
 * $Date: 2010-09-04 13:00:51 +1000 (Sat, 04 Sep 2010) $
 * $Revision: 926 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.box.php $
 * @package EDK
 */

/**
 * Create a box to display TopList awards.
 * @package EDK
 */
class AwardBox
{
	/**
	 * Create an AwardBox from the given TopList and descriptions.
	 */
	function AwardBox($list, $title, $comment, $entity, $award)
	{
		$this->toplist_ = $list;
		$this->title_ = $title;
		$this->comment_ = $comment;
		$this->entity_ = $entity;
		$this->award_ = $award;
	}

	/**
	 * Generate the output html from the template file.
	 */
	function generate()
	{
		global $smarty;

		$rows = array();
		$max = 0;

		for ($i = 1; $i <= 10; $i++) {
			$row = $this->toplist_->getRow();
			if ($row) {
				$rows[] = $row;
			}
			if ($row['cnt'] > $max) {
				$max = $row['cnt'];
			}
		}

		if (empty($rows)) {
			return;
		}

		$pilot = new Pilot($rows[0]['plt_id']);
		$smarty->assign('title', $this->title_);
		$smarty->assign('pilot_portrait', $pilot->getPortraitURL(64));
		$smarty->assign('award_img',
				config::get('cfg_img')."/awards/".$this->award_.".png");
		$smarty->assign('url', edkURI::build(array('a', 'pilot_detail', true),
						array('plt_id', $rows[0]['plt_id'], true)));
		$smarty->assign('name', $pilot->getName());

		$bar = new BarGraph($rows[0]['cnt'], $max);
		$smarty->assign('bar', $bar->generate());
		$smarty->assign('cnt', $rows[0]['cnt']);

		for ($i = 2; $i < 11; $i++) {
			if (!$rows[$i - 1]['plt_id']) {
				break;
			} else if (!$rows[$i - 1]['plt_name']) {
				$pilot = new Pilot($rows[$i - 1]['plt_id']);
				$pilotname = $pilot->getName();
			} else {
				$pilotname = $rows[$i - 1]['plt_name'];
			}
			$bar = new BarGraph($rows[$i - 1]['cnt'], $max);
			$top[$i] = array(
				'url' => edkURI::build(array('a', 'pilot_detail', true),
						array('plt_id', $rows[$i-1]['plt_id'], true)),
				'name' => $pilotname,
				'bar' => $bar->generate(),
				'cnt' => $rows[$i - 1]['cnt']);
		}

		$smarty->assign('top', $top);
		$smarty->assign('comment', $this->comment_);
		return $smarty->fetch(get_tpl('award_box'));
	}

}