<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */


$modInfo['ajcron']['name'] = "AJCron";
$modInfo['ajcron']['abstract'] = "Run scheduled jobs on servers with no cron access.";
$modInfo['ajcron']['about'] = "Core distribution mod.";

require_once('common/xajax/xajax.php');

// make sure we are only running if xajax is enabled
event::register('mod_xajax_initialised', 'ajcron::run');

/**
 * @package EDK
 */
class ajcron
{
    public static function run()
    {
        global $xajax;
        if (isset($xajax))
        {
            if (get_class($xajax) == 'xajax')
            {
                // we're good to go, set up our asynchronous callback
				$xuf = new xajaxUserFunction(array('ajcron', 'ajcron', 'xajax_req'), null);
				$xajax->register(XAJAX_FUNCTION, $xuf);

                // fastest way to check wether we have to run or not
                if (config::get('ajcron_nextrun') < time())
                {
                    // we need to run
                    mod_xajax::xajax();
                    $xajax->configure('waitCursor', false);
                    event::register('page_assembleheader', 'ajcron::insertHTML');
                }
            }
        }
    }

    public static function insertHTML($page)
    {
        $page->addBody('<script type="text/javascript" charset="UTF-8">
            var myLocalCallback = xajax.callback.create(400, 1000);
            myLocalCallback.onExpiration = function(req) { xajax.abortRequest(req); }
            xajax.call(\'xajax_req\', {callback: myLocalCallback});</script>');
    }

    public static function getNextRunDisplay()
    {
        $nextrun = config::get('ajcron_nextrun');
        if ($nextrun == 0)
        {
            return 'never';
        }
        else
        {
            return date('Y-m-d H:i:s', $nextrun);
        }
    }

    public static function resetNextRunCheckbox()
    {
        if (config::get('ajcron_resetNextRun'))
        {
            config::set('ajcron_resetNextRun', 0);
            config::set('ajcron_nextrun', time());
        }
        if (config::get('ajcron_resetRunning'))
        {
            config::set('ajcron_resetRunning', 0);
            config::set('ajcron_running', array());
        }
    }

    public static function getNextRun($intervall)
    {
        $time = time();

        if (strstr($intervall, ':'))
        {
            $nextrun = strtotime($intervall);
            if ($nextrun < $time)
            {
                $nextrun = strtotime('tomorrow '.$intervall);
            }
        }
        elseif (strstr($intervall, '/'))
        {
            $int = substr($intervall, 1);

            // seconds till interval has passed (counts from 0 to int)
            $seconds_passed = $time % ($int*60);

            // reverse, how many seconds are left til 0
            $seconds_to_go = ($int*60) - $seconds_passed;

            // just add to current time and we have the next intervall
            $nextrun = $time + $seconds_to_go;
        }
        return $nextrun;
    }

    public static function parseJobs()
    {
        $jobs = array();
        $arr = explode("\n", config::get('ajcron_jobs'));
        $i = 0;
        foreach ($arr as $job)
        {
            if (!trim($job))
            {
                continue;
            }
            $tmp = preg_split("/".chr(32)."/", trim($job), -1, PREG_SPLIT_NO_EMPTY);
            $nextrun = ajcron::getNextRun($tmp[0]);
            $jobs[$i++] = array('id' => md5(trim($job)), 'nextrun' => $nextrun, 'name' => $tmp[2],  'url' => $tmp[1]);
        }

        return $jobs;
    }

    public static function getRuntable()
    {
        $output = '';
        // load up our crontasks
        $jobs = ajcron::parseJobs();
        $state = config::get('ajcron_running');

        foreach ($jobs as $job)
        {
            if (isset($state[$job['id']]))
            {
                $running = 'running';
            }
            else
            {
                $running = 'waiting';
            }

            if (empty($job['name']))
            {
                $name = '-None found-';
            }
            else
            {
                $name = $job['name'];
            }
            $output .= '<div class="mod-ajcron-job"><b>Name:</b> '.$name.' <b>URL:</b> '.$job['url'].' <b>Nextrun:</b> '.date('Y-m-d H:i:s', $job['nextrun']).'<br/>';
            $output .= '&nbsp;&nbsp;&nbsp;<b>State:</b> '.$running /*.' <b>ID: </b>'.$job['id']*/ .'</div>';
        }

        return $output;
    }

    public static function getNextRuntime()
    {
        // calculate and set new runtime
        $jobs = ajcron::parseJobs();
        $sorttable = array();
        // see which one should be started now
        foreach ($jobs as $job)
        {
            $sorttable[$job['id']] = $job['nextrun'];
        }
        asort($sorttable);
        config::set('ajcron_nextrun', $sorttable[key($sorttable)]);
    }

    public static function xajax_req()
    {
        // if the xajax call gets aborted, ignore that
        @ignore_user_abort(true);
        @set_time_limit(0);

        $state = config::get('ajcron_running');
        if (!is_array($state))
        {
            $state = array();
            config::set('ajcron_running', $state);
        }

        // check for blocking
        if (config::get('ajcron_blocking'))
        {
            // if there is already something running, give up
            if (count($state) >= 1)
            {
                return;
            }
        }

        // load up our crontasks
        $jobs = ajcron::parseJobs();

        // if there are no jobs just quit
        if (!count($jobs))
        {
            return;
        }

        // see which one should be started now
        $sorttable = array();
        foreach ($jobs as $job)
        {
            $sorttable[$job['id']] = $job['nextrun'];
        }
        asort($sorttable);

        foreach ($sorttable as $id => $nextrun)
        {
            // this bypasses already running jobs
            if (isset($state[$id]))
            {
                continue;
            }
            break;
        }

        if (!$id)
        {
            // no id found we could run as all are running
            return;
        }

        // set current id to running
        $state[$id] = 'running';
        $currentJob = null;
        config::set('ajcron_running', $state);
        foreach ($jobs as $job)
        {
            if ($job['id'] == $id)
            {
                $currentJob = $job;
            }
        }

        // run the job (finally)
        $http = new http_request($currentJob['url']);
        $http->set_timeout(120);
        $data = $http->get_content();

        // job done, clean up
        // we need to refresh our variable to prevent overwriting of
        // other running jobs
        $db = DBFactory::getDBQuery(true);
        $db->execute('select * from kb3_config where cfg_site=\''.KB_SITE.'\' and cfg_key=\'ajcron_running\'');
        $row = $db->getRow();
        $state = unserialize($row['cfg_value']);
        unset($state[$id]);
        config::set('ajcron_running', $state);

        // calculate when next to insert ajax
        ajcron::getNextRuntime();

        // testfun!
        $objResponse = new xajaxResponse();
        #$objResponse->Assign("header", "innerHTML", nl2br(var_export($sorttable[key($sorttable)], true)));
        #sleep(15);
        return $objResponse;
    }
	public static function helpFormat()
	{
		return "<div id='ajcron_help'>/65 ".KB_HOST."/cron/cron_fetcher.php [FeedSync]<br />
01:00 ".KB_HOST."/cron/cron_clearup.php [CleanUp]<br /></div>";
	}
}