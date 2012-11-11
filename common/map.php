<?php

require_once('kbconfig.php');
require_once('common/includes/globals.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.killboard.php');
require_once('common/includes/class.page.php');
require_once('common/includes/class.event.php');
require_once('common/includes/class.user.php');
require_once('common/includes/class.session.php');
require_once('common/smarty/Smarty.class.php');
require_once('common/includes/class.registry.php');


error_reporting(E_ALL ^ E_NOTICE);


if(isset($_GET['size'])) {
	$img_size=intval($_GET['size']);
		if($img_size > 700) {
		$img_size=700;
	}	
}else {
	$img_size=250;
}

switch ($_GET['mode'])
{
	case "ship":
	case "faction":
	case "activity":
	case "sys":
		$view = new MapView($_GET['mode'], $img_size);
		$view->setSystemID(intval($_GET['sys_id'])); 
		$view->showLines(config::get('map_act_showlines'));
		$view->showSysNames(config::get('map_act_shownames'));
		$view->setOffset(25);		
		$view->generate();
		break; 
	case "na":
		$view = new MapView($_GET['mode'], $img_size);
		$view->generateNA();
		break;
	default: 
		exit;
}

//-------------------------------------------------------------------------
// Antialising functions
// FROM: http://personal.3d-box.com/php/filledellipseaa.php
//-------------------------------------------------------------------------

// Parses a color value to an array.
function color2rgb($color)
{	
	$rgb = array();

	$rgb[] = 0xFF & ($color >> 16);
	$rgb[] = 0xFF & ($color >> 8);
	$rgb[] = 0xFF & ($color >> 0);

	return $rgb;
}

// Parses a color value to an array.
function color2rgba($color)
{	
	$rgb = array();

	$rgb[] = 0xFF & ($color >> 16);
	$rgb[] = 0xFF & ($color >> 8);
	$rgb[] = 0xFF & ($color >> 0);
	$rgb[] = 0xFF & ($color >> 24);

	return $rgb;
}

// Adapted from http://homepage.smc.edu/kennedy_john/BELIPSE.PDF
function imagefilledellipseaa_Plot4EllipsePoints(&$im, $CX, $CY, $X, $Y, $color, $t)
{	
	imagesetpixel($im, $CX+$X, $CY+$Y, $color); //{point in quadrant 1}
	imagesetpixel($im, $CX-$X, $CY+$Y, $color); //{point in quadrant 2}
	imagesetpixel($im, $CX-$X, $CY-$Y, $color); //{point in quadrant 3}
	imagesetpixel($im, $CX+$X, $CY-$Y, $color); //{point in quadrant 4}

	$aColor = color2rgba($color);
	$mColor = imagecolorallocate($im, $aColor[0], $aColor[1], $aColor[2]);
	if ($t == 1)
	{
		imageline($im, $CX-$X, $CY-$Y+1, $CX+$X, $CY-$Y+1, $mColor);
		imageline($im, $CX-$X, $CY+$Y-1, $CX+$X, $CY+$Y-1, $mColor);
	} else {
		imageline($im, $CX-$X+1, $CY-$Y, $CX+$X-1, $CY-$Y, $mColor);
		imageline($im, $CX-$X+1, $CY+$Y, $CX+$X-1, $CY+$Y, $mColor);
	}
	imagecolordeallocate($im, $mColor);
}

// Adapted from http://homepage.smc.edu/kennedy_john/BELIPSE.PDF
function imagefilledellipseaa(&$im, $CX, $CY, $Width, $Height, $color){	
	$XRadius = floor($Width/2);
	$YRadius = floor($Height/2);

	$baseColor = color2rgb($color);

	$TwoASquare = 2*$XRadius*$XRadius;
	$TwoBSquare = 2*$YRadius*$YRadius;
	$X = $XRadius;
	$Y = 0;
	$XChange = $YRadius*$YRadius*(1-2*$XRadius);
	$YChange = $XRadius*XRadius;
	$EllipseError = 0;
	$StoppingX = $TwoBSquare*$XRadius;
	$StoppingY = 0;

	$alpha = 77;	
	$color = imagecolorexactalpha($im, $baseColor[0], $baseColor[1], $baseColor[2], $alpha);
	while ($StoppingX >= $StoppingY) // {1st set of points, y' > -1}
	{
		imagefilledellipseaa_Plot4EllipsePoints($im, $CX, $CY, $X, $Y, $color, 0);
		$Y++;
		$StoppingY += $TwoASquare;
		$EllipseError += $YChange;
		$YChange += $TwoASquare;
		if ((2*$EllipseError + $XChange) > 0)
		{
			$X--;
			$StoppingX -= $TwoBSquare;
			$EllipseError += $XChange;
			$XChange += $TwoBSquare;
		}
		
		// decide how much of pixel is filled.
		$filled = $X - sqrt(($XRadius*$XRadius - (($XRadius*$XRadius)/($YRadius*$YRadius))*$Y*$Y));
		$alpha = abs(90*($filled)+37);
		imagecolordeallocate($im, $color);
		$color = imagecolorexactalpha($im, $baseColor[0], $baseColor[1], $baseColor[2], $alpha);
	}
	// { 1st point set is done; start the 2nd set of points }

	$X = 0;
	$Y = $YRadius;
	$XChange = $YRadius*$YRadius;
	$YChange = $XRadius*$XRadius*(1-2*$YRadius);
	$EllipseError = 0;
	$StoppingX = 0;
	$StoppingY = $TwoASquare*$YRadius;
	$alpha = 77;	
	$color = imagecolorexactalpha($im, $baseColor[0], $baseColor[1], $baseColor[2], $alpha);

	while ($StoppingX <= $StoppingY) // {2nd set of points, y' < -1}
	{
		imagefilledellipseaa_Plot4EllipsePoints($im, $CX, $CY, $X, $Y, $color, 1);
		$X++;
		$StoppingX += $TwoBSquare;
		$EllipseError += $XChange;
		$XChange += $TwoBSquare;
		if ((2*$EllipseError + $YChange) > 0)
		{
			$Y--;
			$StoppingY -= $TwoASquare;
			$EllipseError += $YChange;
			$YChange += $TwoASquare;
		}
		
		// decide how much of pixel is filled.
		$filled = $Y - sqrt(($YRadius*$YRadius - (($YRadius*$YRadius)/($XRadius*$XRadius))*$X*$X));
		$alpha = abs(90*($filled)+37);
		imagecolordeallocate($im, $color);
		$color = imagecolorexactalpha($im, $baseColor[0], $baseColor[1], $baseColor[2], $alpha);
	}
}
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
// END Antialising functions
// FROM: http://personal.3d-box.com/php/filledellipseaa.php
//-------------------------------------------------------------------------

class MapView
{	
	function MapView($mode, $size )
	{
		$this->mode_ = $mode;
		$this->imgwidth_ = $size;
		$this->imgheight_ = $size;
		$this->ly_ = 0.4 / 1000000000000000;
		$this->offset_ = 10;

		$this->linecolor_ =  array(75, 75, 75);
		$this->captioncolor_ = array(110, 110, 110);
		$this->bgcolor_ = array(30, 30, 30);
		$this->normalcolor_ = array(180, 180, 180);
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

	function setSystemID($systemid)
	{
		$this->systemid_ = $systemid;
		$this->regionid2_ = intval($_GET['region_id']);

		$sql = 'select reg.reg_id, reg.reg_name, con.con_id, con.con_name, sys.sys_name, sys.sys_sec
				from kb3_regions reg, kb3_constellations con, kb3_systems sys
				where reg.reg_id = con.con_reg_id
				and con.con_id = sys.sys_con_id';
		
		if( $this->mode_ == "sys" ) {
			$sql .= ' and sys.sys_id = '.$this->systemid_;
		} else {
			$sql .= ' and reg.reg_id = '.$this->regionid2_;
		}

		$qry = new DBQuery();
		$qry->execute($sql);
		$row = $qry->getRow();

		$this->conid_ = $row['con_id'];
		$this->regionid_ = $row['reg_id'];
		$this->conname_ = $row['con_name'];
		$this->regname_ = $row['reg_name'];
		$this->sysname_ = $row['sys_name'];
		$this->syssec_ = $row['sys_sec'];
		
		if(isset($_GET['year'])) {  //if no year is set we need to take actual yeat
			$this->year_ = intval($_GET['year']);
		}
		else
		{
			$this->year_ = date('Y', time());
		}
		$this->month_ = $_GET['month']; //if these two not set you get a funny year overview... 
		if(!isset($_GET['month'])) { // month and week cannot be both present
			$this->week_ = intval($_GET['week']); 
		}
	} 

	function generate() {
		$regioncache = KB_CACHEDIR . '/img/map/'.KB_SITE.'_'.$this->regionid_.'_'.$this->imgwidth_.'.png';  	
		$is_cached =0;	//INIT
		$title_caption=$this->regname_;

		if (file_exists($regioncache)) {
			$cfgttl = '6';
			$ttyl_sec = $cfgttl * 3600;
			$ttl = filemtime($regioncache)+$ttyl_sec;
			
			if ($ttl <= time()) {
				$is_cached = 0;
				unlink($regioncache);
				unlink($regioncache.".txt");
			} else {
				$img = imagecreatefrompng($regioncache);
				$is_cached = 1;
			}
		}
		
		if ($is_cached == 0) {
			$sql = 'SELECT sys.sys_x, sys.sys_y, sys.sys_z, sys.sys_sec, sys.sys_id, sys.sys_name, sys.sys_id, sjp.sjp_to, con.con_id, con.con_name, reg.reg_id, reg.reg_name, reg.reg_x, reg.reg_z
				FROM kb3_systems sys
				LEFT JOIN kb3_system_jumps sjp ON sys.sys_id = sjp.sjp_from
				JOIN kb3_constellations con ON con.con_id = sys.sys_con_id
				JOIN kb3_regions reg ON reg.reg_id = con.con_reg_id';

			if( $this->mode_ == "sys" ) {
				$sql .= " and reg.reg_id = '".$this->regionid_ ."'";
			} else {
				$sql .= " and reg.reg_id = ".$this->regionid2_;
			}

			$qry = new DBQuery();
			$qry->execute($sql) or die($qry->getErrorMsg());

			if (!$img) $img = imagecreatetruecolor($this->imgwidth_, $this->imgheight_);
			$white = imagecolorallocate($img, 255, 255, 255);
			$red = imagecolorallocate($img, 255, 0, 0);
			
			if(config::get('map_act_cl_bg')) {
				$bcolor = explode( "," , config::get('map_act_cl_bg'));
				mapview::setbgcolor($bcolor[0],$bcolor[1],$bcolor[2]);
			}
			
			$bgcolor = imagecolorallocate($img, $this->bgcolor_[0], $this->bgcolor_[1], $this->bgcolor_[2]);
			imagefilledrectangle($img, 0, 0, $this->imgwidth_, $this->imgheight_, $bgcolor);

			$color = $white;

			$fov = 0; //INIT

			$i = 0;
			$minx = 0;
			$minz = 0;
			$maxx = 0;
			$maxz = 0;
			$mini = 0;
			$maxi = 0;
			$pi = 0;
			$sc = 0;

			$systems=array();

			while ($row = $qry->getRow()) //start while
			{
				$i = $row['sys_id'];

				$systems[] = $i;
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

			// draw lines
			if ($this->showlines_)
			{
				
				if(config::get('map_act_cl_line')) {
					$lcolor = explode( "," , config::get('map_act_cl_line'));
					mapview::setlinecolor($lcolor[0],$lcolor[1],$lcolor[2]);
				}

				foreach($systems as $n) {
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

			//----------------------------------------------
			// draw systems
			//----------------------------------------------
			foreach($systems as $n) {
				if(config::get('map_act_cl_normal')) {
					$scolor = explode( "," , config::get('map_act_cl_normal'));
					mapview::setnormalcolor($scolor[0],$scolor[1],$scolor[2]);
				}
				
				$px = round($this->offset_ + ($sys[$n][0] - $minx) * $xscale);
				$py = round($this->offset_ + ($sys[$n][1] - $minz) * $yscale);

				$color = imagecolorallocate($img, $this->normalcolor_[0], $this->normalcolor_[1], $this->normalcolor_[2]);
				
				imagefilledellipseaa($img, $px, $py, 4, 4, $color); //gm this is the white system dot, shrunkg it down from 6x6 size
				
				$n++;
			}
			
			imagepng($img, $regioncache);
			
			$fp = fopen($regioncache.".txt","w"); // writting offset data into file
			if($fp){						  // This step is needed cause when the image is cached only 1 system is queried from the DB 
				// therefor it is impossible to calculcate the offset again. 
				fwrite($fp, $maxx."\n".$minx."\n".$maxz."\n".$minz);

			}
			fclose($fp);
		}

		//---------------------------------------------------------------------------------------
		//System Highlight starts here
		//---------------------------------------------------------------------------------------
		if ($this->mode_ == "sys")
		{
			$sql = "SELECT sys.sys_x, sys.sys_y, sys.sys_z, sys.sys_sec, sys.sys_id, sys.sys_name, sys.sys_id
					FROM kb3_systems sys
					WHERE sys.sys_id = '".$this->systemid_."'";
			
			$qry = new DBQuery();
			$qry->execute($sql) or die($qry->getErrorMsg());
			
			$fov = 0; //INIT

			$row = $qry->getRow(); 
			
			$x = $row['sys_x'] * $this->ly_;
			$z = $row['sys_z'] * $this->ly_;

			$sys[0] = $x;
			$sys[1] = $z;
			$sys[4] = $row['sys_id'];
			$sys[5] = $row['sys_name'];
			$sys[6] = $row['sys_sec'];
			
			$fp = fopen($regioncache.".txt","r"); // getting offset data from file
			if($fp == FALSE) {
				echo "failt to open $regioncache";exit;
			}
			
			$maxx = fgets($fp);
			$minx = fgets($fp);
			$maxz = fgets($fp);
			$minz = fgets($fp);
			
			fclose($fp);
			$dx = abs($maxx - $minx);
			$dz = abs($maxz - $minz);
			$xscale = 1 / ($dx / ($this->imgwidth_ - ($this->offset_ * 2)));
			$yscale = 1 / ($dz / ($this->imgheight_ - ($this->offset_ * 2)));
			$px = round($this->offset_ + ($sys[0] - $minx) * $xscale);
			$py = round($this->offset_ + ($sys[1] - $minz) * $yscale);

			if(config::get('map_act_cl_hl2')) { 
				$hscolor = explode( "," , config::get('map_act_cl_hl2'));
				$color = imagecolorallocate($img,$hscolor[0],$hscolor[1],$hscolor[2]);
			} else {
				$color = imagecolorallocate($img, '255', '0', '0');
			}
			
			$tlen = 5 * strlen($sys[5]);
			if (($px + $tlen > ($this->imgwidth_ - 20)))
			$sx = $px - $tlen;
			else
			$sx = $px + 5;
			if (($py + 5 > ($this->imgheight_ - 20)))
			$sy = $py - 5;
			else
			$sy = $py + 5;

			imagestring($img, 1, $sx, $sy, $sys[5], $color);
			
			imagefilledellipseaa($img, $px, $py, 6, 6, $color);			
		}
		
		//---------------------------------------------------------------------------------------
		// Activity starts here
		//---------------------------------------------------------------------------------------
		if ($this->mode_ == "activity")
		{
			$kills=0; //INIT			
			$overall_kill=0;						
			$color = imagecolorallocate($img, $this->normalcolor_[0], $this->normalcolor_[1], $this->normalcolor_[2]);
			
			$paint_name=config::get('map_act_shownames');
			

			$sql2 = "SELECT sys.sys_name, sys.sys_x, sys.sys_y, sys.sys_z, sys.sys_sec, sys.sys_id, count( DISTINCT kll.kll_id ) AS kills
				FROM kb3_systems sys, kb3_kills kll, kb3_inv_detail inv, kb3_constellations con, kb3_regions reg
				WHERE kll.kll_system_id = sys.sys_id
				AND inv.ind_kll_id = kll.kll_id";

			if(count(config::get('cfg_allianceid'))) {
				$orargs[] = 'inv.ind_all_id IN ('.implode(",", config::get('cfg_allianceid')).") ";
			}
			if(count(config::get('cfg_corpid'))) {
				$orargs[] = 'inv.ind_crp_id IN ('.implode(",", config::get('cfg_corpid')).") ";
			}
			if(count(config::get('cfg_pilotid'))) {
				$orargs[] = 'inv.ind_plt_id IN ('.implode(",", config::get('cfg_pilotid')).") ";
			}

			$sql2 .= " AND (".implode(" OR ", $orargs).")";


			if(isset($this->week_)) {
				$sql2 .= "  and date_format( kll.kll_timestamp, \"%u\" ) = ".$this->week_;
			}
			if(isset($this->month_)) {
				$sql2 .= "  and date_format( kll.kll_timestamp, \"%m\" ) = ".$this->month_;
			}
			
			$sql2 .= "  AND date_format( kll.kll_timestamp, \"%Y\" ) = ".$this->year_."
					AND con.con_id = sys.sys_con_id
					AND reg.reg_id = con.con_reg_id
					AND reg.reg_id =".$this->regionid2_."
					GROUP BY sys.sys_name
					ORDER BY kills desc";


			$qry2 = new DBQuery();
			$qry2->execute($sql2) or die($qry2->getErrorMsg());
			while ($row2 = $qry2->getRow())
			{
				
				$kills = $row2['kills'];
				$overall_kill = $overall_kill + $kills;
				
				//OFFSET CALCULATION
				$x = $row2['sys_x'] * $this->ly_;
				$z = $row2['sys_z'] * $this->ly_;

				$sys[0] = $x;
				$sys[1] = $z;
				$sys[4] = $row2['sys_id'];
				$sys[5] = $row2['sys_name'];
				$sys[6] = $row2['sys_sec'];
				
				$fp = fopen($regioncache.".txt","r"); // getting offset data from file
				
				$maxx = fgets($fp);
				$minx = fgets($fp);
				$maxz = fgets($fp);
				$minz = fgets($fp);
				
				fclose($fp);
				
				$dx = abs($maxx - $minx);
				$dz = abs($maxz - $minz);
				$xscale = 1 / ($dx / ($this->imgwidth_ - ($this->offset_ * 2)));
				$yscale = 1 / ($dz / ($this->imgheight_ - ($this->offset_ * 2)));
				$px = round($this->offset_ + ($sys[0] - $minx) * $xscale);
				$py = round($this->offset_ + ($sys[1] - $minz) * $yscale);		

				if ($kills==1) { // If there is only one kill we use normal highlight color
					$ratio=1;
					
					if(config::get('map_act_cl_hl')) {
						$hscolor = explode( "," , config::get('map_act_cl_hl'));
						$color = imagecolorallocate($img,$hscolor[0],$hscolor[1],$hscolor[2]);
					}
					else {
						$color = imagecolorallocate($img, '255', '209', '57');
					}
				}
				else { //more then one kill...
					$ratio = $kills + 5; // ...then we add a bit to the ratio
					
					if(config::get('map_act_cl_hl2')) { // Set the color to Highlight 2 and the sphere color with alpha
						$hscolor = explode( "," , config::get('map_act_cl_hl2'));
						$color = imagecolorallocate($img,$hscolor[0],$hscolor[1],$hscolor[2]);
						$color4 = imagecolorresolvealpha($img,$hscolor[0],$hscolor[1],$hscolor[2],'117');
					}
					else {
						$color = imagecolorallocate($img, '255', '0', '0');
						$color4 = imagecolorresolvealpha($img, 255,0,0,117);
					}									
				}
				
				if($ratio > 100) { //now we limit the max-size of the sphere so it doesnt grow to big
					$ratio = 100;
				}
				
				imagefilledellipse($img, $px, $py, $ratio, $ratio, $color4); //paint the sphere -- can not use AA function cause it doesnt work with alpha
				imagefilledellipseaa($img, $px, $py, 6, 6, $color); // use AA function to paint the system dot
				
				if($ratio > 10) { // extend the sphere
					$ratio2=$ratio - 10;
					imagefilledellipse($img, $px, $py, $ratio2, $ratio2, $color4);
				}	 
				if($ratio > 20) { // add another inner layer to the sphere, if it gets big enough
					$ratio3=$ratio - 20;
					imagefilledellipse($img, $px, $py, $ratio3, $ratio3, $color4);
				}	 
				
				if ( $paint_name==1) //if set it will paint the names of systems with activity. But gets a bit overcrowed on smaller sizes...
				{
					$tlen = 5 * strlen($sys[5]);
					if (($px + $tlen > ($this->imgwidth_ - 20)))
					$sx = $px - $tlen;
					else
					$sx = $px + 5;
					if (($py + 5 > ($this->imgheight_ - 20)))
					$sy = $py - 5;
					else
					$sy = $py + 5;

					imagestring($img, 1, $sx, $sy, $sys[5], $color);
					
				}	
			}	 
		}
		
		//---------------------------------------------------------------------------------------
		// Ship / Faction starts here
		//---------------------------------------------------------------------------------------

		if ($this->mode_ == "ship" || $this->mode_ == "faction" ) {
			$kills=0; //INIT			
			$overall_kill=0;						
			$color = imagecolorallocate($img, $this->normalcolor_[0], $this->normalcolor_[1], $this->normalcolor_[2]);
			$paint_name=config::get('map_act_shownames');

			$sql2 = 'select sys.sys_x, sys.sys_y, sys.sys_z, sys.sys_sec,
					sys.sys_id, sys.sys_name, sys.sys_id, con.con_id,
						con.con_name, reg.reg_id, reg.reg_name,
					reg.reg_x, reg.reg_z
					from kb3_systems sys,
					kb3_constellations con, kb3_regions reg
					where con.con_id = sys.sys_con_id
					and reg.reg_id = con.con_reg_id';

			$sql2 .= " and reg.reg_id = ".$this->regionid2_;

			$xml_kills = $this->update_kill_cache();
			

			$qry2 = new DBQuery(); 
			$qry2->execute($sql2) or die($qry2->getErrorMsg());
			while ($row2 = $qry2->getRow())
			{
				$paint=0;
				foreach ($xml_kills as $key => $value) {
					if ($row2['sys_id'] == $key) {
						$kills=$value;
						$paint=1;
					}
				}
				
				if ($paint == 1) {
					$overall_kill = $overall_kill + $kills;
				
					//OFFSET CALCULATION
					$x = $row2['sys_x'] * $this->ly_;
					$z = $row2['sys_z'] * $this->ly_;

					$sys[0] = $x;
					$sys[1] = $z;
					$sys[4] = $row2['sys_id'];
					$sys[5] = $row2['sys_name'];
					$sys[6] = $row2['sys_sec'];
					
					$fp = fopen($regioncache.".txt","r"); // getting offset data from file
					
					$maxx = fgets($fp);
					$minx = fgets($fp);
					$maxz = fgets($fp);
					$minz = fgets($fp);
					
					fclose($fp);
					
					$dx = abs($maxx - $minx);
					$dz = abs($maxz - $minz);
					$xscale = 1 / ($dx / ($this->imgwidth_ - ($this->offset_ * 2)));
					$yscale = 1 / ($dz / ($this->imgheight_ - ($this->offset_ * 2)));
					$px = round($this->offset_ + ($sys[0] - $minx) * $xscale);
					$py = round($this->offset_ + ($sys[1] - $minz) * $yscale);		

					if ($kills==1) { // If there is only one kill we use normal highlight color
						$ratio=1;
						
						if(config::get('map_act_cl_hl')) {
							$hscolor = explode( "," , config::get('map_act_cl_hl'));
							$color = imagecolorallocate($img,$hscolor[0],$hscolor[1],$hscolor[2]);
						}
						else {
							$color = imagecolorallocate($img, '255', '209', '57');
						}
					}
					else { //more then one kill...
						$ratio = $kills + 5; // ...then we add a bit to the ratio
						
						if(config::get('map_act_cl_hl2')) { // Set the color to Highlight 2 and the sphere color with alpha
							$hscolor = explode( "," , config::get('map_act_cl_hl2'));
							$color = imagecolorallocate($img,$hscolor[0],$hscolor[1],$hscolor[2]);
							$color4 = imagecolorresolvealpha($img,$hscolor[0],$hscolor[1],$hscolor[2],'117');
						}
						else {
							$color = imagecolorallocate($img, '255', '0', '0');
							$color4 = imagecolorresolvealpha($img, 255,0,0,117);
						}									
					}
					
					if($ratio > 100) { //now we limit the max-size of the sphere so it doesnt grow to big
						$ratio = 100;
					}
					
					imagefilledellipse($img, $px, $py, $ratio, $ratio, $color4); //paint the sphere -- can not use AA function cause it doesnt work with alpha
					imagefilledellipseaa($img, $px, $py, 6, 6, $color); // use AA function to paint the system dot
					
					if($ratio > 10) { // extend the sphere
						$ratio2=$ratio - 10;
						imagefilledellipse($img, $px, $py, $ratio2, $ratio2, $color4);
					}	 
					if($ratio > 20) { // add another inner layer to the sphere, if it gets big enough
						$ratio3=$ratio - 20;
						imagefilledellipse($img, $px, $py, $ratio3, $ratio3, $color4);
					}	 
					
					if ( $paint_name==1) //if set it will paint the names of systems with activity. But gets a bit overcrowed on smaller sizes...
					{
						$tlen = 5 * strlen($sys[5]);
						if (($px + $tlen > ($this->imgwidth_ - 20)))
						$sx = $px - $tlen;
						else
						$sx = $px + 5;
						if (($py + 5 > ($this->imgheight_ - 20)))
						$sy = $py - 5;
						else
						$sy = $py + 5;

						imagestring($img, 1, $sx, $sy, $sys[5].'('.$kills.')', $color);
					}	
				}	 
			}
		}

		//---------------------------------------------------------------------------------------
		// Activity end here
		//---------------------------------------------------------------------------------------

		// Draw the region name and total kill count.
		if(config::get('map_act_cl_capt')) {
			$scolor = explode( "," , config::get('map_act_cl_capt'));
			mapview::setcaptcolor($scolor[0],$scolor[1],$scolor[2]);
		}
		$captioncolor = imagecolorallocate($img, $this->captioncolor_[0], $this->captioncolor_[1], $this->captioncolor_[2]);

		switch ( $this->mode_ ) {
			case "ship":
			case "faction":
				$title_kill = "Total Kills in the last hour: ".$overall_kill;
				break;
			case "activity":
				if(isset($this->week_)) {
					$title_kill = "Total Kills in Week ".$this->week_.": ".$overall_kill;
				}
				if(isset($this->month_)) {
					$title_kill = "Total Kills in ".date('F',mktime(0,1,0,$this->month_,1,$this->year_)).": ".$overall_kill;
				}
				break;
			default:
				$title_kill = '';
				break;
		}

		$str_loc = $this->imgheight_ - 10;
		imagestring($img, 1, 2, $str_loc, $title_kill, $captioncolor);
		imagestring($img, 1, 2, 2, $title_caption, $captioncolor);

		if ( $this->mode_ == 'ship' || $this->mode_ == 'faction' ) {
			clearstatcache();
			$filetime = filemtime($this->killcache);
			$stringpos = $this->imgwidth_ -  (strlen("Data from: ".date("G:i Y-m-d", $filetime))*5) -5;
			imagestring($img, 1, $stringpos, 2,  "Data from: ".date("G:i Y-m-d", $filetime), $captioncolor);
		}
	
		header("Content-type: image/png");
		imagepng($img);
	}

	function generateNA() {
		global $themename;
		$img = imagecreatetruecolor($this->imgwidth_, $this->imgheight_);
		$image = imagecreatefrompng ( "themes/" . $themename. "/img/na.png" );
		$info = getimagesize("themes/" . $themename. "/img/na.png");
		imagecopyresized ( $img, $image, 0, 0, 0, 0, $this->imgwidth_, $this->imgwidth_, $info[0], $info[1] );

		header("Content-type: image/png");
		imagepng($img,NULL,9,PNG_ALL_FILTERS);
	}

	function update_kill_cache() {
		switch ($this->mode_)
		{
			case "ship":
				$this->killcache = KB_CACHEDIR.'/img/map/kills.txt';  
				$var_xml = 'shipKills';
				break;
			case "faction":
				$this->killcache = KB_CACHEDIR.'/img/map/factionk.txt';  
				$var_xml = 'factionKills';
				break; 
			default: 
				exit;
		}
		
		if (!file_exists($this->killcache))
		{
			$ttl = 0;
		} else {
			$filetime = filemtime($this->killcache);
			$ttl = $filetime+3600;	
		}

		if ($ttl < time()) {
			$count="1";
			$datastring = "";
			$fail=0;

			$url     = "https://api.eveonline.com/map/Kills.xml.aspx";
			$data_curl = array('version'     => 2);
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_curl));

			$curl_error = curl_errno($ch);

			if ($curl_error != 0) {
				$fail = 1;
				
				echo('CURL ERROR : '.$curl_error);
				return false;
			}

			try {
				$xml = new SimpleXMLElement(curl_exec($ch));
			} catch (Exception $e) {
				echo('Error: '.$e->getMessage());
				return false;
			}
			curl_close($ch);
			
			foreach ($xml->xpath('//error') as $error) {
				echo($error); 
				$fail = 1;
			}

			$i=0;
			foreach ($xml->xpath('//row') as $xml) {
				$datastring 		.= $xml['solarSystemID'].",".$xml[$var_xml].",\n";
			}

			if($fail != 1) {
				$fp = fopen($this->killcache,"w"); // writting offset data into file
				if($fp){
					fwrite($fp, $datastring);
				}
				fclose($fp); 
			}
		}

		$fp2 = fopen($this->killcache,"r"); // getting offset data from file
		
		while (!feof($fp2)) {
			$data_temp = explode(",",fgets($fp2));
			if($data_temp[1] > 0) {
				$xml_kills[$data_temp[0]] = $data_temp[1];
			}	
		}
		
		fclose($fp2);
		
		return $xml_kills;
	}
}


?>