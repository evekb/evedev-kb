<?php
class MapView
{
    function MapView($mode, $size = 200)
    {
        $this->mode_ = $mode;
        $this->imgwidth_ = $size;
        $this->imgheight_ = $size;
        $this->ly_ = 0.4 / 1000000000000000;
        $this->offset_ = 10;

        $this->sys_colors_[0] = array(200, 20, 48);
        $this->sys_colors_[1] = array(200, 20, 48);
        $this->sys_colors_[2] = array(200, 20, 48);
        $this->sys_colors_[3] = array(241, 176, 48);
        $this->sys_colors_[4] = array(200, 200, 48);
        $this->sys_colors_[5] = array(219, 241, 48);
        $this->sys_colors_[6] = array(103, 241, 62);
        $this->sys_colors_[7] = array(83, 241, 114);
        $this->sys_colors_[8] = array(26, 241, 183);
        $this->sys_colors_[9] = array(99, 241, 255);
        $this->sys_colors_[10] = array(99, 241, 255);

        $this->linecolor_ = array(75, 75, 75);
        $this->captioncolor_ = array(110, 110, 110);
        $this->bgcolor_ = array(30, 30, 30);
        $this->normalcolor_ = array(81, 103, 146);
        $this->hlcolor_ = array(200, 200, 200);
    }

    function setbgcolor($r, $g, $b)
    {
        $this->bgcolor_ = array($r, $g, $b);
    }

    function setlinecolor($r, $g, $b)
    {
        $this->linecolor_ = array($r, $g, $b);
    }

    function setcaptcolor($r, $g, $b)
    {
        $this->captioncolor_ = array($r, $g, $b);
    }

    function setnormalcolor($r, $g, $b)
    {
        $this->normalcolor_ = array($r, $g, $b);
    }

    function sethlcolor($r, $g, $b)
    {
        $this->hlcolor_ = array($r, $g, $b);
    }

    function paintSecurity($bool)
    {
        $this->psec_ = $bool;
    }

    function setOffset($offset)
    {
        $this->offset_ = $offset;
    }

    function showLines($enable)
    {
        $this->showlines_ = $enable;
    }

    function showSysNames($enable)
    {
        $this->showsysnames_ = $enable;
    }

    function colorSystems($enable)
    {
        $this->colorsystems_ = $enable;
    }

    function setRegionID($regionid)
    {
        $this->regionid_ = $regionid;
    }

    function setSystemID($systemid)
    {
        $this->systemid_ = $systemid;

        $sql = 'select reg.reg_id, reg.reg_name, con.con_id, con.con_name, sys.sys_name, sys.sys_sec
                from kb3_regions reg, kb3_constellations con, kb3_systems sys
	       where reg.reg_id = con.con_reg_id
	         and con.con_id = sys.sys_con_id
		 and sys.sys_id = '.$this->systemid_;

        $qry = new DBQuery();
        $qry->execute($sql);
        $row = $qry->getRow();

        $this->conid_ = $row['con_id'];
        $this->regionid_ = $row['reg_id'];
        $this->conname_ = $row['con_name'];
        $this->regname_ = $row['reg_name'];
        $this->sysname_ = $row['sys_name'];
        $this->syssec_ = $row['sys_sec'];
    }

    function setTitle($title)
    {
        $this->title_ = $title;
    }

    function secColor($img, $security)
    {
        if ($this->psec_)
        {
            if ($security > 0.05)
            {
                $sec_status = round($security * 10);
                return imagecolorallocate($img, $this->sys_colors_[$sec_status][0],
                                          $this->sys_colors_[$sec_status][1],
                                          $this->sys_colors_[$sec_status][2]);
            }
        }

        return imagecolorallocate($img, $this->normalcolor_[0], $this->normalcolor_[1], $this->normalcolor_[2]);
    }

    function generate()
    {
        $sql = 'select sys.sys_x, sys.sys_y, sys.sys_z, sys.sys_sec,
                   sys.sys_id, sys.sys_name, sys.sys_eve_id, sjp.sjp_to, con.con_id,
          	       con.con_name, reg.reg_id, reg.reg_name,
    		       reg.reg_x, reg.reg_z
                   from kb3_systems sys, kb3_system_jumps sjp,
    		       kb3_constellations con, kb3_regions reg
                   where con.con_id = sys.sys_con_id
                   and reg.reg_id = con.con_reg_id
                   and sjp.sjp_from = sys.sys_eve_id';

        if ($this->mode_ == "map")
        {
            $regioncache = 'cache/map/'.KB_SITE.'_'.$this->regionid_.'_'.$this->imgwidth_.'.png';
            $caption = $this->regname_;
        }
        elseif ($this->mode_ == "region")
        {
            $regioncache = 'cache/map/'.KB_SITE.'_'.$this->conid_.'_'.$this->imgwidth_.'.png';
            $sql .= " and reg.reg_id = ".$this->regionid_;
            $caption = $this->conname_;
        }
        elseif ($this->mode_ == "cons")
        {
            $regioncache = 'cache/map/'.KB_SITE.'_'.$this->systemid_.'_'.$this->imgwidth_.'.png';

            $sql .= " and con.con_id = ".$this->conid_;
            $caption = $this->sysname_." (".roundsec($this->syssec_).")";
        }
        if (file_exists($regioncache))
        {
            header("Content-type: image/png");
            readfile($regioncache);
            return;
        }

        if (true)
        {
            $qry = new DBQuery();
            $qry->execute($sql) or die($qry->getErrorMsg());

            if (!$img) $img = imagecreatetruecolor($this->imgwidth_, $this->imgheight_);
            $white = imagecolorallocate($img, 255, 255, 255);
            $red = imagecolorallocate($img, 255, 0, 0);
            $bgcolor = imagecolorallocate($img, $this->bgcolor_[0], $this->bgcolor_[1], $this->bgcolor_[2]);
            imagefilledrectangle($img, 0, 0, $this->imgwidth_, $this->imgheight_, $bgcolor);

            $color = $white;

            $fov = 0;

            $i = 0;
            $minx = 0;
            $minz = 0;
            $maxx = 0;
            $maxz = 0;
            $mini = 0;
            $maxi = 0;
            $pi = 0;
            $sc = 0;
            while ($row = $qry->getRow())
            {
                $i = $row['sys_eve_id'];
                if ($i < $mini || $mini == 0)
                    $mini = $i;
                if ($i > $maxi || $maxi == 0)
                    $maxi = $i;
                $x = $row['sys_x'] * $this->ly_;
                $z = $row['sys_z'] * $this->ly_;

                if ($x < $minx || $minx == 0)
                    $minx = $x;
                if ($x > $maxx || $maxx == 0)
                    $maxx = $x;
                if ($z < $minz || $minz == 0)
                    $minz = $z;
                if ($z > $maxz || $maxz == 0)
                    $maxz = $z;

                $sys[$i][0] = $x;
                $sys[$i][1] = $z;
                if ($i == $pi || $pi == 0)
                {
                    $sys[$i][2][$sc] = $row['sjp_to'];
                    $sys[$i][3] = $sc++;
                }
                else
                {
                    $sc = 0;
                }
                $sys[$i][4] = $row['sys_id'];
                $sys[$i][5] = $row['sys_name'];
                $sys[$i][6] = $row['sys_sec'];
                $sys[$i][7] = $row['con_id'];
                $sys[$i][8] = $row['con_name'];
                $sys[$i][9] = $row['reg_id'];
                $sys[$i][10] = $row['reg_name'];
                $pi = $i;
            }
            $dx = abs($maxx - $minx);
            $dz = abs($maxz - $minz);
            $xscale = 1 / ($dx / ($this->imgwidth_ - ($this->offset_ * 2)));
            $yscale = 1 / ($dz / ($this->imgheight_ - ($this->offset_ * 2)));
            if ($_REQUEST['debug'] == 'admovrd')
            {
                echo "dx:".$dx." dz:".$dz."<br/> xscale:".$xscale." ".$yscale."<br/>";
                echo "minx:".$minx." maxx:".$maxx."<br/>";
                echo "minz:".$minz." maxz:".$maxz."<br/>".$sql."<br/>\n";
                echo nl2br(print_r($this, 1));
                echo nl2br(print_r($qry, 1));
            }

            // draw lines
            if ($this->showlines_)
            {
                $n = $mini;
                while ($n <= $maxi)
                {
                    $px = $this->offset_ + ($sys[$n][0] - $minx) * $xscale;
                    $py = $this->offset_ + ($sys[$n][1] - $minz) * $yscale;

                    $line_col = imagecolorallocate($img, $this->linecolor_[0], $this->linecolor_[1], $this->linecolor_[2]);

                    for ($m = 0; $m <= $sys[$n][3]; $m++)
                    {
                        $sys_to = $sys[$n][2][$m];

                        if ($sys[$sys_to][4] != "")
                        {
                            $px_to = $this->offset_ + ($sys[$sys_to][0] - $minx) * $xscale;
                            $py_to = $this->offset_ + ($sys[$sys_to][1] - $minz) * $yscale;

                            imageline($img, $px, $py, $px_to, $py_to, $line_col);
                        }
                    }

                    $n++;
                }
            }
            // draw systems
            $n = $mini;
            while ($n <= $maxi)
            {
                $px = round($this->offset_ + ($sys[$n][0] - $minx) * $xscale);
                $py = round($this->offset_ + ($sys[$n][1] - $minz) * $yscale);

                if ($this->mode_ == "map")
                {
                    if ($sys[$n][9] == $this->regionid_)
                        $color = imagecolorallocate($img, $this->hlcolor_[0], $this->hlcolor_[1], $this->hlcolor_[2]);
                    else
                        $color = $this->secColor($img, $sys[$n][6]);
                }
                if ($this->mode_ == "region")
                {
                    if ($sys[$n][7] == $this->conid_)
                        $color = imagecolorallocate($img, $this->hlcolor_[0], $this->hlcolor_[1], $this->hlcolor_[2]);
                    else
                        $color = $this->secColor($img, $sys[$n][6]);
                }
                if ($this->mode_ == "cons")
                {
                    if ($sys[$n][4] == $this->systemid_)
                        $color = imagecolorallocate($img, $this->hlcolor_[0], $this->hlcolor_[1], $this->hlcolor_[2]);
                    else
                        $color = $this->secColor($img, $sys[$n][6]);
                }

                if ($this->showsysnames_)
                {
                    $tlen = 5 * strlen($sys[$n][5]);
                    if (($px + $tlen > ($this->imgwidth_ - 20)))
                        $sx = $px - $tlen;
                    else
                        $sx = $px + 5;
                    if (($py + 5 > ($this->imgheight_ - 20)))
                        $sy = $py - 5;
                    else
                        $sy = $py + 5;

                    imagestring($img, 1, $sx, $sy, $sys[$n][5], $color);
                    // imagettftext ( $img, 6, 0,
                    // $sx,
                    // $sy,
                    // $color,
                    // "../fonts/04B_03__.TTF",
                    // $sys[$n][5] );
                }

                $ed = $xscale * 0.75;
                if ($ed > 5)
                {
                    $ed = 5;
                }
                // bug compat: imagefilledellipse doesnt draw ellipses with 1px size
                // on some installations
                if ($ed < 1)
                {
                    imagesetpixel($img, $px, $py, $color);
                }
                elseif ($ed < 3)
                {
                    imagesetpixel($img, $px, $py, $color);
                    imagesetpixel($img, $px+1, $py, $color);
                    imagesetpixel($img, $px-1, $py, $color);
                    imagesetpixel($img, $px, $py-1, $color);
                    imagesetpixel($img, $px, $py+1, $color);
                }
                else
                {
                    imagefilledellipse($img, $px, $py, $ed, $ed, $color);
                }

                $n++;
            }
        }

        $captioncolor = imagecolorallocate($img, $this->captioncolor_[0], $this->captioncolor_[1], $this->captioncolor_[2]);
        if (strlen($this->title_) > 0)
        {
            $title = $this->title_." ".$caption;
        }
        else
        {
            $title = $caption;
        }

        imagestring($img, 1, 2, 2, $title, $captioncolor);
        if ($this->mode_ == "map")
        {
            imagepng($img, $regioncache);
        }
        // optionally cache constellation maps
        elseif ($this->mode_ == 'region' && config::get('map_region_cache'))
        {
            imagepng($img, $regioncache);
        }
        // cache everything if we are reinforced
        elseif (config::get('is_reinforced'))
        {
            imagepng($img, $regioncache);
        }
        header("Content-type: image/png");
        imagepng($img);
    }
}
?>