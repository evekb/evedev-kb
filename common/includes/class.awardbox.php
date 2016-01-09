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
    
        protected $limit_;
	/**
	 * Create an AwardBox from the given TopList and descriptions.
	 */
	function __construct($list, $title, $comment, $entity, $award, $limit = 10)
	{
		$this->toplist_ = $list;
		$this->title_ = $title;
		$this->comment_ = $comment;
		$this->entity_ = $entity;
		$this->award_ = $award;
                $this->limit_ = (int)$limit;
	}

	/**
	 * Generate the output html from the template file.
	 */
	function generate()
	{
		global $smarty;

		$rows = array();
		$max = 0;

		for ($i = 1; $i <= $this->limit_; $i++) {
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

		$smarty->assign('title', $this->title_);
		$smarty->assign('pilot_portrait', $this->getEntityImageUrl($rows[0], 64));
		$smarty->assign('award_img',
				config::get('cfg_img')."/awards/".$this->award_.".png");
		$smarty->assign('url', $this->getEntitytUrl($rows[0]));
		$smarty->assign('name', $this->getEntityName($rows[0]));

		$bar = new BarGraph($rows[0]['cnt'], $max);
		$smarty->assign('bar', $bar->generate());
		$smarty->assign('cnt', $rows[0]['cnt']);

		for ($i = 2; $i < $this->limit_+1; $i++) {
			if (!$rows[$i - 1]) 
                        {
				break;
			} 
                        
                        else 
                        {
				$pilotname = $this->getEntityName($rows[$i - 1]);
			}
			$bar = new BarGraph($rows[$i - 1]['cnt'], $max);
			$top[$i] = array(
				'url' => $this->getEntitytUrl($rows[$i-1]),
				'name' => $pilotname,
				'bar' => $bar->generate(),
				'cnt' => $rows[$i - 1]['cnt']);
		}

		$smarty->assign('top', $top);
		$smarty->assign('comment', $this->comment_);
		return $smarty->fetch(get_tpl('award_box'));
	}
        
        
        protected function getEntityImageUrl($row, $size)
        {
            $pilot = new Pilot($row['plt_id']);
            return $pilot->getPortraitURL($size);
        }
        
        protected function getEntityName($row)
        {
            $pilot = new Pilot($row['plt_id']);
            return $pilot->getName();
        }
        
        protected function getEntitytUrl($row)
        {
            return edkURI::build(array('a', 'pilot_detail', true),
						array('plt_id', $row['plt_id'], true));
        }
                

}