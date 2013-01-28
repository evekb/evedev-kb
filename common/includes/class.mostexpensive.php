<?php
	if(!defined("KB_SITE")) die ("Go Away!");

	class mostexpensive
	{
		public static $week = 0;
		public static $month = 0;
		public static $year = 0;
		const NUM_DISPLAYED = 6;

		public static function GenerateRow($pods = false, $header = true)
		{
			global $smarty;
			$klist = new KillList();
			$klist->setOrdered(true);
			$klist->setOrderBy("kll_isk_loss DESC");
			if( $pods == false ) {
				$klist->setPodsNoobShips(false);
			} else {
				$klist->addVictimShipClass(2);
			}
			$klist->setLimit(self::NUM_DISPLAYED);
	
			if(isset($_GET["w"])) self::$week = intval($_GET["w"]);
			if(isset($_GET["m"])) self::$month = intval($_GET["m"]);
			if(isset($_GET["y"])) self::$year = intval($_GET["y"]);
			self::setTime(self::$week, self::$year, self::$month);
			$view = preg_replace('/[^a-zA-Z0-9_-]/','',$_GET['view']);
			
			if(config::get('show_monthly')) {
				$start = makeStartDate(0, self::$year, self::$month);
				$end = makeEndDate(0, self::$year, self::$month);
				$klist->setStartDate(gmdate('Y-m-d H:i',$start));
				$klist->setEndDate(gmdate('Y-m-d H:i',$end));
				$smarty->assign("displaylist", date('F', mktime(0,0,0,self::$month, 1,self::$year)) . ", " . self::$year);
			} else {
				$klist->setWeek(self::$week);
				$klist->setYear(self::$year);
				$plist->setWeek(self::$week);
				$plist->setYear(self::$year);
				$smarty->assign("displaylist", "Week " . self::$week . ", " . self::$year);
			}

			if(config::get("exp_incloss")) {
				$smarty->assign("displaytype", "Kills and Losses");
				involved::load($klist, "combined");
			} else {
				$smarty->assign("displaytype", "Kills");
				involved::load($klist, "kill");
			}
			
			$kills = array();
			while ($kill = $klist->getKill())
			{
				$kll = array();
				$plt = new Pilot($kill->getVictimID());
				if ($kill->isClassified() && !Session::isAdmin()) {
					$kll['systemsecurity'] = "-";
					$kll['system'] = Language::get("classified");
				} else {
					$kll['systemsecurity'] = $kill->getSolarSystemSecurity();
					$kll['system'] = $kill->getSolarSystemName();
				}
				$kll["id"] = $kill->getID();
				$kll["victim"] = $kill->getVictimName();
				$kll["victimid"] = $kill->getVictimID();
				
				$kll["victimship"] = $kill->getVictimShipName();
				$kll["victimshipid"] = $kill->getVictimShipExternalID();
				$kll["victimshipclass"] = $kill->getVictimShipClassName();
				$kll["victimcorp"] = $kill->getVictimCorpName();
				$kll["victimcorpid"] = $kill->getVictimCorpID();

				$alliance = Alliance::getByID($kill->getVictimAllianceID());
				
				if ( $pods == false ) {
					$kll["victimimageurl"] = $kill->getVictimShipImage(128);
				} else {
					$kll["victimimageurl"] = $plt->getPortraitURL(128);
				}
				$kll["victimallimage"] = $alliance->getPortraitURL(32);
				$kll["victimallname"] = $alliance->getName();
				
				if ((int) number_format($kill->getISKLoss(), 0, "","")>1000000000) {
					$kll["isklost"] = number_format($kill->getISKLoss()/1000000000, 2, ".","") . " Billion";
				} elseif ((int) number_format($kill->getISKLoss(), 0, "","")>1000000) {
					$kll["isklost"] = number_format($kill->getISKLoss()/1000000, 2, ".","") . " Million";
				} else {
					$kll["isklost"] = number_format($kill->getISKLoss(), 0, ".",",");
				}
				
				if (config::get('cfg_allianceid') && in_array($kill->getVictimAllianceID(),config::get('cfg_allianceid'))) {
					$kll["class"] = "kl-loss";
					$kll["classlink"] = '<font color="#AA0000">&bull;</font>';
				} elseif (config::get('cfg_corpid') && in_array($kill->getVictimCorpID(),config::get('cfg_corpid'))) {
					$kll["class"] = "kl-loss";
					$kll["classlink"] = '<font color=\"#AA0000\">&bull;</font>';
				} elseif (config::get('cfg_pilotid') && in_array($kill->getVictimID(),config::get('cfg_pilotid'))) {
					$kll["class"] = "kl-loss";
					$kll["classlink"] = '<font color="#AA0000">&bull;</font>';
				} else {
					$kll["class"] = "kl-kill";
					$kll["classlink"] = '<font color="#00AA00">&bull;</font>';
				}
				$kills[] = $kll;
			}
			if( $header == true) {
				$smarty->assign("header", true);
			} else {
				$smarty->assign("header", false);
			}
			$smarty->assign("killlist", $kills);
			$smarty->assign("width", 100/self::NUM_DISPLAYED);
			return $smarty->fetch(get_tpl('most_expensive_summary'));
		}
		
		public static function display()
		{
			if( config::get('exp_showkill') ) {
				if( config::get('exp_showpod') ) {
					return self::GenerateRow( true, true ) . self::GenerateRow( false, false );
				}
				return self::GenerateRow( false, true );
			} else if( config::get('exp_showpod') ) {
				return self::GenerateRow( true, true );
			}
		}

		public static function setTime($week = 0, $year = 0, $month = 0)
		{
			if ($week) {
				$w = $week;
			} else {
				$w = (int) kbdate("W");
			}
			
			if ($month) {
				$m = $month;
			} else {
				$m = (int) kbdate("m");
			}

			if ($year) {
				$y = $year;
			} else {
				$y = (int) kbdate("o");
			}
			if ($m < 10) $m = "0" . $m;
			if ($w < 10) $w = "0" . $w;
			self::$year = $y;
			self::$month = $m;
			self::$week = $w;
		}
	}
