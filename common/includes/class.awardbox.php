<?php
/*
 * $Date: 2010-09-04 13:00:51 +1000 (Sat, 04 Sep 2010) $
 * $Revision: 926 $
 * $HeadURL: https://evedev-kb.googlecode.com/svn/trunk/common/includes/class.box.php $
 */

//! Create a box to display TopList awards.
class AwardBox
{
    //! Create an AwardBox from the given TopList and descriptions.
    function AwardBox($list, $title, $comment, $entity, $award)
    {
        $this->toplist_ = $list;
        $this->title_ = $title;
        $this->comment_ = $comment;
        $this->entity_ = $entity;
        $this->award_ = $award;
    }
    //!! Generate the output html from the template file.
    function generate()
    {
        global $smarty;

        $rows = array();
        $max = 0;

        for ($i = 1; $i < 11; $i++)
        {
            $row = $this->toplist_->getRow();
            if ($row)
            {
                array_push($rows, $row);
            }
            if ($row['cnt'] > $max)
            {
                $max = $row['cnt'];
            }
        }

        if (!$rows[0]['plt_id'])
        {
            return;
        }

        $pilot = new Pilot($rows[0]['plt_id']);
        $smarty->assign('title', $this->title_);
        $smarty->assign('pilot_portrait', $pilot->getPortraitURL(64));
        $smarty->assign('award_img', IMG_URL."/awards/".$this->award_.".png");
        $smarty->assign('url', "?a=pilot_detail&amp;plt_id=".$rows[0]['plt_id'] );
        $smarty->assign('name', $pilot->getName() );

        $bar = new BarGraph($rows[0]['cnt'], $max, 60);
        $smarty->assign('bar', $bar->generate());
        $smarty->assign('cnt', $rows[0]['cnt']);

        for ($i = 2; $i < 11; $i++)
        {
            if (!$rows[$i - 1]['plt_id'])
            {
                break;
            }
            if(!$rows[$i - 1]['plt_name'])
            {
                $pilot = new Pilot($rows[$i - 1]['plt_id']);
                $pilotname = $pilot->getName();
            }
            else $pilotname = $rows[$i - 1]['plt_name'];
            $bar = new BarGraph($rows[$i - 1]['cnt'], $max, 60);
            $top[$i] = array('url'=> "?a=pilot_detail&amp;plt_id=".$rows[$i - 1]['plt_id'], 'name'=>$pilotname, 'bar'=>$bar->generate(), 'cnt'=>$rows[$i - 1]['cnt']);
        }

        $smarty->assign('top', $top);
        $smarty->assign('comment', $this->comment_);
        return $smarty->fetch(get_tpl('award_box'));
    }
}
?>