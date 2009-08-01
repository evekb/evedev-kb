<?php
require_once("class.graph.php");
//! Create a box to display information in.

//! Box will contain a title, an icon and an array of items.
class Box
{
    //! Create a box and set the title.
    function Box($title = '')
    {
        $this->title_ = $title;
        $this->box_array = array();
    }

    //! Set the Icon.
    function setIcon($icon)
    {
        $this->icon_ = $icon;
    }

    //! Add something to the array that we send to smarty later.
    //! Types can be caption, img, link, points. Only links need all 3 attributes
    function addOption($type, $name, $url = '')
    {
        $this->box_array[] = array('type' => $type, 'name' => $name, 'url' => $url);
    }
    //! Generate the html from the template.
    function generate()
    {
        global $smarty;

        $smarty->assign('count', count($this->box_array));
        if ($this->icon_)
        {
            $smarty->assign('icon', IMG_URL."/".$this->icon_);
        }
        $smarty->assign('title', $this->title_ );
        $smarty->assign('items', $this->box_array);

        return $smarty->fetch(get_tpl('box'));
    }
}
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
        $smarty->assign('award_img', IMG_URL."/awards/".$this->award_.".gif");
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