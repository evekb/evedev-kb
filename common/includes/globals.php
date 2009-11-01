<?php
// current subversion revision
$svnrevision = '$Revision$';
$svnrevision = trim(substr($svnrevision, 10, strlen($svnrevision)-11));

define('SVN_REV', "Dev ".$svnrevision);

define('LATEST_DB_UPDATE',"012");

define('KB_CACHEDIR', 'cache');
define('KB_PAGECACHEDIR', KB_CACHEDIR.'/page');
define('KB_MAILCACHEDIR', KB_CACHEDIR.'/mails');
define('KB_QUERYCACHEDIR', KB_CACHEDIR.'/SQLqueries');

// current version: major.minor.sub
// unpair numbers for minor = development version
define('KB_VERSION', '2.1 alpha');
define('KB_RELEASE', '(Apocrypha)');

// add new corporations here once you've added the logo to img/corps/
$corp_npc = array('Guristas', 'Serpentis Corporation', 'Sansha\'s Nation', 'CONCORD',
	'Mordus Legion', 'Blood Raider', 'Archangels', 'Guardian Angels', 'True Power');

function shorten($shorten, $by = 22)
{
	if (strlen($shorten) > $by)
	{
		$s = substr($shorten, 0, $by) . "...";
	}
	else $s = $shorten;

	return $s;
}

function slashfix($fix)
{
	return addslashes(stripslashes($fix));
}

function roundsec($sec)
{
	if ($sec <= 0)
		$s = 0.0;
	else
		$s = $sec;

	return number_format(round($s, 1), 1);
}
//! Check if a version of this template exists in this theme or for the igb.

/*! If client is igb check if theme has an igb version. If not check in default
 *  theme for one. If client is not igb check if the theme has the template.
 *  If not then again return the default template.
 *
 *  \param $name string containing the name of the template.
 */
function get_tpl($name)
{
	if(config::get('theme_name')== 'default')
	{
		if (IS_IGB)
		{
			if (file_exists('./themes/default/templates/igb_'.$name.'.tpl'))
			{
				return 'igb_'.$name.'.tpl';
			}
		}
		return $name.'.tpl';
	}
	if (IS_IGB)
	{
		if(file_exists('./themes/'.config::get('theme_name').'/templates/igb_'.$name.'.tpl'))
		{
			return 'igb_'.$name.'.tpl';
		}
		elseif(file_exists('./themes/default/templates/igb_'.$name.'.tpl'))
		{
			return '../../default/templates/igb_'.$name.'.tpl';
		}
	}
	if(file_exists('./themes/'.config::get('theme_name').'/templates/'.$name.'.tpl'))
	{
		return $name.'.tpl';
	}
	return '../../default/templates/'.$name.'.tpl';
}

// this is currently only a wrapper but might get
// timestamp adjustment options in the future
function kbdate($format, $timestamp = null)
{
	if ($timestamp === null)
	{
		$timestamp = time();
	}

	if (config::get('date_gmtime'))
	{
		return gmdate($format, $timestamp);
	}
	return date($format, $timestamp);
}

function getYear()
{
	$test = kbdate('o');
	if ($test == 'o')
	{
		$test = kbdate('Y');
	}
	return $test;
}

//! Return start date for the given week, month, year or date.

/*!
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeStartDate($week = 0, $year = 0, $month = 0, $startweek = 0, $startdate = 0)
{
		$qstartdate=0;
		if(intval($year)>0)
		{
			if($week)
			{
				if ($week == 1)
					{$qstartdate = strtotime($year.'-1-1 00:00 UTC');}
				else
					{$qstartdate = strtotime('last Monday +'.($week-1).' weeks UTC', strtotime($year.'-1-1'));}
			}
			else if($month)
				{$qstartdate = strtotime($year.'-'.$month.'-1 00:00 UTC');}
			else if($startweek)
			{
				if ($startweek == 1) {$qstartdate = strtotime($year.'-1-1 00:00 UTC');}
				else{$qstartdate = strtotime('last Monday +'.($week-1).' weeks UTC', strtotime($year.'-1-1'));}
			}
			else
				{$qstartdate = strtotime($year.'-1-1 00:00 UTC');}
		}
		//If set use the latest startdate and earliest enddate set.
		if($startdate && $qstartdate < strtotime($startdate." UTC")) $qstartdate = strtotime($startdate." UTC");
		return $qstartdate;
}

//! Return end date for the given week, month, year or date.

/*!
 *  Priority order of date filters:
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeEndDate($week = 0, $year = 0, $month = 0, $enddate = 0)
{
		if($year)
		{
			if($week)
			{
				if ($week == 53)
					$qenddate = strtotime(($year).'-12-31 23:59 UTC');
				else
					$qenddate = strtotime('last Monday +'.($week).' weeks -1 minute UTC', strtotime($year.'-1-1'));
			}
			else if($month)
			{
				if($month == 12) $qenddate = strtotime(($year).'-12-31 23:59 UTC');
				else $qenddate = strtotime(($year).'-'.($month + 1).'-1 00:00 - 1 minute UTC');
			}
			else
				$qenddate = strtotime(($year).'-12-31 23:59 UTC');
		}
		//If set use the earliest enddate.
		if($enddate && (!$qenddate || ($qenddate && $qenddate > strtotime($enddate." UTC")))) $qenddate = strtotime($enddate." UTC");

		return $qenddate;
}
