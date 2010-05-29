<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */


class BarGraph
{
    function BarGraph($value = 0, $max = 100, $width = 75, $text = "")
    {
        $this->value_ = $value;
        $this->width_ = $width;
        $this->text_ = $text;
        $this->max_ = $max;

        $this->class_ = "bar";
    }

    function generate()
    {
        if ($this->text_ == "") $this->text_ = "&nbsp;";
        if ($this->value_)
            $width = $this->width_ / ($this->max_ / $this->value_);
        else
            $width = 0;
        
        global $smarty;
        $smarty->assign('class', $this->class_);
        $smarty->assign('width', $width);
        $smarty->assign('maxwidth', $this->width_);
        $smarty->assign('text', $this->text_);
        
        return $smarty->fetch(get_tpl("bargraph"));
    }

    function setLow($low, $class)
    {
        if ($this->value_ <= $low)
            $this->class_ = "bar-".$class;
    }
}
?>