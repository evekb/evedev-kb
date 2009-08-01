<?php
class PageSplitter
{
    function PageSplitter($max, $split)
    {
        $this->max_ = $max;
        $this->split_ = $split;
    }

    function getSplit()
    {
        return $this->split_;
    }

    function generate()
    {
        if ($this->max_ / $this->split_ <= 1)
            return;

        $html = "<br><p><b>[</b> Page: ";
        $endpage = ceil($this->max_ / $this->split_);
        if ($_GET['page'])
        {
            $url = preg_replace("/&page=([0-9]+)/", "",
                $_SERVER['QUERY_STRING']);
			$url =preg_replace("/&/", "&amp;", $url);
            $page = $_GET['page'];
        }
        else
        {
            $url = $_SERVER['QUERY_STRING'];
			$url =preg_replace("/&/", "&amp;", $url);
            $page = 1;
        }
        for ($i = 1; $i <= $endpage; $i++)
        {
            if ($i != $page)
            {
                if ($i == 1 || $i == $endpage || (($i >= $page - 1 && $i <= $page + 1)))
                {
                    if ($i != 1)
                        $html .= "<a href=\"?".$url."&amp;page=".$i."\">".$i."</a>&nbsp;";
                    else
                        $html .= "<a href=\"?".$url."\">".$i."</a>&nbsp;";
                }elseif ($i < $page && !$dotted)
                {
                    $dotted = true;
                    $html .= "<b>..&nbsp;</b>";
                }elseif ($i > $page && !$ldotted)
                {
                    $ldotted = true;
                    $html .= "<b>..&nbsp;</b>";
                }
            }
            else
                $html .= "<b>".$i."</b>&nbsp;";
        }
        $html .= "<b>]</b>";
        return $html;
    }
}
?>