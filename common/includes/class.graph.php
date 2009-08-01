<?php

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
        $html = "<div class=".$this->class_." style=\"width: ".$width."px;\">".$this->text_."</div>";
        return $html;
    }

    function setLow($low, $class)
    {
        if ($this->value_ <= $low)
            $this->class_ = "bar-".$class;
    }
}
?>